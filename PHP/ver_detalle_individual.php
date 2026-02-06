<?php
require('../librerias/fpdf.php');
include("db.php");

// Ajustar zona horaria de Venezuela (UTC-4)
date_default_timezone_set('America/Caracas');
$fecha_actual = date('d/m/Y H:i:s');

// Recibir ID del detalle individual
$id_detalle = intval($_GET['id_detalle'] ?? 0);

if ($id_detalle <= 0) {
    die("ID de detalle no recibido");
}

// Consulta del detalle individual de la nómina
$sql = "
SELECT 
    dn.salario_base,
    dn.total_asignaciones,
    dn.total_deducciones,
    dn.total_pagar,
    e.nombre,
    e.apellido,
    e.cedula
FROM detalle_nomina dn
INNER JOIN empleados e ON dn.empleado_id = e.id
WHERE dn.id_detalle = $id_detalle
";

$resultado = mysqli_query($conexion, $sql);

if (!$resultado || mysqli_num_rows($resultado) == 0) {
    die("No hay datos para este empleado");
}

$d = mysqli_fetch_assoc($resultado);

// =======================
// CREAR PDF
// =======================

$pdf = new FPDF('P', 'mm', 'A4');
$pdf->AddPage();

// ----------------------
// CONFIGURACIÓN DE COLORES
// ----------------------
$pdf->SetTextColor(0, 0, 0);
$pdf->SetDrawColor(31, 58, 52);
$pdf->SetFillColor(31, 58, 52);

// ----------------------
// TÍTULO
// ----------------------
$pdf->SetFont('Arial','B',20);
$pdf->Cell(0,12,utf8_decode('RECIBO DE PAGO'),0,1,'C');
$pdf->Ln(2);

$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,utf8_decode('Detalle Individual de Nómina'),0,1,'C');
$pdf->Ln(5);

// ----------------------
// FECHA
// ----------------------
$pdf->SetFont('Arial','I',10);
$pdf->Cell(0,6,'Fecha: '.$fecha_actual,0,1,'R');
$pdf->Ln(3);

// ----------------------
// DATOS DEL EMPLEADO
// ----------------------
$pdf->SetFont('Arial','B',12);
$pdf->Cell(40,8,utf8_decode('Cédula:'),0,0);
$pdf->SetFont('Arial','',12);
$pdf->Cell(0,8,$d['cedula'],0,1);

$pdf->SetFont('Arial','B',12);
$pdf->Cell(40,8,utf8_decode('Empleado:'),0,0);
$pdf->SetFont('Arial','',12);
$pdf->Cell(0,8,utf8_decode($d['nombre'].' '.$d['apellido']),0,1);

$pdf->Ln(5);

// ----------------------
// TABLA DE CONCEPTOS
// ----------------------
$pdf->SetFont('Arial','B',12);
$pdf->SetFillColor(31, 58, 52);
$pdf->SetTextColor(255,255,255);

$pdf->Cell(90,10,utf8_decode('Concepto'),1,0,'C', true);
$pdf->Cell(90,10,utf8_decode('Monto'),1,1,'C', true);

$pdf->SetFont('Arial','',12);
$pdf->SetTextColor(0,0,0);

$pdf->Cell(90,10,utf8_decode('Salario Base'),1);
$pdf->Cell(90,10,'$ '.number_format($d['salario_base'],2),1,1,'R');

$pdf->Cell(90,10,utf8_decode('Asignaciones'),1);
$pdf->Cell(90,10,'$ '.number_format($d['total_asignaciones'],2),1,1,'R');

$pdf->Cell(90,10,utf8_decode('Deducciones'),1);
$pdf->Cell(90,10,'$ '.number_format($d['total_deducciones'],2),1,1,'R');

$pdf->SetFont('Arial','B',12);
$pdf->SetFillColor(31, 58, 52);
$pdf->SetTextColor(255,255,255);

$pdf->Cell(90,10,utf8_decode('TOTAL A PAGAR'),1,0,'C', true);
$pdf->Cell(90,10,'$ '.number_format($d['total_pagar'],2),1,1,'R', true);

$pdf->Ln(15);

// ----------------------
// FIRMA Y HUELLA
// ----------------------
$pdf->SetFont('Arial','B',12);
$pdf->SetTextColor(0,0,0);
$pdf->SetFillColor(255,255,255);

$ancho_celda = 80;
$alto_celda = 25;
$espacio = 30;

$pdf->Cell($ancho_celda, $alto_celda, utf8_decode('Firma'), 1, 0, 'C', true);
$pdf->Cell($espacio, $alto_celda, '', 0, 0);
$pdf->Cell($ancho_celda, $alto_celda, utf8_decode('Huella Dactilar'), 1, 1, 'C', true);

$pdf->Ln(5);
$pdf->SetFont('Arial','I',10);
$pdf->Cell(0,5,utf8_decode('Este documento es un comprobante oficial de pago.'),0,1,'C');

$pdf->Output();
