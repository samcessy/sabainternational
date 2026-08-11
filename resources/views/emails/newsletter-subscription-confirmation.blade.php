@component('mail::message')
# You're on the list

Thanks for subscribing to updates from {{ config('app.name') }}. We'll let you know when there's real news to share.

@component('mail::button', ['url' => $unsubscribeUrl])
Unsubscribe
@endcomponent

If you didn't sign up for this, click unsubscribe above and you won't hear from us again.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
