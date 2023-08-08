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

    Route::get('dashboard', [\App\Http\Controllers\Dashboard::class, 'index'])->name('dashboard');
    Route::get('dashboard/service_exp/renew/{id}', [\App\Http\Controllers\Dashboard::class, 'service_exp_renew'])
        ->name('dashboard.service_exp.renew');
    Route::get('dashboard/service_exp/alert/{id}', [\App\Http\Controllers\Dashboard::class, 'service_exp_alert'])
        ->name('dashboard.service_exp.alert');
    Route::get('dashboard/service_exp/invoice/{id}', [\App\Http\Controllers\Dashboard::class, 'service_exp_invoice'])
        ->name('dashboard.service_exp.invoice');

    Route::get('service', [\App\Http\Controllers\Service::class, 'index'])->name('service.index');
    Route::get('service/create', [\App\Http\Controllers\Service::class, 'create'])->name('service.create');
    Route::post('service/store', [\App\Http\Controllers\Service::class, 'store'])->name('service.store');
    Route::get('service/edit/{id}', [\App\Http\Controllers\Service::class, 'edit'])->name('service.edit');
    Route::post('service/update/{id}', [\App\Http\Controllers\Service::class, 'update'])->name('service.update');
    Route::get('service/destroy/{id}', [\App\Http\Controllers\Service::class, 'destroy'])->name('service.destroy');

    Route::get('customer', [\App\Http\Controllers\Customer::class, 'index'])->name('customer.index');
    Route::get('customer/create', [\App\Http\Controllers\Customer::class, 'create'])->name('customer.create');
    Route::post('customer/store', [\App\Http\Controllers\Customer::class, 'store'])->name('customer.store');
    Route::get('customer/edit/{id}', [\App\Http\Controllers\Customer::class, 'edit'])->name('customer.edit');
    Route::post('customer/update/{id}', [\App\Http\Controllers\Customer::class, 'update'])->name('customer.update');
    Route::get('customer/destroy/{id}', [\App\Http\Controllers\Customer::class, 'destroy'])->name('customer.destroy');

    Route::any('customer/service_exp/create/{id}', [\App\Http\Controllers\CustomerServiceExpiration::class, 'create'])->name('customer.serviceExpiration.create');
    Route::post('customer/service_exp/store', [\App\Http\Controllers\CustomerServiceExpiration::class, 'store'])->name('customer.serviceExpiration.store');
    Route::any('customer/service_exp/edit/{id}', [\App\Http\Controllers\CustomerServiceExpiration::class, 'edit'])->name('customer.serviceExpiration.edit');
    Route::post('customer/service_exp/update/{id}', [\App\Http\Controllers\CustomerServiceExpiration::class, 'update'])->name('customer.serviceExpiration.update');
    Route::get('customer/service_exp/destroy/{id}', [\App\Http\Controllers\CustomerServiceExpiration::class, 'destroy'])->name('customer.serviceExpiration.destroy');

    Route::get('finance/incoming', [\App\Http\Controllers\Finance::class, 'incoming'])->name('finance.incoming');
    Route::get('finance/outcoming', [\App\Http\Controllers\Finance::class, 'outcoming'])->name('finance.outcoming');
    Route::get('finance/outcoming/{category}', [\App\Http\Controllers\Finance::class, 'outcoming_category'])->name('finance.outcoming.category');


    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

});

// ----------------------------------------

Route::get('payment/checkout/{sid}', [\App\Http\Controllers\Payment::class, 'show'])->name('payment.checkout');
Route::post('payment/checkout/update/{sid}', [\App\Http\Controllers\Payment::class, 'update'])->name('payment.update');
Route::get('payment/confirm/{sid}', [\App\Http\Controllers\Payment::class, 'confirm'])->name('payment.confirm');

Route::get('mail/show/{view}/{sid}', [\App\Http\Controllers\Email::class, 'show'])->name('email.show');

Route::get('gapi/scriptable', [\App\Http\Controllers\GoogleSheetsAPI::class, 'scriptableGetJSON']);

/**
 * CronJob
 * ======================================================================
 */
/**
 * serviceM - FiC2DB
 * Recupero i dati da FIC e li importo nel DB
 */
Route::get('finance/fic/get', [\App\Http\Controllers\Finance::class, 'documentsGet'])->name('finance.documents.get');

/**
 * serviceM - gSheets
 * Prendo i dati dal DB e li inserito in un foglio di calcolo Google Sheets
 */
Route::get('gapi/gsheets', [\App\Http\Controllers\GoogleSheetsAPI::class, 'update']);

/**
 * serviceM - autoRenew
 * Auto rinnovo i servizi auto rinnovabili
 */
Route::get('service/autorenew', [\App\Http\Controllers\Service::class, 'autorenew']);

/**
 * serviceM - expiration alert
 * Invio email di avviso scadenza
 */
Route::get('mail/service-expiration/all', [\App\Http\Controllers\Email::class, 'sendExpirationList'])->name('email.exp.all');
/**
 * ======================================================================
 */

require __DIR__.'/auth.php';
