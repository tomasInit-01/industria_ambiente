<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CotiController;
use App\Http\Controllers\CotioController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehiculosController;
use App\Http\Controllers\InventarioLabController;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\CheckAdmin;
use App\Http\Middleware\CheckAuth;
use App\Http\Middleware\EnsureSessionActive;
use App\Http\Controllers\OrdenController;
use App\Http\Controllers\VariableRequeridaController;
use App\Http\Controllers\InventarioMuestreoController;
use App\Http\Controllers\MuestrasController;
use App\Http\Controllers\DashboardController;
use App\Http\Middleware\CheckAdminOrRole;
use App\Http\Controllers\SimpleNotificationController;
use App\Http\Controllers\InformeController;

// Rutas de autenticación
Route::middleware([EnsureSessionActive::class])->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Rutas accesibles solo para usuarios autenticados (sin importar rol)
Route::middleware(CheckAuth::class)->group(function () {
    // Rutas de perfil de usuario
    Route::get('/auth/{id}', [AuthController::class, 'show'])->name('auth.show');
    Route::get('/auth/{id}/edit', [AuthController::class, 'edit'])->name('auth.edit');
    Route::put('/auth/{id}', [AuthController::class, 'update'])->name('auth.update');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Rutas de tareas
    Route::get('/mis-tareas', [CotiController::class, 'showTareas'])->name('mis-tareas');
    Route::get('/tareas-all/{cotio_numcoti}/{cotio_item}/{cotio_subitem}/{instance}', [CotioController::class, 'showTareasAll'])->name('tareas.all.show');
    Route::get('/tareas/{cotio_numcoti}/{cotio_item}/{cotio_subitem}', [CotioController::class, 'showTarea'])->name('tareas.show');
    Route::put('/tareas/{cotio_numcoti}/{cotio_item}/{cotio_subitem}/estado', [CotioController::class, 'updateEstado'])->name('tareas.updateEstado');
    Route::put('/tareas/{cotio_numcoti}/{cotio_item}/{cotio_subitem}/{instance}/fecha-carga', [CotioController::class, 'updateFechaCarga'])->name('tareas.updateFechaCarga');
    Route::post('/asignar-identificacion-muestra', [CotioController::class, 'asignarIdentificacionMuestra'])->name('asignar.identificacion-muestra');
    Route::post('/asignar-suspension-muestra', [CotioController::class, 'asignarSuspensionMuestra'])->name('asignar.suspension-muestra');
    Route::put('/tareas/{cotio_numcoti}/{cotio_item}/{cotio_subitem}/{instance}/resultado', [CotioController::class, 'updateResultado'])->name('tareas.updateResultado');
    Route::put('/tareas/{instance}/mediciones', [CotioController::class, 'updateMediciones'])->name('tareas.updateMediciones');

    // Rutas de ordenes
    Route::get('/mis-ordenes', [OrdenController::class, 'showOrdenes'])->name('mis-ordenes');
    Route::get('/ordenes-all/{cotio_numcoti}/{cotio_item}/{cotio_subitem}/{instance}', [OrdenController::class, 'showOrdenesAll'])->name('ordenes.all.show');

    Route::get('auth/{id}/seguridad', [AuthController::class, 'showSecurity'])->name('auth.security');
    Route::get('auth/{id}/ayuda', [AuthController::class, 'showHelp'])->name('auth.help');

    Route::put('/instancias/{instancia}/herramientas', [OrdenController::class, 'updateHerramientas'])->name('instancias.update-herramientas');
});

