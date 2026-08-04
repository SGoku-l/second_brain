<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\McpController;

Route::controller(McpController::class)->group(function () {

    Route::post('/mcp' , 'handle');

});