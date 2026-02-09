<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['cargo'] != 1) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cálculo de Prestaciones - RRHH</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
            padding: 6px 10px;
            border: 1px solid #e2e2e2;
            border-radius: 6px;
            font-size: 13px;
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
    <a href="administrador.php" class="active">
        <i class="ri-home-4-line"></i> Inicio
    </a>
    <a href="nomina.php">
        <i class="ri-money-dollar-circle-line"></i> Nómina
    </a>

    <a href=""><i class="ri-ball-pen-line"></i>Liquidacion</a>
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
       
    </div>

    <!-- CONTENIDO -->
    <div class="contenido" >
        <div class="max-w-5xl mx-auto bg-white rounded-xl shadow-md overflow-hidden border border-gray-100">
            
            <div class="bg-custom-dark p-3 text-white flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="ri-scales-3-line text-xl text-yellow-400"></i>
                    <h1 class="text-sm font-bold uppercase tracking-wide">Prestaciones Sociales (Art. 142 LOTTT)</h1>
                </div>
                <button onclick="window.print()" class="text-[11px] bg-white/10 hover:bg-white/20 border border-white/20 px-3 py-1 rounded transition">
                    <i class="ri-printer-line"></i> IMPRIMIR
                </button>
            </div>

            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    
                    <div class="space-y-3 bg-gray-50/50 p-4 rounded-lg border border-gray-100">
                        <h3 class="text-xs font-bold text-custom-dark uppercase border-b pb-1">Datos de Entrada</h3>
                        
                        <div>
                            <label class="block text-[10px] font-bold text-gray-400 mb-1 uppercase">Salario Integral (Bs.)</label>
                            <input type="number" id="salario_integral" class="liq-input" value="5000" oninput="calcularVenezuela()">
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 mb-1 uppercase">Años</label>
                                <input type="number" id="años" class="liq-input" value="1" oninput="calcularVenezuela()">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-gray-400 mb-1 uppercase">Meses</label>
                                <input type="number" id="meses" class="liq-input" value="0" oninput="calcularVenezuela()">
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-2 space-y-2">
                        <h3 class="text-xs font-bold text-custom-dark uppercase border-b pb-1">Desglose de Conceptos</h3>
                        
                        <div class="flex justify-between items-center p-2.5 border rounded-lg hover:bg-gray-50 transition">
                            <div>
                                <span class="text-[11px] font-bold text-gray-600 block uppercase">Garantía Trimestral</span>
                                <span id="res_adicionales" class="text-[9px] text-gray-400">+ 0.00 Adicionales (2d/año)</span>
                            </div>
                            <span id="res_garantia" class="font-bold text-gray-700">0.00 Bs.</span>
                        </div>

                        <div class="flex justify-between items-center p-2.5 border-l-4 border-l-green-600 border-y border-r rounded-r-lg bg-green-50/20">
                            <div>
                                <span class="text-[11px] font-bold text-green-700 block uppercase">Retroactividad (30D)</span>
                                <span class="text-[9px] text-green-600/60 italic">Cálculo basado en último salario</span>
                            </div>
                            <span id="res_retroactividad" class="font-bold text-gray-700">0.00 Bs.</span>
                        </div>

                        <div class="flex items-center gap-2 px-2 py-1 bg-blue-50/50 rounded border border-blue-100">
                            <i class="ri-information-line text-blue-500 text-xs"></i>
                            <p class="text-[9px] text-blue-600 leading-tight">Se aplicará automáticamente el monto más favorable para el trabajador.</p>
                        </div>
                    </div>
                </div>

                <div class="mt-5 bg-custom-dark rounded-lg p-4 text-white flex justify-between items-center shadow-inner relative overflow-hidden">
                    <div class="absolute right-0 opacity-5 -rotate-12 translate-x-4">
                        <i class="ri-scales-3-line text-7xl"></i>
                    </div>
                    
                    <div class="relative z-10">
                        <h2 class="text-[10px] uppercase opacity-70 tracking-widest font-bold">Monto Total a Liquidar</h2>
                        <p class="text-[9px] italic opacity-50">Sujeto a retenciones de ley</p>
                    </div>
                    
                    <div class="relative z-10 text-right">
                        <span class="text-xs mr-1 font-light uppercase opacity-80">Bs.</span>
                        <span id="monto_final" class="text-4xl font-black text-yellow-400 tracking-tighter">0.00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function calcularVenezuela() {
        const salario = parseFloat(document.getElementById('salario_integral').value) || 0;
        const anos = parseFloat(document.getElementById('años').value) || 0;
        const meses = parseFloat(document.getElementById('meses').value) || 0;
        const salarioDiario = salario / 30;

        // 1. Garantía
        const diasGarantia = (anos * 60) + (meses * 5);
        const totalGarantia = diasGarantia * salarioDiario;

        // 2. Adicionales
        let diasAdicionales = (anos > 1) ? Math.min((anos - 1) * 2, 30) : 0;
        const totalAdicionales = diasAdicionales * salarioDiario;

        // 3. Retroactividad
        let anosParaRetro = (meses >= 6) ? anos + 1 : anos;
        const totalRetroactividad = (anosParaRetro * 30) * salarioDiario;

        // Comparación
        const resultadoFinal = Math.max(totalGarantia + totalAdicionales, totalRetroactividad);

        const bsf = new Intl.NumberFormat('es-VE', { minimumFractionDigits: 2 });
        
        document.getElementById('res_garantia').innerText = bsf.format(totalGarantia) + " Bs.";
        document.getElementById('res_adicionales').innerText = "+ " + bsf.format(totalAdicionales) + " Adicionales (2d/año)";
        document.getElementById('res_retroactividad').innerText = bsf.format(totalRetroactividad) + " Bs.";
        document.getElementById('monto_final').innerText = bsf.format(resultadoFinal);
    }
    window.onload = calcularVenezuela;
</script>

</body>
</html>
