<?php


include("db.php");

$anio_actual = date('Y');

/* ===========================
   SALDOS DE VACACIONES
=========================== */
$saldos = mysqli_query($conexion, "
    SELECT 
        e.id,
        e.cedula,
        e.nombre,
        e.apellido,
        vs.anio,
        vs.dias_acumulados,
        vs.dias_disfrutados,
        vs.dias_pendientes
    FROM vacaciones_saldo vs
    INNER JOIN empleados e ON vs.empleado_id = e.id
    WHERE vs.anio = $anio_actual
    ORDER BY e.nombre
");

/* ===========================
   PROCESAR FORMULARIO (Lógica Corregida)
=========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['empleado_id'])) {

    $empleado_id = intval($_POST['empleado_id']);
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $observaciones = mysqli_real_escape_string($conexion, $_POST['observaciones']);
    $creada_por = $_SESSION['usuario'];

    // 1. OBTENER O CREAR SALDO AUTOMÁTICAMENTE
    $qSaldo = mysqli_query($conexion, "
        SELECT dias_pendientes 
        FROM vacaciones_saldo 
        WHERE empleado_id=$empleado_id AND anio=$anio_actual
    ");

    if (mysqli_num_rows($qSaldo) == 0) {
        $dias_base = 15; // Días iniciales por ley
        mysqli_query($conexion, "
            INSERT INTO vacaciones_saldo 
            (empleado_id, anio, dias_acumulados, dias_disfrutados, dias_pendientes, actualizado_en) 
            VALUES ($empleado_id, $anio_actual, $dias_base, 0, $dias_base, NOW())
        ");
        // Volvemos a consultar para tener el dato listo
        $qSaldo = mysqli_query($conexion, "SELECT dias_pendientes FROM vacaciones_saldo WHERE empleado_id=$empleado_id AND anio=$anio_actual");
    }

    $saldo = mysqli_fetch_assoc($qSaldo);
    $dias_disponibles = $saldo['dias_pendientes'];

    // 2. CALCULAR DÍAS SOLICITADOS Y FERIADOS
    $inicio = new DateTime($fecha_inicio);
    $fin = new DateTime($fecha_fin);
    $fin->modify('+1 day'); // Para incluir el último día en el conteo

    $periodo = new DatePeriod($inicio, new DateInterval('P1D'), $fin);

    $dias_totales = 0;
    $dias_feriados = 0;
    $feriados_ids = [];

    foreach ($periodo as $fecha) {
        $dias_totales++;
        $f = $fecha->format('Y-m-d');

        // Verificar si este día es feriado en tu tabla 'feriados'
        $qF = mysqli_query($conexion, "SELECT id_feriado FROM feriados WHERE fecha='$f'");
        if (mysqli_num_rows($qF) > 0) {
            $dias_feriados++;
            $rowF = mysqli_fetch_assoc($qF);
            $feriados_ids[] = $rowF['id_feriado'];
        }
    }

    $dias_habiles = $dias_totales - $dias_feriados;

    // 3. VALIDACIÓN FINAL Y GUARDADO
    if ($dias_habiles > $dias_disponibles) {
        header("Location: vacaciones.php?error=exceso");
        exit();
    }

    $sqlInsertVac = "INSERT INTO vacaciones (
        empleado_id, fecha_inicio, fecha_fin,
        dias_solicitados, dias_habiles, dias_feriados,
        observaciones, creada_por, estado
    ) VALUES (
        $empleado_id, '$fecha_inicio', '$fecha_fin',
        $dias_totales, $dias_habiles, $dias_feriados,
        '$observaciones', '$creada_por', 'pendiente'
    )";

    if (mysqli_query($conexion, $sqlInsertVac)) {
        $id_vacacion = mysqli_insert_id($conexion);
        // Registrar relación con feriados encontrados
        foreach ($feriados_ids as $id_fer) {
            mysqli_query($conexion, "INSERT INTO vacaciones_feriados (id_vacacion, id_feriado) VALUES ($id_vacacion, $id_fer)");
        }
        header("Location: vacaciones.php?ok=1");
    } else {
        die("Error al guardar: " . mysqli_error($conexion));
    }
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $empleado_id = intval($_POST['empleado_id']);
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $observaciones = mysqli_real_escape_string($conexion, $_POST['observaciones']);
    $creada_por = $_SESSION['usuario'];

    // --- AQUÍ EMPIEZA EL CAMBIO ---
    
    // Intentar obtener saldo
    $qSaldo = mysqli_query($conexion, "
        SELECT dias_pendientes 
        FROM vacaciones_saldo 
        WHERE empleado_id=$empleado_id AND anio=$anio_actual
    ");

    // Si el registro NO existe, lo creamos automáticamente
    if (mysqli_num_rows($qSaldo) == 0) {
        $dias_base = 15; // Días de ley iniciales
        
        $sqlInsert = "INSERT INTO vacaciones_saldo 
                      (empleado_id, anio, dias_acumulados, dias_disfrutados, dias_pendientes, actualizado_en) 
                      VALUES ($empleado_id, $anio_actual, $dias_base, 0, $dias_base, NOW())";
        
        if (mysqli_query($conexion, $sqlInsert)) {
            // Volvemos a consultar para continuar con el flujo normal
            $qSaldo = mysqli_query($conexion, "SELECT dias_pendientes FROM vacaciones_saldo WHERE empleado_id=$empleado_id AND anio=$anio_actual");
        } else {
            die("Error al inicializar saldo: " . mysqli_error($conexion));
        }
    }

    $saldo = mysqli_fetch_assoc($qSaldo);
    $dias_disponibles = $saldo['dias_pendientes'];

    // --- AQUÍ TERMINA EL CAMBIO Y SIGUE TU LÓGICA DE CALCULAR DÍAS ---

    $inicio = new DateTime($fecha_inicio);
    // ... resto de tu código igual ...

    // Validar saldo suficiente
    if ($dias_habiles > $dias_disponibles) {
        header("Location: vacaciones.php?error=exceso");
        exit();
    }

    // Insertar vacaciones
    mysqli_query($conexion, "
        INSERT INTO vacaciones (
            empleado_id, fecha_inicio, fecha_fin,
            dias_solicitados, dias_habiles, dias_feriados,
            observaciones, creada_por
        ) VALUES (
            $empleado_id, '$fecha_inicio', '$fecha_fin',
            $dias_totales, $dias_habiles, $dias_feriados,
            '$observaciones', '$creada_por'
        )
    ");

    $id_vacacion = mysqli_insert_id($conexion);

    foreach ($feriados_ids as $id_feriado) {
        mysqli_query($conexion, "
            INSERT INTO vacaciones_feriados (id_vacacion, id_feriado)
            VALUES ($id_vacacion, $id_feriado)
        ");
    }

    header("Location: vacaciones.php?ok=1");
    exit();
}

/* ===========================
   DATOS PARA VISTA
=========================== */
$empleados = mysqli_query($conexion, "
    SELECT id, nombre, apellido 
    FROM empleados 
    WHERE estado='activo'
");

$vacaciones = mysqli_query($conexion, "
    SELECT v.*, e.nombre, e.apellido
    FROM vacaciones v
    JOIN empleados e ON v.empleado_id = e.id
    ORDER BY v.creada_en DESC
");
?>


<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['cargo'] != 1) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panel del Administrador</title>
<link rel="stylesheet" href="../css/vacasiones.css">
<!-- Iconos RemixIcon -->
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">


</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
     <div class="sidebar-header">
       
        <h2>RRHH Admin</h2>
         <i class="ri-building-2-fill logo-icon"></i>
    </div>
    <a href="administrador.php" >
        <i class="ri-home-4-line"></i> Inicio
    </a>
    <a href="nomina.php">
        <i class="ri-money-dollar-circle-line"></i> Nómina
    </a>

    <a href=""><i class="ri-ball-pen-line"></i>Liquidacion</a>
    <a href="vacaciones.php" class="active">  <i class="ri-sun-line"></i></i> Vacaciones</a>
    
    <a href="listar_empleados.php">
        <i class="ri-team-line"></i> Empleados
    </a>

    <a href="listar_usuario.php">
        <i class="ri-user-settings-line"></i> Usuarios
    </a>
    <a href="reportes.php">
        <i class="ri-bar-chart-line"></i> Reportes
    </a>
             
    <a href="contactar.php">
      <i class="ri-mail-line"></i> Agendar entrevistas 
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
        <a href="solicitudes _de_vacaciones.php" class="top-button">sulicitudes de vacaciones</a>
        
    </div>

    <!-- CONTENIDO -->
    <div class="contenido">
       <h2> Gestión de Vacaciones</h2>

       
<div class="form-compacto">
    <h3><i class="ri-calendar-check-fill"></i> Nueva Solicitud de Vacaciones</h3>
    <form method="post">
        
        <div class="form-group">
            <label><i class="ri-user-3-fill"></i> EMPLEADO</label>
            <select name="empleado_id" required>
                <option value="">Seleccionar...</option>
                <?php 
                mysqli_data_seek($empleados, 0); 
                while ($e = mysqli_fetch_assoc($empleados)) { ?>
                    <option value="<?= $e['id'] ?>">
                        <?= $e['nombre']." ".$e['apellido'] ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="form-group">
            <label><i class="ri-calendar-line"></i> FECHA INICIO</label>
            <input type="date" name="fecha_inicio" required>
        </div>

        <div class="form-group">
            <label><i class="ri-calendar-line"></i> FECHA FIN</label>
            <input type="date" name="fecha_fin" required>
        </div>

        <div class="form-group">
            <label><i class="ri-edit-2-line"></i> OBSERVACIONES</label>
            <input type="text" name="observaciones" placeholder="Ej: Motivos personales...">
        </div>

        <button type="submit" class="btn-registrar">
            <i class="ri-save-3-fill"></i> GUARDAR
        </button>
        
    </form>
<br>
<br>
<?php if (isset($_GET['ok'])) { ?>
<p style="color:green;">Solicitud registrada correctamente</p>
<?php } ?>

<?php if (isset($_GET['error']) && $_GET['error']=='saldo') { ?>
<p style="color:red;">❌ El empleado no tiene saldo inicializado</p>
<?php } ?>

<?php if (isset($_GET['error']) && $_GET['error']=='exceso') { ?>
<p style="color:red;">❌ No tiene suficientes días disponibles</p>
<?php } ?>

<!-- SALDOS -->
<h3> Saldo de Vacaciones <?= $anio_actual ?></h3>
<table border="1" cellpadding="8">
<tr>
    <th>Empleado</th>
    <th>Acumulados</th>
    <th>Disfrutados</th>
    <th>Pendientes</th>
</tr>

<?php while ($s = mysqli_fetch_assoc($saldos)) { ?>
<tr>
    <td><?= $s['nombre']." ".$s['apellido'] ?></td>
    <td><?= $s['dias_acumulados'] ?></td>
    <td><?= $s['dias_disfrutados'] ?></td>
    <td><strong><?= $s['dias_pendientes'] ?></strong></td>
</tr>
<?php } ?>
</table>

<hr>

</body>
</html>

