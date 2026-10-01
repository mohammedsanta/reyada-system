<?php

use App\Http\Controllers\BankClientController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\BankPanelController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeePerformanceController;
use App\Http\Controllers\PtpController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/banks', function () {
//     return view('banks.banks_view');
// })->name('banks.index');
Route::redirect('/', '/dashboard');

Route::get('/dashboard', function () {
    return view('dashboard.dashboard');
})->name('dashboard');

Route::resource('banks', BankController::class);
Route::get('/dashboard', DashboardController::class)->name('dashboard');
Route::resource('users', UserController::class)->only(['index']);
Route::get('/employees', [EmployeePerformanceController::class, 'index'])->name('employees.index');
Route::resource('banks.clients', BankClientController::class)->only(['update', 'destroy']);
// PTP Hub of one bank: /banks/1/ptp
Route::get('banks/{bank}/ptp', [PtpController::class, 'index'])->name('banks.ptp.index');
Route::get('banks/{bank}/panel', BankPanelController::class)->name('banks.panel');
