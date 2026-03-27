<?php
require('../librerias/fpdf.php');
require('consultas_nomina.php');

$id_detalle = $_GET['id_detalle'];

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
        // LOGO
        $this->Image('../img/logo.png', 30, 15, 30);

        // Empresa
        $this->SetFont('Arial','B',14);
        $this->SetTextColor(31,58,52);
        $this->Cell(0,10,'KAO SHOP',0,1,'R');

        $this->Ln(10);

        // TÍTULO
        $this->SetFont('Arial','B',13);
        $this->SetTextColor(31,58,52);
        $this->Cell(0,10,'RECIBO DE PAGO DE NOMINA',0,1,'C');

        $this->Ln(5);
    }
}

$pdf = new PDF();
$pdf->AddPage();

// Márgenes
$pdf->SetLeftMargin(25);
$pdf->SetRightMargin(25);

$pdf->SetFont('Arial','',10);
$pdf->SetTextColor(0,0,0);

/* =========================
   EMPRESA / EMPLEADO
=========================*/
$pdf->Cell(100,6,"Empresa: KAO SHOP",0,0);
$pdf->Cell(0,6,"RIF: J-31321135-0",0,1);

$pdf->Cell(100,6,"Trabajador: ".$nomina['empleado'],0,0);
$pdf->Cell(0,6,"Cedula: ".$nomina['cedula'],0,1);

$pdf->Cell(100,6,"Ingreso: ".$nomina['fecha_ingreso'],0,0);
$pdf->Cell(0,6,"Tipo Nomina: ".$nomina['tipo'],0,1);

$pdf->Cell(
    0,
    6,
    "Periodo: ".$nomina['fecha_inicio']." al ".$nomina['fecha_fin'],
0,1);

$pdf->Ln(8);

/* =========================
   TABLA
=========================*/
$pdf->SetFont('Arial','B',10);
$pdf->SetFillColor(31,58,52);
$pdf->SetTextColor(255,255,255);

$pdf->Cell(100,7,'Concepto',1,0,'C',true);
$pdf->Cell(40,7,'Asignaciones',1,0,'C',true);
$pdf->Cell(40,7,'Deducciones',1,1,'C',true);

$pdf->SetFont('Arial','',9);
$pdf->SetTextColor(0,0,0);

/* ========= ASIGNACIONES =========*/
$total_asig = 0;

while($row = $asignaciones->fetch_assoc())
{
    $pdf->Cell(100,6,$row['concepto'],1);
    $pdf->Cell(40,6,number_format($row['monto'],2),1,0,'R');
    $pdf->Cell(40,6,'0.00',1,1,'R');

    $total_asig += $row['monto'];
}

/* ========= DEDUCCIONES =========*/
$total_deduc = 0;

while($row = $deducciones->fetch_assoc())
{
    $pdf->Cell(100,6,$row['concepto'],1);
    $pdf->Cell(40,6,'0.00',1,0,'R');
    $pdf->Cell(40,6,number_format($row['monto'],2),1,1,'R');

    $total_deduc += $row['monto'];
}

/* =========================
   TOTALES
=========================*/
$pdf->SetFont('Arial','B',10);

$pdf->Cell(100,7,'Totales',1);
$pdf->Cell(40,7,number_format($total_asig,2),1,0,'R');
$pdf->Cell(40,7,number_format($total_deduc,2),1,1,'R');

$neto = $total_asig + $nomina['salario_nomina'] - $total_deduc;

$pdf->SetTextColor(31,58,52);

$pdf->Cell(100,7,'Neto a pagar',1);
$pdf->Cell(80,7,number_format($neto,2),1,1,'R');

$pdf->SetTextColor(0,0,0);

/* =========================
   FIRMAS
=========================*/
$pdf->Ln(20);

// Líneas de firma más profesionales
$pdf->Line(25, 250, 80, 250);
$pdf->Line(90, 250, 145, 250);
$pdf->Line(155, 250, 210, 250);

$pdf->SetY(252);

$pdf->Cell(65,6,'Firma',0,0,'C');
$pdf->Cell(65,6,'Huella',0,0,'C');
$pdf->Cell(60,6,'Sello',0,1,'C');

/* =========================
   OUTPUT
=========================*/
$pdf->Output("I","recibo_nomina.pdf");
?>