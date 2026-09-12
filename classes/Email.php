<?php

namespace Classes;

use PHPMailer\PHPMailer\PHPMailer;

class Email {

    public string $email;
    public string $nombre;
    public string $token;
    public string $error = '';

    public function __construct(string $email, string $nombre, string $token = '') {
        $this->email = $email;
        $this->nombre = $nombre;
        $this->token = $token;
    }

    private function configurar(PHPMailer $mail): void {
        $config = obtenerConfiguracion();
        $remitenteEmail = (defined('MAIL_FROM') && !empty(MAIL_FROM)) ? MAIL_FROM : (!empty($config->email) ? $config->email : MAIL_USER);
        $remitenteNombre = !empty($config->nombre) ? $config->nombre : 'Gym';

        $mail->isSMTP();
        $mail->Host = MAIL_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = MAIL_USER;
        $mail->Password = MAIL_PASS;
        $mail->SMTPSecure = defined('MAIL_ENCRYPTION') ? MAIL_ENCRYPTION : ((MAIL_PORT === 465) ? 'ssl' : 'tls');
        $mail->Port = MAIL_PORT;
        $mail->setFrom($remitenteEmail, $remitenteNombre);
        $mail->addAddress($this->email, $this->nombre);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
    }

    public function enviarConfirmacion() {
        $config = obtenerConfiguracion();
        $gymNombre = !empty($config->nombre) ? $config->nombre : 'el gimnasio';

        $mail = new PHPMailer();
        $this->configurar($mail);
        $mail->Subject = 'Confirma tu Cuenta - ' . $gymNombre;

        $contenido = '<html>';
        $contenido .= '<p><strong>Hola ' . $this->nombre . '</strong></p>';
        $contenido .= '<p>Has creado una cuenta en ' . $gymNombre . '. Confirmala con el siguiente enlace:</p>';
        $contenido .= '<p>Presiona acá: <a href="' . MAIL_URL . '/confirmar-cuenta?token=' . $this->token . '">Confirmar Cuenta</a></p>';
        $contenido .= '<p>Si no creaste esta cuenta, podés ignorar este mensaje.</p>';
        $contenido .= '</html>';

        $mail->Body = $contenido;
        return (bool) $mail->send();
    }

    public function enviarInstrucciones() {
        $config = obtenerConfiguracion();
        $gymNombre = !empty($config->nombre) ? $config->nombre : 'el gimnasio';

        $mail = new PHPMailer();
        $this->configurar($mail);
        $mail->Subject = 'Reestablece tu Password - ' . $gymNombre;

        $contenido = '<html>';
        $contenido .= '<p><strong>Hola ' . $this->nombre . '</strong></p>';
        $contenido .= '<p>Solicitaste reestablecer tu password en ' . $gymNombre . '. Seguí este enlace:</p>';
        $contenido .= '<p>Presiona acá: <a href="' . MAIL_URL . '/recuperar?token=' . $this->token . '">Reestablecer Password</a></p>';
        $contenido .= '<p>Si no lo pediste, ignorá este mensaje.</p>';
        $contenido .= '</html>';

        $mail->Body = $contenido;
        return (bool) $mail->send();
    }

    /**
     * Enviar recordatorio preventivo 7 días antes del vencimiento
     */
    public function enviarAvisoVencimientoProximo(string $planNombre, string $fechaFin): bool {
        $config = obtenerConfiguracion();
        $gymNombre = !empty($config->nombre) ? $config->nombre : 'el gimnasio';

        $mail = new PHPMailer();
        $this->configurar($mail);
        $mail->Subject = 'Tu membresía de ' . $planNombre . ' vence en 7 días - ' . $gymNombre;

        $fechaFormateada = date('d/m/Y', strtotime($fechaFin));

        $contenido = '<html>';
        $contenido .= '<div style="font-family: Arial, sans-serif; color: #1e293b; line-height: 1.6; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden;">';
        $contenido .= '<div style="background-color: #0f172a; padding: 25px; text-align: center; color: white;">';
        $contenido .= '<h2 style="margin: 0; font-size: 22px;">' . htmlspecialchars($gymNombre) . '</h2>';
        $contenido .= '<p style="margin: 5px 0 0 0; color: #94a3b8; font-size: 14px;">Aviso de Vencimiento de Membresía</p>';
        $contenido .= '</div>';
        $contenido .= '<div style="padding: 25px;">';
        $contenido .= '<p style="font-size: 16px;"><strong>¡Hola ' . htmlspecialchars($this->nombre) . '!</strong></p>';
        $contenido .= '<p>Queremos recordarte que tu membresía para el plan <strong>' . htmlspecialchars($planNombre) . '</strong> está próxima a vencer.</p>';
        $contenido .= '<div style="background-color: #f8fafc; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0; border-radius: 4px;">';
        $contenido .= '<p style="margin: 0; font-size: 15px;"><strong>Fecha de vencimiento:</strong> ' . $fechaFormateada . ' (en 7 días)</p>';
        $contenido .= '</div>';
        $contenido .= '<p>Para continuar entrenando sin interrupciones y mantener tus turnos y rutinas al día, podés renovar tu membresía directamente desde tu panel de alumno:</p>';
        $contenido .= '<div style="text-align: center; margin: 30px 0;">';
        $contenido .= '<a href="' . MAIL_URL . '/cliente/planes" style="background-color: #149b2b; color: white; padding: 14px 28px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block; font-size: 15px;">Renovar mi Membresía</a>';
        $contenido .= '</div>';
        $contenido .= '<p style="font-size: 13px; color: #64748b;">Si ya realizaste la renovación o tenés alguna duda, comunicate con la administración del gimnasio.</p>';
        $contenido .= '</div>';
        $contenido .= '<div style="background-color: #f1f5f9; padding: 15px; text-align: center; font-size: 12px; color: #94a3b8;">';
        $contenido .= '<p style="margin: 0;">Mensaje automático enviado por ' . htmlspecialchars($gymNombre) . '.</p>';
        $contenido .= '</div>';
        $contenido .= '</div>';
        $contenido .= '</html>';

        $mail->Body = $contenido;
        if (!$mail->send()) {
            $this->error = $mail->ErrorInfo;
            return false;
        }
        return true;
    }

