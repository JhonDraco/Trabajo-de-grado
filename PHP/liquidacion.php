<?php
include("seguridad.php");
verificarSesion();
bloquearSiNo(puedeAdministrador());

include("db.php");

$mensaje = "";
$error   = "";

/* =============================================
   BACKEND: PROCESAR LIQUIDACIÓN
   Se ejecuta cuando el form es enviado
   ============================================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['procesar_liquidacion'])) {

    $empleado_id       = (int)$_POST['empleado_id'];
    $salario_base      = floatval($_POST['salario_base']);
    $anos              = (int)$_POST['anos'];
    $meses             = (int)$_POST['meses'];
    $es_injustificado  = isset($_POST['despido_injustificado']) ? 1 : 0;
    $motivo            = mysqli_real_escape_string($conexion, $_POST['motivo'] ?? 'Retiro voluntario');

    // --- Recalcular en PHP para no depender del JS ---
    $salario_diario_base     = $salario_base / 30;
    $factor_integral         = 1 + (30/360) + (15/360);
    $salario_diario_integral = ($salario_base * $factor_integral) / 30;

    // Art. 142 A/B — Garantía acumulada
    $dias_garantia    = ($anos * 60) + ($meses * 5);
    $total_garantia   = $dias_garantia * $salario_diario_integral;
    $dias_adicionales = ($anos > 1) ? min(($anos - 1) * 2, 30) : 0;
    $total_adicionales = $dias_adicionales * $salario_diario_integral;

    // Art. 142 C — Retroactividad
    $anos_retro          = ($meses >= 6) ? $anos + 1 : $anos;
    $total_retroactividad = ($anos_retro * 30) * $salario_diario_integral;

    // Comparación Art. 142 — el mayor
    $prestaciones = max($total_garantia + $total_adicionales, $total_retroactividad);

    // Fraccionados
    $utilidades_prop = ($meses * 2.5) * $salario_diario_base;
    $bono_vac_prop   = ($meses * 1.25) * $salario_diario_base;

    // Indemnización Art. 92
    $indemnizacion = $es_injustificado ? $prestaciones : 0;

    $total_liquidacion = $prestaciones + $utilidades_prop + $bono_vac_prop + $indemnizacion;

    mysqli_begin_transaction($conexion);

    try {

        // 1. Registrar la liquidación
        $sql_liq = "INSERT INTO liquidaciones 
                    (empleado_id, salario_base, anos_servicio, meses_adicionales,
                     prestaciones_sociales, utilidades_prop, bono_vacacional_prop,
                     indemnizacion, total_liquidacion, motivo, despido_injustificado, fecha_liquidacion)
                    VALUES 
                    ($empleado_id, $salario_base, $anos, $meses,
                     $prestaciones, $utilidades_prop, $bono_vac_prop,
                     $indemnizacion, $total_liquidacion, '$motivo', $es_injustificado, NOW())";

        mysqli_query($conexion, $sql_liq);

        // 2. Salida Segura — marcar empleado como Inactivo
        mysqli_query($conexion,
            "UPDATE empleados SET estado = 'Inactivo' WHERE id = $empleado_id"
        );

        mysqli_commit($conexion);
        $mensaje = "✅ Liquidación procesada correctamente. El empleado ha sido marcado como Inactivo.";

    } catch (Exception $e) {
        mysqli_rollback($conexion);
        $error = "❌ Error al procesar: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Liquidación - RRHH</title>
<link rel="stylesheet" href="../css/administrador.css">
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
<style>
    :root { --green-dark:#1f3a34; --green-mid:#2b4a42; }

    .liq-panel {
        max-width: 900px;
        margin: 0 auto;
        background: white;
        border-radius: 12px;
        box-shadow: 0 6px 24px rgba(0,0,0,0.1);
        overflow: hidden;
        border: 1px solid #e2e2e2;
    }
    .liq-header {
        background: var(--green-dark);
        color: white;
        padding: 16px 24px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .liq-header-icon {
        background: #f6c90e;
        border-radius: 8px;
        width: 36px; height: 36px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; color: var(--green-dark);
        flex-shrink: 0;
    }
    .liq-header h1  { font-size: 14px; font-weight: 700; letter-spacing: 0.05em; }
    .liq-header p   { font-size: 11px; opacity: 0.65; margin-top: 2px; }

    .liq-body { display: grid; grid-template-columns: 1fr 1fr; gap: 0; }
    .liq-col  { padding: 24px; }
    .liq-col-left  { background: #f9f9f9; border-right: 1px solid #eee; }
    .liq-col-right { background: white; }

    .liq-section-title {
        font-size: 11px; font-weight: 700; color: var(--green-dark);
        text-transform: uppercase; letter-spacing: 0.08em;
        margin-bottom: 14px;
        display: flex; align-items: center; gap: 6px;
    }
    .liq-label {
        font-size: 11px; font-weight: 600; color: #777;
        text-transform: uppercase; letter-spacing: 0.05em;
        display: block; margin-bottom: 4px;
    }
    .liq-input {
        width: 100%; padding: 9px 12px;
        border: 2px solid #e2e2e2; border-radius: 6px;
        font-size: 14px; outline: none; transition: 0.2s;
        background: white;
    }
    .liq-input:focus { border-color: var(--green-mid); }
    .liq-input[readonly] { background: #f3f3f3; color: #555; }
    .liq-group { margin-bottom: 14px; }
    .liq-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

    .check-despido {
        display: flex; align-items: center; gap: 8px;
        background: #fff8dc; border: 1px solid #f0d060;
        border-radius: 8px; padding: 10px 14px;
        margin-bottom: 14px; cursor: pointer;
        font-size: 11px; font-weight: 700;
        color: #7a5c00; text-transform: uppercase;
    }
    .check-despido input { width: 16px; height: 16px; accent-color: var(--green-dark); }

    .desglose-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 10px 14px;
        border: 1px solid #eee; border-radius: 8px;
        margin-bottom: 8px; font-size: 13px;
        background: white;
        transition: border-color 0.2s;
    }
    .desglose-row:hover { border-color: #ccc; }
    .desglose-row .concepto { font-weight: 600; color: #444; font-size: 11px; text-transform: uppercase; }
    .desglose-row .sub      { font-size: 10px; color: #aaa; margin-top: 2px; }
    .desglose-row .monto    { font-weight: 700; color: #1f3a34; font-size: 13px; }

    .desglose-row.retro   { border-left: 4px solid #28a745; background: #f0fff4; }
    .desglose-row.indem   { border-left: 4px solid #dc3545; background: #fff5f5; }
    .desglose-row.indem .concepto { color: #c0392b; }

    .nota-legal {
        background: #eef4ff; border: 1px solid #c8d8f8;
        border-radius: 8px; padding: 10px 14px;
        font-size: 11px; color: #2c5282; margin: 12px 0;
        display: flex; gap: 8px;
    }

    .total-box {
        background: var(--green-dark);
        border-radius: 10px; padding: 18px 20px;
        color: white; margin-top: 14px;
        border-bottom: 4px solid #f6c90e;
    }
    .total-box .label { font-size: 10px; font-weight: 700; color: #f6c90e; text-transform: uppercase; letter-spacing: 0.08em; }
    .total-box .monto { font-size: 32px; font-weight: 900; letter-spacing: -1px; margin-top: 4px; }
    .total-box .bs    { font-size: 13px; opacity: 0.6; margin-left: 4px; }

    .btn-procesar {
        width: 100%; padding: 12px;
        background: var(--green-dark); color: white;
        border: none; border-radius: 8px;
        font-size: 14px; font-weight: 700;
        cursor: pointer; margin-top: 14px;
        display: flex; align-items: center; justify-content: center; gap: 8px;
        transition: background 0.2s;
    }
    .btn-procesar:hover { background: var(--green-mid); }
    .btn-procesar:disabled { background: #aaa; cursor: not-allowed; }

    .msg-ok  { background:#d4edda; color:#155724; border:1px solid #c3e6cb; border-radius:8px; padding:12px 16px; margin-bottom:16px; font-weight:600; }
    .msg-err { background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; border-radius:8px; padding:12px 16px; margin-bottom:16px; font-weight:600; }

    /* Campos ocultos que se llenan por PHP/JS */
    .input-hidden { display: none; }
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
    <a href="nomina.php"><i class="ri-money-dollar-circle-line"></i> Nómina</a>
    <a href="liquidacion.php" class="active"><i class="ri-ball-pen-line"></i> Liquidación</a>
    <a href="vacaciones.php"><i class="ri-sun-line"></i> Vacaciones</a>
    <a href="listar_empleados.php"><i class="ri-team-line"></i> Empleados</a>
    <a href="listar_usuario.php"><i class="ri-user-settings-line"></i> Roles</a>
    <a href="reportes.php"><i class="ri-bar-chart-line"></i> Reportes</a>
    <a href="contactar.php"><i class="ri-mail-line"></i> Email</a>
