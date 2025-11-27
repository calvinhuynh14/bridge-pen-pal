@component('mail::message')
# Congratulations!

Hello {{ $userName }},

Great news! Your volunteer application to **{{ $organizationName }}** has been **approved**!

@component('mail::panel')
You can now log in to your account and start connecting with residents as a pen pal!
@endcomponent

Log in to your account to begin your pen pal journey and start making meaningful connections.

@component('mail::button', ['url' => route('login', ['type' => 'volunteer'])])
Log In to Your Account
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent

