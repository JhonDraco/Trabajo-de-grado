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
<link rel="stylesheet" href="../css/asignaciones.css">
<!-- Iconos RemixIcon -->
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
<title>Cálculo de Prestaciones - RRHH</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    corePlugins: {
      preflight: false,
    }
  }
</script>
<link rel="stylesheet" href="../css/administrador.css">
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">
<style>
        :root {
            --green-dark: #1f3a34;
            --green-mid: #2b4a42;
        }
        .bg-custom-dark { background-color: var(--green-dark); }
        .text-custom-dark { color: var(--green-dark); }
        
        /* Inputs más compactos */
        .liq-input {
            width: 100%;
            padding: 9px 15px;
            border: 2px solid #e2e2e2;
            border-radius: 4px;
            font-size: 14px;
            outline: none;
            transition: 0.2s;
        }
        .liq-input:focus { border-color: var(--green-mid); box-shadow: 0 0 0 2px rgba(43, 74, 66, 0.1); }
    </style>

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

    <a href="liquidacion.php"class="active"><i class="ri-ball-pen-line"></i>Liquidacion</a>
    <a href="vacaciones.php">  <i class="ri-sun-line"></i></i> Vacaciones</a>
    
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
        <a href="" class="top-button">Despido injustificado</a>

    </div>

<div class="contenido px-4 py-6">
    <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
        
        <div class="bg-custom-dark px-4 py-3 text-white flex items-center justify-between shadow-md">
            <div class="flex items-center gap-3">
                <div class="bg-yellow-400 p-1.5 rounded-lg">
                    <i class="ri-scales-3-line text-lg text-custom-dark"></i>
                </div>
                <div>
                    <h1 class="text-xs font-bold uppercase tracking-wider">Cálculo de Prestaciones Sociales</h1>
                    <p class="text-[9px] opacity-70 uppercase">Liquidación según Art. 142 LOTTT</p>
                </div>
            </div>
            <div class="text-right">
                <span class="text-[10px] bg-white/10 px-2 py-1 rounded border border-white/20 uppercase font-semibold">Borrador Interno</span>
            </div>
        </div>

        <div class="p-5">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-5">
                
                <div class="md:col-span-5 space-y-4 bg-gray-50 p-4 rounded-xl border border-gray-100">
                    <div class="flex items-center gap-2 mb-1">
                        <i class="ri-user-settings-fill text-custom-dark text-sm"></i>
                        <h3 class="text-xs font-bold text-custom-dark uppercase tracking-tight">Información del Trabajador</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-3">
                        <div>
                            <label class="block text-[9px] font-bold text-gray-500 mb-1 uppercase tracking-tighter">Número de Cédula</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 text-xs">V-</span>
                                <input type="number" id="cedula" class="liq-input pl-7 font-bold text-custom-dark" placeholder="Ej: 20123456">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[9px] font-bold text-gray-500 mb-1 uppercase tracking-tighter">Nombre</label>
                                <input type="text" id="nombre" class="liq-input bg-white" readonly placeholder="...">
                            </div>
                            <div>
                                <label class="block text-[9px] font-bold text-gray-500 mb-1 uppercase tracking-tighter">Apellido</label>
                                <input type="text" id="apellido" class="liq-input bg-white" readonly placeholder="...">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[9px] font-bold text-gray-500 mb-1 uppercase tracking-tighter">Salario Integral Mensual (Bs.)</label>
                            <input type="number" id="salario_integral" class="liq-input font-bold text-green-700" value="5000" oninput="calcularVenezuela()">
                        </div>

                        <div class="grid grid-cols-2 gap-2 p-2 bg-white rounded-lg border border-gray-200">
                            <div>
                                <label class="block text-[9px] font-bold text-gray-400 mb-1 uppercase text-center italic">Años de Serv.</label>
                                <input type="number" id="años" class="liq-input text-center font-bold" value="1" oninput="calcularVenezuela()">
                            </div>
                            <div>
                                <label class="block text-[9px] font-bold text-gray-400 mb-1 uppercase text-center italic">Meses Adic.</label>
                                <input type="number" id="meses" class="liq-input text-center font-bold" value="0" oninput="calcularVenezuela()">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-7 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <i class="ri-file-list-3-line text-custom-dark text-sm"></i>
                            <h3 class="text-xs font-bold text-custom-dark uppercase tracking-tight">Desglose de la Liquidación</h3>
                        </div>
                        
                        <div class="space-y-2">
                            <div class="group flex justify-between items-center p-3 border border-gray-100 rounded-lg hover:border-gray-300 hover:bg-white transition-all shadow-sm">
                                <div class="flex flex-col">
                                    <span class="text-[10px] font-bold text-gray-600 uppercase">Garantía de Prestaciones</span>
                                    <span id="res_adicionales" class="text-[9px] text-gray-400 flex items-center gap-1">
                                        <i class="ri-add-circle-line"></i> + 0.00 Adicionales (Art. 142 B)
                                    </span>
                                </div>
                                <div class="text-right">
                                    <span id="res_garantia" class="text-xs font-bold text-gray-700">0.00 Bs.</span>
                                </div>
                            </div>

                            <div class="flex justify-between items-center p-3 border-l-4 border-l-green-600 border-y border-r rounded-r-lg bg-green-50/30 shadow-sm">
                                <div class="flex flex-col">
                                    <span class="text-[10px] font-bold text-green-800 uppercase">Cálculo Retroactivo (Art. 142 C)</span>
                                    <span class="text-[9px] text-green-600 font-medium italic">30 días por cada año trabajado</span>
                                </div>
                                <div class="text-right">
                                    <span id="res_retroactividad" class="text-xs font-bold text-gray-700">0.00 Bs.</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 flex items-start gap-2 p-3 bg-blue-50 rounded-lg border border-blue-100">
                            <i class="ri-information-fill text-blue-500 text-sm"></i>
                            <p class="text-[9px] text-blue-700 leading-normal">
                                <strong>Nota legal:</strong> El sistema compara automáticamente la garantía acumulada frente a la retroactividad, seleccionando siempre el monto mayor para proteger el beneficio del trabajador según la LOTTT.
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 bg-custom-dark rounded-xl p-4 text-white shadow-lg relative overflow-hidden border-b-4 border-yellow-400">
                        <div class="absolute right-[-10px] top-[-10px] opacity-10 rotate-12">
                            <i class="ri-money-dollar-box-line text-8xl"></i>
                        </div>
                        
                        <div class="flex justify-between items-end relative z-10">
                            <div>
                                <p class="text-[10px] uppercase font-bold text-yellow-400 mb-1">Total Neto a Pagar</p>
                                <div class="flex items-baseline gap-1">
                                    <span class="text-3xl font-black tracking-tighter" id="monto_final">0,00</span>
                                    <span class="text-xs font-bold opacity-60">Bs.</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <button onclick="window.print()" class="bg-white/10 hover:bg-white/20 p-2 rounded-lg transition border border-white/20">
                                    <i class="ri-printer-line text-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