// Rutas para usuarios con nivel 900 o más (admin)
Route::middleware([CheckAdminOrRole::class])->group(function () {
    // Dashboard
    Route::get('/', [CotiController::class, 'index'])->name('cotizaciones.index');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Gestión de cotizaciones
    Route::get('/cotizaciones/{cotizacion}', [CotiController::class, 'showDetalle'])->name('cotizaciones.ver-detalle');
    Route::get('/cotizaciones/{cotizacion}/pdf', [CotiController::class, 'generateFullPdf'])->name('cotizaciones.full.pdf');
    Route::get('/cotizaciones/{cotizacion}/qr/all', [CotiController::class, 'printAllQr'])->name('cotizaciones.qr.all');
    Route::get('/cotizaciones/{cotizacion}/item/{item}/qr', [CotioController::class, 'generateItemQr'])->name('cotizaciones.item.qr');
    Route::get('/cotizaciones/{cotizacion}/categoria/{item}/{instance}', [CotioController::class, 'verCategoria'])->name('categoria.ver');
    
    // Gestión de tareas
    Route::post('/asignar-fechas', [CotioController::class, 'asignarFechas'])->name('asignar.fechas');
    Route::post('/tareas/actualizar-estado', [CotioController::class, 'actualizarEstado'])->name('tareas.actualizar-estado');
    Route::post('/asignar-frecuencia', [CotioController::class, 'asignarFrecuencia'])->name('asignar.frecuencia');
    Route::post('/asignar-identificacion', [CotioController::class, 'asignarIdentificacion'])->name('asignar.identificacion');
    Route::post('/tareas/pasar-muestreo', [CotioController::class, 'pasarMuestreo'])->name('tareas.pasar-muestreo');
    Route::post('/tareas/pasar-analisis', [OrdenController::class, 'pasarAnalisis'])->name('tareas.pasar-analisis');
    Route::post('/asignar-detalles', [CotioController::class, 'asignarDetalles'])->name('asignar.detalles');
    Route::post('/asignar-responsable-tarea', [CotioController::class, 'asignarResponsableTareaIndividual'])->name('asignar.responsable.tarea');
    Route::delete('/tareas/{cotizacion}/{item}/{subitem}/herramientas/{herramienta_id}', [CotioController::class, 'desasignarHerramienta'])->name('tareas.desasignar-herramienta');
    Route::delete('/tareas/{cotizacion}/{item}/{subitem}/vehiculos/{vehiculo_id}', [CotioController::class, 'desasignarVehiculo'])->name('tareas.desasignar-vehiculo');
    Route::post('/enable-ot/{cotio_numcoti}/{cotio_item}/{cotio_subitem}/{instance}', [CotioController::class, 'enableOt'])->name('categorias.enable-ot');
    Route::post('/disable-ot/{cotio_numcoti}/{cotio_item}/{cotio_subitem}/{instance}', [CotioController::class, 'disableOt'])->name('categorias.disable-ot');


    // Gestión de usuarios
    Route::get('/users', [UserController::class, 'showUsers'])->name('users.showUsers');
    Route::get('/users/create', [UserController::class, 'createUser'])->name('users.createUser');
    Route::post('/users', [UserController::class, 'storeUser'])->name('users.storeUser');
    Route::get('/users/{usu_codigo}', [UserController::class, 'showUser'])->name('users.showUser');
    Route::put('/users/{usu_codigo}', [UserController::class, 'update'])->name('users.update');
    

    // Gestión de sectores
    Route::get('/sectores', [UserController::class, 'showSectores'])->name('sectores.showSectores');
    Route::get('/sectores/create', [UserController::class, 'createSector'])->name('sectores.create');
    Route::get('/sectores/{sector_codigo}', [UserController::class, 'showSector'])->name('sectores.showSector');
    Route::put('/sectores/{sector_codigo}', [UserController::class, 'updateSector'])->name('sectores.updateSector');
    Route::post('/sectores', [UserController::class, 'storeSector'])->name('sectores.store');


    // Gestión de vehículos
    Route::get('/vehiculos', [VehiculosController::class, 'index'])->name('vehiculos.index');
    Route::get('/vehiculos/create', [VehiculosController::class, 'create'])->name('vehiculos.create');
    Route::post('/vehiculos', [VehiculosController::class, 'store'])->name('vehiculos.store');
    Route::get('/api/vehiculos', [VehiculosController::class, 'getVehiculosApi'])->name('vehiculos.api');
    Route::get('/vehiculos/{id}/edit', [VehiculosController::class, 'getVehiculo'])->name('vehiculos.show');
    Route::put('/vehiculos/{id}', [VehiculosController::class, 'update'])->name('vehiculos.update');
    Route::delete('/vehiculos/{id}', [VehiculosController::class, 'destroy'])->name('vehiculos.destroy');

    // Gestión de variables requeridas
    Route::get('/variables-requeridas', [VariableRequeridaController::class, 'index'])->name('variables-requeridas.index');
    Route::get('/variables-requeridas/create', [VariableRequeridaController::class, 'create'])->name('variables-requeridas.create');
    Route::post('/variables-requeridas', [VariableRequeridaController::class, 'store'])->name('variables-requeridas.store');
    Route::get('/variables-requeridas/{variableRequerida}', [VariableRequeridaController::class, 'show'])->name('variables-requeridas.show');
    Route::get('/variables-requeridas/{variableRequerida}/edit', [VariableRequeridaController::class, 'edit'])->name('variables-requeridas.edit');
    Route::put('/variables-requeridas/{variableRequerida}', [VariableRequeridaController::class, 'update'])->name('variables-requeridas.update');
    Route::delete('/variables-requeridas/{variableRequerida}', [VariableRequeridaController::class, 'destroy'])->name('variables-requeridas.destroy');
    
    // Rutas para grupos de variables
    Route::get('/variables-requeridas/grupo/{groupName}/editar', [VariableRequeridaController::class, 'editGroup'])
    ->name('variables-requeridas.edit-group');

    Route::put('/variables-requeridas/grupo/{groupName}', [VariableRequeridaController::class, 'updateGroup'])
    ->name('variables-requeridas.update-group');
    
    // Gestión de inventarios
    Route::get('/inventarios', [InventarioLabController::class, 'index'])->name('inventarios.index');
    Route::get('/inventarios/create', [InventarioLabController::class, 'create'])->name('inventarios.create');
    Route::post('/inventarios', [InventarioLabController::class, 'store'])->name('inventarios.store');
    Route::get('/inventarios/{id}/edit', [InventarioLabController::class, 'show'])->name('inventarios.show');
    Route::put('/inventarios/{id}', [InventarioLabController::class, 'update'])->name('inventarios.update');
    Route::delete('/inventarios/{id}', [InventarioLabController::class, 'destroy'])->name('inventarios.destroy');

    Route::get('/api/instancias/{instancia}/herramientas', [App\Http\Controllers\OrdenController::class, 'apiHerramientasInstancia']);

    // Gestión de inventarios de muestreo
    Route::get('/inventarios-muestreo', [InventarioMuestreoController::class, 'index'])->name('inventarios-muestreo.index');
    Route::get('/inventarios-muestreo/create', [InventarioMuestreoController::class, 'create'])->name('inventarios-muestreo.create');
    Route::post('/inventarios-muestreo', [InventarioMuestreoController::class, 'store'])->name('inventarios-muestreo.store');
    Route::get('/inventarios-muestreo/{id}/edit', [InventarioMuestreoController::class, 'show'])->name('inventarios-muestreo.show');
    Route::put('/inventarios-muestreo/{id}', [InventarioMuestreoController::class, 'update'])->name('inventarios-muestreo.update');
    Route::delete('/inventarios-muestreo/{id}', [InventarioMuestreoController::class, 'destroy'])->name('inventarios-muestreo.destroy');


    // Gestión de ordenes de trabajo
    Route::get('/ordenes', [OrdenController::class, 'index'])->name('ordenes.index');
    Route::get('/ordenes/{cotizacion}', [OrdenController::class, 'showDetalle'])->name('ordenes.ver-detalle');
    Route::get('/ordenes/{cotizacion}/categoria/{item}/{instance}', [OrdenController::class, 'verOrden'])->name('categoria.verOrden');
    Route::post('/asignar-detalles-analisis', [OrdenController::class, 'asignarDetallesAnalisis'])->name('asignar.detalles-analisis');
    Route::post('/ordenes/{ordenId}/asignacion-masiva', [OrdenController::class, 'asignacionMasiva'])->name('ordenes.asignacionMasiva');
    Route::post('/ordenes/finalizar-todas', [OrdenController::class, 'finalizarTodas'])->name('ordenes.finalizar-todas');
    Route::post('/ordenes/{ordenId}/remover-responsable', [OrdenController::class, 'removerResponsable'])->name('ordenes.remover-responsable');
    Route::post('/ordenes/{cotio_numcoti}/{cotio_item}/{cotio_subitem}/{instance}/enable-informe', [OrdenController::class, 'enableInforme'])->name('ordenes.enable-informe');
    Route::post('/ordenes/{cotio_numcoti}/{cotio_item}/{cotio_subitem}/{instance}/disable-informe', [OrdenController::class, 'disableInforme'])->name('ordenes.disable-informe');
    Route::post('/ordenes/{cotizacion}/deshacer-asignaciones', [OrdenController::class, 'deshacerAsignaciones'])->name('ordenes.deshacer-asignaciones');
    Route::post('/ordenes/actualizar-estado', [OrdenController::class, 'actualizarEstado'])->name('ordenes.actualizar-estado');
    Route::post('/ordenes/{cotizacion}/deshacer-asignaciones', [OrdenController::class, 'deshacerAsignaciones'])->name('ordenes.deshacer-asignaciones');

    // Gestión de muestras
    Route::get('/muestras', [MuestrasController::class, 'index'])->name('muestras.index');
    Route::get('/show/{coti_num}', [MuestrasController::class, 'show'])->name('muestras.show');
    Route::get('/muestras/{cotizacion}/categoria/{item}/{instance}', [MuestrasController::class, 'verMuestra'])->name('categoria.verMuestra');
    Route::post('/asignar-detalles-muestra', [MuestrasController::class, 'asignarDetallesMuestra'])->name('asignar.detalles-muestra');
    Route::get('/muestras/{cotizacion}/categoria/{item}/{instance}/ver', [MuestrasController::class, 'verMuestra'])->name('muestras.ver');
    Route::post('/muestras/asignacion-masiva', [MuestrasController::class, 'asignacionMasiva'])->name('muestras.asignacion-masiva');
    Route::post('/muestras/finalizar-todas', [MuestrasController::class, 'finalizarTodas'])->name('muestras.finalizar-todas');
    Route::post('/muestras/remover-responsable', [MuestrasController::class, 'removerResponsable'])->name('muestras.remover-responsable');
    Route::get('/muestras/{instancia}/datos-recoordinacion', [MuestrasController::class, 'getDatosRecoordinacion']);
    Route::post('/muestras/recoordinar', [MuestrasController::class, 'recoordinar'])->name('muestras.recoordinar');
    Route::put('/muestras/update-variable', [MuestrasController::class, 'updateVariable'])->name('muestras.updateVariable');
    Route::put('/muestras/update-all-data', [MuestrasController::class, 'updateAllData'])->name('muestras.updateAllData');
    Route::post('/muestras/pasar-directo-a-ot/{cotio_numcoti}/{cotio_item}/{instance_number}', [MuestrasController::class, 'pasarDirectoAOT'])->name('muestras.pasar-directo-a-ot');
    Route::delete('/muestras/quitar-directo-a-ot/{cotio_numcoti}/{cotio_item}/{instance_number}', [MuestrasController::class, 'quitarDirectoAOT'])->name('muestras.quitar-directo-a-ot');



    //informes
    Route::get('/informes', [InformeController::class, 'index'])->name('informes.index');
    Route::get('/informes/{cotio_numcoti}/{cotio_item}/{instance_number}', [InformeController::class, 'show'])->name('informes.show');
    Route::get('/informes/pdf-masivo/{cotizacion}', [InformeController::class, 'generarPdfMasivo'])->name('informes.pdf-masivo');
    Route::get('/informes/{cotio_numcoti}/{cotio_item}/{instance_number}/pdf', [InformeController::class, 'generarPdf'])->name('informes.pdf');
    Route::get('/informes-api/{cotio_numcoti}/{cotio_item}/{instance_number}', [InformeController::class, 'getInformeData'])->name('api.informes.get');
    Route::put('/informes-api/{cotio_numcoti}/{cotio_item}/{instance_number}', [InformeController::class, 'updateInforme'])->name('api.informes.update');
    Route::post('/ordenes/actualizar-estado', [OrdenController::class, 'actualizarEstado'])->name('ordenes.actualizar-estado');


    Route::get('/notificaciones', [SimpleNotificationController::class, 'index'])->name('notificaciones.index');
    Route::post('/notificaciones/{id}/leida', [SimpleNotificationController::class, 'marcarComoLeida'])->name('notificaciones.leida');
    Route::post('/notificaciones/leer-todas', [SimpleNotificationController::class, 'marcarTodasComoLeidas'])->name('notificaciones.leer-todas');
    Route::post('/notificaciones/marcar-leidas', [SimpleNotificationController::class, 'marcarLeidas'])->name('notificaciones.marcar-leidas');

    // Gestión de calibraciones del inventario de laboratorio
    Route::get('/calibracion', [App\Http\Controllers\CalibracionController::class, 'index'])->name('calibracion.index');
    Route::post('/calibracion/ejecutar-verificacion', [App\Http\Controllers\CalibracionController::class, 'ejecutarVerificacion'])->name('calibracion.ejecutar-verificacion');
    Route::get('/calibracion/estadisticas', [App\Http\Controllers\CalibracionController::class, 'estadisticas'])->name('calibracion.estadisticas');
    Route::post('/calibracion/notificaciones/{id}/leida', [App\Http\Controllers\CalibracionController::class, 'marcarLeida'])->name('calibracion.marcar-leida');
    Route::get('/calibracion/equipos-proximos', [App\Http\Controllers\CalibracionController::class, 'equiposProximos'])->name('calibracion.equipos-proximos');
    Route::get('/calibracion/equipos-vencidos', [App\Http\Controllers\CalibracionController::class, 'equiposVencidos'])->name('calibracion.equipos-vencidos');

    Route::get('/facturacion', [App\Http\Controllers\FacturacionController::class, 'index'])->name('facturacion.index');
    Route::get('/facturacion/listado', [App\Http\Controllers\FacturacionController::class, 'listarFacturas'])->name('facturacion.listado');
    Route::get('/facturacion/detalle/{id}', [App\Http\Controllers\FacturacionController::class, 'verFactura'])->name('facturacion.ver');
    Route::get('/facturacion/{factura}/descargar', [App\Http\Controllers\FacturacionController::class, 'descargar'])->name('facturacion.descargar');
    Route::get('/facturacion/facturar/{cotizacion}', [App\Http\Controllers\FacturacionController::class, 'facturar'])->name('facturacion.show');
    Route::post('/facturacion/facturar/{cotizacion}', [App\Http\Controllers\FacturacionController::class, 'generarFacturaArca'])->name('facturacion.facturar');
});



