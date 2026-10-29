<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeSalaryController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EmployeeComplianceSettingsController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\ContractServiceController;
use App\Http\Controllers\ServiceVehicleController;





Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [PageController::class, 'sendContact'])->name('contact.send');

// Placeholder because no login.html was included with the supplied files.
Route::get('/login', [PageController::class, 'loginPlaceholder'])->name('login');








Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

    Route::get('/register', [AuthController::class, 'showRegistration'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    //Route::view('/', 'dashboard.index')->name('dashboard');
    Route::view('/dashboard', 'dashboard.index')->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/employee-compliance-settings', [EmployeeComplianceSettingsController::class, 'index'])
        ->name('employee-compliance-settings.index');
    Route::put('/employee-compliance-settings', [EmployeeComplianceSettingsController::class, 'update'])
        ->middleware('can:manage-compliance-settings')
        ->name('employee-compliance-settings.update');

    Route::middleware('can:manage-users')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::patch('/users/{user}/role', [UserController::class, 'updateRole'])
            ->name('users.role.update');
    });
});









Route::middleware(['auth'])->group(function () {
    Route::get('/employees', [EmployeeController::class, 'index'])
        ->name('employees.index');

    Route::post('/employees', [EmployeeController::class, 'store'])
        ->middleware('can:records.create')
        ->name('employees.store');

    Route::put('/employees/{id}', [EmployeeController::class, 'update'])
        ->middleware('can:records.update')
        ->name('employees.update');

    Route::delete('/employees/{id}', [EmployeeController::class, 'destroy'])
        ->middleware('can:records.delete')
        ->name('employees.destroy');

    Route::get('/employee-salaries', [EmployeeSalaryController::class, 'index'])
        ->name('employee-salaries.index');

    Route::get('/employee-salaries/export', [EmployeeSalaryController::class, 'export'])
        ->name('employee-salaries.export');

    Route::post('/employee-salaries', [EmployeeSalaryController::class, 'store'])
        ->middleware('can:records.create')
        ->name('employee-salaries.store');

    Route::put('/employee-salaries/{id}', [EmployeeSalaryController::class, 'update'])
        ->middleware('can:records.update')
        ->name('employee-salaries.update');

    Route::delete('/employee-salaries/{id}', [EmployeeSalaryController::class, 'destroy'])
        ->middleware('can:records.delete')
        ->name('employee-salaries.destroy');

    Route::get('/contracts', [ContractController::class, 'index'])
        ->name('contracts.index');

    Route::get('/contracts/check-number', [ContractController::class, 'checkNumber'])
        ->name('contracts.check-number');

    Route::get('/contracts/{contractId}/services', [ContractServiceController::class, 'edit'])
        ->name('contracts.services.edit');

    Route::put('/contracts/{contractId}/services', [ContractServiceController::class, 'update'])
        ->middleware('can:records.update')
        ->name('contracts.services.update');

    Route::get('/contracts/{contractId}/services/{assignmentId}/move', [ContractServiceController::class, 'editMove'])
        ->middleware('can:records.update')
        ->name('contracts.services.move.edit');

    Route::post('/contracts/{contractId}/services/{assignmentId}/move', [ContractServiceController::class, 'move'])
        ->middleware('can:records.update')
        ->name('contracts.services.move');

    Route::get('/service-vehicles', [ServiceVehicleController::class, 'index'])
        ->name('service-vehicles.index');

    Route::post('/service-vehicles', [ServiceVehicleController::class, 'store'])
        ->middleware('can:records.create')
        ->name('service-vehicles.store');

    Route::put('/service-vehicles/{id}', [ServiceVehicleController::class, 'update'])
        ->middleware('can:records.update')
        ->name('service-vehicles.update');

    Route::delete('/service-vehicles/{id}', [ServiceVehicleController::class, 'destroy'])
        ->middleware('can:records.delete')
        ->name('service-vehicles.destroy');

    Route::post('/contracts', [ContractController::class, 'store'])
        ->middleware('can:records.create')
        ->name('contracts.store');

    Route::put('/contracts/{id}', [ContractController::class, 'update'])
        ->middleware('can:records.update')
        ->name('contracts.update');

    Route::delete('/contracts/{id}', [ContractController::class, 'destroy'])
        ->middleware('can:records.delete')
        ->name('contracts.destroy');
});
