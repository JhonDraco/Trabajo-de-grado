<?php

require('../librerias/fpdf.php');


$pdf = new FPDF();
$pdf->AddPage();
$pdf->Output();

?>
