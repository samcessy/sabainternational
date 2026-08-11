<?php

use App\Http\Controllers\ContactSubmissionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\NewsletterSubscriptionController;
use App\Http\Controllers\NewsletterUnsubscribeController;
use App\Http\Controllers\PartnershipInquiryController;
use App\Http\Controllers\VolunteerApplicationController;
use App\Http\Middleware\EnsureTwoFactorEnabled;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Home')->name('home');

Route::middleware(['auth', 'verified', EnsureTwoFactorEnabled::class])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::post('admin/media', [MediaController::class, 'store'])->name('media.store');
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
