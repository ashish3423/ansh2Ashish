<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeSalaryController;
use App\Http\Controllers\PageController;





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
});









Route::middleware(['auth'])->group(function () {
    Route::get('/employees', [EmployeeController::class, 'index'])
        ->name('employees.index');

    Route::post('/employees', [EmployeeController::class, 'store'])
        ->name('employees.store');

    Route::put('/employees/{id}', [EmployeeController::class, 'update'])
        ->name('employees.update');

    Route::delete('/employees/{id}', [EmployeeController::class, 'destroy'])
        ->name('employees.destroy');

    Route::get('/employee-salaries', [EmployeeSalaryController::class, 'index'])
        ->name('employee-salaries.index');

    Route::post('/employee-salaries', [EmployeeSalaryController::class, 'store'])
        ->name('employee-salaries.store');

    Route::put('/employee-salaries/{id}', [EmployeeSalaryController::class, 'update'])
        ->name('employee-salaries.update');

    Route::delete('/employee-salaries/{id}', [EmployeeSalaryController::class, 'destroy'])
        ->name('employee-salaries.destroy');
});






