<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

/**
 * Bot detection for anonymous public forms (Contact, Newsletter, Volunteer,
 * Partnership) — see docs/product-requirements.md §7 and
 * docs/audit/current-website-audit.md F-7 (the live site's reCAPTCHA ships
 * with an empty site key, i.e. no working spam protection at all).
 *
 * The honeypot field is intentionally NOT part of each Form Request's
 * validation rules: a bot that gets a 422 back learns the field is
 * checked and adapts. Instead, controllers check this after validation
 * passes and silently pretend success — same redirect, no record created,
 * no email sent.
 */
trait DetectsHoneypot
{
    protected function isHoneypotTriggered(Request $request): bool
    {
        return filled($request->input('website'));
    }
}
