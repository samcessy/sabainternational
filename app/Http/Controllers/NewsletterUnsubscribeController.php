<?php

namespace App\Http\Controllers;

use App\Enums\SubscriberStatus;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

/**
 * One-click unsubscribe via a signed URL emailed with every newsletter
 * message — saba.md §22 requires this on every send, not a login-gated
 * preference page.
 */
class NewsletterUnsubscribeController extends Controller
{
    public function __invoke(NewsletterSubscriber $newsletterSubscriber): RedirectResponse
    {
        $newsletterSubscriber->update([
            'status' => SubscriberStatus::Unsubscribed,
            'unsubscribed_at' => now(),
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'You have been unsubscribed.',
        ]);

        return redirect('/');
    }
}
