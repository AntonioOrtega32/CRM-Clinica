<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

use App\Http\Controllers\CalendarController;
use App\Http\Controllers\DailyExpensesController;

Route::prefix('calendar')->group(function () {
    Route::get('/', [CalendarController::class, 'index'])->name('calendar.index');
    Route::delete('/{id}', [CalendarController::class, 'destroy'])->name('calendar.destroy');
    Route::post('/search', [CalendarController::class, 'searchEventsByTitle'])->name('calendar.search');
    
    // Solo una ruta para agenda por clínica
    Route::post('/agenda', [CalendarController::class, 'agendaByClinic'])->name('calendar.agendaByClinic');


    // Ruta para copiar agenda

});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});



Route::post('/finanzas/gastos/totales', [DailyExpensesController::class, 'loadTotals']);
Route::get('/test', function () {
    return response()->json(['ok' => true]);
});
Route::post(
    'finanzas/gastos/diarios',
    [DailyExpensesController::class, 'loadDaily']
);
Route::post(
    'finanzas/gastos/eliminar',
    [DailyExpensesController::class, 'deleteExpense']
);
Route::post(
    'finanzas/corte/generar',
    [DailyExpensesController::class, 'generateDailyCorte']
);
