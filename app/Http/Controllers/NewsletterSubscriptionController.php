<?php

namespace App\Http\Controllers;

use App\Enums\SubscriberStatus;
use App\Http\Controllers\Concerns\DetectsHoneypot;
use App\Http\Requests\StoreNewsletterSubscriptionRequest;
use App\Mail\NewsletterSubscriptionConfirmation;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class NewsletterSubscriptionController extends Controller
{
    use DetectsHoneypot;

    public function store(StoreNewsletterSubscriptionRequest $request): RedirectResponse
    {
        if ($this->isHoneypotTriggered($request)) {
            return $this->successRedirect();
        }

        $subscriber = NewsletterSubscriber::query()->updateOrCreate(
            ['email' => $request->validated('email')],
            [
                'consent_timestamp' => now(),
                'consent_ip' => $request->ip(),
                'status' => SubscriberStatus::Subscribed,
                'unsubscribed_at' => null,
            ],
        );

        Mail::to($subscriber->email)->queue(new NewsletterSubscriptionConfirmation($subscriber));

        return $this->successRedirect();
    }

    private function successRedirect(): RedirectResponse
    {
        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "You're subscribed — check your inbox for confirmation.",
        ]);

        return back();
    }
}
