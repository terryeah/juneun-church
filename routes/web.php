<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\MembershipRequestController;
use App\Http\Controllers\DownloadsController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\GivingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\WorshipController;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/worship', WorshipController::class)->name('worship');
Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/news/{announcement}', [NewsController::class, 'show'])->name('news.show');
Route::get('/events', EventController::class)->name('events');
Route::get('/people', StaffController::class)->name('people');
Route::get('/downloads', DownloadsController::class)->name('downloads');

/** 주보 moved into 자료실; the old address is kept for anything linking to it. */
Route::permanentRedirect('/bulletins', '/downloads')->name('bulletins');
Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');
Route::get('/gallery/{album}', [GalleryController::class, 'show'])->name('gallery.show');
Route::get('/giving', GivingController::class)->name('giving');
Route::get('/location', LocationController::class)->name('location');

/** The short address handed to 성도 for their account page. */
Route::get('/profile', fn () => redirect(Filament::getPanel('admin')->getProfileUrl()))->name('profile');

/** Public sign-up: throttled because anyone on the internet can post to it. */
Route::get('/signup', [MembershipRequestController::class, 'create'])->name('signup');
Route::post('/signup', [MembershipRequestController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('signup.store');

/** Public login, throttled for the same reason. */
Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('login.store');

/** Signing out from the public site, which now carries 성도 전용 pages. */
Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');
