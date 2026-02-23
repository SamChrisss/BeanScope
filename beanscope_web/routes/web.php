<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CoffeePredictionController;

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


Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/predict', [CoffeePredictionController::class, 'index'])->name('predict.index');
Route::post('/predict', [CoffeePredictionController::class, 'predict'])->name('predict');


