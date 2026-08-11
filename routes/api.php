<?php

use App\Http\Controllers\Api\CampaignController;
use App\Http\Controllers\Api\PageController;
use App\Http\Controllers\Api\ProgramController;
use App\Http\Controllers\Api\StoryController;
use App\Http\Controllers\Api\StripeWebhookController;
use App\Http\Controllers\Api\TeamController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public API
|--------------------------------------------------------------------------
|
| Deliberately thin — read-only, published-content-only, no auth. Per
| docs/architecture/api-architecture.md §1, the site's own forms and pages
| are Inertia-rendered and never call this API; it exists solely for
| external consumers (future partner integrations, AEO tooling).
|
*/
Route::prefix('v1')->middleware('throttle:api')->group(function () {
    Route::get('pages', [PageController::class, 'index']);
    Route::get('pages/{slug}', [PageController::class, 'show']);

    Route::get('programs', [ProgramController::class, 'index']);
    Route::get('programs/{slug}', [ProgramController::class, 'show']);

    Route::get('stories', [StoryController::class, 'index']);
    Route::get('stories/{slug}', [StoryController::class, 'show']);

    Route::get('team', [TeamController::class, 'index']);

    Route::get('campaigns', [CampaignController::class, 'index']);
    Route::get('campaigns/{slug}', [CampaignController::class, 'show']);
});

/*
|--------------------------------------------------------------------------
| Stripe Webhook
|--------------------------------------------------------------------------
|
| Not rate-limited by IP — Stripe is the caller, not a browser client.
| Protected by signature verification + idempotency instead. See
| docs/architecture/api-architecture.md §4, §1.
|
*/
Route::post('v1/payments/webhook', StripeWebhookController::class);
