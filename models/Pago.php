<?php

namespace Model;

class Pago extends ActiveRecord {
    protected static string $tabla = 'pagos';
    protected static array $columnasDB = ['id', 'membresia_id', 'monto', 'estado', 'metodo_pago', 'fecha_pago', 'mp_preference_id', 'mp_payment_id', 'mp_status'];

    public ?int $id;
    public mixed $membresia_id;
    public mixed $monto;
    public string $estado;
    public string $metodo_pago;
    public ?string $fecha_pago;
    public ?string $mp_preference_id;
    public ?string $mp_payment_id;
    public ?string $mp_status;

    public function __construct(array $args = []) {
        $this->id = $args['id'] ?? null;
        $this->membresia_id = $args['membresia_id'] ?? '';
        $this->monto = $args['monto'] ?? '';
        $this->estado = $args['estado'] ?? 'pendiente';
        $this->metodo_pago = $args['metodo_pago'] ?? 'mercadopago';
        $this->fecha_pago = $args['fecha_pago'] ?? date('Y-m-d H:i:s');
        $this->mp_preference_id = $args['mp_preference_id'] ?? null;
        $this->mp_payment_id = $args['mp_payment_id'] ?? null;
        $this->mp_status = $args['mp_status'] ?? null;
    }

    public function validar(): array {
        self::$alertas = [];

        if(!$this->membresia_id) {
            self::$alertas['error'][] = 'El ID de la membresía es obligatorio';
        }
        if(!$this->monto) {
            self::$alertas['error'][] = 'El monto del pago es obligatorio';
        }

        return self::$alertas;
    }

    /**
     * Resuelve la cláusula WHERE y título legible para los filtros de período estándar
     */
    public static function obtenerFiltroPeriodo(string $periodo): array {
        $mesesEspanol = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        $mesActualNum = (int)date('n');
        $anioActual = (int)date('Y');

        switch($periodo) {
            case 'mes_anterior':
                $where = "YEAR(pagos.fecha_pago) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH) AND MONTH(pagos.fecha_pago) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)";
                $mesAntNum = (int)date('n', strtotime('-1 month'));
                $anioAnt = (int)date('Y', strtotime('-1 month'));
                $titulo = "Mes Anterior ({$mesesEspanol[$mesAntNum]} {$anioAnt})";
                break;
            case 'ultimos_30':
                $where = "pagos.fecha_pago >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                $titulo = "Últimos 30 Días";
                break;
            case 'anio_actual':
                $where = "YEAR(pagos.fecha_pago) = {$anioActual}";
                $titulo = "Año {$anioActual}";
                break;
            case 'historico':
                $where = "1=1";
                $titulo = "Histórico Completo";
                break;
            case 'mes_actual':
            default:
                $periodo = 'mes_actual';
                $where = "YEAR(pagos.fecha_pago) = {$anioActual} AND MONTH(pagos.fecha_pago) = {$mesActualNum}";
                $titulo = "Mes Actual ({$mesesEspanol[$mesActualNum]} {$anioActual})";
                break;
        }

        return [
            'periodo' => $periodo,
            'where' => $where,
            'titulo' => $titulo
        ];
    }

    /**
     * Trae el listado completo de pagos en el período con datos del alumno y plan
     */
    public static function obtenerPagosPorPeriodo(string $wherePeriodo): array {
        $query = "
            SELECT 
                pagos.id,
                pagos.membresia_id,
                pagos.monto,
                pagos.estado,
                pagos.metodo_pago,
                pagos.fecha_pago,
                pagos.mp_payment_id,
                pagos.mp_status,
                usuarios.id AS usuario_id,
                CONCAT(usuarios.nombre, ' ', usuarios.apellido) AS cliente_nombre,
                usuarios.email AS cliente_email,
                usuarios.dni AS cliente_dni,
                usuarios.telefono AS cliente_telefono,
                planes.id AS plan_id,
                planes.nombre AS plan_nombre,
                planes.tipo_disciplina AS plan_disciplina,
                membresias.estado AS membresia_estado,
                membresias.fecha_inicio AS membresia_inicio,
                membresias.fecha_fin AS membresia_fin
            FROM pagos
            INNER JOIN membresias ON membresias.id = pagos.membresia_id
            INNER JOIN usuarios ON usuarios.id = membresias.usuario_id
            INNER JOIN planes ON planes.id = membresias.plan_id
            WHERE {$wherePeriodo}
            ORDER BY pagos.fecha_pago DESC, pagos.id DESC
        ";

        return self::queryArray($query);
    }

