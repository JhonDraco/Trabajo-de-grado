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
   ROLES (cargo_id)
============================ */
function esAdmin(){     return $_SESSION['cargo_id'] == 1; }
function esEmpleado(){  return $_SESSION['cargo_id'] == 2; }
function esAnalista(){  return $_SESSION['cargo_id'] == 3; }
function esRRHH(){      return $_SESSION['cargo_id'] == 4; }
function esFinanzas(){  return $_SESSION['cargo_id'] == 5; }

/* ============================================================
   PERMISOS GRANULARES — un permiso por VERBO + ENTIDAD
   Admin (1) siempre incluido: acceso total, nunca se le oculta nada.
============================================================ */

/* ----- EMPLEADOS ----- */
function puedeListarEmpleados(){  return in_array($_SESSION['cargo_id'], [1,4,3]); }
function puedeCrearEmpleado(){    return in_array($_SESSION['cargo_id'], [1,4,3]); }
function puedeEditarEmpleado(){   return in_array($_SESSION['cargo_id'], [1,4,3]); } // incluye activar/desactivar (campo estado)
function puedeEliminarEmpleado(){ return $_SESSION['cargo_id'] == 1; }

/* ----- VACACIONES (panel de gestión, no el portal del trabajador) ----- */
function puedeVerVacaciones(){        return in_array($_SESSION['cargo_id'], [1,4,3]); }
function puedeRegistrarVacaciones(){  return in_array($_SESSION['cargo_id'], [1,4,3]); } // crear solicitud
function puedeAprobarVacaciones(){    return in_array($_SESSION['cargo_id'], [1,4]); }   // aprobar / rechazar
function puedeInicializarVacaciones(){return in_array($_SESSION['cargo_id'], [1,4]); }

/* ----- PERMISOS / INCIDENCIAS ----- */
function puedeRegistrarIncidencia(){ return in_array($_SESSION['cargo_id'], [1,4,3]); }
function puedeAprobarPermiso(){      return in_array($_SESSION['cargo_id'], [1,4]); }

/* ----- ASIGNACIONES ----- */
function puedeVerAsignaciones(){    return in_array($_SESSION['cargo_id'], [1,4,3]); }
function puedeCrearAsignacion(){    return in_array($_SESSION['cargo_id'], [1,4,3]); }
function puedeEditarAsignacion(){   return in_array($_SESSION['cargo_id'], [1,4,3]); }
function puedeEliminarAsignacion(){ return in_array($_SESSION['cargo_id'], [1,4]); }

/* ----- DEDUCCIONES ----- */
function puedeVerDeducciones(){    return in_array($_SESSION['cargo_id'], [1,4,3]); }
function puedeCrearDeduccion(){    return in_array($_SESSION['cargo_id'], [1,4,3]); }
function puedeEditarDeduccion(){   return in_array($_SESSION['cargo_id'], [1,4,3]); }
function puedeEliminarDeduccion(){ return in_array($_SESSION['cargo_id'], [1,4]); }

/* ----- HORAS EXTRA ----- */
function puedeVerHorasExtra(){       return in_array($_SESSION['cargo_id'], [1,4,3]); }
function puedeRegistrarHorasExtra(){ return in_array($_SESSION['cargo_id'], [1,4,3]); }
function puedeEliminarHorasExtra(){  return in_array($_SESSION['cargo_id'], [1,4]); }

/* ----- NÓMINA ----- */
function puedeVerNomina(){        return in_array($_SESSION['cargo_id'], [1,4,5]); }
function puedeGenerarNomina(){    return in_array($_SESSION['cargo_id'], [1,4]); }
function puedeRecalcularNomina(){ return in_array($_SESSION['cargo_id'], [1,4]); }
function puedePagarNomina(){      return in_array($_SESSION['cargo_id'], [1,5]); }
function puedeConfirmarPago(){    return in_array($_SESSION['cargo_id'], [1,5]); }
function puedeCerrarNomina(){     return $_SESSION['cargo_id'] == 1; }
function puedeEliminarNomina(){   return $_SESSION['cargo_id'] == 1; }

/* ----- LIQUIDACIÓN ----- */
function puedeVerLiquidacion(){     return in_array($_SESSION['cargo_id'], [1,4,5]); }
function puedeGenerarLiquidacion(){ return in_array($_SESSION['cargo_id'], [1,4]); }

/* ----- FERIADOS ----- */
function puedeGestionarFeriados(){ return in_array($_SESSION['cargo_id'], [1,4]); }

/* ----- USUARIOS / CONFIGURACIÓN (solo Admin) ----- */
function puedeVerUsuarios(){     return in_array($_SESSION['cargo_id'], [1,4]); } // RRHH puede ver, no crear ni cambiar roles
function puedeCrearUsuario(){    return $_SESSION['cargo_id'] == 1; }
function puedeCambiarRoles(){    return $_SESSION['cargo_id'] == 1; }
function puedeEliminarUsuario(){ return $_SESSION['cargo_id'] == 1; }
function puedeConfiguracion(){   return $_SESSION['cargo_id'] == 1; }

/* ----- REPORTES / AUDITORÍA ----- */
function puedeReportes(){        return in_array($_SESSION['cargo_id'], [1,3,4,5]); }
function puedeVerBitacora(){     return $_SESSION['cargo_id'] == 1; }
function puedeEliminarBitacora(){return $_SESSION['cargo_id'] == 1; }

/* ----- PORTAL DEL TRABAJADOR ----- */
function puedeEmpleado(){ return $_SESSION['cargo_id'] == 2; }

/* ============================================================
   ALIAS DE COMPATIBILIDAD (temporal)
   Varios archivos todavía no migrados llaman a estos nombres viejos.
   A medida que migremos cada módulo, vamos borrando su alias de aquí.
============================================================ */
function puedeAdministrador(){    return in_array($_SESSION['cargo_id'], [1,3,4,5]); } // todos menos Trabajador
function puedeAsignaciones(){     return puedeVerAsignaciones(); }
function puedeVacaciones(){       return puedeVerVacaciones(); }
function puedeListarUsuarios(){   return puedeVerUsuarios(); }
function puedeDeducciones(){      return puedeVerDeducciones(); }
function puedeSalariosArchivos(){ return in_array($_SESSION['cargo_id'], [1,3,4]); }

/* ============================
   AUDITORÍA / BITÁCORA
============================ */
function registrar_auditoria($conexion, $accion, $modulo, $descripcion) {
    $usuario     = $_SESSION['usuario']  ?? 'desconocido';
    $cargo_id    = $_SESSION['cargo_id'] ?? 0;
    $ip          = mysqli_real_escape_string($conexion, $_SERVER['REMOTE_ADDR'] ?? '');
    $descripcion = mysqli_real_escape_string($conexion, $descripcion);
    $accion      = mysqli_real_escape_string($conexion, $accion);
    $modulo      = mysqli_real_escape_string($conexion, $modulo);

    mysqli_query($conexion,
        "INSERT INTO auditoria (usuario, cargo_id, accion, modulo, descripcion, ip)
         VALUES ('$usuario', $cargo_id, '$accion', '$modulo', '$descripcion', '$ip')"
    );
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
