<?php

use App\Http\Controllers\TelegramBotController;
use Illuminate\Support\Facades\Route;


Route::post('/webhook' , [TelegramBotController::class,'webhook']);
