<?php

use App\Http\Controllers\AboutPageController;
use App\Http\Controllers\Admin\AuditLogController as AdminAuditLogController;
use App\Http\Controllers\Admin\CampaignController as AdminCampaignController;
use App\Http\Controllers\Admin\ContactSubmissionController as AdminContactSubmissionController;
use App\Http\Controllers\Admin\DocumentController as AdminDocumentController;
use App\Http\Controllers\Admin\DonationController as AdminDonationController;
use App\Http\Controllers\Admin\EventController as AdminEventController;
use App\Http\Controllers\Admin\ImpactMetricController as AdminImpactMetricController;
use App\Http\Controllers\Admin\ImpactMetricValueController as AdminImpactMetricValueController;
use App\Http\Controllers\Admin\MediaController as AdminMediaController;
use App\Http\Controllers\Admin\NewsletterSubscriberController as AdminNewsletterSubscriberController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\PartnershipInquiryController as AdminPartnershipInquiryController;
use App\Http\Controllers\Admin\ProgramController as AdminProgramController;
use App\Http\Controllers\Admin\RedirectController as AdminRedirectController;
use App\Http\Controllers\Admin\StoryController as AdminStoryController;
use App\Http\Controllers\Admin\TeamMemberController as AdminTeamMemberController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\VolunteerApplicationController as AdminVolunteerApplicationController;
use App\Http\Controllers\ContactPageController;
use App\Http\Controllers\ContactSubmissionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentPageController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\EventPageController;
use App\Http\Controllers\GivePageController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\NewsletterSubscriptionController;
use App\Http\Controllers\NewsletterUnsubscribeController;
use App\Http\Controllers\PagePageController;
use App\Http\Controllers\PartnershipInquiryController;
use App\Http\Controllers\PartnershipPageController;
use App\Http\Controllers\ProgramPageController;
use App\Http\Controllers\RedirectResolutionController;
use App\Http\Controllers\SearchController;
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
Route::get('pages/{slug}', [PagePageController::class, 'show'])->name('pages.show');
Route::get('documents', [DocumentPageController::class, 'index'])->name('documents.index');
Route::get('documents/{document}', [DocumentPageController::class, 'show'])->name('documents.show');
Route::get('events', [EventPageController::class, 'index'])->name('events.index');
Route::get('events/{slug}', [EventPageController::class, 'show'])->name('events.show');
Route::get('search', [SearchController::class, 'index'])->name('search.index');

Route::middleware(['auth', 'verified', EnsureTwoFactorEnabled::class])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::post('admin/media', [MediaController::class, 'store'])->name('media.store');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('programs', AdminProgramController::class)->except('show');
        Route::resource('stories', AdminStoryController::class)->except('show');
        Route::resource('team-members', AdminTeamMemberController::class)->except('show');
        Route::resource('campaigns', AdminCampaignController::class)->except('show');
        Route::resource('documents', AdminDocumentController::class)->except('show');
        Route::resource('events', AdminEventController::class)->except('show');
        Route::resource('pages', AdminPageController::class)->except('show');
        Route::resource('redirects', AdminRedirectController::class)->except('show');
        Route::resource('impact-metrics', AdminImpactMetricController::class)->except('show');
        Route::post('impact-metrics/{impact_metric}/values', [AdminImpactMetricValueController::class, 'store'])->name('impact-metrics.values.store');
        Route::delete('impact-metrics/{impact_metric}/values/{value}', [AdminImpactMetricValueController::class, 'destroy'])->name('impact-metrics.values.destroy');
        Route::get('donations', [AdminDonationController::class, 'index'])->name('donations.index');
        Route::get('donations/export', [AdminDonationController::class, 'export'])->name('donations.export');
        Route::resource('contact-submissions', AdminContactSubmissionController::class)->only(['index', 'update', 'destroy']);
        Route::resource('volunteer-applications', AdminVolunteerApplicationController::class)->only(['index', 'update', 'destroy']);
        Route::resource('partnership-inquiries', AdminPartnershipInquiryController::class)->only(['index', 'update', 'destroy']);
        Route::get('newsletter-subscribers', [AdminNewsletterSubscriberController::class, 'index'])->name('newsletter-subscribers.index');
        Route::post('newsletter-subscribers/{newsletter_subscriber}/unsubscribe', [AdminNewsletterSubscriberController::class, 'unsubscribe'])->name('newsletter-subscribers.unsubscribe');
        Route::delete('newsletter-subscribers/{newsletter_subscriber}', [AdminNewsletterSubscriberController::class, 'destroy'])->name('newsletter-subscribers.destroy');
        Route::resource('users', AdminUserController::class)->except(['show']);
        Route::post('users/{user}/send-password-reset', [AdminUserController::class, 'sendPasswordReset'])->name('users.send-password-reset');
        Route::get('audit-logs', [AdminAuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('media', [AdminMediaController::class, 'index'])->name('media.index');
        Route::get('media/picker', [AdminMediaController::class, 'picker'])->name('media.picker');
        Route::delete('media/{media}', [AdminMediaController::class, 'destroy'])->name('media.destroy');
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

// Must stay last — only reached once no other route has matched. Looks up
// the requested path in the redirects table (saba.md §12.1) and issues the
// configured redirect, or falls through to the normal 404 handling.
Route::fallback(RedirectResolutionController::class);
