<?php
/**
 * Script CLI / Cron Job: Automatización de Membresías y Recordatorios de Vencimiento
 *
 * Tareas:
 * 1. Marcar automáticamente como 'vencida' toda membresía activa cuya fecha_fin sea menor a hoy (CURDATE()).
 * 2. Enviar recordatorio preventivo por correo a alumnos cuya membresía vence en 7 días (tipo '7dias').
 * 3. Enviar aviso por correo a alumnos cuya membresía vence hoy (tipo 'hoy').
 *
 * Uso:
 *   php scripts/cron-membresias.php
 *   php scripts/cron-membresias.php --dry-run   (Simulación sin cambios ni envíos)
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die("Acceso denegado. Este script solo puede ejecutarse mediante la línea de comandos (CLI).\n");
}

require_once __DIR__ . '/../includes/app.php';

use Model\ActiveRecord;
use Model\Membresia;
use Model\AvisoEnviado;
use Classes\Email;

$inicioTiempo = microtime(true);
$fechaActual = date('Y-m-d');
$horaActual = date('H:i:s');
$isDryRun = in_array('--dry-run', $argv ?? []);

echo "====================================================================\n";
echo "   CRON DE MEMBRESÍAS - GYM MVC\n";
echo "   Fecha: {$fechaActual} {$horaActual} (Zona: " . date_default_timezone_get() . ")\n";
if ($isDryRun) {
    echo "   [MODO SIMULACIÓN ACTIVADO (--dry-run) - No se aplicarán cambios]\n";
}
echo "====================================================================\n\n";

$db = ActiveRecord::getDB();

// ====================================================================
// TAREA 1: Vencer membresías activas cuya fecha_fin ya pasó (< CURDATE())
// ====================================================================
echo ">> [1/3] Verificando membresías caducadas para pasar a estado 'vencida'...\n";

$queryCaducadas = "
    SELECT 
        membresias.id, 
        membresias.usuario_id, 
        membresias.fecha_fin, 
        planes.nombre AS plan_nombre,
        CONCAT(usuarios.nombre, ' ', usuarios.apellido) AS cliente_nombre
    FROM membresias
    INNER JOIN planes ON planes.id = membresias.plan_id
    INNER JOIN usuarios ON usuarios.id = membresias.usuario_id
    WHERE membresias.estado = 'activa' 
      AND membresias.fecha_fin < '{$fechaActual}'
    ORDER BY membresias.fecha_fin ASC
";

$resCaducadas = $db->query($queryCaducadas);
$totalVencidas = 0;

if ($resCaducadas && $resCaducadas->num_rows > 0) {
    while ($row = $resCaducadas->fetch_assoc()) {
        $id = (int)$row['id'];
        $cliente = $row['cliente_nombre'];
        $plan = $row['plan_nombre'];
        $vencimiento = $row['fecha_fin'];

        if (!$isDryRun) {
            $db->query("UPDATE membresias SET estado = 'vencida' WHERE id = {$id}");
        }

        echo "   -> Membresía #{$id} [{$plan}] de {$cliente} (Venció: {$vencimiento}) => Estado actualizado a 'vencida'.\n";
        $totalVencidas++;
    }
} else {
    echo "   -> No se encontraron membresías activas vencidas pendientes de actualización.\n";
}

echo "   Total membresías vencidas procesadas: {$totalVencidas}\n\n";

// ====================================================================
// TAREA 2: Enviar avisos preventivos a 7 días del vencimiento
// ====================================================================
echo ">> [2/3] Verificando avisos preventivos (vencimiento en 7 días)...\n";

$fecha7Dias = date('Y-m-d', strtotime($fechaActual . ' + 7 days'));

$query7Dias = "
    SELECT 
        membresias.id AS membresia_id,
        membresias.usuario_id,
        membresias.fecha_fin,
        usuarios.nombre AS usuario_nombre,
        usuarios.apellido AS usuario_apellido,
        usuarios.email AS usuario_email,
        planes.nombre AS plan_nombre
    FROM membresias
    INNER JOIN usuarios ON usuarios.id = membresias.usuario_id
    INNER JOIN planes ON planes.id = membresias.plan_id
    WHERE membresias.estado = 'activa'
      AND membresias.fecha_fin = '{$fecha7Dias}'
      AND membresias.id NOT IN (
          SELECT membresia_id FROM avisos_enviados WHERE tipo = '7dias'
      )
    ORDER BY membresias.id ASC
";

$res7Dias = $db->query($query7Dias);
$totalAvisos7Dias = 0;

if ($res7Dias && $res7Dias->num_rows > 0) {
    while ($row = $res7Dias->fetch_assoc()) {
        $membresiaId = (int)$row['membresia_id'];
        $emailCliente = $row['usuario_email'];
        $nombreCompleto = trim($row['usuario_nombre'] . ' ' . $row['usuario_apellido']);
        $planNombre = $row['plan_nombre'];
        $fechaFin = $row['fecha_fin'];

        echo "   -> Membresía #{$membresiaId} [{$planNombre}]: Alumno {$nombreCompleto} ({$emailCliente}) vence el {$fechaFin}...\n";

        if (!$isDryRun) {
            try {
                $email = new Email($emailCliente, $nombreCompleto);
                $enviado = $email->enviarAvisoVencimientoProximo($planNombre, $fechaFin);

                if ($enviado) {
                    AvisoEnviado::registrar($membresiaId, '7dias');
                    echo "      [OK] Correo de recordatorio (7 días) enviado y registrado exitosamente.\n";
                    $totalAvisos7Dias++;
                } else {
                    $motivo = !empty($email->error) ? ": " . $email->error : "";
                    echo "      [ERROR] Falló el envío del correo a {$emailCliente}{$motivo}.\n";
                }
                sleep(1); // 1s de pausa para respetar el límite de 1 email/segundo de Mailtrap/SMTP
            } catch (Throwable $e) {
                echo "      [EXCEPCIÓN] Error al procesar aviso: " . $e->getMessage() . "\n";
            }
        } else {
            echo "      [SIMULACIÓN] Se enviaría recordatorio a {$emailCliente}.\n";
            $totalAvisos7Dias++;
        }
    }
} else {
    echo "   -> No hay membresías que venzan en 7 días ({$fecha7Dias}) pendientes de notificación.\n";
}

echo "   Total avisos a 7 días procesados: {$totalAvisos7Dias}\n\n";

// Pausa preventiva entre fases para respetar límites de cuota SMTP por segundo
sleep(2);

// ====================================================================
// TAREA 3: Enviar avisos de vencimiento en el día de HOY
// ====================================================================
echo ">> [3/3] Verificando avisos de vencimiento para el día de HOY ({$fechaActual})...\n";

$queryHoy = "
    SELECT 
        membresias.id AS membresia_id,
        membresias.usuario_id,
        membresias.fecha_fin,
        usuarios.nombre AS usuario_nombre,
        usuarios.apellido AS usuario_apellido,
        usuarios.email AS usuario_email,
        planes.nombre AS plan_nombre
    FROM membresias
    INNER JOIN usuarios ON usuarios.id = membresias.usuario_id
    INNER JOIN planes ON planes.id = membresias.plan_id
    WHERE membresias.estado = 'activa'
      AND membresias.fecha_fin = '{$fechaActual}'
      AND membresias.id NOT IN (
          SELECT membresia_id FROM avisos_enviados WHERE tipo = 'hoy'
      )
    ORDER BY membresias.id ASC
";

$resHoy = $db->query($queryHoy);
$totalAvisosHoy = 0;

if ($resHoy && $resHoy->num_rows > 0) {
    while ($row = $resHoy->fetch_assoc()) {
        $membresiaId = (int)$row['membresia_id'];
        $emailCliente = $row['usuario_email'];
        $nombreCompleto = trim($row['usuario_nombre'] . ' ' . $row['usuario_apellido']);
        $planNombre = $row['plan_nombre'];

        echo "   -> Membresía #{$membresiaId} [{$planNombre}]: Alumno {$nombreCompleto} ({$emailCliente}) vence HOY...\n";

        if (!$isDryRun) {
            try {
                $email = new Email($emailCliente, $nombreCompleto);
                $enviado = $email->enviarAvisoVencimientoHoy($planNombre);

                if ($enviado) {
                    AvisoEnviado::registrar($membresiaId, 'hoy');
                    echo "      [OK] Correo de vencimiento de hoy enviado y registrado exitosamente.\n";
                    $totalAvisosHoy++;
                } else {
                    $motivo = !empty($email->error) ? ": " . $email->error : "";
                    echo "      [ERROR] Falló el envío del correo a {$emailCliente}{$motivo}.\n";
                }
                sleep(1); // 1s de pausa para respetar el límite de 1 email/segundo de Mailtrap/SMTP
            } catch (Throwable $e) {
                echo "      [EXCEPCIÓN] Error al procesar aviso: " . $e->getMessage() . "\n";
            }
        } else {
            echo "      [SIMULACIÓN] Se enviaría aviso de vencimiento de hoy a {$emailCliente}.\n";
            $totalAvisosHoy++;
        }
    }
} else {
    echo "   -> No hay membresías que venzan hoy ({$fechaActual}) pendientes de notificación.\n";
}

echo "   Total avisos de hoy procesados: {$totalAvisosHoy}\n\n";

// ====================================================================
// RESUMEN FINAL
// ====================================================================
$duracion = round(microtime(true) - $inicioTiempo, 3);

echo "====================================================================\n";
echo "   RESUMEN DE EJECUCIÓN\n";
echo "   - Membresías actualizadas a 'vencida': {$totalVencidas}\n";
echo "   - Recordatorios preventivos (7 días) enviados: {$totalAvisos7Dias}\n";
echo "   - Avisos de vencimiento (hoy) enviados: {$totalAvisosHoy}\n";
echo "   - Tiempo total de ejecución: {$duracion} segundos\n";
echo "====================================================================\n";