Route::middleware([CheckAdminOrRole::class])->group(function () {
    Route::get('/', [CotiController::class, 'index'])->name('cotizaciones.index');

    Route::get('/dashboard/analisis', [DashboardController::class, 'dashboardAnalisis'])->name('dashboard.analisis');

    Route::get('/cotizaciones/{cotizacion}', [CotiController::class, 'showDetalle'])->name('cotizaciones.ver-detalle');
    Route::get('/cotizaciones/{cotizacion}/pdf', [CotiController::class, 'generateFullPdf'])->name('cotizaciones.full.pdf');
    Route::get('/cotizaciones/{cotizacion}/qr/all', [CotiController::class, 'printAllQr'])->name('cotizaciones.qr.all');
    Route::get('/cotizaciones/{cotizacion}/item/{item}/qr', [CotioController::class, 'generateItemQr'])->name('cotizaciones.item.qr');
    Route::get('/cotizaciones/{cotizacion}/categoria/{item}/{instance}', [CotioController::class, 'verCategoria'])->name('categoria.ver');
    
    Route::post('/asignar-fechas', [CotioController::class, 'asignarFechas'])->name('asignar.fechas');
    Route::post('/tareas/actualizar-estado', [CotioController::class, 'actualizarEstado'])->name('tareas.actualizar-estado');
    Route::post('/asignar-frecuencia', [CotioController::class, 'asignarFrecuencia'])->name('asignar.frecuencia');
    Route::post('/asignar-identificacion', [CotioController::class, 'asignarIdentificacion'])->name('asignar.identificacion');
    Route::post('/tareas/pasar-muestreo', [CotioController::class, 'pasarMuestreo'])->name('tareas.pasar-muestreo');
    Route::post('/tareas/pasar-analisis', [OrdenController::class, 'pasarAnalisis'])->name('tareas.pasar-analisis');
    Route::post('/asignar-detalles', [CotioController::class, 'asignarDetalles'])->name('asignar.detalles');
    Route::post('/asignar-responsable-tarea', [CotioController::class, 'asignarResponsableTareaIndividual'])->name('asignar.responsable.tarea');
    Route::delete('/tareas/{cotizacion}/{item}/{subitem}/herramientas/{herramienta_id}', [CotioController::class, 'desasignarHerramienta'])->name('tareas.desasignar-herramienta');
    Route::delete('/tareas/{cotizacion}/{item}/{subitem}/vehiculos/{vehiculo_id}', [CotioController::class, 'desasignarVehiculo'])->name('tareas.desasignar-vehiculo');
    Route::post('/enable-ot/{cotio_numcoti}/{cotio_item}/{cotio_subitem}/{instance}', [CotioController::class, 'enableOt'])->name('categorias.enable-ot');
    Route::post('/disable-ot/{cotio_numcoti}/{cotio_item}/{cotio_subitem}/{instance}', [CotioController::class, 'disableOt'])->name('categorias.disable-ot');


    // Gestión de inventarios
    Route::get('/inventarios', [InventarioLabController::class, 'index'])->name('inventarios.index');
    Route::get('/inventarios/create', [InventarioLabController::class, 'create'])->name('inventarios.create');
    Route::post('/inventarios', [InventarioLabController::class, 'store'])->name('inventarios.store');
    Route::get('/inventarios/{id}/edit', [InventarioLabController::class, 'show'])->name('inventarios.show');
    Route::put('/inventarios/{id}', [InventarioLabController::class, 'update'])->name('inventarios.update');
    Route::delete('/inventarios/{id}', [InventarioLabController::class, 'destroy'])->name('inventarios.destroy');

    // Gestión de variables requeridas
    Route::get('/variables-requeridas', [VariableRequeridaController::class, 'index'])->name('variables-requeridas.index');
    Route::get('/variables-requeridas/create', [VariableRequeridaController::class, 'create'])->name('variables-requeridas.create');
    Route::post('/variables-requeridas', [VariableRequeridaController::class, 'store'])->name('variables-requeridas.store');
    Route::get('/variables-requeridas/{variableRequerida}', [VariableRequeridaController::class, 'show'])->name('variables-requeridas.show');
    Route::get('/variables-requeridas/{variableRequerida}/edit', [VariableRequeridaController::class, 'edit'])->name('variables-requeridas.edit');
    Route::put('/variables-requeridas/{variableRequerida}', [VariableRequeridaController::class, 'update'])->name('variables-requeridas.update');
    Route::delete('/variables-requeridas/{variableRequerida}', [VariableRequeridaController::class, 'destroy'])->name('variables-requeridas.destroy');

    // Rutas para grupos de variables
    Route::get('/variables-requeridas/grupo/{groupName}/editar', [VariableRequeridaController::class, 'editGroup'])
    ->name('variables-requeridas.edit-group');

    Route::put('/variables-requeridas/grupo/{groupName}', [VariableRequeridaController::class, 'updateGroup'])
    ->name('variables-requeridas.update-group');
    
    Route::get('/api/instancias/{instancia}/herramientas', [App\Http\Controllers\OrdenController::class, 'apiHerramientasInstancia']);

    // Gestión de ordenes de trabajo
    Route::get('/ordenes', [OrdenController::class, 'index'])->name('ordenes.index');
    Route::get('/ordenes/{cotizacion}', [OrdenController::class, 'showDetalle'])->name('ordenes.ver-detalle');
    Route::get('/ordenes/{cotizacion}/categoria/{item}/{instance}', [OrdenController::class, 'verOrden'])->name('categoria.verOrden');
    Route::post('/asignar-detalles-analisis', [OrdenController::class, 'asignarDetallesAnalisis'])->name('asignar.detalles-analisis');
    Route::post('/ordenes/{ordenId}/asignacion-masiva', [OrdenController::class, 'asignacionMasiva'])->name('ordenes.asignacionMasiva');
    Route::post('/ordenes/finalizar-todas', [OrdenController::class, 'finalizarTodas'])->name('ordenes.finalizar-todas');
    Route::post('/ordenes/{ordenId}/remover-responsable', [OrdenController::class, 'removerResponsable'])->name('ordenes.remover-responsable');
    Route::post('/ordenes/{cotio_numcoti}/{cotio_item}/{cotio_subitem}/{instance}/enable-informe', [OrdenController::class, 'enableInforme'])->name('ordenes.enable-informe');
    Route::post('/ordenes/{cotio_numcoti}/{cotio_item}/{cotio_subitem}/{instance}/disable-informe', [OrdenController::class, 'disableInforme'])->name('ordenes.disable-informe');
    Route::post('/ordenes/{cotizacion}/deshacer-asignaciones', [OrdenController::class, 'deshacerAsignaciones'])->name('ordenes.deshacer-asignaciones');
    Route::post('/ordenes/actualizar-estado', [OrdenController::class, 'actualizarEstado'])->name('ordenes.actualizar-estado');
    Route::post('/ordenes/{cotizacion}/deshacer-asignaciones', [OrdenController::class, 'deshacerAsignaciones'])->name('ordenes.deshacer-asignaciones');


    Route::get('/notificaciones', [SimpleNotificationController::class, 'index'])->name('notificaciones.index');
    Route::post('/notificaciones/{id}/leida', [SimpleNotificationController::class, 'marcarComoLeida'])->name('notificaciones.leida');
    Route::post('/notificaciones/leer-todas', [SimpleNotificationController::class, 'marcarTodasComoLeidas'])->name('notificaciones.leer-todas');
    Route::post('/notificaciones/marcar-leidas', [SimpleNotificationController::class, 'marcarLeidas'])->name('notificaciones.marcar-leidas');



    // Gestión de usuarios
    Route::get('/users', [UserController::class, 'showUsers'])->name('users.showUsers');
    Route::get('/users/create', [UserController::class, 'createUser'])->name('users.createUser');
    Route::post('/users', [UserController::class, 'storeUser'])->name('users.storeUser');
    Route::get('/users/{usu_codigo}', [UserController::class, 'showUser'])->name('users.showUser');
    Route::put('/users/{usu_codigo}', [UserController::class, 'update'])->name('users.update');
    

    // Gestión de sectores
    Route::get('/sectores', [UserController::class, 'showSectores'])->name('sectores.showSectores');
    Route::get('/sectores/create', [UserController::class, 'createSector'])->name('sectores.create');
    Route::get('/sectores/{sector_codigo}', [UserController::class, 'showSector'])->name('sectores.showSector');
    Route::put('/sectores/{sector_codigo}', [UserController::class, 'updateSector'])->name('sectores.updateSector');
    Route::post('/sectores', [UserController::class, 'storeSector'])->name('sectores.store');
    

});


