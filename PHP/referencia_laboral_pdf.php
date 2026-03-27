<?php
require('../librerias/fpdf.php');

date_default_timezone_set('America/Caracas');
setlocale(LC_TIME, 'es_ES.UTF-8', 'spanish');

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

        $this->Ln(15);
    }
}

$pdf = new PDF();
$pdf->AddPage();

// Márgenes
$pdf->SetLeftMargin(25);
$pdf->SetRightMargin(25);

// Datos
$nombre   = $_POST['name'] ?? '';
$apellido = $_POST['apellido'] ?? '';
$cedula   = $_POST['cedula'] ?? '';

// Fecha
$fecha = strftime('%d de %B de %Y');
$hora  = date('H:i');

// ===== FECHA =====
$pdf->SetFont('Arial','',11);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,8,utf8_decode("Caracas, $fecha - $hora"),0,1,'R');

$pdf->Ln(10);

// ===== TÍTULO =====
$pdf->SetFont('Arial','B',13);
$pdf->SetTextColor(31,58,52);
$pdf->Cell(0,10,'CARTA DE REFERENCIA',0,1,'C');

$pdf->Ln(10);

// ===== DESTINATARIO =====
$pdf->SetTextColor(0,0,0);
$pdf->SetFont('Arial','',11);
$pdf->Cell(0,8,'A quien pueda interesar:',0,1,'L');

$pdf->Ln(10);

// ===== CUERPO =====
$pdf->SetFont('Arial','',11);

$texto1 = utf8_decode(
"Por medio de la presente, quien suscribe, hace constar que conoce al ciudadano $nombre $apellido, titular de la cédula de identidad Nº $cedula, y puede dar fe de que es una persona responsable, honesta y comprometida con sus labores."
);

$pdf->MultiCell(0,7,$texto1,0,'J');

$pdf->Ln(5);

$texto2 = utf8_decode(
"Durante el tiempo en que mantuvo relación profesional con nuestra organización, demostró excelentes valores personales, buen trato interpersonal, capacidad de trabajo en equipo y alto sentido de responsabilidad."
);

$pdf->MultiCell(0,7,$texto2,0,'J');

$pdf->Ln(5);

$texto3 = utf8_decode(
"Por lo anteriormente expuesto, se recomienda ampliamente para cualquier actividad laboral o profesional que se le asigne."
);

$pdf->MultiCell(0,7,$texto3,0,'J');

$pdf->Ln(25);

// ===== FIRMA =====
$pdf->Line(120, 220, 180, 220);

$pdf->Ln(5);

$pdf->SetX(120);
$pdf->Cell(60,6,'Director de Recursos Humanos',0,1,'C');

$pdf->SetX(120);
$pdf->Cell(60,6,'Venezuela',0,1,'C');

$pdf->Output();
?>