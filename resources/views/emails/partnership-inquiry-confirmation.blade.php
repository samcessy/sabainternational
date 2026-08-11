@component('mail::message')
# Thanks, {{ $partnershipInquiry->contact_name }}

We've received your partnership inquiry on behalf of {{ $partnershipInquiry->organization_name }}. Someone from our team will be in touch soon.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