Route::middleware([CheckAdminOrRole::class])->group(function () {
    Route::get('/', [CotiController::class, 'index'])->name('cotizaciones.index');
    Route::get('/cotizaciones/{cotizacion}', [CotiController::class, 'showDetalle'])->name('cotizaciones.ver-detalle');
    Route::get('/cotizaciones/{cotizacion}/pdf', [CotiController::class, 'generateFullPdf'])->name('cotizaciones.full.pdf');
    Route::get('/cotizaciones/{cotizacion}/qr/all', [CotiController::class, 'printAllQr'])->name('cotizaciones.qr.all');
    Route::get('/cotizaciones/{cotizacion}/item/{item}/qr', [CotioController::class, 'generateItemQr'])->name('cotizaciones.item.qr');
    Route::get('/cotizaciones/{cotizacion}/categoria/{item}/{instance}', [CotioController::class, 'verCategoria'])->name('categoria.ver');

    Route::get('/dashboard/muestreo', [DashboardController::class, 'dashboardMuestreo'])->name('dashboard.muestreo');
    
    Route::post('/asignar-fechas', [CotioController::class, 'asignarFechas'])->name('asignar.fechas');
    Route::post('/tareas/actualizar-estado', [CotioController::class, 'actualizarEstado'])->name('tareas.actualizar-estado');
    Route::post('/asignar-frecuencia', [CotioController::class, 'asignarFrecuencia'])->name('asignar.frecuencia');
    Route::post('/asignar-identificacion', [CotioController::class, 'asignarIdentificacion'])->name('asignar.identificacion');
    Route::post('/tareas/pasar-muestreo', [CotioController::class, 'pasarMuestreo'])->name('tareas.pasar-muestreo');
    Route::post('/tareas/pasar-analisis', [OrdenController::class, 'pasarAnalisis'])->name('tareas.pasar-analisis');
    Route::post('/asignar-detalles', [CotioController::class, 'asignarDetalles'])->name('asignar.detalles');
    Route::post('/asignar-responsable-tarea', [CotioController::class, 'asignarResponsableTareaIndividual'])->name('asignar.responsable.tarea');
    Route::delete('/tareas/{cotizacion}/{item}/{subitem}/herramientas/{herramienta_id}', [CotioController::class, 'desasignarHerramienta'])->name('tareas.desasignar-herramienta');
    Route::delete('/tareas/{cotizacion}/{item}/{subitem}/vehiculos/{vehiculo_id}', [CotioController::class, 'desasignarVehiculo'])->name('tareas.desasignar-vehiculo');
    Route::post('/enable-ot/{cotio_numcoti}/{cotio_item}/{cotio_subitem}/{instance}', [CotioController::class, 'enableOt'])->name('categorias.enable-ot');
    Route::post('/disable-ot/{cotio_numcoti}/{cotio_item}/{cotio_subitem}/{instance}', [CotioController::class, 'disableOt'])->name('categorias.disable-ot');


    // Gestión de inventarios de muestreo
    Route::get('/inventarios-muestreo', [InventarioMuestreoController::class, 'index'])->name('inventarios-muestreo.index');
    Route::get('/inventarios-muestreo/create', [InventarioMuestreoController::class, 'create'])->name('inventarios-muestreo.create');
    Route::post('/inventarios-muestreo', [InventarioMuestreoController::class, 'store'])->name('inventarios-muestreo.store');
    Route::get('/inventarios-muestreo/{id}/edit', [InventarioMuestreoController::class, 'show'])->name('inventarios-muestreo.show');
    Route::put('/inventarios-muestreo/{id}', [InventarioMuestreoController::class, 'update'])->name('inventarios-muestreo.update');
    Route::delete('/inventarios-muestreo/{id}', [InventarioMuestreoController::class, 'destroy'])->name('inventarios-muestreo.destroy');


    // Gestión de variables requeridas
    Route::get('/variables-requeridas', [VariableRequeridaController::class, 'index'])->name('variables-requeridas.index');
    Route::get('/variables-requeridas/create', [VariableRequeridaController::class, 'create'])->name('variables-requeridas.create');
    Route::post('/variables-requeridas', [VariableRequeridaController::class, 'store'])->name('variables-requeridas.store');
    Route::get('/variables-requeridas/{variableRequerida}', [VariableRequeridaController::class, 'show'])->name('variables-requeridas.show');
    Route::get('/variables-requeridas/{variableRequerida}/edit', [VariableRequeridaController::class, 'edit'])->name('variables-requeridas.edit');
    Route::put('/variables-requeridas/{variableRequerida}', [VariableRequeridaController::class, 'update'])->name('variables-requeridas.update');
    Route::delete('/variables-requeridas/{variableRequerida}', [VariableRequeridaController::class, 'destroy'])->name('variables-requeridas.destroy');

    // Rutas para grupos de variables
    Route::get('/variables-requeridas/grupo/{groupName}/editar', [VariableRequeridaController::class, 'editGroup'])
    ->name('variables-requeridas.edit-group');

    Route::put('/variables-requeridas/grupo/{groupName}', [VariableRequeridaController::class, 'updateGroup'])
    ->name('variables-requeridas.update-group');

    // Gestión de muestras
    Route::get('/muestras', [MuestrasController::class, 'index'])->name('muestras.index');
    Route::get('/show/{coti_num}', [MuestrasController::class, 'show']);
    Route::get('/muestras/{cotizacion}/categoria/{item}/{instance}', [MuestrasController::class, 'verMuestra'])->name('categoria.verMuestra');
    Route::post('/asignar-detalles-muestra', [MuestrasController::class, 'asignarDetallesMuestra'])->name('asignar.detalles-muestra');
    Route::get('/muestras/{cotizacion}/categoria/{item}/{instance}/ver', [MuestrasController::class, 'verMuestra'])->name('muestras.ver');
    Route::post('/muestras/asignacion-masiva', [MuestrasController::class, 'asignacionMasiva'])->name('muestras.asignacion-masiva');
    Route::post('/muestras/finalizar-todas', [MuestrasController::class, 'finalizarTodas'])->name('muestras.finalizar-todas');
    Route::post('/muestras/remover-responsable', [MuestrasController::class, 'removerResponsable'])->name('muestras.remover-responsable');
    Route::get('/muestras/{instancia}/datos-recoordinacion', [MuestrasController::class, 'getDatosRecoordinacion']);
    Route::post('/muestras/recoordinar', [MuestrasController::class, 'recoordinar'])->name('muestras.recoordinar');
    Route::put('/muestras/update-variable', [MuestrasController::class, 'updateVariable'])->name('muestras.updateVariable');
    Route::put('/muestras/update-all-data', [MuestrasController::class, 'updateAllData'])->name('muestras.updateAllData');


    // Gestión de vehículos
    Route::get('/vehiculos', [VehiculosController::class, 'index'])->name('vehiculos.index');
    Route::get('/vehiculos/create', [VehiculosController::class, 'create'])->name('vehiculos.create');
    Route::post('/vehiculos', [VehiculosController::class, 'store'])->name('vehiculos.store');
    Route::get('/api/vehiculos', [VehiculosController::class, 'getVehiculosApi'])->name('vehiculos.api');
    Route::get('/vehiculos/{id}/edit', [VehiculosController::class, 'getVehiculo'])->name('vehiculos.show');
    Route::put('/vehiculos/{id}', [VehiculosController::class, 'update'])->name('vehiculos.update');
    Route::delete('/vehiculos/{id}', [VehiculosController::class, 'destroy'])->name('vehiculos.destroy');


    // Gestión de usuarios
    Route::get('/users', [UserController::class, 'showUsers'])->name('users.showUsers');
    Route::get('/users/create', [UserController::class, 'createUser'])->name('users.createUser');
    Route::post('/users', [UserController::class, 'storeUser'])->name('users.storeUser');
    Route::get('/users/{usu_codigo}', [UserController::class, 'showUser'])->name('users.showUser');
    Route::put('/users/{usu_codigo}', [UserController::class, 'update'])->name('users.update');
    

    // Gestión de sectores
    Route::get('/sectores', [UserController::class, 'showSectores'])->name('sectores.showSectores');
    Route::get('/sectores/create', [UserController::class, 'createSector'])->name('sectores.create');
    Route::get('/sectores/{sector_codigo}', [UserController::class, 'showSector'])->name('sectores.showSector');
    Route::put('/sectores/{sector_codigo}', [UserController::class, 'updateSector'])->name('sectores.updateSector');
    Route::post('/sectores', [UserController::class, 'storeSector'])->name('sectores.store');
    


    // routes/web.php
    Route::get('/notificaciones', [SimpleNotificationController::class, 'index'])->name('notificaciones.index');
    Route::post('/notificaciones/{id}/leida', [SimpleNotificationController::class, 'marcarComoLeida'])->name('notificaciones.leida');
    Route::post('/notificaciones/leer-todas', [SimpleNotificationController::class, 'marcarTodasComoLeidas'])->name('notificaciones.leer-todas');
    Route::post('/notificaciones/marcar-leidas', [SimpleNotificationController::class, 'marcarLeidas'])->name('notificaciones.marcar-leidas');
});


Route::middleware([CheckAdminOrRole::class])->group(function () {
    Route::get('/facturacion', [App\Http\Controllers\FacturacionController::class, 'index'])->name('facturacion.index');
    Route::get('/facturacion/listado', [App\Http\Controllers\FacturacionController::class, 'listarFacturas'])->name('facturacion.listado');
    Route::get('/facturacion/detalle/{id}', [App\Http\Controllers\FacturacionController::class, 'verFactura'])->name('facturacion.ver');
    Route::get('/facturacion/{factura}/descargar', [App\Http\Controllers\FacturacionController::class, 'descargar'])->name('facturacion.descargar');
    Route::get('/facturacion/facturar/{cotizacion}', [App\Http\Controllers\FacturacionController::class, 'facturar'])->name('facturacion.show');
    Route::post('/facturacion/facturar/{cotizacion}', [App\Http\Controllers\FacturacionController::class, 'generarFacturaArca'])->name('facturacion.facturar');
});

// API: Herramientas de una instancia (temporal, aquí en web.php)

