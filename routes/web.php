<?php

use Illuminate\Support\Facades\Route;

Route::group(
    config('restricted-access.route'),
    function () {
        Route::post(
            '{uuid}/check-pin',
            \LinkRestrictedAccess\Http\Controllers\CheckPinController::class
        )->name('check-pin');
        Route::post(
            '{uuid}/open-document',
            \LinkRestrictedAccess\Http\Controllers\OpenDocumentController::class
        )->name('open-document');
    }
);
