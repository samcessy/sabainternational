<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SubscriberStatus;
use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * No create/store - subscribers only self-register through the public
 * newsletter form (NewsletterSubscriptionController), never through this
 * admin UI. unsubscribe() exists here so staff can honor an unsubscribe
 * request made through any channel (phone, reply-to-email, etc.), not only
 * the signed one-click link every send includes.
 */
class NewsletterSubscriberController extends Controller
{
    public function __construct(private readonly AuditLogger $auditLogger) {}

    public function index(): Response
    {
        $this->authorize('viewAny', NewsletterSubscriber::class);

        return Inertia::render('admin/newsletter-subscribers/Index', [
            'subscribers' => NewsletterSubscriber::query()
                ->latest('created_at')
                ->paginate(30)
                ->through(fn (NewsletterSubscriber $subscriber) => [
                    'id' => $subscriber->id,
                    'email' => $subscriber->email,
                    'status' => $subscriber->status->value,
                    'status_label' => $subscriber->status->label(),
                    'frequency_preference' => $subscriber->frequency_preference,
                    'consent_timestamp' => $subscriber->consent_timestamp?->toIso8601String(),
                    'unsubscribed_at' => $subscriber->unsubscribed_at?->toIso8601String(),
                ]),
            'totals' => [
                'subscribed_count' => NewsletterSubscriber::query()->where('status', SubscriberStatus::Subscribed)->count(),
            ],
        ]);
    }

    public function unsubscribe(NewsletterSubscriber $newsletterSubscriber): RedirectResponse
    {
        $this->authorize('update', $newsletterSubscriber);

        $oldValues = $newsletterSubscriber->only(['status', 'unsubscribed_at']);

        $newsletterSubscriber->update([
            'status' => SubscriberStatus::Unsubscribed,
            'unsubscribed_at' => now(),
        ]);

        $this->auditLogger->log(
            request()->user(),
            'unsubscribe',
            $newsletterSubscriber,
            $oldValues,
            $newsletterSubscriber->only(['status', 'unsubscribed_at'])
        );

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "\"{$newsletterSubscriber->email}\" was unsubscribed.",
        ]);

        return back();
    }

    public function destroy(NewsletterSubscriber $newsletterSubscriber): RedirectResponse
    {
        $this->authorize('delete', $newsletterSubscriber);

        $email = $newsletterSubscriber->email;
        $this->auditLogger->log(request()->user(), 'delete', $newsletterSubscriber, oldValues: $newsletterSubscriber->only(['email', 'status']));
        $newsletterSubscriber->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "\"{$email}\" was deleted.",
        ]);

        return back();
    }
}
