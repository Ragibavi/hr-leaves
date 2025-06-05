<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/admins/data', [AdminController::class, 'getData'])->name('admins.data');
    Route::resource('admins', AdminController::class);

    Route::get('/employees/data', [EmployeeController::class, 'getData'])->name('employees.data');
    Route::resource('employees', EmployeeController::class);

    Route::get('/leaves/data', [LeaveController::class, 'getData'])->name('leaves.data');
    Route::get('/leaves/dataDetail/{id}', [LeaveController::class, 'getDataDetail'])->name('leaves.dataDetail');
    Route::get('/leave/employees', [LeaveController::class, 'indexWithLeaves'])->name('leaves.employees');
    Route::get('/leave/employee/{id}', [LeaveController::class, 'employeeLeaves'])->name('leaves.employeeLeaves');
    Route::resource('leaves', LeaveController::class);
});

require __DIR__.'/auth.php';
