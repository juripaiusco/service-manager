<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});*/

Route::middleware('auth')->group(function () {

    Route::get('/', function () {
        return to_route('dashboard');
    });

    Route::get('/dashboard', [\App\Http\Controllers\Dashboard::class, 'index'])->name('dashboard');


    Route::get('/service', [\App\Http\Controllers\Service::class, 'index'])->name('service.index');
    Route::get('/service/create', [\App\Http\Controllers\Service::class, 'create'])->name('service.create');
    Route::post('/service/store', [\App\Http\Controllers\Service::class, 'store'])->name('service.store');
    Route::get('/service/edit/{id}', [\App\Http\Controllers\Service::class, 'edit'])->name('service.edit');
    Route::post('/service/update/{id}', [\App\Http\Controllers\Service::class, 'update'])->name('service.update');
    Route::get('/service/destroy/{id}', [\App\Http\Controllers\Service::class, 'destroy'])->name('service.destroy');

    Route::get('/customer', [\App\Http\Controllers\Customer::class, 'index'])->name('customer.index');
    Route::get('/customer/create', [\App\Http\Controllers\Customer::class, 'create'])->name('customer.create');
    Route::post('/customer/store', [\App\Http\Controllers\Customer::class, 'store'])->name('customer.store');
    Route::get('/customer/edit/{id}', [\App\Http\Controllers\Customer::class, 'edit'])->name('customer.edit');
    Route::post('/customer/update/{id}', [\App\Http\Controllers\Customer::class, 'update'])->name('customer.update');
    Route::get('/customer/destroy/{id}', [\App\Http\Controllers\Customer::class, 'destroy'])->name('customer.destroy');

    Route::get('/customer/service_exp/create', [\App\Http\Controllers\CustomerServiceExpiration::class, 'create'])->name('customer.serviceExpiration.create');
    Route::post('/customer/service_exp/store', [\App\Http\Controllers\CustomerServiceExpiration::class, 'store'])->name('customer.serviceExpiration.store');
    Route::get('/customer/service_exp/edit/{id}', [\App\Http\Controllers\CustomerServiceExpiration::class, 'edit'])->name('customer.serviceExpiration.edit');
    Route::post('/customer/service_exp/update/{id}', [\App\Http\Controllers\CustomerServiceExpiration::class, 'update'])->name('customer.serviceExpiration.update');
    Route::get('/customer/service_exp/destroy/{id}', [\App\Http\Controllers\CustomerServiceExpiration::class, 'destroy'])->name('customer.serviceExpiration.destroy');


    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});

require __DIR__.'/auth.php';