</aside>

<div class="main">
    <header>
        <h2><i class="ri-ball-pen-line"></i> Liquidación de Prestaciones</h2>
        <div>
            <span>👤 <?php echo $_SESSION['usuario']; ?></span> |
            <a href="cerrar_sesion.php">Cerrar sesión</a>
        </div>
    </header>

    <div class="top-menu"></div>

    <div class="contenido">

        <?php if ($mensaje): ?>
            <div class="msg-ok"><?= $mensaje ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="msg-err"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" id="form-liquidacion">
        <input type="hidden" name="procesar_liquidacion" value="1">
        <input type="hidden" name="empleado_id"   id="hidden_empleado_id">
        <input type="hidden" name="salario_base"  id="hidden_salario_base">
        <input type="hidden" name="anos"          id="hidden_anos">
        <input type="hidden" name="meses"         id="hidden_meses">

        <div class="liq-panel">

            <!-- HEADER -->
            <div class="liq-header">
                <div class="liq-header-icon"><i class="ri-scales-3-line"></i></div>
                <div>
                    <h1>Cálculo de Prestaciones Sociales</h1>
                    <p>Liquidación según Art. 142 LOTTT — Venezuela</p>
                </div>
            </div>

            <div class="liq-body">

                <!-- COLUMNA IZQUIERDA: DATOS -->
                <div class="liq-col liq-col-left">

                    <div class="liq-section-title">
                        <i class="ri-user-settings-line"></i> Datos del Trabajador
                    </div>

                    <label class="check-despido">
                        <input type="checkbox" id="despido_injustificado" name="despido_injustificado" onchange="calcular()">
                        ¿Despido Injustificado? — Activa Art. 92 (Doble)
                    </label>

                    <div class="liq-group">
                        <label class="liq-label">Cédula de Identidad</label>
                        <input type="number" id="cedula" class="liq-input" placeholder="Ej: 20123456">
                        <div id="cedula-status" style="font-size:11px; color:#888; margin-top:4px;"></div>
                    </div>

                    <div class="liq-grid-2 liq-group">
                        <div>
                            <label class="liq-label">Nombre</label>
                            <input type="text" id="nombre" class="liq-input" readonly placeholder="...">
                        </div>
                        <div>
                            <label class="liq-label">Apellido</label>
                            <input type="text" id="apellido" class="liq-input" readonly placeholder="...">
                        </div>
                    </div>

                    <div class="liq-group">
                        <label class="liq-label">Salario Base Mensual (Bs.)</label>
                        <input type="number" id="salario_integral" class="liq-input" value="0" step="0.01" oninput="calcular()">
                    </div>

                    <div class="liq-grid-2 liq-group">
                        <div>
                            <label class="liq-label">Años de servicio</label>
                            <input type="number" id="anos" class="liq-input" value="0" oninput="calcular()">
                        </div>
                        <div>
                            <label class="liq-label">Meses adicionales</label>
                            <input type="number" id="meses" class="liq-input" value="0" oninput="calcular()">
                        </div>
                    </div>

                    <div class="liq-group">
                        <label class="liq-label">Motivo de egreso</label>
                        <select name="motivo" class="liq-input">
                            <option value="Retiro voluntario">Retiro voluntario</option>
                            <option value="Despido justificado">Despido justificado</option>
                            <option value="Despido injustificado">Despido injustificado</option>
                            <option value="Mutuo acuerdo">Mutuo acuerdo</option>
                            <option value="Jubilación">Jubilación</option>
                        </select>
                    </div>

                </div>

                <!-- COLUMNA DERECHA: DESGLOSE -->
                <div class="liq-col liq-col-right">

                    <div class="liq-section-title">
                        <i class="ri-file-list-3-line"></i> Desglose de la Liquidación
                    </div>

                    <!-- Garantía -->
                    <div class="desglose-row">
                        <div>
                            <div class="concepto">Garantía de Prestaciones (Art. 142 A/B)</div>
                            <div class="sub" id="sub_garantia">5 días/mes — + adicionales por antigüedad</div>
                        </div>
                        <div class="monto" id="res_garantia">0,00 Bs.</div>
                    </div>

                    <!-- Retroactividad -->
                    <div class="desglose-row retro">
                        <div>
                            <div class="concepto" style="color:#155724;">Retroactividad (Art. 142 C)</div>
                            <div class="sub">30 días × año trabajado — se usa si es mayor</div>
                        </div>
                        <div class="monto" id="res_retroactividad">0,00 Bs.</div>
                    </div>

                    <!-- Vacaciones fraccionadas -->
                    <div class="desglose-row">
                        <div>
                            <div class="concepto">Vacaciones Fraccionadas</div>
                            <div class="sub">Días proporcionales al período trabajado</div>
                        </div>
                        <div class="monto" id="res_vacaciones">0,00 Bs.</div>
                    </div>

                    <!-- Bono vacacional fraccionado -->
                    <div class="desglose-row">
                        <div>
                            <div class="concepto">Bono Vacacional Fraccionado</div>
                            <div class="sub">1.25 días por mes desde último aniversario</div>
                        </div>
                        <div class="monto" id="res_bonovac">0,00 Bs.</div>
                    </div>

                    <!-- Utilidades fraccionadas -->
                    <div class="desglose-row">
                        <div>
                            <div class="concepto">Utilidades Fraccionadas</div>
                            <div class="sub">2.5 días por mes trabajado en el año</div>
                        </div>
                        <div class="monto" id="res_utilidades">0,00 Bs.</div>
                    </div>

                    <!-- Indemnización Art. 92 -->
                    <div class="desglose-row indem" id="fila_indem" style="display:none;">
                        <div>
                            <div class="concepto">Indemnización Art. 92 — "La Doble"</div>
                            <div class="sub">Igual al monto de prestaciones sociales</div>
                        </div>
                        <div class="monto" id="res_indemnizacion">0,00 Bs.</div>
                    </div>

                    <!-- Nota legal -->
                    <div class="nota-legal">
                        <i class="ri-information-fill" style="flex-shrink:0; margin-top:1px;"></i>
                        <span>Se compara automáticamente la <strong>Garantía vs Retroactividad</strong> y se aplica siempre el monto mayor según Art. 142 LOTTT.</span>
                    </div>

                    <!-- Total -->
                    <div class="total-box">
                        <div class="label">Total Neto a Pagar</div>
                        <div>
                            <span class="monto" id="monto_final">0,00</span>
                            <span class="bs">Bs.</span>
                        </div>
                    </div>

                    <button type="submit" class="btn-procesar" id="btn-procesar" disabled>
                        <i class="ri-check-double-line"></i>
                        Procesar Liquidación y Marcar como Inactivo
                    </button>

                </div>
            </div><!-- fin liq-body -->
        </div><!-- fin liq-panel -->
        </form>

    </div><!-- fin contenido -->
