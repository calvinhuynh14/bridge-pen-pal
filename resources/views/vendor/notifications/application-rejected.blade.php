@component('mail::message')
# Application Update

Hello {{ $userName }},

Thank you for your interest in volunteering with **{{ $organizationName }}**. After careful review, we regret to inform you that your application was not approved at this time.

@if($rejectionReason)
@component('mail::panel')
## Reason for Rejection:

{{ $rejectionReason }}
@endcomponent
@endif

We appreciate your interest in our pen pal program and encourage you to reach out if you have any questions.

@component('mail::button', ['url' => route('application.submitted')])
View Application Status
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent

