<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\algebricoController;

use App\Http\Controllers\SimplexController;

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

Route::get('/simplex',[SimplexController::class, 'simplex']);

Route::get('/', function () {
    return view('parte1');
});

Route::get('/parte1', function () {
    return view('parte1');
})->name('parte1');

Route::post('/parte2', [algebricoController::class, 'index'])->name('parte2');

Route::post('/parte3', [SimplexController::class, 'simplex'])->name('parte3');

Route::fallback(function () {

    echo 'Anota acessada não existe. <a href= "' . route('parte1') . '">Clique aqui</a> para ir para a página inicial';
});
