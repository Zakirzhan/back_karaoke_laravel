<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VideoScrapeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
// */

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::get('/getMixData/{id}', [VideoScrapeController::class, 'getMixData']);

Route::get('/downloadVideo/{id}', [VideoScrapeController::class, 'downloadVideo']);


Route::get('/getDownloadingStatus/{trackId}', [VideoScrapeController::class, 'getDownloadingStatus']);


Route::get('/createMixFromTrack/{trackId}', [VideoScrapeController::class, 'createMixFromTrack']);


Route::get('/getMixingStatus/{taskId}', [VideoScrapeController::class, 'getMixingStatus']);



