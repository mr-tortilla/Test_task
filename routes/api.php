<?php

use App\Http\Controllers\RatesController;
use Illuminate\Support\Facades\Route;

Route::any('/v1', [ RatesController::class, 'proceed' ])->middleware([ 'auth' ]);
