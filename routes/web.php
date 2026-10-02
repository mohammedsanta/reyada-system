<?php

use App\Http\Controllers\BankArchiveController;
use App\Http\Controllers\BankClientController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\BankImportController;
use App\Http\Controllers\BankPanelController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\ConfirmationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DcrController;
use App\Http\Controllers\DistributionController;
use App\Http\Controllers\EmployeeDetailsController;
use App\Http\Controllers\EmployeePerformanceController;
use App\Http\Controllers\PtpController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserAssignmentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserPermissionController;
use App\Http\Controllers\UserTeamController;
use App\Http\Controllers\VisitController;
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

Route::resource('users', UserController::class);
  
Route::get('banks/{bank}/scope/import', [BankImportController::class, 'create'])->name('banks.scope.import');
Route::post('banks/{bank}/scope/import', [BankImportController::class, 'store'])->name('banks.scope.import.store');
 

Route::get('banks/{bank}/distribution', [DistributionController::class, 'index'])->name('banks.distribution.index');
Route::post('banks/{bank}/distribution', [DistributionController::class, 'store'])->name('banks.distribution.store');
 

Route::get('confirmations', [ConfirmationController::class, 'index'])->name('confirmations.index');
Route::post('confirmations/{payment}/approve', [ConfirmationController::class, 'approve'])->name('confirmations.approve');
Route::post('confirmations/{payment}/reject', [ConfirmationController::class, 'reject'])->name('confirmations.reject');

Route::resource('users', UserController::class)->only(['index', 'create', 'store']);
Route::resource('roles', RoleController::class)->except('show');

Route::get('users/{user}/permissions', [UserPermissionController::class, 'edit'])->name('users.permissions.edit');
Route::put('users/{user}/permissions', [UserPermissionController::class, 'update'])->name('users.permissions.update');
 
Route::get('users/{user}/assignments', [UserAssignmentController::class, 'edit'])->name('users.assignments.edit');
Route::put('users/{user}/assignments', [UserAssignmentController::class, 'update'])->name('users.assignments.update');

Route::get('users/{user}/team', UserTeamController::class)->name('users.team');

Route::get('employees/{code}', EmployeeDetailsController::class)->name('employees.show');

Route::get('banks/{bank}/dcr', [DcrController::class, 'index'])->name('banks.dcr.index');
Route::put('banks/{bank}/dcr/{report}', [DcrController::class, 'update'])->name('banks.dcr.update');
 


Route::get('banks/{bank}/complaints', [ComplaintController::class, 'index'])->name('banks.complaints.index');
Route::post('banks/{bank}/complaints', [ComplaintController::class, 'store'])->name('banks.complaints.store');
Route::get('banks/{bank}/complaints/{complaint}', [ComplaintController::class, 'show'])->name('banks.complaints.show');
Route::put('banks/{bank}/complaints/{complaint}', [ComplaintController::class, 'update'])->name('banks.complaints.update');


 
Route::get('banks/{bank}/visits', [VisitController::class, 'index'])->name('banks.visits.index');
Route::post('banks/{bank}/visits', [VisitController::class, 'store'])->name('banks.visits.store');
Route::put('banks/{bank}/visits/{visit}', [VisitController::class, 'update'])->name('banks.visits.update');

Route::get('banks/{bank}/archives', [BankArchiveController::class, 'index'])->name('banks.archives.index');
Route::get('banks/{bank}/archives/{archive}', [BankArchiveController::class, 'show'])->name('banks.archives.show');
