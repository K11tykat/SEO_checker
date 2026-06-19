<?php

use App\Http\Controllers\AuditHistoryController;
use Illuminate\Support\Facades\Route;

// Главная — список исторических проверок + кнопка «Начать проверку».
Route::get('/', [AuditHistoryController::class, 'index'])->name('history.index');

// Запуск новой проверки по введённым URL.
Route::post('/audit/run', [AuditHistoryController::class, 'run'])->name('audit.run');

// Результаты текущей (только что выполненной) проверки.
Route::get('/audit/{id}/results', [AuditHistoryController::class, 'results'])->name('audit.results');

// Экспорт выбранных отчётов в PDF.
Route::post('/audit/{id}/pdf', [AuditHistoryController::class, 'exportPdf'])->name('audit.pdf');

// Удаление проверки целиком.
Route::delete('/audit/{id}', [AuditHistoryController::class, 'destroy'])->name('audit.destroy');

// Детальная страница исторической проверки.
Route::get('/history/{id}', [AuditHistoryController::class, 'show'])->name('history.show');
