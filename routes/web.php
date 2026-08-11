<?php

use App\Http\Controllers\AboutPageController;
use App\Http\Controllers\Admin\ProgramController as AdminProgramController;
use App\Http\Controllers\ContactPageController;
use App\Http\Controllers\ContactSubmissionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\GivePageController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\NewsletterSubscriptionController;
use App\Http\Controllers\NewsletterUnsubscribeController;
use App\Http\Controllers\PartnershipInquiryController;
use App\Http\Controllers\PartnershipPageController;
use App\Http\Controllers\ProgramPageController;
use App\Http\Controllers\StoryPageController;
use App\Http\Controllers\VolunteerApplicationController;
use App\Http\Controllers\VolunteerPageController;
use App\Http\Middleware\EnsureTwoFactorEnabled;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Home')->name('home');

Route::get('give', [GivePageController::class, 'show'])->name('give.show');
Route::get('give/thank-you', [GivePageController::class, 'thankYou'])->name('give.thank-you');
Route::get('about', [AboutPageController::class, 'show'])->name('about.show');
Route::get('contact', [ContactPageController::class, 'show'])->name('contact.show');
Route::get('volunteer', [VolunteerPageController::class, 'show'])->name('volunteer.show');
Route::get('partner', [PartnershipPageController::class, 'show'])->name('partnership.show');
Route::get('programs', [ProgramPageController::class, 'index'])->name('programs.index');
Route::get('programs/{slug}', [ProgramPageController::class, 'show'])->name('programs.show');
Route::get('stories', [StoryPageController::class, 'index'])->name('stories.index');
Route::get('stories/{slug}', [StoryPageController::class, 'show'])->name('stories.show');

Route::middleware(['auth', 'verified', EnsureTwoFactorEnabled::class])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::post('admin/media', [MediaController::class, 'store'])->name('media.store');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('programs', AdminProgramController::class)->except('show');
    });
});

// Anonymous public forms — no auth, rate-limited per
// docs/architecture/database-erd.md §3 and saba.md §23.2.
Route::middleware('throttle:public-forms')->group(function () {
    Route::post('contact', [ContactSubmissionController::class, 'store'])->name('contact.store');
    Route::post('newsletter/subscribe', [NewsletterSubscriptionController::class, 'store'])->name('newsletter.subscribe');
    Route::post('volunteer', [VolunteerApplicationController::class, 'store'])->name('volunteer.store');
    Route::post('partnership', [PartnershipInquiryController::class, 'store'])->name('partnership.store');
});

Route::get('newsletter/unsubscribe/{newsletterSubscriber}', NewsletterUnsubscribeController::class)
    ->middleware('signed')
    ->name('newsletter.unsubscribe');

// saba.md §8.3 — max 5 donation-initiation attempts per IP per hour.
Route::post('donations', [DonationController::class, 'store'])
    ->middleware('throttle:donations')
    ->name('donations.store');

require __DIR__.'/settings.php';
