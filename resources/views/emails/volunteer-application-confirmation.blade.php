@component('mail::message')
# Thanks, {{ $volunteerApplication->name }}

We've received your volunteer application. Someone from our team will follow up with next steps.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
