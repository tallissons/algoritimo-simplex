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

Route::post('/simplex',[SimplexController::class, 'simplex'])->name('simplex');
Route::get('/tabela',[SimplexController::class, 'tabela'])->name('tabela');

Route::get('/', function () {
    return view('parte1');
})->name('inicio');

Route::get('/parte1', function () {
    return view('parte1');
})->name('parte1');

Route::post('/parte2', [algebricoController::class, 'index'])->name('parte2');

Route::post('/parte3', [algebricoController::class, 'calcular'])->name('parte3');

Route::fallback(function () {

    echo 'Anota acessada não existe. <a href= "' . route('parte1') . '">Clique aqui</a> para ir para a página inicial';
});
