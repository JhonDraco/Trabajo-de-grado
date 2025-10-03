<?php
// index.php
// Menú de botones para sistema de nómina

$menuItems = [
    ['id'=>'nominas',   'label'=>'Nóminas',         'icon'=>'💵'],
    ['id'=>'empleados', 'label'=>'Registrar Empleado','icon'=>'🧑‍💼'],
    ['id'=>'asistencias','label'=>'Asistencias',   'icon'=>'📅'],
    ['id'=>'reportes',  'label'=>'Reportes',        'icon'=>'📊'],
    ['id'=>'deducciones','label'=>'Deducciones',   'icon'=>'➖'],
    ['id'=>'ajustes',   'label'=>'Configuración',   'icon'=>'⚙️'],
];
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Sistema de Nómina - Menú</title>
<style>
  body{
    margin:0;
    font-family:Arial, sans-serif;
    background:#f0f2f5;
    display:flex;
    justify-content:center;
    align-items:center;
    min-height:100vh;
  }
  .menu-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(120px,1fr));
    gap:18px;
    width:100%;
    max-width:500px;
    padding:20px;
  }
  .menu-btn{
    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:center;
    padding:22px;
    background:white;
    border-radius:18px;
    text-decoration:none;
    color:#333;
    font-weight:600;
    font-size:14px;
    box-shadow:0 4px 8px rgba(0,0,0,0.08);
    transition:transform 0.15s ease, box-shadow 0.15s ease;
  }
  .menu-btn span.icon{
    font-size:32px;
    margin-bottom:10px;
  }
  .menu-btn:hover{
    transform:translateY(-3px);
    box-shadow:0 8px 16px rgba(0,0,0,0.12);
  }
</style>
</head>
<body>
<<<<<<< HEAD
  <div class="menu-grid">
    <?php foreach($menuItems as $item): ?>
      <a href="?page=<?= $item['id'] ?>" class="menu-btn">
        <span class="icon"><?= $item['icon'] ?></span>
        <span><?= $item['label'] ?></span>
      </a>
    <?php endforeach; ?>
  </div>
=======
    <h1>Bienvenido administrador _</h1>
    <a href="cerrar_sesion.php">Cerrar sesion</a>
>>>>>>> 7bc7c3b9168edd5bf440916fafaeb90782f3f2cc
</body>
</html>
