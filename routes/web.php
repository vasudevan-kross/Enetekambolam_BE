<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ForgotPasswordController;
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

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('password/reset/confirm/{token}/password_reset_email&vsur$93&8$8&$)&$*=password_reset_email', [ForgotPasswordController::class, 'ResetPassword'])->name('ResetPasswordGet');
