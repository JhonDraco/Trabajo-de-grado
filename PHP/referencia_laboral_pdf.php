<?php

require('../librerias/fpdf.php');

// Zona horaria Venezuela
date_default_timezone_set('America/Caracas');
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'spanish');

$pdf = new FPDF();
$pdf->AddPage();

// Márgenes
$pdf->SetLeftMargin(25);
$pdf->SetRightMargin(25);

// Datos dinámicos
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

// ===== FECHA =====
$pdf->SetFont('Arial','',11);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,8,utf8_decode("Caracas, $fecha - $hora"),0,1,'L');

// Espacio
$pdf->Ln(10);

// ===== DESTINATARIO =====
$pdf->Cell(0,8,'A quien pueda interesar',0,1,'L');

// Espacio
$pdf->Ln(10);

// ===== TÍTULO =====
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,8,'CARTA DE REFERENCIA',0,1,'L');

// Espacio
$pdf->Ln(8);

// ===== CUERPO =====
$pdf->SetFont('Arial','',11);

$texto1 = utf8_decode(
    "Por medio de la presente, quien suscribe, hace constar que conoce al ciudadano "
    ."$nombre $apellido, titular de la cédula de identidad Nº $cedula, y puede dar fe "
    ."de que es una persona responsable, honesta y comprometida con sus labores."
);

$pdf->MultiCell(0,7,$texto1,0,'J');

// Espacio
$pdf->Ln(5);

$texto2 = utf8_decode(
    "Durante el tiempo en que mantuvo relación profesional con nuestra organización, "
    ."demostró excelentes valores personales, buen trato interpersonal, capacidad de "
    ."trabajo en equipo y alto sentido de responsabilidad."
);

$pdf->MultiCell(0,7,$texto2,0,'J');

// Espacio
$pdf->Ln(5);

$texto3 = utf8_decode(
    "Por lo anteriormente expuesto, lo recomendamos ampliamente para cualquier "
    ."actividad laboral o profesional que considere pertinente."
);

$pdf->MultiCell(0,7,$texto3,0,'J');

// Espacio para firma
$pdf->Ln(20);

// ===== FIRMA =====


$pdf->SetFont('Arial','',11);
$pdf->Cell(0,6,'Director de Recursos Humanos.',0,1,'L');
$pdf->Cell(0,6,'Venezuela',0,1,'L');

$pdf->Output();
