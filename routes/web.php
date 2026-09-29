<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LawyerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\LawyerDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\LawyerController as AdminLawyerController;
use App\Http\Controllers\Admin\SpecializationController as AdminSpecializationController;
use App\Http\Controllers\Admin\LocationController as AdminLocationController;
use App\Http\Controllers\Admin\FaqController as AdminFaqController;
use App\Http\Controllers\Admin\NewsController as AdminNewsController;
use App\Http\Controllers\Admin\AppointmentController as AdminAppointmentController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home.index');
Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::view('/sitemap', 'sitemap')->name('sitemap');
Route::get('/announcements', [\App\Http\Controllers\PublicContentController::class, 'announcements'])->name('announcements');
Route::get('/faq', [\App\Http\Controllers\PublicContentController::class, 'faq'])->name('faq');

Route::get('/lawyers', [LawyerController::class, 'index'])->name('lawyers.index');
Route::get('/lawyers/{id}', [LawyerController::class, 'show'])->name('lawyers.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/lawyer/register', [AuthController::class, 'showLawyerRegister'])->name('lawyer.register');
    Route::post('/lawyer/register', [AuthController::class, 'registerLawyer'])->name('lawyer.register.store');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');


Route::middleware(['auth', 'active'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'password'])->name('profile.password');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::patch('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.readAll');

    Route::middleware('role:customer')->prefix('my')->name('customer.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/appointments', [AppointmentController::class, 'index'])->name('appointments.index');
        Route::get('/appointments/{appointment}', [AppointmentController::class, 'show'])->name('appointments.show');
        Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
        Route::get('/appointments/{appointment}/reschedule', [AppointmentController::class, 'rescheduleForm'])->name('appointments.reschedule.form');
        Route::post('/appointments/{appointment}/reschedule', [AppointmentController::class, 'reschedule'])->name('appointments.reschedule');
        Route::post('/appointments/{appointment}/review', [ReviewController::class, 'store'])->name('appointments.review');
        Route::get('/lawyers/{lawyer}/appointments/create', [AppointmentController::class, 'create'])->name('appointments.create');
        Route::post('/lawyers/{lawyer}/appointments', [AppointmentController::class, 'store'])->name('appointments.store');
    });

    Route::middleware('role:lawyer')->prefix('lawyer')->name('lawyer.')->group(function () {
        Route::get('/dashboard', [LawyerDashboardController::class, 'index'])->name('dashboard');
        Route::patch('/appointments/{appointment}', [LawyerDashboardController::class, 'updateAppointment'])->name('appointments.update');
        Route::post('/appointments/{appointment}/reschedule', [LawyerDashboardController::class, 'rescheduleAppointment'])->name('appointments.reschedule');
        Route::post('/slots', [LawyerDashboardController::class, 'storeSlot'])->name('slots.store');
        Route::put('/slots/{slot}', [LawyerDashboardController::class, 'updateSlot'])->name('slots.update');
        Route::patch('/slots/{slot}/toggle', [LawyerDashboardController::class, 'toggleSlot'])->name('slots.toggle');
        Route::delete('/slots/{slot}', [LawyerDashboardController::class, 'deleteSlot'])->name('slots.destroy');
    });
});


Route::middleware(['auth','superadmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class,'index'])->name('dashboard');

    Route::get('users', [AdminUserController::class,'index'])->name('users.index');
    Route::get('users/create', [AdminUserController::class,'create'])->name('users.create');
    Route::post('users', [AdminUserController::class,'store'])->name('users.store');
    Route::get('users/{user}/edit', [AdminUserController::class,'edit'])->name('users.edit');
    Route::put('users/{user}', [AdminUserController::class,'update'])->name('users.update');
    Route::patch('users/{user}/toggle', [AdminUserController::class,'toggle'])->name('users.toggle');
    Route::delete('users/{user}', [AdminUserController::class,'destroy'])->name('users.destroy');

    Route::get('lawyers', [AdminLawyerController::class,'index'])->name('lawyers.index');
    Route::get('lawyers/{lawyer}', [AdminLawyerController::class,'show'])->name('lawyers.show');
    Route::get('lawyers/{lawyer}/edit', [AdminLawyerController::class,'edit'])->name('lawyers.edit');
    Route::put('lawyers/{lawyer}', [AdminLawyerController::class,'update'])->name('lawyers.update');
    Route::patch('lawyers/{lawyer}/verify', [AdminLawyerController::class,'verify'])->name('lawyers.verify');
    Route::patch('lawyers/{lawyer}/toggle', [AdminLawyerController::class,'toggle'])->name('lawyers.toggle');
    Route::delete('lawyers/{lawyer}', [AdminLawyerController::class,'destroy'])->name('lawyers.destroy');

    Route::resource('specializations', AdminSpecializationController::class)->except('show');
    Route::resource('locations', AdminLocationController::class)->except('show');
    Route::resource('faqs', AdminFaqController::class)->except('show');
    Route::resource('news', AdminNewsController::class)->except('show');

    Route::get('appointments', [AdminAppointmentController::class,'index'])->name('appointments.index');
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
    Route::patch('reviews/{review}/toggle', [AdminReviewController::class, 'toggle'])->name('reviews.toggle');
    Route::get('notifications', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications', [\App\Http\Controllers\Admin\NotificationController::class, 'store'])->name('notifications.store');
    Route::patch('appointments/{appointment}/status', [AdminAppointmentController::class,'updateStatus'])->name('appointments.status');
    Route::delete('appointments/{appointment}', [AdminAppointmentController::class,'destroy'])->name('appointments.destroy');
});