// 1. Función para calcular tiempo (Mantenla igual)
function calcularTiempoServicio(fechaIngreso) {
    const inicio = new Date(fechaIngreso);
    const fin = new Date(); 
    let años = fin.getFullYear() - inicio.getFullYear();
    let meses = fin.getMonth() - inicio.getMonth();

    if (meses < 0) {
        años--;
        meses += 12;
    }
    document.getElementById('años').value = años;
    document.getElementById('meses').value = meses;
}

// 2. Función de cálculo de dinero (Mantenla igual)
function calcularVenezuela() {
    const salario = parseFloat(document.getElementById('salario_integral').value) || 0;
    const anos = parseFloat(document.getElementById('años').value) || 0;
    const meses = parseFloat(document.getElementById('meses').value) || 0;
    const salarioDiario = salario / 30;

    const diasGarantia = (anos * 60) + (meses * 5);
    const totalGarantia = diasGarantia * salarioDiario;

    let diasAdicionales = (anos > 1) ? Math.min((anos - 1) * 2, 30) : 0;
    const totalAdicionales = diasAdicionales * salarioDiario;

    let anosParaRetro = (meses >= 6) ? anos + 1 : anos;
    const totalRetroactividad = (anosParaRetro * 30) * salarioDiario;

    const resultadoFinal = Math.max(totalGarantia + totalAdicionales, totalRetroactividad);
    const bsf = new Intl.NumberFormat('es-VE', { minimumFractionDigits: 2 });
    
    document.getElementById('res_garantia').innerText = bsf.format(totalGarantia) + " Bs.";
    document.getElementById('res_adicionales').innerText = "+ " + bsf.format(totalAdicionales) + " Adicionales (2d/año)";
    document.getElementById('res_retroactividad').innerText = bsf.format(totalRetroactividad) + " Bs.";
    document.getElementById('monto_final').innerText = bsf.format(resultadoFinal);
}

// 3. EVENTOS: Esto es lo que debes corregir
window.onload = function() {
    // Ejecutar cálculo inicial
    calcularVenezuela();

    // Configurar el buscador de cédula
    const inputCedula = document.getElementById('cedula');
    
    inputCedula.addEventListener('blur', function() {
        let cedula = this.value;
        if (cedula.length > 4) {
            fetch('buscar_empleado.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'cedula=' + encodeURIComponent(cedula)
            })
    .then(response => response.json())
.then(data => {
    console.log("Datos exactos de la BD:", data);

    if (data && data.nombre) {
        // Asignamos usando los nombres exactos que viste en la consola
        document.getElementById('nombre').value = data.nombre;
        document.getElementById('apellido').value = data.apellido;
        
        // Aquí estaba el error: cambiamos salario_integral por salario_base
        document.getElementById('salario_integral').value = data.salario_base;
        
        if(data.fecha_ingreso) {
            calcularTiempoServicio(data.fecha_ingreso);
        }
        
        // Ejecutamos el cálculo de la liquidación
        calcularVenezuela(); 
    } else {
        alert("Empleado no encontrado");
    }
})            .catch(error => console.error('Error en fetch:', error));
        }
    });
};
</script>

</body>
</html>