    /**
     * Calcula métricas agregadas de facturación para el período seleccionado
     */
    public static function obtenerKpisPorPeriodo(string $wherePeriodo): array {
        $query = "
            SELECT 
                COUNT(pagos.id) AS total_transacciones,
                SUM(CASE WHEN pagos.estado = 'aprobado' THEN pagos.monto ELSE 0 END) AS facturacion_total,
                SUM(CASE WHEN pagos.estado = 'aprobado' THEN 1 ELSE 0 END) AS cant_aprobados,
                SUM(CASE WHEN pagos.estado = 'pendiente' THEN 1 ELSE 0 END) AS cant_pendientes,
                SUM(CASE WHEN pagos.estado = 'rechazado' THEN 1 ELSE 0 END) AS cant_rechazados,
                
                -- Desglose por método de pago (solo aprobados)
                SUM(CASE WHEN pagos.estado = 'aprobado' AND pagos.metodo_pago = 'mercadopago' THEN pagos.monto ELSE 0 END) AS monto_mercadopago,
                SUM(CASE WHEN pagos.estado = 'aprobado' AND pagos.metodo_pago = 'mercadopago' THEN 1 ELSE 0 END) AS cant_mercadopago,
                SUM(CASE WHEN pagos.estado = 'aprobado' AND pagos.metodo_pago = 'efectivo' THEN pagos.monto ELSE 0 END) AS monto_efectivo,
                SUM(CASE WHEN pagos.estado = 'aprobado' AND pagos.metodo_pago = 'efectivo' THEN 1 ELSE 0 END) AS cant_efectivo
            FROM pagos
            INNER JOIN membresias ON membresias.id = pagos.membresia_id
            WHERE {$wherePeriodo}
        ";

        $filas = self::queryArray($query);
        $kpi = $filas[0] ?? [];

        $facturacionTotal = (float)($kpi['facturacion_total'] ?? 0);
        $cantAprobados = (int)($kpi['cant_aprobados'] ?? 0);
        $ticketPromedio = ($cantAprobados > 0) ? round($facturacionTotal / $cantAprobados, 2) : 0;

        return [
            'totalTransacciones' => (int)($kpi['total_transacciones'] ?? 0),
            'facturacionTotal' => $facturacionTotal,
            'cantAprobados' => $cantAprobados,
            'cantPendientes' => (int)($kpi['cant_pendientes'] ?? 0),
            'cantRechazados' => (int)($kpi['cant_rechazados'] ?? 0),
            'ticketPromedio' => $ticketPromedio,
            'montoMercadoPago' => (float)($kpi['monto_mercadopago'] ?? 0),
            'cantMercadoPago' => (int)($kpi['cant_mercadopago'] ?? 0),
            'montoEfectivo' => (float)($kpi['monto_efectivo'] ?? 0),
            'cantEfectivo' => (int)($kpi['cant_efectivo'] ?? 0)
        ];
    }

    /**
     * Trae desglose de recaudación agrupado por plan / disciplina
     */
    public static function obtenerRecaudacionPorPlan(string $wherePeriodo): array {
        $query = "
            SELECT 
                planes.id AS plan_id,
                planes.nombre AS plan_nombre,
                planes.tipo_disciplina,
                planes.precio,
                COUNT(pagos.id) AS total_pagos,
                SUM(CASE WHEN pagos.estado = 'aprobado' THEN pagos.monto ELSE 0 END) AS total_recaudado,
                SUM(CASE WHEN pagos.estado = 'aprobado' THEN 1 ELSE 0 END) AS pagos_aprobados
            FROM planes
            INNER JOIN membresias ON membresias.plan_id = planes.id
            INNER JOIN pagos ON pagos.membresia_id = membresias.id
            WHERE {$wherePeriodo}
            GROUP BY planes.id, planes.nombre, planes.tipo_disciplina, planes.precio
            ORDER BY total_recaudado DESC, planes.nombre ASC
        ";

        return self::queryArray($query);
    }

    /**
     * Trae las membresías activas que vencen en los próximos N días
     */
    public static function obtenerProximosVencimientos(int $dias = 7): array {
        $dias = max(1, $dias);
        $query = "
            SELECT 
                membresias.id AS membresia_id,
                membresias.fecha_inicio,
                membresias.fecha_fin,
                DATEDIFF(membresias.fecha_fin, CURDATE()) AS dias_restantes,
                usuarios.id AS usuario_id,
                CONCAT(usuarios.nombre, ' ', usuarios.apellido) AS cliente_nombre,
                usuarios.email AS cliente_email,
                usuarios.dni AS cliente_dni,
                usuarios.telefono AS cliente_telefono,
                planes.id AS plan_id,
                planes.nombre AS plan_nombre,
                planes.precio AS plan_precio,
                planes.tipo_disciplina
            FROM membresias
            INNER JOIN usuarios ON usuarios.id = membresias.usuario_id
            INNER JOIN planes ON planes.id = membresias.plan_id
            WHERE membresias.estado = 'activa'
              AND membresias.fecha_fin >= CURDATE()
              AND membresias.fecha_fin <= DATE_ADD(CURDATE(), INTERVAL {$dias} DAY)
            ORDER BY membresias.fecha_fin ASC, usuarios.nombre ASC
        ";

        return self::queryArray($query);
    }
}
