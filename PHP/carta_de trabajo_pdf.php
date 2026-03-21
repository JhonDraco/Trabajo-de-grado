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

// Fecha alineada derecha
$pdf->SetFont('Arial','',11);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,8,utf8_decode("Caracas, $fecha - $hora"),0,1,'R');

$pdf->Ln(10);

// Título
$pdf->SetFont('Arial','B',13);
$pdf->Cell(0,10,'CONSTANCIA DE TRABAJO',0,1,'C');

$pdf->Ln(10);

// Destinatario
$pdf->SetFont('Arial','',11);
$pdf->Cell(0,8,'Para RR.HH.',0,1,'L');

$pdf->Ln(10);

// Texto
$pdf->SetFont('Arial','',11);

$texto1 = utf8_decode(
"   Mediante la presente se hace constar que el ciudadano $nombre $apellido, 
titular de la cédula de identidad N° $cedula, 
laboró en nuestra empresa desempeñándose como Ayudante de Banca y gestión de tesorería,
bajo modalidad de tiempo parcial (18 horas mensuales), 
durante el periodo comprendido entre enero de 2004 y enero de 2008.");

$pdf->MultiCell(0,7,$texto1,0,'J');

$pdf->Ln(5);

$texto2 = utf8_decode(
"Durante su permanencia en la empresa, demostró responsabilidad, compromiso, excelente trato interpersonal y habilidades de liderazgo."
);

$pdf->MultiCell(0,7,$texto2,0,'J');

$pdf->Ln(25);

// Línea firma
$pdf->Line(120, 220, 180, 220);

$pdf->Ln(5);

// Firma alineada derecha
$pdf->SetX(120);
$pdf->Cell(60,6,'Director de Recursos Humanos',0,1,'C');

$pdf->SetX(120);
$pdf->Cell(60,6,'Venezuela',0,1,'C');

$pdf->Output();
?>