<?php
session_start();
require('../librerias/fpdf.php');
include("db.php");

if(!isset($_GET['id'])){
    die("ID no especificado");
}

$id = intval($_GET['id']);

$query = mysqli_query($conexion,"
    SELECT v.*, e.nombre, e.apellido, e.cedula
    FROM vacaciones v
    JOIN empleados e ON v.empleado_id = e.id
    WHERE v.id_vacacion = $id
");

$datos = mysqli_fetch_assoc($query);

if(!$datos || $datos['estado'] != 'aprobado'){
    die("La vacación no existe o no está aprobada.");
}

/* =========================
   PDF
=========================*/
class PDF extends FPDF {
    function Header() {
        // LOGO
        $this->Image('../img/logo.png', 30, 15, 30);

        // EMPRESA
        $this->SetFont('Arial','B',14);
        $this->SetTextColor(31,58,52);
        $this->Cell(0,10,'KAO SHOP',0,1,'R');

        $this->Ln(10);

        // TÍTULO
        $this->SetFont('Arial','B',13);
        $this->Cell(0,10,utf8_decode('CONSTANCIA DE VACACIONES'),0,1,'C');

        $this->Ln(5);
    }
}

$pdf = new PDF();
$pdf->AddPage();

// Márgenes
$pdf->SetLeftMargin(25);
$pdf->SetRightMargin(25);

// ===== FECHA ACTUAL =====
$pdf->SetFont('Arial','',11);
$pdf->SetTextColor(0,0,0);
$pdf->Cell(0,8,utf8_decode("Caracas, ".strftime('%d de %B de %Y')),0,1,'R');

$pdf->Ln(10);

// ===== CUERPO =====
$pdf->SetFont('Arial','',11);

$texto = "La empresa certifica que el trabajador "
        .$datos['nombre']." ".$datos['apellido']
        .", titular de la cédula ".$datos['cedula']
        .", disfrutará de un período vacacional desde el "
        .date('d/m/Y', strtotime($datos['fecha_inicio']))
        ." hasta el "
        .date('d/m/Y', strtotime($datos['fecha_fin']))
        .", por un total de "
        .$datos['dias_habiles']." días hábiles.";

$pdf->MultiCell(0,7,utf8_decode($texto),0,'J');

$pdf->Ln(10);

// ===== DATOS DE APROBACIÓN =====
$pdf->SetFont('Arial','',11);

$pdf->Cell(0,7,utf8_decode("Aprobado por: ".$datos['aprobado_por']),0,1);
$pdf->Cell(0,7,utf8_decode("Fecha de aprobación: ".date('d/m/Y H:i', strtotime($datos['fecha_aprobacion']))),0,1);

$pdf->Ln(25);

// ===== FIRMA =====
$pdf->Line(120, 220, 180, 220);

$pdf->Ln(5);

$pdf->SetX(120);
$pdf->Cell(60,6,'Firma y Sello de la Empresa',0,1,'C');

ob_end_clean();
$pdf->Output("I","Constancia_Vacaciones_$id.pdf");
?>