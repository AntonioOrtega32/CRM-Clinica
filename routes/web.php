<?php

use App\Http\Controllers\LandingController; // landing pública
use App\Http\Controllers\Panel\LandingController as PanelLandingController; // panel
use App\Http\Controllers\Panel\PanelController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Panel\DoctorSantanaController as PanelDoctorSantanaController; // panel Dr. Santana
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\finanzasController;
use App\Http\Controllers\InventarioController;

use App\Http\Controllers\BunnyController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ProcedimientosController;
use App\Http\Controllers\ClientesController;
use App\Http\Controllers\DailyExpensesController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\LeadsController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\NominaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VentasController;
use App\Http\Controllers\ReceiptController;

Route::get('/dashboard', function () {
    return view('panel.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';

//Prueba de Revista Digital
use App\Http\Controllers\RevistaController;

Route::get('/Revista-Digital/{token}', [RevistaController::class, 'viewer'])
    ->name('revista.viewer')
    ->middleware('signed'); // valida firma + expiración

Route::get('/Revista-Digital/{token}/pdf', [RevistaController::class, 'streamPdf'])
    ->name('revista.pdf')
    ->middleware('signed');
// Fin prueba Revista Digital

// Mostrar landing pública
Route::get('/', [LandingController::class, 'index'])->name('landing.index');
//Clinicas Capilar Elite
Route::get('/clinicas/santafe', [LandingController::class, 'clinicaSantafe'])->name('landing.santafe');
Route::get('/clinicas/pedregal', [LandingController::class, 'clinicaPedregal'])->name('landing.pedregal');
Route::get('/clinicas/queretaro', [LandingController::class, 'clinicaQueretaro'])->name('landing.queretaro');


//Equipo
Route::get('/equipo', [LandingController::class, 'equipo'])->name('landing.equipo');
// Tecnologías
Route::get('/tecnologias', [LandingController::class, 'tecnologias'])->name('landing.tecnologias');
//
Route::get('/servicios', [LandingController::class, 'servicios'])->name('landing.servicios');


Route::prefix('panel')->name('panel.')->middleware(['auth'])->group(function () {


    //Prototipo de ruta de link al pdf de la revista digital
    Route::get('/revista-links', [PanelLandingController::class, 'indexRev'])->name('revista.links');
    Route::post('/revista-links', [PanelLandingController::class, 'generate'])->name('revista.links.generate');
    
    //Crudo de la revista digital
    Route::post('/revista/upload', [RevistaController::class, 'upload'])->name('revista.upload');
    Route::delete('/revista', [RevistaController::class, 'destroy'])->name('revista.destroy');

    
    //Fin prototipo revista digital

    /* -------------------------------------- RUTAS DE MARKETING -------------------------------------- */

    // Marketing / Seguimiento px
    route::get('/marketing/index', [MarketingController::class, 'index'])
        ->name('marketing.index');

    Route::get('/marketing/tracking-px/data', [MarketingController::class, 'trackingPxData'])
        ->name('marketing.trackingpx.data');


    /* -------------------------------------- FIN RUTAS DE MARKETING ----------------------------------- */

    /* ------------------------------------- RUTAS DE CLIENTES ---------------------------------------- */

    //Index de clientes
    Route::get('clientes', [ClientesController::class, 'indexClientes'])->name('clientes.index');

    //Cambio de estatus de clientes
    Route::post('/clientes/update-status', [ClientesController::class, 'updateStatus'])
        ->name('clientes.update-status');

    //Actualizar numero de expediente medico
    Route::post('/clientes/update-numero-exp', [ClientesController::class, 'updateMedicalRecord'])
        ->name('clientes.update-med-record');

    //Tab historial de transacciones
    // Historial de transacciones
    Route::get('/leads/{lead}/transactions', [ClientesController::class, 'transactions'])
        ->name('leads.transactions');

    //Eliminar transacción
    Route::delete('/leads/payments/{payment}', [ClientesController::class, 'deletePayment'])
        ->name('leads.payments.delete');


    // Valoración
    Route::get('/leads/{lead}/assessment', [ClientesController::class, 'assessment'])
        ->name('leads.assessment');

    Route::post('/leads/{lead}/assessment', [ClientesController::class, 'storeAssessment'])
        ->name('leads.assessment.store');

    //Generar pdf
    Route::get('/clientes/{leadId}/valoracion/pdf', [ClientesController::class, 'valoracionPdf'])
        ->name('clientes.valoracion.pdf');

    //Obtener pdf de valoracion
    Route::get('/clientes/{leadId}/valoracion/pdf/existe', [ClientesController::class, 'getAssessmentPdf'])
        ->name('clientes.valoracion.pdf.existe');


    //Rutas auxiliares para clientes
    //Trear el nombre de los pacientes por su numero de expediente
    Route::post('/clientes/get-patient', [ClientesController::class, 'getPatientByRecord2'])
        ->name('clientes.get-patient');
    //Fotos de valoracion
    Route::post('/leads/photos', [LeadsController::class, 'getPhotos'])
        ->name('leads.photos');

    Route::get('/panel/leads/{id}/assessment', [ClientesController::class, 'assessment']);

    /* ------------------------------------- FIN RUTAS DE CLIENTES ------------------------------------ */


    /* -------------------------------------- RUTAS DE LEADS ------------------------------------------- */

    //Index de leads
    Route::get('/leads/reporte', [LeadsController::class, 'index'])
        ->name('leads.index');
    //Llenar tabla de leads via ajax con jeison tipo server side
    /*Route::post('/leads/reporte/datatable', [LeadsController::class, 'datatable'])
    ->name('leads.reporte.datatable');*/
    //Llenar tabla de leads sin ser server side
    // routes/web.php
    Route::get('/leads/reporte/data', [LeadsController::class, 'dataAll'])
        ->name('leads.reporte.data');
    //Semaforo de leads
    Route::post('/leads/{leadId}/semaforo', [LeadsController::class, 'updateSemaforo'])
        ->name('leads.semaforo.update');


    //reporte de leads desde clientes
    Route::get('/leads/{id}', [LeadsController::class, 'show'])
        ->name('leads.show');

    //Fotos de valoracion
    Route::post('/leads/{id}/photos', [LeadsController::class, 'getPhotos'])
        ->name('leads.photos');

    //Guardar imagen del fockin conejo
    Route::post(
        '/leads/photos/upload',
        [LeadsController::class, 'uploadPhotos']
    )->name('leads.photos.upload');


    //Eliminar imagen del fockin conejo
    Route::delete(
        '/leads/{lead}/photos',
        [LeadsController::class, 'deletePhoto']
    )->name('leads.photos.delete');


    //Archivos de historial medico
    Route::post('/leads/hc', [LeadsController::class, 'getHC'])
        ->name('leads.hc');

    Route::post('/leads/hc/upload', [LeadsController::class, 'uploadHC'])
        ->name('leads.hc.upload');

    Route::delete('/leads/{lead}/hc', [LeadsController::class, 'deleteHC'])
        ->name('leads.hc.delete');


    //Archivo de identificacion
    Route::post('/leads/id', [LeadsController::class, 'getID'])
        ->name('leads.id');

    Route::post('/leads/id/upload', [LeadsController::class, 'uploadID'])
        ->name('leads.id.upload');

    Route::delete('/leads/id/delete/{lead}', [LeadsController::class, 'deleteID'])
        ->name('leads.id.delete');


    //Archivo de laboratorios
    Route::post('/leads/labs', [LeadsController::class, 'getLabs'])
        ->name('leads.labs');

    Route::post('/leads/labs/upload', [LeadsController::class, 'uploadLabs'])
        ->name('leads.labs.upload');

    Route::delete('/leads/labs/delete/{lead}', [LeadsController::class, 'deleteLabs'])
        ->name('leads.labs.delete');

    //Contrato
    Route::post('/leads/contrato/generar', [LeadsController::class, 'generarContrato'])
        ->name('leads.contrato.generar');

    Route::patch('/leads/{Id}/summary', [LeadsController::class, 'updateSummary'])->name('leads.summary.update');

    // Enfermedades 
    Route::get('/leads/{lead}/health', [LeadsController::class, 'getHealth'])->name('leads.health.get');
    Route::post('/leads/{lead}/health', [LeadsController::class, 'saveHealth'])->name('leads.health.save');


    /* -------------------------------------- FIN RUTAS DE LEADS ---------------------------------------- */

    // ---------------------------------------- rutas de inventarios ----------------------------------------
    Route::get('inventario', [InventarioController::class, 'index'])->name('inventario.index');
    //Jeison para traer los productos
    /*Route::get('/inventario/general-json', [InventarioController::class, 'generalJson'])
    ->name('inventario.general.json');*/    
    Route::post('inventario/movimiento', [InventarioController::class, 'movimientoInv'])->name('inventario.movimiento');
    Route::put('inventario/update', [InventarioController::class, 'updateProd'])->name('inventario.update');
    Route::delete('inventario/destroy/{id}', [InventarioController::class, 'destroyProd'])->name('inventario.destroy');
    Route::post('inventario/salida', [InventarioController::class, 'salidaProducto'])->name('inventario.salida');
    //Kits medicos
    Route::get('/productos', [InventarioController::class, 'getProductos'])->name('getProductos'); // Trae todos los productos disponibles
    Route::get('/obtener', [InventarioController::class, 'getKit'])->name('getKit');         // Trae los productos del kit (por tipo y clínica)
    Route::post('/guardar', [InventarioController::class, 'guardarKit'])->name('guardarKit');    // Guarda o actualiza los kits
    Route::post('/dashboard/load-patients', [PanelController::class, 'loadPatients'])
        ->name('loadPatients');
    Route::match(['get', 'post'], '/vacaciones-card', [PanelController::class, 'vacacionesCard'])
        ->name('vacacionesCard');
    // Ruta para obtener empleados con 1 año o más
    Route::match(['get', 'post'], 'empleados-aniversario', [PanelController::class, 'empleadosAniversario'])
        ->name('empleadosAniversario');
    //Salida rapida
    Route::post('/salidas-rapidas', [InventarioController::class, 'registrarSalidaRapida'])->name('salidas.rapidas');

    //Rutas de apartado de finanzas
    //Rutas de gastos
    Route::get('gastos', [finanzasController::class, 'indexGastos'])->name('gastos.index');
    Route::post('/gastos/guardarGasto', [finanzasController::class, 'guardarGasto'])->name('gastos.guardar');
    Route::put('/gastos/{id}', [finanzasController::class, 'actualizarGasto'])->name('gastos.actualizar');
    Route::delete('/gastos/{id}', [finanzasController::class, 'eliminarGasto'])->name('gastos.eliminar');
    //Suma de totales de gastos
    Route::get('/gastos/fechas', [finanzasController::class, 'getGastosPorFechas'])->name('gastos.fechas');
    //Suma de totales alterno
    Route::post('/gastos/total', [finanzasController::class, 'totalGastos'])
        ->name('gastos.total');

    //Rutas de ingresos
    Route::get('ingresos', [finanzasController::class, 'indexIngresos'])->name('ingresos.index');
    Route::get('ingresos-transacciones/data', [finanzasController::class, 'getTransacciones'])->name('ingresosTransacciones.data');

    //Rutas de cortes diarios
    Route::get('cortesDiarios', [finanzasController::class, 'indexCortesDiarios'])->name('cortesDiarios.index');
    Route::post('/load-all-daily', [finanzasController::class, 'loadAllDaily'])->name('corte.loadAllDaily');
    Route::post('/load-total-daily', [finanzasController::class, 'loadTotalDaily'])->name('corte.loadTotalDaily');
    Route::post('/add-sign', [finanzasController::class, 'addSignByDay'])->name('corte.addSignByDay');
    Route::post('/delete-sign', [finanzasController::class, 'deleteSignByDay'])->name('corte.deleteSignByDay');
    Route::post('/generate-cash-closing', [finanzasController::class, 'generateCashClosingDaily'])->name('corte.generateCashClosingDaily');
    //---------------------------------------- fin rutas de inventarios ----------------------------------------   

    //------------------------------------ Rutas del modulo de procedimientos ------------------------------------------------
    // Procedimientos
    Route::get('/procedimientos', [ProcedimientosController::class, 'indexProcedimientos'])->name('procedimientos.index');
    Route::get('/procedimientos/info/{id}', [ProcedimientosController::class, 'info'])->name('procedimientos.info');


    //Actualizar procedimiento
    Route::post('/procedimientos/update', [ProcedimientosController::class, 'update'])->name('procedimientos.update');

    //Procedimientos nptas medicas
    Route::post('/notas/agregar', [ProcedimientosController::class, 'addNote'])->name('notas.add');
    Route::delete('/notas/eliminar/{id}', [ProcedimientosController::class, 'deleteNote'])->name('notas.delete');
    Route::post('/notas/listar', [ProcedimientosController::class, 'getNotes'])->name('notas.list');

    //Dar de alta
    Route::post('/procedimientos/actualizar-alta', [ProcedimientosController::class, 'actualizarAlta'])
        ->name('procedimientos.actualizarAlta');


    //Ver fotos de procedimientos, subir foto y eliminar foto
    Route::get('/procedimientos/fotos/{num_med_record}/{step?}', [ProcedimientosController::class, 'verFotos'])
        ->name('procedimientos.fotos');

    Route::post('/procedimientos/fotos/upload/{num_med_record}/{step?}', [ProcedimientosController::class, 'subirFoto'])
        ->name('procedimientos.fotos.upload');

    Route::delete('/procedimientos/fotos/delete', [ProcedimientosController::class, 'eliminarFoto'])
        ->name('procedimientos.fotos.delete');

    //Funciones para segundo procedimiento (retoque)
    //Ver fotos segundo procedimiento
    Route::get('/procedimientos/fotos/touchup/{num_med_record}/{step?}', [ProcedimientosController::class, 'verFotosTouchup'])
        ->name('procedimientos.fotos.touchup');

    // Subir foto
    Route::post(
        '/panel/procedimientos/fotos/subir/{num_med_record}/{step}',
        [ProcedimientosController::class, 'subirFotoSegundo']
    )->name('procedimientos.subirFotoSegundo');

    // Eliminar foto
    Route::delete(
        '/panel/procedimientos/fotos/eliminar',
        [ProcedimientosController::class, 'eliminarFotoSegundo']
    )->name('procedimientos.eliminarFotoSegundo');

    //  Rutas para micropigmentacion
    // Ver fotos micro
    Route::get(
        '/procedimientos/fotos/micro/{num_med_record}/{step?}',
        [ProcedimientosController::class, 'verFotosMicro']
    )->name('procedimientos.fotos.micro');

    // Subir fotos micro
    Route::post(
        '/panel/procedimientos/fotos/micro/subir/{num_med_record}/{step}',
        [ProcedimientosController::class, 'subirFotoMicro']
    )->name('procedimientos.subirFotoMicro');

    // Eliminar fotos micro
    Route::delete(
        '/panel/procedimientos/fotos/micro/eliminar',
        [ProcedimientosController::class, 'eliminarFotoMicro']
    )->name('procedimientos.eliminarFotoMicro');


    // Tratamientos sin expediente médico
    Route::get('/tratamientos', [ProcedimientosController::class, 'indexTratamientos'])->name('tratamientos.index');

    // Tratamientos
    Route::post(
        '/procedimientos/tratamientos/store',
        [ProcedimientosController::class, 'guardarTreatment']
    )->name('procedimientos.tratamientos.store');

    // Guardar nota de treatment
    Route::post(
        '/procedimientos/notas/store',
        [ProcedimientosController::class, 'guardarNotaTratamiento']
    )
        ->name('procedimientos.notas.store');

    // Eliminar nota de treatment
    Route::delete(
        '/procedimientos/notas/delete/{id}',
        [ProcedimientosController::class, 'eliminarNotaTratamiento']
    )->name('procedimientos.notas.delete');


    // Mostrar fotos de un treatment
    Route::get(
        '/procedimientos/fotos/treatment/{num_med_record}/{treatmentId}/{trat?}',
        [ProcedimientosController::class, 'verFotosTreatment']
    )
        ->name('procedimientos.fotos.treatment');

    // Subir foto treatment
    Route::post(
        '/procedimientos/fotos/subir/treatment/{num_med_record}/{treatmentId}',
        [ProcedimientosController::class, 'subirFotoTreatment']
    )
        ->name('procedimientos.subirFotoTreatment');


    // Eliminar foto treatment
    Route::delete(
        '/procedimientos/fotos/eliminar/treatment',
        [ProcedimientosController::class, 'eliminarFotoTreatment']
    )
        ->name('procedimientos.eliminarFotoTreatment');

    // JSON para recargar fotos vía AJAX
    Route::get(
        '/procedimientos/fotos/json/{num_med_record}/{treatmentId}',
        [ProcedimientosController::class, 'obtenerFotosTreatmentJson']
    )
        ->name('procedimientos.fotos.json');

    // Mostrar notas de un treatment
    Route::post(
        '/procedimientos/notas/show',
        [ProcedimientosController::class, 'showNotas']
    )->name('procedimientos.notas.show');

    //Crear tratamiento sin expediente
    Route::post(
        '/procedimientos/treatments/sin-exp/store',
        [ProcedimientosController::class, 'guardarTreatmentSinExp']
    )->name('procedimientos.treatments.sinexp.store');

    //Alterna funciona
    Route::get(
        '/tratamientos/sin-expediente/{px_id}/{treatmentId?}',
        [ProcedimientosController::class, 'verFotosTreatmentSinExpediente']
    )->name('tratamientos.sin-exp.view');

    // JSON
    Route::get(
        '/tratamientos/sin-expediente/json/{px_id}/{treatmentId}',
        [ProcedimientosController::class, 'obtenerFotosTreatmentSinExpJson']
    )->name('tratamientos.sin-exp.json');

    // Subir foto de tratamiento sin expediente
    Route::post(
        '/tratamientos/sin-expediente/{px_id}/{treatmentId}/foto',
        [ProcedimientosController::class, 'subirFotoTreatmentSinExp']
    )->name('tratamientos.sinexp.foto.store');

    // Eliminar foto de tratamiento sin expediente
    Route::delete('/tratamientos/sin-expediente/{px_id}/{treatmentId}/foto', [ProcedimientosController::class, 'eliminarFotoTratamientoSinExp'])
        ->name('tratamientos.sinexp.foto.delete');

    //Funciones auxiliares para procedimientos
    // Generar recibo PDF de tratamiento (SIN crear tratamiento)
    Route::post('/receipts/treatments/generate', [ProcedimientosController::class, 'generateTreatmentReceipt'])
        ->name('receipts.treatments.generate');

    //Rutas de recibos de tratamientos y productos PX
    Route::get('/receipts/treatments/list/{px_id}', [ProcedimientosController::class, 'listTreatmentReceipts'])
        ->name('receipts.treatments.list');


    // con expediente
    Route::post('/receipts/treatments/generate-exp', [ProcedimientosController::class, 'generateTreatmentReceiptExp'])
        ->name('receipts.treatments.generateExp');
    Route::get('/receipts/treatments/list-exp/{num_med_record}', [ProcedimientosController::class, 'listTreatmentReceiptsExp']);

    //firmas de procedimientos
    Route::post('/procedimientos/guardar-firma', [ProcedimientosController::class, 'guardarFirma'])->name('procedimientos.guardarFirma');
    Route::post('/procedimientos/buscar-firma', [ProcedimientosController::class, 'buscarFirma'])->name('procedimientos.buscarFirma');
    Route::delete('/procedimientos/eliminar-firma', [ProcedimientosController::class, 'eliminarFirma'])->name('procedimientos.eliminarFirma');

    Route::post('/get-patient-name', [ProcedimientosController::class, 'getPatientName'])->name('get.patient.name');
    Route::post('/add-treatment', [ProcedimientosController::class, 'addTreatment'])->name('add.treatment');

    /* ------------------------------------------- Termian rutas de procedimientos ---------------------------------------------------- */

    Route::get('usuarios/create', [RegisteredUserController::class, 'create'])->name('usuarios.create');
    Route::post('usuarios/store', [RegisteredUserController::class, 'store'])->name('usuarios.store');

    Route::get('usuarios', [UserController::class, 'index'])->name('usuarios.index');
    Route::delete('usuarios/{user}', [UserController::class, 'destroy'])->name('usuarios.destroy');
    Route::put('usuarios/{user}', [UserController::class, 'update'])->name('usuarios.update');
    Route::get('usuarios/{user}', [UserController::class, 'show'])->name('usuarios.show');
    // Listar empleados (vista principal)
    // Listar empleados (vista principal)
    Route::get('/empleados', [EmpleadoController::class, 'index'])->name('empleados.index');

    // Crear empleado
    Route::post('/empleados', [EmpleadoController::class, 'store'])->name('empleados.store');

    // Editar empleado (formulario modal enviado con PUT)
    Route::put('/empleados/{empleado}', [EmpleadoController::class, 'update'])->name('empleados.update');

    // Eliminar empleado
    Route::post('panel/empleados/{empleado}/destroy', [EmpleadoController::class, 'destroy'])->name('panel.empleados.destroy');

    Route::get('usuarios/{user}/edit', [UserController::class, 'edit'])->name('usuarios.edit');
    Route::delete('usuarios/{user}', [UserController::class, 'destroy'])->name('usuarios.destroy');

    route::get('/landing', [PanelLandingController::class, 'index'])->name('landing.index');
    // Crear resultado público (opcional, si lo necesitas)
    Route::get('/landing/resultados/create', [PanelLandingController::class, 'createResultado'])->name('landing.resultado.create');
    Route::post('/landing/resultados', [PanelLandingController::class, 'storeResultado'])->name('landing.resultado.store');

    //rutas de inventarios
    Route::get('inventario', [InventarioController::class, 'index'])->name('inventario.index');
    Route::get('/api/products', [InventarioController::class, 'getProducts']);
    Route::post('inventario/movimiento', [InventarioController::class, 'movimientoInv'])->name('inventario.movimiento');
    Route::put('inventario/update', [InventarioController::class, 'updateProd'])->name('inventario.update');
    Route::delete('inventario/destroy/{id}', [InventarioController::class, 'destroyProd'])->name('inventario.destroy');
    Route::post('inventario/salida', [InventarioController::class, 'salidaProducto'])->name('inventario.salida');
    //Kits medicos
    Route::get('/productos', [InventarioController::class, 'getProductos'])->name('getProductos'); // Trae todos los productos disponibles
    Route::get('/obtener', [InventarioController::class, 'getKit'])->name('getKit');         // Trae los productos del kit (por tipo y clínica)
    Route::post('/guardar', [InventarioController::class, 'guardarKit'])->name('guardarKit');    // Guarda o actualiza los kits

    //Salida rapida
    Route::post('/salidas-rapidas', [InventarioController::class, 'registrarSalidaRapida'])->name('salidas.rapidas');

    //Rutas de apartado de finanzas
    //Rutas de gastos
    Route::get('gastos', [finanzasController::class, 'indexGastos'])->name('gastos.index');
    Route::post('/gastos/guardarGasto', [finanzasController::class, 'guardarGasto'])->name('gastos.guardar');
    Route::put('/gastos/{id}', [finanzasController::class, 'actualizarGasto'])->name('gastos.actualizar');
    Route::delete('/gastos/{id}', [finanzasController::class, 'eliminarGasto'])->name('gastos.eliminar');
    //Suma de totales de gastos
    Route::get('/gastos/fechas', [finanzasController::class, 'getGastosPorFechas'])->name('gastos.fechas');

    //Rutas de ingresos
    Route::get('ingresos', [finanzasController::class, 'indexIngresos'])->name('ingresos.index');
    Route::get('ingresos-transacciones/data', [finanzasController::class, 'getTransacciones'])->name('ingresosTransacciones.data');

    //Rustas de cortes diarios
    Route::get('cortesDiarios', [finanzasController::class, 'indexCortesDiarios'])->name('cortesDiarios.index');
    Route::post('/load-all-daily', [finanzasController::class, 'loadAllDaily'])->name('corte.loadAllDaily');
    Route::post('/load-total-daily', [finanzasController::class, 'loadTotalDaily'])->name('corte.loadTotalDaily');
    Route::post('/add-sign', [finanzasController::class, 'addSignByDay'])->name('corte.addSignByDay');
    Route::post('/delete-sign', [finanzasController::class, 'deleteSignByDay'])->name('corte.deleteSignByDay');
    Route::post('/generate-cash-closing', [finanzasController::class, 'generateCashClosingDaily'])->name('corte.generateCashClosingDaily');

    // crear encabezado
    Route::post('landing/encabezado', [PanelLandingController::class, 'storeEncabezado'])->name('landing.encabezado.store');
    Route::put('landing/encabezado/{encabezado}', [PanelLandingController::class, 'updateEncabezado'])->name('landing.encabezado.update');
    // eliminar encabezado
    Route::delete('landing/encabezado/{encabezado}', [PanelLandingController::class, 'destroyEncabezado'])->name('landing.encabezado.destroy');
    Route::post('landing/encabezado', [PanelLandingController::class, 'storeEncabezado'])->name('landing.encabezado.store');
    //Rutas de blog
    Route::post('landing/blog', [PanelLandingController::class, 'createBlog'])->name('landing.blog.store');
    Route::put('landing/blog/{blog}', [PanelLandingController::class, 'editBlog'])->name('landing.blog.update');
    //eliminar blog
    Route::delete('landing/blog/{blog}', [PanelLandingController::class, 'destroyBlog'])->name('landing.blog.destroy');

    //Rutas de servicios
    Route::post('landing/servicios', [PanelLandingController::class, 'createServicios'])->name('landing.servicios.store');
    Route::put('landing/servicios/{servicios}', [PanelLandingController::class, 'editServicios'])->name(name: 'landing.servicios.update');
    //eliminar servicios
    Route::delete('landing/servicios/{servicios}', [PanelLandingController::class, 'destroyServicios'])->name('landing.servicios.destroy');

    Route::get('/', [PanelController::class, 'index'])->name('panel.index');
    Route::get('/landing', [PanelLandingController::class, 'index'])->name('landing.index');
    Route::get('/landing/resultados/create', [PanelLandingController::class, 'createResultado'])->name('landing.resultado.create');
    Route::post('/landing/resultados', [PanelLandingController::class, 'storeResultado'])->name('landing.resultado.store');
    Route::put('/landing/resultados/{resultado}', [PanelLandingController::class, 'update'])->name('landing.resultado.update');
    // Quiénes Somos
    Route::post('landing/quienes_somos', [panelLandingController::class, 'storeQuienesSomos'])->name('landing.quienes_somos.store');
    Route::put('landing/quienes_somos/{quienes_somos}', [LandingController::class, 'updateQuienesSomos'])->name('landing.quienes_somos.update');

    // Casos de Éxito
    Route::get('casos-exito', [PanelLandingController::class, 'indexCasoexito'])->name('casos.index');
    Route::post('casos-exito', [PanelLandingController::class, 'storeExito'])->name('casos.store');
    Route::put('casos-exito/{caso}', [PanelLandingController::class, 'updateExito'])->name('casos.update');
    Route::delete('casos-exito/{caso}', [PanelLandingController::class, 'destroyExito'])->name('casos.destroy');

    // ------------------- Rutas de calendario --------------------------------------------------------
    // Listado de vacaciones
    Route::get('/holidays', [HolidayController::class, 'index'])->name('holidays.index');



    // Formulario para crear
    Route::get('holidays/create', [HolidayController::class, 'create'])->name('holidays.create');

    // Guardar nueva vacación
    Route::post('holidays/store', [HolidayController::class, 'store'])->name('holidays.store');
    Route::match(['get', 'post'], '/vacaciones-card', [PanelController::class, 'vacacionesCard'])
        ->name('vacacionesCard');
    // Ruta para obtener empleados con 1 año o más
    Route::match(['get', 'post'], 'empleados-aniversario', [PanelController::class, 'empleadosAniversario'])
        ->name('empleadosAniversario');

    Route::delete('/{holiday}', [HolidayController::class, 'destroy'])->name('holidays.destroy');
    Route::post(
        '/calendar/store-evento',
        [CalendarController::class, 'storeEvento']
    )->name('calendar.storeEvento');

    Route::get('calendario', function () {
        return view('panel.calendario.index'); // Ajusta la carpeta/nombre del Blade
    })->name('calendar');
    Route::get('calendario-pedregal', function () {
        return view('panel.calendario.pedregal'); // Ajusta la carpeta/nombre del Blade
    })->name('calendar.pedregal');
    Route::get('calendario-queretaro', function () {
        return view('panel.calendario.queretaro'); // Ajusta la carpeta/nombre del Blade
    })->name('calendar.queretaro');
    Route::post('/calendar/agenda-by-clinic', [CalendarController::class, 'agendaByClinic'])
        ->name('calendar.agendaByClinic');
    Route::post('/calendar/store', [CalendarController::class, 'store'])->name('calendar.store');
    Route::put('/calendar/update/{id}', [CalendarController::class, 'update'])->name('calendar.update');
    Route::delete('/calendar/destroy/{id}', [CalendarController::class, 'destroy'])->name('calendar.destroy');
    Route::post('calendar/copy-agenda', action: [CalendarController::class, 'copyAgenda'])->name('calendar.copyAgenda');
    Route::get('calendar/search-by-expediente',   [CalendarController::class, 'searchEventsByMedicalRecord'])->name('calendar.search.expediente');

    //------------------------------------ FIN Rutas de calendario ------------------------------------------------ 


    //Rutas del panel del DRSantana


    // Vista principal del panel Dr. Santana
    Route::get('/doctor-santana', [PanelDoctorSantanaController::class, 'indexDrsantana'])->name('drsantana.index');

    // Blog Dr. Santana
    Route::get('/doctor-santana/blog', [PanelDoctorSantanaController::class, 'indexsBlog'])->name('drsantana.blog.index'); // vista lista de blogs
    Route::get('/doctor-santana/blog/list', [PanelDoctorSantanaController::class, 'getBlogsdr'])->name('drsantana.blog.list'); // AJAX / JSON
    Route::post('/doctor-santana/blog', [PanelDoctorSantanaController::class, 'storeBlogdr'])->name('drsantana.blog.store');
    Route::put('/doctor-santana/blog/{id}', [PanelDoctorSantanaController::class, 'updateBlogdr'])->name('drsantana.blog.update');
    Route::delete('/doctor-santana/blog/{id}', [PanelDoctorSantanaController::class, 'destroyBlogdr'])->name('drsantana.blog.destroy');
    //Route::get('/doctor-santana/blog', [PanelDoctorSantanaController::class, 'getBlogsdr'])->name('drsantana.blog.list');

    // Galería Dr. Santana
    Route::get('/doctor-santana/galeria', [PanelDoctorSantanaController::class, 'indexGaleria'])->name('drsantana.galeria.index');
    Route::post('/doctor-santana/galeria', [PanelDoctorSantanaController::class, 'storeGaleria'])->name('drsantana.galeria.store');
    Route::put('/doctor-santana/galeria/{galeria}', [PanelDoctorSantanaController::class, 'updateGaleria'])->name('drsantana.galeria.update');
    Route::delete('/doctor-santana/galeria/{galeria}', [PanelDoctorSantanaController::class, 'destroyGaleria'])->name('drsantana.galeria.destroy');

    // Trayectoria Dr. Santana
    Route::get('/doctor-santana/trayectoria', [PanelDoctorSantanaController::class, 'indexTrayectoria'])->name('drsantana.trayectoria.index');
    Route::post('/doctor-santana/trayectoria', [PanelDoctorSantanaController::class, 'storeTrayectoria'])->name('drsantana.trayectoria.store');
    Route::put('/doctor-santana/trayectoria/{trayectoria}', [PanelDoctorSantanaController::class, 'updateTrayectoria'])->name('drsantana.trayectoria.update');
    Route::delete('/doctor-santana/trayectoria/{trayectoria}', [PanelDoctorSantanaController::class, 'destroyTrayectoria'])->name('drsantana.trayectoria.destroy');

    // Contacto Dr. Santana
    Route::get('/doctor-santana/contacto', [PanelDoctorSantanaController::class, 'indexContacto'])->name('drsantana.contacto.index');
    Route::get('/doctor-santana/contacto/{contacto}', [PanelDoctorSantanaController::class, 'showContacto'])->name('drsantana.contacto.show');

    // Certificaciones
    //Route::get('/doctor-santana/certificaciones', [PanelDoctorSantanaController::class, 'indexCertificaciones'])->name('certificaciones.index');
    Route::post('/certificaciones', [PanelDoctorSantanaController::class, 'CerStore'])->name('certificaciones.store');
    Route::put('/certificaciones/{id}', [PanelDoctorSantanaController::class, 'CerUpdate'])->name('certificaciones.update');
    Route::delete('/certificaciones/{id}', [PanelDoctorSantanaController::class, 'CerDestroy'])->name('certificaciones.destroy');

    // Resultados Dr. Santana
    Route::get('/doctor-santana/resultados', [PanelDoctorSantanaController::class, 'indexResultados'])->name('drsantana.resultados.index');
    Route::post('/doctor-santana/resultados', [PanelDoctorSantanaController::class, 'storeResultadoDR'])->name('drsantana.resultados.store');
    Route::put('/doctor-santana/resultados/{resultado}', [PanelDoctorSantanaController::class, 'updateResultadoDR'])->name('drsantana.resultados.update');


    // Contacto
    Route::post('/doctor-santana/contacto', [PanelDoctorSantanaController::class, 'storeContacto'])->name('drsantana.contacto.store');
    Route::put('/doctor-santana/contacto/{contacto}', [PanelDoctorSantanaController::class, 'updateContacto'])->name('drsantana.contacto.update');
    Route::delete('/doctor-santana/contacto/{contacto}', [PanelDoctorSantanaController::class, 'destroyContacto'])->name('drsantana.contacto.destroy');


    Route::get('ventas/create', [VentasController::class, 'create'])->name('ventas.create');
    Route::post('ventas', [VentasController::class, 'store'])->name('ventas.store');
    Route::get('ventas', [VentasController::class, 'index'])->name('ventas.index');


    Route::get('/notifications/patient', [ProcedimientosController::class, 'getPatientNotifications']);
    Route::get('/notifications', [ProcedimientosController::class, 'viewNotifications']);
    Route::post('/notifications/create', [ProcedimientosController::class, 'createNotification'])->name('notifications.create');
    Route::get('/notifications/nextNotification', action: [ProcedimientosController::class, 'nextNotification']);
    Route::post('/notifications/delete', [ProcedimientosController::class, 'destroy'])->name('notifications.delete');

    Route::get('/finanzas/presupuestos', [BudgetController::class, 'index'])
        ->name('budgets.index');

    // AJAX
    Route::post('/finanzas/subcategorias', [BudgetController::class, 'storeSubcategory'])->name('storeSubcategory');
    Route::put('/finanzas/subcategorias/{id}', [BudgetController::class, 'updateSubcategory'])->name('updateSubcategory');
    Route::delete('/finanzas/subcategorias/{id}', [BudgetController::class, 'deleteSubcategory']);

    Route::post('/finanzas/categorias', [BudgetController::class, 'storeCategory'])->name('storeCategory');
    Route::put('/finanzas/categorias/{id}', [BudgetController::class, 'updateCategory'])->name('updateCategory');
    Route::delete('/finanzas/categorias/{id}', [BudgetController::class, 'deleteCategory']);

    Route::get('/nomina', [NominaController::class, 'index'])
        ->name('nomina.index');

    Route::get('/nomina/data', [NominaController::class, 'data'])
        ->name('nomina.data');

    Route::post('/nomina/delete', [NominaController::class, 'destroy'])
        ->name('nomina.delete');

    Route::post('/nomina/layout', [NominaController::class, 'generateLayout'])
        ->name('nomina.layout');

    Route::get('/nomina/layout/download', [NominaController::class, 'downloadLayout'])
        ->name('nomina.layout.download');
    Route::post(
        '/panel/finanzas/nomina/save',
        [NominaController::class, 'save']
    )->name('nomina.save');

    Route::get('/receipts', [ReceiptController::class, 'index']);
    Route::post('/receipts', [ReceiptController::class, 'store'])->name('receipts.store');
    Route::post('/receiptsproduc', [ReceiptController::class, 'storeproduc'])->name('receipts.storeproduc');
    Route::post('/storeAbono', [ReceiptController::class, 'storeAbono'])->name('receipts.storeAbono');
    Route::post(
        '/receipts/storetreatment',
        [ReceiptController::class, 'storeTreatment']
    )->name('receipts.storetreatment');
    Route::get('receipts/{id}/pdf', [ReceiptController::class, 'pdf'])->name('receipts.pdf');
    Route::post('/receipts/storeAnticipo', [ReceiptController::class, 'storeAnticipo'])->name('receipts.storeAnticipo');

    Route::post(
        '/finanzas/corte/existe',
        [DailyExpensesController::class, 'getDailyCorteIfExists']
    )->name('corte.existe');

    Route::post(
        '/finanzas/corte/generar',
        [DailyExpensesController::class, 'generateDailyCorte']
    )->name('finanzas.corte.generar');

Route::post('/daily/update-terminal', [DailyExpensesController::class, 'updateTerminal']);


    //Firmas
    Route::post('/finanzas/corte/firma/save', [DailyExpensesController::class, 'saveDailyFirma'])
        ->name('finanzas.corte.firma.save');

    Route::post('/finanzas/corte/firma/get', [DailyExpensesController::class, 'getDailyFirma'])
        ->name('finanzas.corte.firma.get');

Route::get(
    '/leads/{id}/liquidacion-json',
    [LeadsController::class, 'liquidacionJson']
);


    Route::post(
        '/notifications/delete',
        [ProcedimientosController::class, 'destroy']
    )->name('notifications.destroy');
    Route::get('/leads/{id}/anticipo-json', [LeadsController::class, 'anticipoJson']);

    // --- IGNORE ---
Route::get('holidays/{holiday}', [HolidayController::class, 'show'])
     ->name('holidays.show');
});
