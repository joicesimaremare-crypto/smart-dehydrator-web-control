<?php

use App\Http\Controllers\DeviceController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DeviceController::class, 'index']);
Route::get('/devices', [DeviceController::class, 'list']);
Route::post('/devices/all-off', [DeviceController::class, 'allOff']);
Route::post('/devices/{device}/toggle', [DeviceController::class, 'toggle']);

// untuk ESP32
Route::get('/esp/state', [DeviceController::class, 'esp']);