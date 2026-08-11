@component('mail::message')
# Thanks for reaching out, {{ $contactSubmission->name }}

We've received your message and someone from our team will get back to you soon.

**Subject:** {{ $contactSubmission->subject->value }}

> {{ $contactSubmission->message }}

If this wasn't you, you can safely ignore this email.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
