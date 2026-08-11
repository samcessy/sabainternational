<?php

namespace App\Http\Controllers;

use App\Enums\AdminPermission;
use App\Http\Controllers\Concerns\DetectsHoneypot;
use App\Http\Requests\StorePartnershipInquiryRequest;
use App\Mail\PartnershipInquiryConfirmation;
use App\Models\PartnershipInquiry;
use App\Models\User;
use App\Notifications\NewPartnershipInquiryNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;

class PartnershipInquiryController extends Controller
{
    use DetectsHoneypot;

    public function store(StorePartnershipInquiryRequest $request): RedirectResponse
    {
        if ($this->isHoneypotTriggered($request)) {
            return $this->successRedirect();
        }

        $partnershipInquiry = PartnershipInquiry::create($request->safe()->except('consent'));

        Mail::to($partnershipInquiry->email)->queue(new PartnershipInquiryConfirmation($partnershipInquiry));

        Notification::send(
            User::withPermission(AdminPermission::ViewEngagement),
            new NewPartnershipInquiryNotification($partnershipInquiry),
        );

        return $this->successRedirect();
    }

    private function successRedirect(): RedirectResponse
    {
        Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Thanks for reaching out — we'll be in touch soon.",
        ]);

        return back();
    }
}
