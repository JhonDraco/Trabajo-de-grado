<?php

require('../librerias/fpdf.php');

// Zona horaria de Venezuela
date_default_timezone_set('America/Caracas');
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'spanish');

$pdf = new FPDF();
$pdf->AddPage();

// Márgenes
$pdf->SetLeftMargin(25);
$pdf->SetRightMargin(25);

// Variables POST
$nombre   = isset($_POST['name']) ? $_POST['name'] : '';
$apellido = isset($_POST['apellido']) ? $_POST['apellido'] : '';
$cedula   = isset($_POST['cedula']) ? $_POST['cedula'] : '';

// Fecha y hora
$fecha = strftime('%d de %B de %Y');
$hora  = date('H:i');

// ===== EMPRESA =====
$pdf->SetFont('Arial','B',12);
$pdf->SetTextColor(0,102,204);
$pdf->Cell(0,10,'EMPRESA XX S.A.',0,1,'L');

// Espacio
$pdf->Ln(15);

// ===== FECHA Y HORA =====
$pdf->SetFont('Arial','',11);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,8,utf8_decode("Caracas, $fecha - $hora"),0,1,'L');

// Espacio
$pdf->Ln(10);

// ===== DESTINATARIO =====
$pdf->Cell(0,8,'Para RR.HH',0,1,'L');

// Espacio
$pdf->Ln(10);

// ===== CUERPO =====
$texto1 = utf8_decode(
    "Mediante la siguiente carta laboral, se hace constancia de que el trabajador "
    ."$nombre $apellido, de cédula $cedula, trabajó a tiempo parcial en nuestra empresa "
    ."en el periodo comprendido entre enero del año 2004 y enero del año 2008, con un "
    ."contrato laboral de 18 horas mensuales con el cargo de Ayudante de Banca y gestión "
    ."de tesorería."
);

$pdf->MultiCell(0,7,$texto1,0,'J');

// Espacio
$pdf->Ln(5);

$texto2 = utf8_decode(
    "Durante todo el periodo laboral demostró ser un trabajador con grandes cualidades, "
    ."buen trato personal y capacidad de liderazgo, eficiente y responsable."
);

$pdf->MultiCell(0,7,$texto2,0,'J');

// Espacio para firma
$pdf->Ln(20);

// ===== FIRMA =====

$pdf->SetFont('Arial','',11);
$pdf->Cell(0,6,'Director de Recursos Humanos.',0,1,'L');
$pdf->Cell(0,6,'Venezuela',0,1,'L');

$pdf->Output();
