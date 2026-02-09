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
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="../css/administrador.css">
<!-- Iconos RemixIcon -->
<link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet">


</head>
<body class="bg-gray-100 p-4">

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
        <a href="" class="top-button">Función de nuestro sistema</a>
        <a href="" class="top-button">Propósito</a>
        <a href="" class="top-button">Visión</a>
    </div>

    <!-- CONTENIDO -->
    <div class="contenido">
        <div class="max-w-4xl mx-auto bg-white rounded-xl shadow-xl overflow-hidden border border-gray-300">
        
        <div class="bg-yellow-500 p-4 border-b-4 border-blue-600">
            <h1 class="text-center text-xl font-extrabold text-blue-900 uppercase tracking-tighter">
                Cálculo de Prestaciones Sociales (Art. 142 LOTTT)
            </h1>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="space-y-4 bg-gray-50 p-4 rounded-lg">
                    <h3 class="font-bold text-gray-700 border-b">Datos del Trabajador</h3>
                    
                    <div>
                        <label class="block text-sm font-medium">Último Salario Integral (Bs.)</label>
                        <input type="number" id="salario_integral" class="w-full p-2 border rounded" value="5000" oninput="calcularVenezuela()">
                        <p class="text-[10px] text-gray-500 italic">*Incluye alícuota de bonos y utilidades.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Años de Servicio</label>
                        <input type="number" id="años" class="w-full p-2 border rounded" value="1" oninput="calcularVenezuela()">
                    </div>

                    <div>
                        <label class="block text-sm font-medium">Fracción meses (si aplica)</label>
                        <input type="number" id="meses" class="w-full p-2 border rounded" value="0" oninput="calcularVenezuela()">
                    </div>
                </div>

                <div class="space-y-4">
                    <h3 class="font-bold text-gray-700 border-b">Cálculo de Garantía (Literal A y B)</h3>
                    
                    <div class="flex justify-between items-center bg-blue-50 p-3 rounded">
                        <span class="text-sm">Garantía Trimestral (15 días x Trimestre)</span>
                        <span id="res_garantia" class="font-bold text-blue-700">0.00 Bs.</span>
                    </div>

                    <div class="flex justify-between items-center bg-blue-50 p-3 rounded">
                        <span class="text-sm">Días Adicionales (2 días por año)</span>
                        <span id="res_adicionales" class="font-bold text-blue-700">0.00 Bs.</span>
                    </div>

                    <div class="flex justify-between items-center bg-green-50 p-3 rounded border border-green-200">
                        <span class="text-sm font-bold">Retroactividad (Literal C - 30 días/año)</span>
                        <span id="res_retroactividad" class="font-bold text-green-700">0.00 Bs.</span>
                    </div>
                </div>
            </div>

            <div class="bg-amber-50 p-4 rounded-lg border-l-4 border-amber-400 mb-6">
                <p class="text-xs text-amber-800">
                    <strong>Nota Legal:</strong> Según el Art. 142, el trabajador recibe lo que sea más favorable entre el acumulado de garantía (trimestral + adicionales) y el cálculo retroactivo (30 días por año al último salario).
                </p>
            </div>

            <div class="bg-blue-900 text-white p-6 rounded-xl flex justify-between items-center">
                <div>
                    <h2 class="text-lg opacity-80 uppercase">Monto Total a Pagar</h2>
                    <p class="text-xs text-blue-300">Basado en el cálculo más favorable para el trabajador</p>
                </div>
                <div class="text-4xl font-black text-yellow-400" id="monto_final">
                    0.00 Bs.
                </div>
            </div>
        </div>
    </div>

    <script>
        function calcularVenezuela() {
            const salario = parseFloat(document.getElementById('salario_integral').value) || 0;
            const anos = parseFloat(document.getElementById('años').value) || 0;
            const meses = parseFloat(document.getElementById('meses').value) || 0;

            // 1. Cálculo de Garantía Trimestral (Aprox 60 días al año)
            const diasGarantia = anos * 60 + (meses * 5);
            const totalGarantia = diasGarantia * (salario / 30);

            // 2. Días Adicionales (2 días por cada año después del primero, máx 30)
            let diasAdicionales = 0;
            if (anos > 1) {
                diasAdicionales = Math.min((anos - 1) * 2, 30);
            }
            const totalAdicionales = diasAdicionales * (salario / 30);

            // 3. Retroactividad (30 días por cada año o fracción mayor a 6 meses)
            let anosParaRetro = anos;
            if (meses >= 6) anosParaRetro += 1;
            const totalRetroactividad = (anosParaRetro * 30) * (salario / 30);

            // 4. Comparativo (Lo que más beneficie al trabajador)
            const acumuladoGarantia = totalGarantia + totalAdicionales;
            const resultadoFinal = Math.max(acumuladoGarantia, totalRetroactividad);

            // Formateo
            const bsf = new Intl.NumberFormat('es-VE', { minimumFractionDigits: 2 });
            
            document.getElementById('res_garantia').innerText = bsf.format(totalGarantia) + " Bs.";
            document.getElementById('res_adicionales').innerText = bsf.format(totalAdicionales) + " Bs.";
            document.getElementById('res_retroactividad').innerText = bsf.format(totalRetroactividad) + " Bs.";
            document.getElementById('monto_final').innerText = bsf.format(resultadoFinal) + " Bs.";
        }

        window.onload = calcularVenezuela;
    </script>

    </div>
</div>

</body>
</html>
