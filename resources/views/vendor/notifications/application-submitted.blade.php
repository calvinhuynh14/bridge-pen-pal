@component('mail::message')
# Application Submitted Successfully!

Hello {{ $userName }},

Thank you for your interest in becoming a pen pal volunteer! Your application has been successfully submitted to **{{ $organizationName }}**.

@component('mail::panel')
## What happens next?

- Your application will be reviewed by the organization
- You'll receive an email notification once a decision is made
- If approved, you'll be able to log in and start your pen pal journey
@endcomponent

**Important:** Please verify your email address to ensure you receive updates about your application status. Check your inbox for a verification email.

@component('mail::button', ['url' => route('application.submitted')])
View Application Status
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent

