<?php

namespace Controllers;

use MVC\Router;
use Model\Membresia;
use Model\Pago;
use Model\Plan;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Client\Payment\PaymentClient;

class PagoController {

    public static function crearPreferencia(Router $router): void {
        isCliente();

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $plan_id = filter_var($_POST['plan_id'] ?? null, FILTER_VALIDATE_INT);
            if(!$plan_id) {
                header('Location: /cliente/planes');
                exit;
            }

            $plan = Plan::find($plan_id);
            if(!$plan || (int)$plan->activo !== 1) {
                header('Location: /cliente/planes');
                exit;
            }

            $usuario_id = (int)$_SESSION['id'];

            // 1. Validar que no tenga ya una membresía activa para este plan
            $activa = Membresia::SQL("SELECT * FROM membresias WHERE usuario_id = {$usuario_id} AND plan_id = {$plan_id} AND estado = 'activa' LIMIT 1");
            if(!empty($activa)) {
                // Ya tiene una activa
                header("Location: /cliente/pago/respuesta?status=ya_activa");
                exit;
            }

            // 2. Crear membresía pendiente
            $membresia = new Membresia([
                'usuario_id' => $usuario_id,
                'plan_id' => $plan->id,
                'estado' => 'pendiente'
            ]);
            $resultadoMembresia = $membresia->guardar();
            $membresia_id = $resultadoMembresia['id'];

            // 3. Crear pago pendiente
            $pago = new Pago([
                'membresia_id' => $membresia_id,
                'monto' => $plan->precio,
                'estado' => 'pendiente',
                'metodo_pago' => 'mercadopago',
                'fecha_pago' => date('Y-m-d H:i:s')
            ]);
            $resultadoPago = $pago->guardar();
            $pago_id = $resultadoPago['id'];

            // 4. Configurar Mercado Pago
            // 4. Configurar Mercado Pago
            MercadoPagoConfig::setAccessToken($_ENV['MP_ACCESS_TOKEN'] ?? '');
            
            $app_url = $_ENV['APP_URL'] ?? 'http://localhost:3000';
            
            // Forzar HTTPS si es dominio remoto (Mercado Pago rechaza auto_return si la URL no es HTTPS)
            if(!str_contains($app_url, 'localhost') && str_starts_with($app_url, 'http://')) {
                $app_url = str_replace('http://', 'https://', $app_url);
            }

            // En desarrollo local en Windows (con localhost o ngrok) suele fallar el certificado SSL de cURL.
            if(str_contains($app_url, 'localhost') || str_contains($app_url, 'ngrok')) {
                MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);
            }

            $client = new PreferenceClient();

