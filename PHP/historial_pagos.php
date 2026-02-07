<?php
session_start();
include("db.php");

$sql = "SELECT p.*, n.fecha_inicio, n.fecha_fin 
        FROM pagos p 
        JOIN nomina n ON p.id_nomina = n.id_nomina
        ORDER BY p.fecha_pago DESC";
$res = mysqli_query($conexion, $sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Historial de Pagos</title>

<link rel="stylesheet" href="../css/historial_de_pagos.css">
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">

<style>
.buscador {
    width: 100%;
    padding: 10px;
    margin: 15px 0;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 15px;
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-header">
        <h2>RRHH Admin</h2>
        <i class="ri-building-2-fill logo-icon"></i>
    </div>

    <a href="administrador.php"><i class="ri-home-4-line"></i> Inicio</a>
    <a href="nomina.php" class="active"><i class="ri-money-dollar-circle-line"></i> Nómina</a>
    <a href="listar_empleados.php"><i class="ri-team-line"></i> Empleados</a>
    <a href="reportes.php"><i class="ri-bar-chart-line"></i> Reportes</a>
</aside>

<!-- MAIN -->
<div class="main">

<header>
    <h2>Panel de Administración - RRHH</h2>
    <div>
        <span>👤 <?= $_SESSION['usuario'] ?></span> |
        <a href="cerrar_sesion.php">Cerrar sesión</a>
    </div>
</header>

<div class="top-menu">
    <a href="generar_nomina.php" class="top-button"><i class="ri-file-text-line"></i> Generar Nómina</a>
    <a href="ver_nomina.php" class="top-button"><i class="ri-eye-line"></i> Ver Nóminas</a>
</div>

<div class="contenido">
    <div class="card-container">

        <h2>📜 Historial de Pagos</h2>

        <!-- BUSCADOR -->
        <input type="text" id="buscador" class="buscador" placeholder="🔍 Buscar por ID, método, fecha, notas...">

        <div class="table-container">
            <table id="tablaPagos">
                <thead>
                    <tr>
                        <th>ID Pago</th>
                        <th>ID Nómina</th>
                        <th>Período</th>
                        <th>Fecha de Pago</th>
                        <th>Total Pagado</th>
                        <th>Método</th>
                        <th>Notas</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($p = mysqli_fetch_assoc($res)) { ?>
                    <tr>
                        <td><?= $p['id_pago'] ?></td>
                        <td><?= $p['id_nomina'] ?></td>
                        <td><?= $p['fecha_inicio'] ?> / <?= $p['fecha_fin'] ?></td>
                        <td><?= $p['fecha_pago'] ?></td>
                        <td>Bs <?= number_format($p['total_pagado'],2) ?></td>
                        <td><?= $p['metodo'] ?></td>
                        <td><?= $p['notas'] ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

</div>

<!-- BUSCADOR AUTOMÁTICO -->
<script>
document.getElementById('buscador').addEventListener('keyup', function () {
    const filtro = this.value.toLowerCase();
    const filas = document.querySelectorAll('#tablaPagos tbody tr');

    filas.forEach(fila => {
        const texto = fila.textContent.toLowerCase();
        fila.style.display = texto.includes(filtro) ? '' : 'none';
    });
});
</script>

</body>
</html>
