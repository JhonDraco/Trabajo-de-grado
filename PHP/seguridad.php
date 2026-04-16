<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/* ============================
   VERIFICAR SESIÓN ACTIVA
============================ */
function verificarSesion(){
    if (!isset($_SESSION['usuario'])) {
        header("Location: index.php");
        exit();
    }
}

/* ============================
   ROLES
============================ */

function esAdmin(){
    return $_SESSION['cargo_id'] == 1;
}

function esEmpleado(){
    return $_SESSION['cargo_id'] == 2;
}

function esAnalista(){
    return $_SESSION['cargo_id'] == 3;
}

function esRRHH(){
    return $_SESSION['cargo_id'] == 4;
}

function esFinanzas(){
    return $_SESSION['cargo_id'] == 5;
}

/* ============================
   PERMISOS
============================ */

function puedeAdministrador(){
    return in_array($_SESSION['cargo_id'], [1,3,4,5]);
}
function puedeVerNomina(){
    return in_array($_SESSION['cargo_id'], [1,5,3,4]);
}

function puedeGenerarNomina(){
    return in_array($_SESSION['cargo_id'], [1,4]);
}

function puedeAsignaciones(){
    return in_array($_SESSION['cargo_id'], [1,3]);
}

function puedePagarNomina(){
    return in_array($_SESSION['cargo_id'], [1,5]);
}

function puedeVacaciones(){
    return in_array($_SESSION['cargo_id'], [1,5]);
}
function puedeEliminarNomina(){
    return $_SESSION['cargo_id'] == 1;
}

function puedeListarUsuarios(){
    return in_array($_SESSION['cargo_id'], [1,4]);
}

function puedeListarEmpleados(){
    return in_array($_SESSION['cargo_id'], [1,4]);
}

function puedeDeducciones(){
    return in_array($_SESSION['cargo_id'], [1,4]);
}

function puedeSalariosArchivos(){
    return in_array($_SESSION['cargo_id'], [1, 3, 4]);
}

function puedeReportes(){
    return in_array($_SESSION['cargo_id'], [1,3,4,5]);
}
/* ============================
   BLOQUEAR ACCESO
============================ */

function puedeEmpleado(){
    return $_SESSION['cargo_id'] == 2;
}
function bloquearSiNo($permiso){

    if(!$permiso){

        echo "<script>
        alert('⛔ No tienes permiso para acceder a este módulo');

        if(document.referrer){
            window.history.back();
        }else{
            window.location='administrador.php';
        }

        </script>";

        exit();
    }

}