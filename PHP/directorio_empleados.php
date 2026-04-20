<?php
include("seguridad.php");

verificarSesion();
bloquearSiNo(puedeSalariosArchivos());

include("db.php");

$buscar = "";
$where  = "";

if (isset($_GET['buscar']) && $_GET['buscar'] != "") {
    $buscar = mysqli_real_escape_string($conexion, $_GET['buscar']);
    $where  = "WHERE nombre LIKE '%$buscar%'
               OR apellido LIKE '%$buscar%'
               OR cedula LIKE '%$buscar%'";
}

$resultado = mysqli_query($conexion,
    "SELECT id, cedula, nombre, apellido, telefono, email, salario_base, estado, fecha_ingreso
     FROM empleados
     $where
     ORDER BY nombre ASC"
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Directorio de Empleados</title>
<link rel="stylesheet" href="../css/listar_empleados.css">
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
<style>
    .tabla-empleados tr.fila-empleado {
        cursor: pointer;
        transition: background 0.15s;
    }
    .tabla-empleados tr.fila-empleado:hover {
        background: #eaf3ef;
    }
    .badge-estado {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .badge-activo   { background: #d4edda; color: #155724; }
    .badge-inactivo { background: #f8d7da; color: #721c24; }
    .hint {
        font-size: 12px;
        color: #aaa;
        margin-top: 6px;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .salario-col { font-weight: 600; color: #1f3a34; }
    .total-resultado {
        font-size: 13px;
        color: #888;
        margin-bottom: 12px;
    }
</style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
<div class="sidebar-header">
    <img src="../img/logo.png" alt="Logo" class="logo">
    <h3 class="system-title">KAO SHOP</h3>
</div>
    <a href="administrador.php"><i class="ri-home-4-line"></i> Inicio</a>
    <a href="generar_nomina.php"><i class="ri-money-dollar-circle-line"></i> Nómina</a>
    <a href="liquidacion.php"><i class="ri-ball-pen-line"></i> Liquidación</a>
    <a href="vacaciones.php"><i class="ri-sun-line"></i> Vacaciones</a>
    <a href="listar_empleados.php" class="active"><i class="ri-team-line"></i> Empleados</a>
    <a href="listar_usuario.php"><i class="ri-user-settings-line"></i> Roles</a>
    <a href="reportes.php"><i class="ri-bar-chart-line"></i> Reportes</a>
    <?php if (esAdmin()): ?>
    <a href="bitacora.php"><i class="ri-file-shield-2-line"></i> Bitácora</a>
    <?php endif; ?>
    <a href="contactar.php"><i class="ri-mail-line"></i> Email</a>
</aside>

<div class="main">

    <header>
        <h2><i class="ri-folder-user-line"></i> Salarios y Archivos</h2>
        <div>
            <span>👤 <?php echo $_SESSION['usuario']; ?></span> |
            <a href="cerrar_sesion.php">Cerrar sesión</a>
        </div>
    </header>

    <div class="top-menu">
        <a href="listar_empleados.php" class="top-button">
            <i class="ri-arrow-left-line"></i> Volver a Empleados
        </a>
    </div>

    <div class="contenido">

        <h3>Buscar Empleado</h3>

        <form method="GET" class="buscador">
            <input
                type="text"
                name="buscar"
                placeholder="Buscar por nombre, apellido o cédula"
                value="<?php echo htmlspecialchars($buscar); ?>"
                autofocus>
            <button type="submit">
                <i class="ri-search-line"></i> Buscar
            </button>
            <?php if ($buscar): ?>
                <a href="directorio_empleados.php" style="margin-left:8px; font-size:13px; color:#888;">
                    <i class="ri-close-line"></i> Limpiar
                </a>
            <?php endif; ?>
        </form>

        <p class="hint">
            <i class="ri-information-line"></i>
            Haz clic en cualquier empleado para ver sus salarios, asignaciones, deducciones y documentos.
        </p>

        <?php
        $total = mysqli_num_rows($resultado);
        echo "<p class='total-resultado'>".($buscar ? "Resultados para \"$buscar\": " : "Total empleados: ")."<strong>$total</strong></p>";
        ?>

        <table class="tabla-empleados">
            <tr>
                <th>Cédula</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Teléfono</th>
                <th>Email</th>
                <th>Salario base</th>
                <th>Ingreso</th>
                <th>Estado</th>
            </tr>

            <?php while ($f = mysqli_fetch_assoc($resultado)): ?>
            <tr class="fila-empleado"
                onclick="window.location='salarios_archivos.php?id=<?= $f['id'] ?>'"
                title="Ver salarios y archivos de <?= htmlspecialchars($f['nombre'].' '.$f['apellido']) ?>">
                <td><?= htmlspecialchars($f['cedula']) ?></td>
                <td><?= htmlspecialchars($f['nombre']) ?></td>
                <td><?= htmlspecialchars($f['apellido']) ?></td>
                <td><?= $f['telefono'] ?: '—' ?></td>
                <td><?= $f['email'] ?: '—' ?></td>
                <td class="salario-col">Bs. <?= number_format($f['salario_base'], 2) ?></td>
                <td><?= date('d/m/Y', strtotime($f['fecha_ingreso'])) ?></td>
                <td>
                    <span class="badge-estado <?= strtolower($f['estado']) == 'activo' ? 'badge-activo' : 'badge-inactivo' ?>">
                        <?= ucfirst($f['estado']) ?>
                    </span>
                </td>
            </tr>
            <?php endwhile; ?>

            <?php if ($total === 0): ?>
            <tr>
                <td colspan="8" style="text-align:center; color:#aaa; padding:20px;">
                    No se encontraron empleados<?= $buscar ? " para \"$buscar\"" : "" ?>.
                </td>
            </tr>
            <?php endif; ?>

        </table>

    </div>
</div>

<?php mysqli_close($conexion); ?>
</body>
</html>