</div><!-- fin main -->

<script>
const fmt = new Intl.NumberFormat('es-VE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

function calcular() {
    const salario = parseFloat(document.getElementById('salario_integral').value) || 0;
    const anos    = parseInt(document.getElementById('anos').value) || 0;
    const meses   = parseInt(document.getElementById('meses').value) || 0;
    const injust  = document.getElementById('despido_injustificado').checked;

    if (salario === 0 && anos === 0 && meses === 0) {
        document.getElementById('monto_final').innerText = '0,00';
        return;
    }

    // Salarios diarios
    const factorIntegral     = 1 + (30/360) + (15/360);
    const salarioDiarioBase  = salario / 30;
    const salarioDiarioInt   = (salario * factorIntegral) / 30;

    // Art. 142 A/B — Garantía
    const diasGarantia    = (anos * 60) + (meses * 5);
    const totalGarantia   = diasGarantia * salarioDiarioInt;
    const diasAdic        = (anos > 1) ? Math.min((anos - 1) * 2, 30) : 0;
    const totalAdic       = diasAdic * salarioDiarioInt;

    // Art. 142 C — Retroactividad
    const anosRetro       = (meses >= 6) ? anos + 1 : anos;
    const totalRetro      = (anosRetro * 30) * salarioDiarioInt;

    // Prestaciones = mayor entre ambos
    const prestaciones    = Math.max(totalGarantia + totalAdic, totalRetro);

    // Fraccionados
    const mesesBase       = meses > 0 ? meses : 0;
    const vacFrac         = (mesesBase * 1.25) * salarioDiarioBase;  // Vacaciones
    const bonoFrac        = (mesesBase * 1.25) * salarioDiarioBase;  // Bono vacacional
    const utilFrac        = (mesesBase * 2.5)  * salarioDiarioBase;  // Utilidades

    // Art. 92 — Indemnización
    const indem = injust ? prestaciones : 0;

    const total = prestaciones + vacFrac + bonoFrac + utilFrac + indem;

    // Mostrar
    document.getElementById('res_garantia').innerText      = fmt.format(totalGarantia + totalAdic) + ' Bs.';
    document.getElementById('sub_garantia').innerText      = diasGarantia + ' días garantía + ' + diasAdic + ' días adicionales';
    document.getElementById('res_retroactividad').innerText = fmt.format(totalRetro) + ' Bs.' + (totalRetro > totalGarantia + totalAdic ? ' ✓ aplicado' : '');
    document.getElementById('res_vacaciones').innerText    = fmt.format(vacFrac) + ' Bs.';
    document.getElementById('res_bonovac').innerText       = fmt.format(bonoFrac) + ' Bs.';
    document.getElementById('res_utilidades').innerText    = fmt.format(utilFrac) + ' Bs.';
    document.getElementById('monto_final').innerText       = fmt.format(total);

    // Indemnización: mostrar/ocultar fila
    document.getElementById('fila_indem').style.display = injust ? 'flex' : 'none';
    document.getElementById('res_indemnizacion').innerText = fmt.format(indem) + ' Bs.';

    // Sincronizar hidden inputs para el POST
    document.getElementById('hidden_salario_base').value = salario;
    document.getElementById('hidden_anos').value         = anos;
    document.getElementById('hidden_meses').value        = meses;

    // Habilitar botón solo si hay empleado cargado
    const hayEmpleado = document.getElementById('hidden_empleado_id').value !== '';
    document.getElementById('btn-procesar').disabled = !hayEmpleado;
}

// Buscar empleado por cédula
document.getElementById('cedula').addEventListener('blur', function () {
    const cedula = this.value.trim();
    if (cedula.length < 4) return;

    document.getElementById('cedula-status').innerText = 'Buscando...';

    fetch('buscar_empleado.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'cedula=' + encodeURIComponent(cedula)
    })
    .then(r => r.json())
    .then(data => {
        if (data && data.nombre) {
            document.getElementById('nombre').value          = data.nombre;
            document.getElementById('apellido').value        = data.apellido;
            document.getElementById('salario_integral').value = data.salario_base;
            document.getElementById('hidden_empleado_id').value = data.id ?? '';
            document.getElementById('cedula-status').innerText  = '✅ Empleado encontrado';

            if (data.fecha_ingreso) {
                const inicio = new Date(data.fecha_ingreso);
                const hoy    = new Date();
                let anos  = hoy.getFullYear() - inicio.getFullYear();
                let meses = hoy.getMonth()    - inicio.getMonth();
                if (meses < 0) { anos--; meses += 12; }
                document.getElementById('anos').value  = anos;
                document.getElementById('meses').value = meses;
            }
            calcular();
        } else {
            document.getElementById('cedula-status').innerText = '⚠️ Empleado no encontrado';
            document.getElementById('nombre').value   = '';
            document.getElementById('apellido').value = '';
            document.getElementById('hidden_empleado_id').value = '';
        }
    })
    .catch(() => {
        document.getElementById('cedula-status').innerText = '❌ Error al buscar';
    });
});

window.onload = calcular;
</script>
</body>
</html>