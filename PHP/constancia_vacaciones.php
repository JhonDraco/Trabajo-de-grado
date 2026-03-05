<?php
session_start();
require('../librerias/fpdf.php');
include("db.php");

if(!isset($_GET['id'])){
    die("ID no especificado");
}

$id = intval($_GET['id']);

// ELIMINADO: e.cargo de la consulta SQL
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
   ESTRUCTURA PDF
=========================*/
class PDF extends FPDF {
    function Header() {
        $this->SetFont('Arial','B',14);
        $this->Cell(0,10,'BAZAR ANORIKEV C.A',0,1,'L');
        $this->SetFont('Arial','I',10);
        $this->Cell(0,5,'RIF: J-31321135-0',0,1,'L');
        $this->Ln(10);
    }
}

$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);

$pdf->Cell(0,10,utf8_decode('CONSTANCIA DE VACACIONES'),0,1,'C');
$pdf->Ln(10);

$pdf->SetFont('Arial','',12);

// ELIMINADO: La frase que mencionaba el cargo
$texto = "La empresa certifica que el trabajador "
        .$datos['nombre']." ".$datos['apellido']
        .", titular de la cedula ".$datos['cedula']
        .", disfrutara de un periodo vacacional desde el "
        .date('d/m/Y', strtotime($datos['fecha_inicio']))
        ." hasta el "
        .date('d/m/Y', strtotime($datos['fecha_fin']))
        .", por un total de "
        .$datos['dias_habiles']." dias habiles.";

$pdf->MultiCell(0,8,utf8_decode($texto));

$pdf->Ln(15);

$pdf->SetFont('Arial','B',11);
$pdf->Cell(0,8,utf8_decode("Aprobado por: ".$datos['aprobado_por']),0,1);
$pdf->Cell(0,8,utf8_decode("Fecha de aprobación: ".date('d/m/Y H:i', strtotime($datos['fecha_aprobacion']))),0,1);

$pdf->Ln(30);

$pdf->Cell(0,8,"______________________________",0,1,'C');
$pdf->Cell(0,8,"Firma y Sello de la Empresa",0,1,'C');

ob_end_clean();
$pdf->Output("I","Constancia_Vacaciones_$id.pdf");
?>