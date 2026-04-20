<?php
include("seguridad.php");
verificarSesion();
bloquearSiNo(puedeAdministrador());
include("db.php");

$mensaje = "";
$error   = "";
$tipo_registro = $_POST['tipo_registro'] ?? $_GET['tipo'] ?? 'completo';

$cargos_res = mysqli_fetch_all(
    mysqli_query($conexion, "SELECT cargo_id, nombre_cargo FROM cargo ORDER BY cargo_id"),
    MYSQLI_ASSOC
);

$sin_usuario = mysqli_fetch_all(mysqli_query($conexion,
    "SELECT e.id, e.nombre, e.apellido, e.cedula
     FROM empleados e
     WHERE e.id NOT IN (SELECT empleado_id FROM usuarios WHERE empleado_id IS NOT NULL)
     AND e.estado = 'activo'
     ORDER BY e.nombre"
), MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $tipo = $_POST['tipo_registro'];

    $usr_usuario    = trim($_POST['usuario'] ?? '');
    $usr_contrasena = $_POST['contrasena'] ?? '';
    $usr_cargo      = (int)($_POST['cargo_id'] ?? 0);
    $usr_nombre     = trim($_POST['nombre_completo'] ?? '');

    $emp_cedula   = trim($_POST['cedula'] ?? '');
    $emp_nombre   = trim($_POST['emp_nombre'] ?? '');
    $emp_apellido = trim($_POST['emp_apellido'] ?? '');
    $emp_dir      = trim($_POST['direccion'] ?? '');
    $emp_tel      = trim($_POST['telefono'] ?? '');
    $emp_email    = trim($_POST['emp_email'] ?? '');
    $emp_ingreso  = $_POST['fecha_ingreso'] ?? '';
    $emp_salario  = floatval($_POST['salario_base'] ?? 0);

    // Validar usuario único
    if (in_array($tipo, ['completo', 'solo_usuario', 'vincular']) && $usr_usuario) {
        $chk = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE usuario = ?");
        $chk->bind_param("s", $usr_usuario);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            $error = "⚠️ El nombre de usuario <strong>$usr_usuario</strong> ya existe. Elige otro.";
            $chk->close();
            goto fin;
        }
        $chk->close();
    }

    mysqli_begin_transaction($conexion);

    try {

        $hash = password_hash($usr_contrasena, PASSWORD_BCRYPT);

        if ($tipo === 'completo') {

            $s1 = $conexion->prepare(
                "INSERT INTO empleados (cedula,nombre,apellido,direccion,telefono,email,fecha_ingreso,salario_base)
                 VALUES (?,?,?,?,?,?,?,?)"
            );
            $s1->bind_param("sssssssd", $emp_cedula,$emp_nombre,$emp_apellido,$emp_dir,$emp_tel,$emp_email,$emp_ingreso,$emp_salario);
            $s1->execute();
            $nuevo_id = $conexion->insert_id;
            $s1->close();

            $nombre_completo = "$emp_nombre $emp_apellido";
            $s2 = $conexion->prepare(
                "INSERT INTO usuarios (nombre_apellido,usuario,clave,cargo_id,empleado_id) VALUES (?,?,?,?,?)"
            );
            $s2->bind_param("sssii", $nombre_completo,$usr_usuario,$hash,$usr_cargo,$nuevo_id);
            $s2->execute();
            $s2->close();

            registrar_auditoria($conexion,'CREAR','Empleados',
                "Registró empleado+usuario: $emp_nombre $emp_apellido (C.I: $emp_cedula), usuario: $usr_usuario");
            $mensaje = "✅ Empleado <strong>$emp_nombre $emp_apellido</strong> y usuario <strong>$usr_usuario</strong> registrados y vinculados.";

        } elseif ($tipo === 'solo_empleado') {

            $s1 = $conexion->prepare(
                "INSERT INTO empleados (cedula,nombre,apellido,direccion,telefono,email,fecha_ingreso,salario_base)
                 VALUES (?,?,?,?,?,?,?,?)"
            );
            $s1->bind_param("sssssssd", $emp_cedula,$emp_nombre,$emp_apellido,$emp_dir,$emp_tel,$emp_email,$emp_ingreso,$emp_salario);
            $s1->execute();
            $s1->close();

            registrar_auditoria($conexion,'CREAR','Empleados',
                "Registró solo empleado: $emp_nombre $emp_apellido (C.I: $emp_cedula)");
            $mensaje = "✅ Empleado <strong>$emp_nombre $emp_apellido</strong> registrado sin acceso al sistema.";

        } elseif ($tipo === 'solo_usuario') {

            $s1 = $conexion->prepare(
                "INSERT INTO usuarios (nombre_apellido,usuario,clave,cargo_id) VALUES (?,?,?,?)"
            );
            $s1->bind_param("sssi", $usr_nombre,$usr_usuario,$hash,$usr_cargo);
            $s1->execute();
            $s1->close();

            registrar_auditoria($conexion,'CREAR','Usuarios',
                "Registró usuario administrativo: $usr_usuario (cargo_id: $usr_cargo)");
            $mensaje = "✅ Usuario administrativo <strong>$usr_usuario</strong> creado.";

        } elseif ($tipo === 'vincular') {

            $emp_id = (int)$_POST['empleado_existente'];
            $emp_data = mysqli_fetch_assoc(mysqli_query($conexion,
                "SELECT nombre, apellido FROM empleados WHERE id = $emp_id"));
            $nombre_completo = $emp_data['nombre'].' '.$emp_data['apellido'];

            $s1 = $conexion->prepare(
                "INSERT INTO usuarios (nombre_apellido,usuario,clave,cargo_id,empleado_id) VALUES (?,?,?,?,?)"
            );
            $s1->bind_param("sssii", $nombre_completo,$usr_usuario,$hash,$usr_cargo,$emp_id);
            $s1->execute();
            $s1->close();

            registrar_auditoria($conexion,'CREAR','Usuarios',
                "Vinculó usuario '$usr_usuario' al empleado ID $emp_id ($nombre_completo)");
            $mensaje = "✅ Usuario <strong>$usr_usuario</strong> vinculado a <strong>$nombre_completo</strong>.";
        }

        mysqli_commit($conexion);

    } catch (Exception $e) {
        mysqli_rollback($conexion);
        $error = "❌ Error: " . $e->getMessage();
    }

    fin:;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registrar Empleado y Usuario</title>
