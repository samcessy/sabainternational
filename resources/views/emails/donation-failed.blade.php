@component('mail::message')
# We couldn't process your donation

Unfortunately your recent donation attempt to {{ config('app.name') }} didn't go through. No amount was charged.

If you'd like to try again, please return to our donation page. If this keeps happening, feel free to reach out and we'll help sort it out.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
