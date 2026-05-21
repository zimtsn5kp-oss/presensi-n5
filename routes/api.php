<?php

use App\Http\Controllers\Api\FingerprintApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('fingerprints/{fingerprint}')->middleware('throttle:120,1')->group(function () {
    Route::post('ping', [FingerprintApiController::class, 'ping']);
    Route::post('logs', [FingerprintApiController::class, 'storeLog']);
});
