@component('mail::message')
# Welcome to Daybyday CRM {{$user['name']}}

You are one step closer to making your business more organized

Your registered email-id is {{$user['email']}}, Please click on the below link to verify your email account
@component('mail::button', ['url' => url('/user/verify', $user->verifyUser->token)])
Verify Email
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
