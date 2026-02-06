<?php
require 'db.php';

/* =========================
   CABECERA DEL RECIBO
=========================*/
function obtenerNomina($id_detalle)
{
    global $conexion;

    $sql = "SELECT 
                e.id,
                e.cedula,
                CONCAT(e.nombre,' ',e.apellido) AS empleado,
                e.fecha_ingreso,
                e.salario_base,

                n.fecha_inicio,
                n.fecha_fin,
                n.tipo,

                dn.salario_base AS salario_nomina,
                dn.total_asignaciones,
                dn.total_deducciones,
                dn.total_pagar

            FROM detalle_nomina dn

            INNER JOIN empleados e 
                ON e.id = dn.empleado_id

            INNER JOIN nomina n 
                ON n.id_nomina = dn.id_nomina

            WHERE dn.id_detalle = $id_detalle";

    return $conexion->query($sql)->fetch_assoc();
}


function obtenerAsignaciones($id_detalle)
{
    global $conexion;

    $sql = "SELECT 
                ta.nombre AS concepto,
                da.monto
            FROM detalle_asignacion da

            INNER JOIN tipo_asignacion ta
                ON ta.id_asignacion = da.id_asignacion

            WHERE da.id_detalle = $id_detalle";

    return $conexion->query($sql);
}
function obtenerDeducciones($id_detalle)
{
    global $conexion;

    $sql = "SELECT 
                td.nombre AS concepto,
                dd.monto
            FROM detalle_deduccion dd

            INNER JOIN tipo_deduccion td
                ON td.id_tipo = dd.id_tipo

            WHERE dd.id_detalle = $id_detalle";

    return $conexion->query($sql);
}
