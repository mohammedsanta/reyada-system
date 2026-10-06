<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\BankArchiveController;
use App\Http\Controllers\BankClientAssignController;
use App\Http\Controllers\BankClientController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\BankImportController;
use App\Http\Controllers\BankPanelController;
use App\Http\Controllers\BankScopeEditController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\ConfirmationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DcrController;
use App\Http\Controllers\DistributionController;
use App\Http\Controllers\Employee\EmployeeDetailsController;
use App\Http\Controllers\EmployeePerformanceController;
use App\Http\Controllers\GovernorateController;
use App\Http\Controllers\InstallmentCompanyController;
use App\Http\Controllers\LoanTypeController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OperationController;
use App\Http\Controllers\OverviewController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\PromiseController;
use App\Http\Controllers\PtpController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
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

Route::get('banks/{bank}/scope/edit', [BankScopeEditController::class, 'edit'])->name('banks.scope.edit');
Route::put('banks/{bank}/scope/edit', [BankScopeEditController::class, 'update'])->name('banks.scope.update');


Route::get('banks/{bank}/ptp/create', [PromiseController::class, 'create'])->name('banks.ptp.create');
Route::post('banks/{bank}/ptp', [PromiseController::class, 'store'])->name('banks.ptp.store');
Route::get('banks/{bank}/ptp/{promise}', [PromiseController::class, 'show'])->name('banks.ptp.show');
Route::put('banks/{bank}/ptp/{promise}', [PromiseController::class, 'update'])->name('banks.ptp.update');


Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
Route::post('notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
Route::post('notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');

Route::get('overview', OverviewController::class)->name('overview.index');

Route::get('operations', OperationController::class)->name('operations.index');

Route::resource('installment-companies', InstallmentCompanyController::class)
    ->except('show')
    ->parameters(['installment-companies' => 'company']);
 
// Control panel of one company: /installment-companies/1/panel
Route::get('installment-companies/{company}/panel', [InstallmentCompanyController::class, 'panel'])->name('installment-companies.panel');

Route::get('archives', [ArchiveController::class, 'index'])->name('archives.index');

Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
Route::post('reports', [ReportController::class, 'store'])->name('reports.store');
 
Route::get('reports/exports', [ReportController::class, 'exports'])->name('reports.exports');
Route::post('reports/exports/{export}/retry', [ReportController::class, 'retry'])->name('reports.exports.retry');
Route::delete('reports/exports/{export}', [ReportController::class, 'destroy'])->name('reports.exports.destroy');

 
Route::post('banks/{bank}/clients/assign', BankClientAssignController::class)->name('banks.clients.assign');

Route::get(
    '/banks/{bank}/clients/assign/employees/search',
    [BankClientAssignController::class, 'searchEmployees']
)->name('banks.clients.assign.employees.search');

Route::post(
    '/banks/{bank}/clients/assign',
    BankClientAssignController::class
)->name('banks.clients.assign');


Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

// "/" opens the dashboard (or the login page if the visitor is not logged in)
Route::redirect('/', '/dashboard');
 
// ---- only for visitors who are NOT logged in ----
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store']);
 
    Route::get('forgot-password', [PasswordResetController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetController::class, 'store'])->name('password.email');
 
    Route::get('reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('reset-password', [PasswordResetController::class, 'update'])->name('password.store');
});
 
// ---- logout: only for logged in users, and only by POST (the sidebar menu already posts to it) ----
Route::post('logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');
 
 
/* ---------------------------------------------------------------------------------------------
   bootstrap/app.php : tell Laravel where to send people
   (inside ->withMiddleware(function (Middleware $middleware) { ... }) )
 
       $middleware->redirectGuestsTo(fn () => route('login'));       // not logged in  -> login page
       $middleware->redirectUsersTo(fn () => route('dashboard'));    // already logged in and opens /login -> dashboard
   --------------------------------------------------------------------------------------------- */
 

Route::get('account', [AccountController::class, 'edit'])->name('account.edit');
Route::put('account', [AccountController::class, 'update'])->name('account.update');
Route::put('account/password', [AccountController::class, 'password'])->name('account.password');

Route::get('/t403', fn () => abort(403, 'لا يمكن تعديل حساب النظام.'));
Route::get('/t419', fn () => abort(419));
Route::get('/t429', fn () => abort(429));
Route::get('/t500', fn () => abort(500));
Route::get('/t503', fn () => abort(503));
// 404: just open any URL that does not exist

Route::get('clients', [ClientController::class, 'index'])->name('clients.index');

// 

// System settings
Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
 
// Lookup lists (the URL parameter names match the controller methods)
Route::resource('loan-types', LoanTypeController::class)
    ->only(['index', 'store', 'update', 'destroy'])
    ->parameters(['loan-types' => 'type']);
 
Route::resource('governorates', GovernorateController::class)
    ->only(['index', 'store', 'update', 'destroy'])
    ->parameters(['governorates' => 'governorate']);
 