@component('mail::message')
# Thank you for your gift

Your {{ $donation->frequency->value === 'monthly' ? 'monthly' : '' }} donation of **${{ number_format($donation->amount_cents / 100, 2) }}** to {{ config('app.name') }} means a real difference for the children, youth, and families we serve in East Africa.

Your receipt is attached to this email for your records.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
