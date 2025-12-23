<?php
require('../librerias/fpdf.php');
include("db.php");

if (!isset($_GET['id_detalle'])) {
    die("ID de detalle no especificado.");
}

$id_detalle = intval($_GET['id_detalle']);

// ===== ASIGNACIONES =====
$sql_asig = "
SELECT ta.nombre, ta.tipo, da.monto 
FROM detalle_asignacion da
JOIN tipo_asignacion ta ON da.id_asignacion = ta.id_asignacion
WHERE da.id_detalle = $id_detalle
";
$asignaciones = mysqli_query($conexion, $sql_asig);

// ===== DEDUCCIONES =====
$sql_ded = "
SELECT td.nombre, td.porcentaje, dd.monto 
FROM detalle_deduccion dd
JOIN tipo_deduccion td ON dd.id_tipo = td.id_tipo
WHERE dd.id_detalle = $id_detalle
";

// 👉 IMPORTANTE: usar otro resultset
$deducciones = mysqli_query($conexion, $sql_ded);

// ===== PDF =====
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',14);

// ===== TÍTULO =====
$pdf->Cell(0,10,utf8_decode('Detalle Individual de Nómina'),0,1,'C');
$pdf->Ln(5);

// ================= ASIGNACIONES =================
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,8,utf8_decode('Asignaciones Aplicadas'),0,1,'L');
$pdf->Ln(3);

// Cabecera tabla
$pdf->SetFont('Arial','B',10);
$pdf->Cell(70,8,'Nombre',1,0,'C');
$pdf->Cell(40,8,'Tipo',1,0,'C');
$pdf->Cell(40,8,'Monto',1,1,'C');

// Cuerpo
$pdf->SetFont('Arial','',10);

while ($a = mysqli_fetch_assoc($asignaciones)) {
    $pdf->Cell(70,8,utf8_decode($a['nombre']),1,0,'L');
    $pdf->Cell(40,8,utf8_decode($a['tipo']),1,0,'C');
    $pdf->Cell(40,8,number_format($a['monto'],2),1,1,'R');
}

// Espacio
$pdf->Ln(10);

// ================= DEDUCCIONES =================
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,8,utf8_decode('Deducciones Aplicadas'),0,1,'L');
$pdf->Ln(3);

// Cabecera tabla
$pdf->SetFont('Arial','B',10);
$pdf->Cell(70,8,'Nombre',1,0,'C');
$pdf->Cell(40,8,'%',1,0,'C');
$pdf->Cell(40,8,'Monto',1,1,'C');

// Cuerpo
$pdf->SetFont('Arial','',10);

while ($d = mysqli_fetch_assoc($deducciones)) {
    $pdf->Cell(70,8,utf8_decode($d['nombre']),1,0,'L');
    $pdf->Cell(40,8,$d['porcentaje'].'%',1,0,'C');
    $pdf->Cell(40,8,number_format($d['monto'],2),1,1,'R');
}

$pdf->Output();
