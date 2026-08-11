<?php

namespace App\Http\Controllers;

use App\Enums\AdminPermission;
use App\Http\Controllers\Concerns\DetectsHoneypot;
use App\Http\Requests\StoreVolunteerApplicationRequest;
use App\Mail\VolunteerApplicationConfirmation;
use App\Models\User;
use App\Models\VolunteerApplication;
use App\Notifications\NewVolunteerApplicationNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;

class VolunteerApplicationController extends Controller
{
    use DetectsHoneypot;

    public function store(StoreVolunteerApplicationRequest $request): RedirectResponse
    {
        if ($this->isHoneypotTriggered($request)) {
            return $this->successRedirect();
        }

        $volunteerApplication = VolunteerApplication::create($request->safe()->except('consent'));

        Mail::to($volunteerApplication->email)->queue(new VolunteerApplicationConfirmation($volunteerApplication));

        Notification::send(
            User::withPermission(AdminPermission::ViewEngagement),
            new NewVolunteerApplicationNotification($volunteerApplication),
        );

        return $this->successRedirect();
    }

    private function successRedirect(): RedirectResponse
    {
        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Thanks for applying — we\'ll be in touch soon.',
        ]);

        return back();
    }
}