    /**
     * Enviar notificación de vencimiento en el día de hoy
     */
    public function enviarAvisoVencimientoHoy(string $planNombre): bool {
        $config = obtenerConfiguracion();
        $gymNombre = !empty($config->nombre) ? $config->nombre : 'el gimnasio';

        $mail = new PHPMailer();
        $this->configurar($mail);
        $mail->Subject = '¡Atención! Tu membresía de ' . $planNombre . ' vence hoy - ' . $gymNombre;

        $contenido = '<html>';
        $contenido .= '<div style="font-family: Arial, sans-serif; color: #1e293b; line-height: 1.6; max-width: 600px; margin: 0 auto; border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden;">';
        $contenido .= '<div style="background-color: #dc2626; padding: 25px; text-align: center; color: white;">';
        $contenido .= '<h2 style="margin: 0; font-size: 22px;">' . htmlspecialchars($gymNombre) . '</h2>';
        $contenido .= '<p style="margin: 5px 0 0 0; color: #fecaca; font-size: 14px;">Tu membresía vence hoy</p>';
        $contenido .= '</div>';
        $contenido .= '<div style="padding: 25px;">';
        $contenido .= '<p style="font-size: 16px;"><strong>¡Hola ' . htmlspecialchars($this->nombre) . '!</strong></p>';
        $contenido .= '<p>Te informamos que hoy es el <strong>último día</strong> de vigencia de tu membresía para el plan <strong>' . htmlspecialchars($planNombre) . '</strong>.</p>';
        $contenido .= '<div style="background-color: #fef2f2; border-left: 4px solid #ef4444; padding: 15px; margin: 20px 0; border-radius: 4px;">';
        $contenido .= '<p style="margin: 0; font-size: 15px; color: #991b1b;">A partir de mañana tu membresía figurará como <strong>vencida</strong> y no podrás reservar nuevos turnos ni consultar tus rutinas hasta que renueves tu pase.</p>';
        $contenido .= '</div>';
        $contenido .= '<p>¡No pierdas tu ritmo de entrenamiento! Renová tu membresía ahora mismo de forma rápida y segura:</p>';
        $contenido .= '<div style="text-align: center; margin: 30px 0;">';
        $contenido .= '<a href="' . MAIL_URL . '/cliente/planes" style="background-color: #149b2b; color: white; padding: 14px 28px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block; font-size: 15px;">Renovar Membresía Online</a>';
        $contenido .= '</div>';
        $contenido .= '<p style="font-size: 13px; color: #64748b;">Si ya abonaste en recepción o por transferencia, aguardá la acreditación de la administración.</p>';
        $contenido .= '</div>';
        $contenido .= '<div style="background-color: #f1f5f9; padding: 15px; text-align: center; font-size: 12px; color: #94a3b8;">';
        $contenido .= '<p style="margin: 0;">Mensaje automático enviado por ' . htmlspecialchars($gymNombre) . '.</p>';
        $contenido .= '</div>';
        $contenido .= '</div>';
        $contenido .= '</html>';

        $mail->Body = $contenido;
        if (!$mail->send()) {
            $this->error = $mail->ErrorInfo;
            return false;
        }
        return true;
    }
}
