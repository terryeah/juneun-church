<?php

use App\Http\Controllers\Auth\MembershipRequestController;
use App\Http\Controllers\BulletinController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\GivingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\WorshipController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/worship', WorshipController::class)->name('worship');
Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/news/{announcement}', [NewsController::class, 'show'])->name('news.show');
Route::get('/events', EventController::class)->name('events');
Route::get('/people', StaffController::class)->name('people');
Route::get('/bulletins', BulletinController::class)->name('bulletins');
Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');
Route::get('/gallery/{album}', [GalleryController::class, 'show'])->name('gallery.show');
Route::get('/giving', GivingController::class)->name('giving');
Route::get('/location', LocationController::class)->name('location');

/** Public sign-up: throttled because anyone on the internet can post to it. */
Route::get('/signup', [MembershipRequestController::class, 'create'])->name('signup');
Route::post('/signup', [MembershipRequestController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('signup.store');
