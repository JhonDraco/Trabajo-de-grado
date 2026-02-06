<?php
require('../librerias/fpdf.php');
require('consultas_nomina.php');

$id_detalle = $_GET['id'];

$nomina = obtenerNomina($id_detalle);
$asignaciones = obtenerAsignaciones($id_detalle);
$deducciones = obtenerDeducciones($id_detalle);

/* =========================
   PDF
=========================*/
class PDF extends FPDF
{
    function Header()
    {
        $this->SetFont('Arial','B',14);
        $this->Cell(0,10,'RECIBO DE PAGO DE NOMINA',0,1,'C');
        $this->Ln(5);
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial','',10);

/* =========================
   EMPRESA (FIJA)
=========================*/
$pdf->Cell(100,6,"Empresa: BAZAR ANORIKEV C.A",0,0);
$pdf->Cell(0,6,"RIF: J-31321135-0",0,1);

/* =========================
   EMPLEADO
=========================*/
$pdf->Cell(100,6,"Trabajador: ".$nomina['empleado'],0,0);
$pdf->Cell(0,6,"Cedula: ".$nomina['cedula'],0,1);

$pdf->Cell(100,6,"Ingreso: ".$nomina['fecha_ingreso'],0,0);
$pdf->Cell(0,6,"Tipo Nomina: ".$nomina['tipo'],0,1);

$pdf->Cell(
    0,
    6,
    "Periodo: ".$nomina['fecha_inicio']." al ".$nomina['fecha_fin'],
0,1);

$pdf->Ln(5);

/* =========================
   TABLA
=========================*/
$pdf->SetFont('Arial','B',9);

$pdf->Cell(120,6,'Concepto',1);
$pdf->Cell(35,6,'Asignaciones',1);
$pdf->Cell(35,6,'Deducciones',1);
$pdf->Ln();

$pdf->SetFont('Arial','',9);

/* ========= ASIGNACIONES =========*/
$total_asig = 0;

while($row = $asignaciones->fetch_assoc())
{
    $pdf->Cell(120,6,$row['concepto'],1);
    $pdf->Cell(35,6,number_format($row['monto'],2),1);
    $pdf->Cell(35,6,'0.00',1);
    $pdf->Ln();

    $total_asig += $row['monto'];
}

/* ========= DEDUCCIONES =========*/
$total_deduc = 0;

while($row = $deducciones->fetch_assoc())
{
    $pdf->Cell(120,6,$row['concepto'],1);
    $pdf->Cell(35,6,'0.00',1);
    $pdf->Cell(35,6,number_format($row['monto'],2),1);
    $pdf->Ln();

    $total_deduc += $row['monto'];
}

/* =========================
   TOTALES
=========================*/
$pdf->SetFont('Arial','B',10);

$pdf->Cell(120,6,'Totales',1);
$pdf->Cell(35,6,number_format($total_asig,2),1);
$pdf->Cell(35,6,number_format($total_deduc,2),1);
$pdf->Ln();

$neto = $total_asig + $nomina['salario_nomina'] - $total_deduc;

$pdf->Cell(120,6,'Neto a pagar',1);
$pdf->Cell(70,6,number_format($neto,2),1);

/* =========================
   FIRMAS
=========================*/
$pdf->Ln(15);

$pdf->Cell(60,6,'Firma: ____________',0,0);
$pdf->Cell(60,6,'Huella: ____________',0,0);
$pdf->Cell(60,6,'Sello: ____________',0,1);

/* =========================
   OUTPUT
=========================*/
$pdf->Output("I","recibo_nomina.pdf");