<link rel="stylesheet" href="../css/formulario_para_registrar_empleado.css">
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
<style>
    .tipo-tabs { display:flex; gap:8px; margin-bottom:24px; flex-wrap:wrap; }
    .tipo-tab {
        flex:1; min-width:140px; padding:12px 10px;
        border:2px solid #e2e2e2; border-radius:10px; cursor:pointer;
        text-align:center; font-size:13px; font-weight:600; color:#888;
        background:#f9f9f9; transition:all 0.2s; user-select:none;
    }
    .tipo-tab:hover { border-color:#2b4a42; color:#1f3a34; }
    .tipo-tab.activo { border-color:#1f3a34; background:#1f3a34; color:white; }
    .tipo-tab i { display:block; font-size:22px; margin-bottom:4px; }
    .seccion-form { border-top:2px solid #f0f0f0; margin-top:20px; padding-top:18px; }
    .seccion-titulo {
        font-size:12px; font-weight:700; color:#1f3a34;
        text-transform:uppercase; letter-spacing:.06em;
        margin-bottom:14px; display:flex; align-items:center; gap:8px;
    }
    .form-grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
    .msg-ok  { background:#d4edda; color:#155724; border:1px solid #c3e6cb; border-radius:8px; padding:12px 16px; margin-bottom:16px; font-weight:600; }
    .msg-err { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; border-radius:8px; padding:12px 16px; margin-bottom:16px; font-weight:600; }
    .panel-oculto  { display:none; }
    .panel-visible { display:block; }
    .nota-info { background:#e8f4f8; border:1px solid #b8ddf0; border-radius:8px; padding:10px 14px; font-size:12px; color:#0c5460; margin-bottom:14px; display:flex; gap:8px; }
</style>
</head>
<body>

<aside class="sidebar">
<div class="sidebar-header">
    <img src="../img/logo.png" alt="Logo" class="logo">
    <h3 class="system-title">KAO SHOP</h3>
</div>
    <a href="administrador.php"><i class="ri-home-4-line"></i> Inicio</a>
    <a href="nomina.php"><i class="ri-money-dollar-circle-line"></i> Nómina</a>
    <a href="liquidacion.php"><i class="ri-ball-pen-line"></i> Liquidación</a>
    <a href="vacaciones.php"><i class="ri-sun-line"></i> Vacaciones</a>
    <a href="listar_empleados.php" class="active"><i class="ri-team-line"></i> Empleados</a>
    <a href="listar_usuario.php"><i class="ri-user-settings-line"></i> Roles</a>
    <a href="reportes.php"><i class="ri-bar-chart-line"></i> Reportes</a>
    <a href="contactar.php"><i class="ri-mail-line"></i> Email</a>
</aside>

<div class="main">
    <header>
        <h2><i class="ri-user-add-line"></i> Registro de Empleados y Usuarios</h2>
        <div>
            <span>👤 <?php echo $_SESSION['usuario']; ?></span> |
            <a href="cerrar_sesion.php">Cerrar sesión</a>
        </div>
    </header>
    <div class="top-menu">
        <a href="listar_empleados.php" class="top-button"><i class="ri-team-line"></i> Lista de empleados</a>
        <a href="listar_usuario.php"   class="top-button"><i class="ri-user-settings-line"></i> Lista de usuarios</a>
    </div>

    <div class="form-card">

        <?php if ($mensaje): ?><div class="msg-ok"><?= $mensaje ?></div><?php endif; ?>
        <?php if ($error):   ?><div class="msg-err"><?= $error ?></div><?php endif; ?>

        <!-- TABS -->
        <div class="tipo-tabs">
            <div class="tipo-tab <?= $tipo_registro=='completo'      ? 'activo':'' ?>" onclick="cambiarTipo('completo')">
                <i class="ri-user-add-line"></i>Empleado + Usuario
            </div>
            <div class="tipo-tab <?= $tipo_registro=='solo_empleado' ? 'activo':'' ?>" onclick="cambiarTipo('solo_empleado')">
                <i class="ri-id-card-line"></i>Solo Empleado
            </div>
            <div class="tipo-tab <?= $tipo_registro=='solo_usuario'  ? 'activo':'' ?>" onclick="cambiarTipo('solo_usuario')">
                <i class="ri-shield-user-line"></i>Solo Usuario Admin
            </div>
            <div class="tipo-tab <?= $tipo_registro=='vincular'      ? 'activo':'' ?>" onclick="cambiarTipo('vincular')">
                <i class="ri-links-line"></i>Vincular Existente
            </div>
        </div>

        <form method="POST">
        <input type="hidden" name="tipo_registro" id="tipo_registro" value="<?= htmlspecialchars($tipo_registro) ?>">

        <!-- PANEL: Empleado + Usuario -->
        <div id="panel_completo" class="<?= $tipo_registro=='completo' ? 'panel-visible':'panel-oculto' ?>">
            <div class="nota-info"><i class="ri-information-line"></i> Registra la ficha del empleado y su acceso en un solo paso. Quedan vinculados automáticamente.</div>
            <div class="seccion-titulo"><i class="ri-id-card-line"></i> Datos del Empleado</div>
            <div class="form-grid-2">
                <div><label>Cédula</label><input type="text" name="cedula"></div>
                <div><label>Fecha de ingreso</label><input type="date" name="fecha_ingreso"></div>
                <div><label>Nombre</label><input type="text" name="emp_nombre"></div>
                <div><label>Apellido</label><input type="text" name="emp_apellido"></div>
                <div><label>Teléfono</label><input type="text" name="telefono"></div>
                <div><label>Email</label><input type="email" name="emp_email"></div>
            </div>
            <label>Dirección</label><textarea name="direccion" rows="2"></textarea>
            <label>Salario base (Bs.)</label><input type="number" step="0.01" name="salario_base" value="0">
            <div class="seccion-form">
                <div class="seccion-titulo"><i class="ri-lock-password-line"></i> Credenciales de Acceso</div>
                <div class="form-grid-2">
                    <div><label>Nombre de usuario</label><input type="text" name="usuario" placeholder="Ej: jperez"></div>
                    <div><label>Contraseña</label><input type="password" name="contrasena"></div>
                </div>
                <label>Rol</label>
                <select name="cargo_id">
                    <?php foreach ($cargos_res as $c): ?>
                    <option value="<?= $c['cargo_id'] ?>"><?= htmlspecialchars($c['nombre_cargo']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit"><i class="ri-save-3-line"></i> Registrar Empleado y Usuario</button>
        </div>

        <!-- PANEL: Solo Empleado -->
        <div id="panel_solo_empleado" class="<?= $tipo_registro=='solo_empleado' ? 'panel-visible':'panel-oculto' ?>">
            <div class="nota-info"><i class="ri-information-line"></i> Registra la ficha sin darle acceso al sistema. Puedes vincularlo después desde "Vincular Existente".</div>
            <div class="seccion-titulo"><i class="ri-id-card-line"></i> Datos del Empleado</div>
            <div class="form-grid-2">
                <div><label>Cédula</label><input type="text" name="cedula"></div>
                <div><label>Fecha de ingreso</label><input type="date" name="fecha_ingreso"></div>
                <div><label>Nombre</label><input type="text" name="emp_nombre"></div>
                <div><label>Apellido</label><input type="text" name="emp_apellido"></div>
                <div><label>Teléfono</label><input type="text" name="telefono"></div>
                <div><label>Email</label><input type="email" name="emp_email"></div>
            </div>
            <label>Dirección</label><textarea name="direccion" rows="2"></textarea>
            <label>Salario base (Bs.)</label><input type="number" step="0.01" name="salario_base" value="0">
            <button type="submit"><i class="ri-save-3-line"></i> Registrar Solo Empleado</button>
        </div>

        <!-- PANEL: Solo Usuario Admin -->
        <div id="panel_solo_usuario" class="<?= $tipo_registro=='solo_usuario' ? 'panel-visible':'panel-oculto' ?>">
            <div class="nota-info"><i class="ri-information-line"></i> Crea un usuario con acceso al sistema sin ficha de empleado. Útil para roles administrativos externos.</div>
            <div class="seccion-titulo"><i class="ri-shield-user-line"></i> Datos del Usuario</div>
            <label>Nombre completo</label><input type="text" name="nombre_completo" placeholder="Nombre y Apellido">
            <div class="form-grid-2">
                <div><label>Nombre de usuario</label><input type="text" name="usuario" placeholder="Ej: admin2"></div>
                <div><label>Contraseña</label><input type="password" name="contrasena"></div>
            </div>
            <label>Rol</label>
            <select name="cargo_id">
                <?php foreach ($cargos_res as $c): ?>
                <option value="<?= $c['cargo_id'] ?>"><?= htmlspecialchars($c['nombre_cargo']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit"><i class="ri-save-3-line"></i> Crear Usuario Administrativo</button>
        </div>

        <!-- PANEL: Vincular Existente -->
        <div id="panel_vincular" class="<?= $tipo_registro=='vincular' ? 'panel-visible':'panel-oculto' ?>">
            <div class="nota-info"><i class="ri-information-line"></i> Crea un usuario y lo vincula a un empleado que ya existe pero no tiene acceso al sistema.</div>
            <div class="seccion-titulo"><i class="ri-links-line"></i> Empleado a Vincular</div>
            <label>Selecciona el empleado</label>
            <select name="empleado_existente">
                <option value="">— Selecciona —</option>
                <?php foreach ($sin_usuario as $e): ?>
                <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['nombre'].' '.$e['apellido']) ?> — C.I: <?= $e['cedula'] ?></option>
                <?php endforeach; ?>
            </select>
            <?php if (empty($sin_usuario)): ?>
                <p style="color:#28a745;font-size:13px;margin-top:8px;"><i class="ri-checkbox-circle-line"></i> Todos los empleados activos ya tienen usuario vinculado.</p>
            <?php endif; ?>
            <div class="seccion-form">
                <div class="seccion-titulo"><i class="ri-lock-password-line"></i> Credenciales</div>
                <div class="form-grid-2">
                    <div><label>Nombre de usuario</label><input type="text" name="usuario" placeholder="Ej: jperez"></div>
                    <div><label>Contraseña</label><input type="password" name="contrasena"></div>
                </div>
                <label>Rol</label>
                <select name="cargo_id">
                    <?php foreach ($cargos_res as $c): ?>
                    <option value="<?= $c['cargo_id'] ?>"><?= htmlspecialchars($c['nombre_cargo']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit"><i class="ri-links-line"></i> Vincular y Crear Usuario</button>
        </div>

        </form>
    </div>
</div>

<script>
const orden = ['completo','solo_empleado','solo_usuario','vincular'];
function cambiarTipo(tipo) {
    document.getElementById('tipo_registro').value = tipo;
    orden.forEach(t => {
        document.getElementById('panel_' + t).className = 'panel-oculto';
    });
    document.getElementById('panel_' + tipo).className = 'panel-visible';
    document.querySelectorAll('.tipo-tab').forEach((tab, i) => {
        tab.classList.toggle('activo', orden[i] === tipo);
    });
}
</script>
<?php mysqli_close($conexion); ?>
</body>
</html>