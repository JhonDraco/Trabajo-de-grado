<?php

session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['cargo'] != 1) {
    header("Location: index.php");
    exit();
}

include("db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nombre'])) {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $tipo = ($_POST['tipo'] === 'porcentaje') ? 'porcentaje' : 'fijo';
    $porcentaje = floatval($_POST['porcentaje']);
    $desc = mysqli_real_escape_string($conexion, $_POST['descripcion']);
    mysqli_query($conexion, "INSERT INTO tipo_asignacion (nombre, tipo, porcentaje, descripcion) VALUES ('$nombre','$tipo',$porcentaje,'$desc')");
    header("Location: asignaciones.php");
    exit();
}

$res = mysqli_query($conexion, "SELECT * FROM tipo_asignacion");

$empleados = mysqli_query($conexion, "SELECT id, nombre FROM empleados ORDER BY nombre");

$asignaciones_emp = mysqli_query($conexion, "

    SELECT 
    ae.id_asig_emp,
    e.nombre AS empleado,
    ta.nombre AS asignacion,
    ae.monto,
    ae.creada_en

    FROM asignacion_empleado ae

    JOIN empleados e 
    ON e.id = ae.empleado_id

    JOIN tipo_asignacion ta 
    ON ta.id_asignacion = ae.id_asignacion

    WHERE ae.activa = 1

    ORDER BY ae.creada_en DESC

    ");

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panel del Administrador</title>
<link rel="stylesheet" href="../css/asignaciones.css">
<!-- Iconos RemixIcon -->
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">


</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
<div class="sidebar-header">
    <img src="../img/logo.png" alt="Logo" class="logo">
    <h3 class="system-title">KAO SHOP</h3>
</div>
    <a href="administrador.php" >
        <i class="ri-home-4-line"></i> Inicio
    </a>
    <a href="nomina.php"class="active">
        <i class="ri-money-dollar-circle-line"></i> Nómina
    </a>

    <a href="liquidacion.php"><i class="ri-ball-pen-line"></i>Liquidacion</a>
    <a href="vacaciones.php">  <i class="ri-sun-line"></i></i> Vacaciones</a>
    
    <a href="listar_empleados.php">
        <i class="ri-team-line"></i> Empleados
    </a>

    <a href="listar_usuario.php">
        <i class="ri-user-settings-line"></i> Roles
    </a>
    <a href="reportes.php">
        <i class="ri-bar-chart-line"></i> Reportes
    </a>
             
    <a href="contactar.php">
      <i class="ri-mail-line"></i> Email
    </a>
    
   
</a>

   
</aside>


<div class="main">

    <!-- HEADER -->
    <header>
        <h2>Panel de Administración - RRHH</h2>
        <div>
            <span>👤 <?php echo $_SESSION['usuario']; ?></span> |
            <a href="cerrar_sesion.php">Cerrar sesión</a>
        </div>
    </header>

    <!-- TOP MENU HORIZONTAL -->
    <div class="top-menu">
       <a href="asignaciones.php" class="top-button"><i class="ri-add-circle-line"></i> Asignaciones</a>
       <a href="deducciones.php" class="top-button"><i class="ri-subtract-line"></i> Deducciónes</a>
       <a href="generar_nomina.php" class="top-button"><i class="ri-file-text-line"></i> Generar Nómina</a>
       <a href="ver_nomina.php" class="top-button"><i class="ri-eye-line"></i> Ver Nóminas</a>
       <a href="pagar_nomina.php" class="top-button"><i class="ri-eye-line"></i> Pagar Nominas</a>
        <a href="historial_pagos.php" class="top-button"><i class="ri-file-text-line"></i> Ver Historial de Pagos</a>

    </div>

    <!-- CONTENIDO -->
 <div class="contenido">
    <h3><i class="ri-add-box-line"></i> Nueva Asignación</h3>

    <div class="form-group-compact">
    <label>Aplicar a</label>

    <select name="aplica_a">

      <option value="todos">Todos los empleados</option>

      <option value="manual">Seleccionar empleados</option>
    </select>



    <div class="form-container-compact">
        <form method="post" class="form-grid">
            <div class="form-group-compact">
                <label>Nombre</label>
                <input type="text" name="nombre" placeholder="Nombre de asignación" required>
            </div>

            <div class="form-group-compact">
                <label>Tipo</label>
                <select name="tipo">
                    <option value="fijo">Fijo (Bs)</option>
                    <option value="porcentaje">Porcentaje (%)</option>
                </select>
            </div>

            <div class="form-group-compact">
                <label>Porcentaje</label>
                <input type="number" name="porcentaje" step="0.01" value="0.00" required>
            </div>

            <div class="form-group-compact">
                <label>Descripción corta</label>
                <input type="text" name="descripcion" placeholder="Opcional...">
            </div>

            <button type="submit" class="btn-guardar-compact" style="grid-column: span 1;">
                <i class="ri-save-3-line"></i> Guardar
            </button>
        </form>
    </div>
    <h3>Lista de Asignaciones</h3>
    <table border="1" cellpadding="5">
<tr><th>Nombre</th><th>Tipo</th><th>porcentaje</th><th>Acción</th></tr>
<?php while($d = mysqli_fetch_assoc($res)) { ?>
<tr>
  <td><?=htmlspecialchars($d['nombre'])?></td>
  <td><?= $d['tipo'] ?></td>
  <td><?= number_format($d['porcentaje'],2) ?><?= $d['tipo']=='porcentaje' ? '%' : '' ?></td>
  <td><a href="eliminar_asignacion.php?id=<?=$d['id_asignacion']?>" onclick="return confirm('Eliminar?')">Eliminar</a></td>
</tr>
<?php } ?>
</table>
    </div>


</div>

<h3><i class="ri-user-add-line"></i> Asignar a Empleado</h3>

<div class="form-container-compact">

    <form method="post" action="guardar_asignacion_empleado.php" class="form-grid">

    <div class="form-group-compact">
    <label>Empleado</label>

    <select name="empleado_id" required>

    <option value="">Seleccione</option>

    <?php while($emp = mysqli_fetch_assoc($empleados)){ ?>

    <option value="<?= $emp['id'] ?>">
    <?= htmlspecialchars($emp['nombre']) ?>
    </option>

    <?php } ?>

    </select>

    </div>

    


    <div class="form-group-compact">
    <label>Asignación</label>

    <select name="asignacion_id" id="asignacion_select" required>

    <?php
    $res2 = mysqli_query($conexion,"SELECT * FROM tipo_asignacion");

    while($as = mysqli_fetch_assoc($res2)){
    ?>

    <option 
            value="<?= $as['id_asignacion'] ?>"
            data-tipo="<?= $as['tipo'] ?>"
            >
            <?= htmlspecialchars($as['nombre']) ?>
    </option>

    <?php } ?>

    </select>

    </div>


    <div class="form-group-compact" id="campo_monto">
        <label>Monto</label>
        <input type="number" step="0.01" name="monto">
    </div>


    <button type="submit" class="btn-guardar-compact">
    <i class="ri-save-line"></i> Asignar
    </button>

    </form>

</div>

    <h3><i class="ri-team-line"></i> Asignaciones a Empleados</h3>

    

            <table border="1" cellpadding="5">

            <tr>
            <th>Empleado</th>
            <th>Asignación</th>
            <th>Monto</th>
            <th>Fecha</th>
            <th>Acción</th>
            </tr>

            <?php while($a = mysqli_fetch_assoc($asignaciones_emp)) { ?>

            <tr>

            <td><?= htmlspecialchars($a['empleado']) ?></td>

            <td><?= htmlspecialchars($a['asignacion']) ?></td>

            <td><?= number_format($a['monto'],2) ?> Bs</td>

            <td><?= $a['creada_en'] ?></td>

            <td>

            <a href="eliminar_asignacion_empleado.php?id=<?= $a['id_asig_emp'] ?>"
            onclick="return confirm('Eliminar asignación?')">

            Eliminar

            </a>

            </td>

            </tr>

            <?php } ?>

            </table>
</div>
            <script>

const selectAsignacion = document.getElementById("asignacion_select");
const campoMonto = document.getElementById("campo_monto");

selectAsignacion.addEventListener("change", function(){

let tipo = this.options[this.selectedIndex].dataset.tipo;

if(tipo === "porcentaje"){

campoMonto.style.display = "none";

}else{

campoMonto.style.display = "block";

}

});

</script>
</body>
</html>

