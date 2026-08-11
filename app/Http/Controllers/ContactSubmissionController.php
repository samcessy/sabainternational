<?php

namespace App\Http\Controllers;

use App\Enums\AdminPermission;
use App\Http\Controllers\Concerns\DetectsHoneypot;
use App\Http\Requests\StoreContactSubmissionRequest;
use App\Mail\ContactSubmissionConfirmation;
use App\Models\ContactSubmission;
use App\Models\User;
use App\Notifications\NewContactSubmissionNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;

class ContactSubmissionController extends Controller
{
    use DetectsHoneypot;

    public function store(StoreContactSubmissionRequest $request): RedirectResponse
    {
        if ($this->isHoneypotTriggered($request)) {
            return $this->successRedirect();
        }

        $contactSubmission = ContactSubmission::create($request->safe()->except('consent'));

        Mail::to($contactSubmission->email)->queue(new ContactSubmissionConfirmation($contactSubmission));

        Notification::send(
            User::withPermission(AdminPermission::ViewEngagement),
            new NewContactSubmissionNotification($contactSubmission),
        );

        return $this->successRedirect();
    }

    private function successRedirect(): RedirectResponse
    {
        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Thanks for reaching out — we'll get back to you soon.",
        ]);

        return back();
    }
}
