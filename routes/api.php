<?php

use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\AuteurController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategorieController;
use App\Http\Controllers\API\ContactController;
use App\Http\Controllers\API\EventController;
use App\Http\Controllers\API\ForgortPasswordController;
use App\Http\Controllers\API\LibrairieController;
use App\Http\Controllers\API\NewsletterController;
use App\Http\Controllers\API\ParcourController;
use App\Models\Parcour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [ForgortPasswordController::class, 'sendResetLinkEmail']);
Route::post('/reset-password', [ForgortPasswordController::class, 'resetPassword'])->name('password.reset');


Route::middleware('auth:api')->group(function () {
    Route::get('/me',  [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::post('/change-password', [AuthController::class, 'changePassword']);

    Route::get('/librairie', [LibrairieController::class, 'index']);
    Route::get('/librairie/{id}', [LibrairieController::class, 'showFile']);
    Route::get('/librairie/detail/{id}', [LibrairieController::class, 'showInfo']);
    Route::get('/librairie/{id}/download', [LibrairieController::class, 'download']);

    Route::get('/event', [EventController::class, 'index']);
    Route::get('/event/detail/{id}', [EventController::class, 'showEventDetailById']);

    Route::get('/parcours/list', [ParcourController::class, 'index']);
    Route::get('/parcours/detail/{id}', [ParcourController::class, 'showParcoursDetail']);
});

//admin routes
Route::middleware(['auth:api', 'admin'])->group(function () {

    Route::post('/admin/store', [AdminController::class, 'store']);
    Route::get('/admin/pending-member', [AdminController::class, 'getPendingMember']);
    Route::get('/admin/rejected-member', [AdminController::class, 'getRejectMember']);
    Route::post('/admin/approve-member/{id}', [AdminController::class, 'approveMember']);
    Route::post('/admin/reject-member/{id}', [AdminController::class, 'rejectMember']);
    Route::post('/admin/pending-member/{id}', [AdminController::class, 'pendingMemberAfterApprove']);
    Route::get('/admin/active', [AdminController::class, 'getActiveMember']);
    Route::get('/admin/all-member', [AdminController::class, 'getAllMember']);
    Route::get('/admin/admin-list', [AdminController::class, 'getAdmin']);
    Route::post('/admin/change-status/{id}/member', [AdminController::class, 'changeMemberStatus']);

    Route::post('/librairie', [LibrairieController::class, 'store']);
    Route::put('/librairie/{id}', [LibrairieController::class, 'update']);
    Route::delete('/librairie/{id}', [LibrairieController::class, 'destroy']);

    Route::get('/auteur', [AuteurController::class, 'index']);
    Route::post('/auteur', [AuteurController::class, 'store']);
    Route::delete('/auteur/delete/{id}', [AuteurController::class, 'destroy']);

    Route::post('/event', [EventController::class, 'store']);
    Route::put('/event/update/{id}', [EventController::class, 'update']);
    Route::delete('/event/delete/{id}', [EventController::class, 'destroy']);

    Route::post('/parcours/store', [ParcourController::class, 'store']);
    Route::put('/parcours/update/{id}', [ParcourController::class, 'update']);
    Route::delete('/parcours/delete/{id}', [ParcourController::class, 'destroy']);

    Route::post('/admin/categorie', [CategorieController::class, 'store']);
});

#design by softskills


//contact routes
Route::post('/contact', [ContactController::class, 'store']);
//newsletter routes
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe']);
Route::post('/newsletter/unsubscribe', [NewsletterController::class, 'unsubscribe']);

// Route::get('/newsletter/subscribers', [NewsletterController::class, 'getSubscribers'])->middleware('api:auth');