            try {
                $preference = $client->create([
                    "items" => [
                        [
                            "title" => "Plan " . $plan->nombre,
                            "quantity" => 1,
                            "unit_price" => (float)$plan->precio,
                            "currency_id" => "ARS"
                        ]
                    ],
                    "back_urls" => [
                        "success" => $app_url . "/cliente/pago/respuesta?status=success",
                        "failure" => $app_url . "/cliente/pago/respuesta?status=failure",
                        "pending" => $app_url . "/cliente/pago/respuesta?status=pending"
                    ],
                    "auto_return" => "approved",
                    "external_reference" => (string)$pago_id,
                    "notification_url" => $app_url . "/api/webhooks/mercadopago"
                ]);

                // Actualizamos el pago con el preference_id
                $pagoSincronizado = Pago::find($pago_id);
                $pagoSincronizado->mp_preference_id = $preference->id;
                $pagoSincronizado->guardar();

                // Redirigimos al usuario a Mercado Pago
                $esPrueba = str_starts_with($_ENV['MP_ACCESS_TOKEN'] ?? '', 'TEST-') || (!empty($_ENV['MP_SANDBOX']) && $_ENV['MP_SANDBOX'] === 'true');
                $redirectUrl = ($esPrueba && !empty($preference->sandbox_init_point))
                    ? $preference->sandbox_init_point
                    : $preference->init_point;

                header("Location: " . $redirectUrl);
                exit;
                
            } catch (\MercadoPago\Exceptions\MPApiException $e) {
                $response = $e->getApiResponse();
                $detalle = $response ? json_encode($response->getContent()) : $e->getMessage();
                error_log("Error de Mercado Pago API: " . $detalle);
                header("Location: /cliente/pago/respuesta?status=error_mp&detalle=" . urlencode((string)$detalle));
                exit;
            } catch (\Throwable $e) {
                error_log("Error al crear preferencia MP: " . $e->getMessage());
                header("Location: /cliente/pago/respuesta?status=error_mp&detalle=" . urlencode($e->getMessage()));
                exit;
            }
        }
    }

    public static function respuesta(Router $router): void {
        isCliente();

        $payment_id = filter_var($_GET['payment_id'] ?? $_GET['collection_id'] ?? null, FILTER_VALIDATE_INT);
        $status = filter_var($_GET['status'] ?? $_GET['collection_status'] ?? 'unknown', FILTER_SANITIZE_SPECIAL_CHARS);

        // Si Mercado Pago nos redirige con el ID del pago aprobado, sincronizamos de inmediato
        if($payment_id) {
            self::sincronizarPago((int)$payment_id);
        }

        $router->render('cliente/pago-respuesta', [
            'nombre' => $_SESSION['nombre'] ?? '',
            'status' => $status,
            'tipo' => 'dashboard'
        ]);
    }

    public static function webhook(): void {
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            $payload = json_decode(file_get_contents('php://input'), true) ?? [];

            // Detectar tipo de evento y payment_id (soportando tanto JSON V2 como IPN / Query String)
            $type = $payload['type'] ?? $_GET['type'] ?? $_GET['topic'] ?? null;
            $payment_id = $payload['data']['id'] ?? $_GET['data_id'] ?? $_GET['id'] ?? null;

            if(($type === 'payment' || is_null($type)) && $payment_id) {
                self::sincronizarPago((int)$payment_id);
            }
            
            http_response_code(200);
            exit;
        }
    }

    /**
     * Consulta a Mercado Pago el estado real del pago y activa la membresía de forma segura e idempotente
     */
    public static function sincronizarPago(int $payment_id): bool {
        MercadoPagoConfig::setAccessToken($_ENV['MP_ACCESS_TOKEN'] ?? '');
        
        $app_url = $_ENV['APP_URL'] ?? 'http://localhost:3000';
        if(str_contains($app_url, 'localhost') || str_contains($app_url, 'ngrok')) {
            MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);
        }

        $client = new PaymentClient();
        
        try {
            $paymentInfo = $client->get((int)$payment_id);
            
            $external_reference = $paymentInfo->external_reference;
            $status = $paymentInfo->status; // approved, rejected, pending, etc.

            if($external_reference) {
                $pago = Pago::find($external_reference);
                
                if($pago) {
                    // Idempotencia: solo procesamos si el estado cambió o no tiene mp_payment_id
                    if($pago->mp_status !== $status || $pago->mp_payment_id !== (string)$payment_id) {
                        $pago->mp_status = $status;
                        $pago->mp_payment_id = (string)$payment_id;
                        
                        if($status === 'approved') {
                            $pago->estado = 'aprobado';
                            
                            // Activar membresía
                            $membresia = Membresia::find($pago->membresia_id);
                            if($membresia && $membresia->estado !== 'activa') {
                                $membresia->estado = 'activa';
                                $membresia->fecha_inicio = date('Y-m-d');
                                
                                // Calcular fecha de fin
                                $plan = Plan::find($membresia->plan_id);
                                if($plan) {
                                    $dias = (int)$plan->duracion_dias;
                                    $membresia->fecha_fin = date('Y-m-d', strtotime("+{$dias} days"));
                                }
                                $membresia->guardar();
                            }
                        } elseif ($status === 'rejected' || $status === 'cancelled' || $status === 'refunded') {
                            $pago->estado = 'rechazado';
                            
                            $membresia = Membresia::find($pago->membresia_id);
                            if($membresia && $membresia->estado === 'pendiente') {
                                $membresia->estado = 'cancelada';
                                $membresia->guardar();
                            }
                        }
                        
                        $pago->guardar();
                        return true;
                    }
                }
            }
        } catch (\Exception $e) {
            error_log("Error al sincronizar pago con Mercado Pago ID {$payment_id}: " . $e->getMessage());
            return false;
        }

        return false;
    }
}
