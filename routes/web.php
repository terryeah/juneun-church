<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\MembershipRequestController;
use App\Http\Controllers\DownloadsController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\RestrictedFileController;
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
/**
 * A 성도 전용 file is fetched through the application so somebody can
 * be asked who they are. An open one keeps its direct CDN address.
 */
Route::get('/downloads/bulletin/{bulletin}', [RestrictedFileController::class, 'bulletin'])->name('bulletin.file');
Route::get('/downloads/document/{document}', [RestrictedFileController::class, 'document'])->name('document.file');

Route::get('/album', [AlbumController::class, 'index'])->name('album.index');
Route::get('/album/{album}', [AlbumController::class, 'show'])->name('album.show');

/**
 * The gallery was called /gallery until it grew a second kind of album
 * and became 앨범. Links to it are in messages and search results that
 * nobody can go back and edit, so the old addresses lead to the new
 * ones permanently rather than dead-ending.
 */
Route::permanentRedirect('/gallery', '/album');
Route::get('/gallery/{album}', fn (string $album) => redirect()->route('album.show', $album, 301))
    ->name('gallery.show');
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

/** The second step for accounts with an authenticator app; it throttles per account itself. */
Route::get('/login/challenge', [LoginController::class, 'challenge'])->name('login.challenge');
Route::post('/login/challenge', [LoginController::class, 'challengeStore'])->name('login.challenge.store');

/** Signing out from the public site, which now carries 성도 전용 pages. */
Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');
