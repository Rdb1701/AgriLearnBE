@component('mail::message')
# You're invited to join **{{ $classroomName }}**

Hello,

You've been invited to join the class **{{ $classroomName }}** on AgriLearn.

Click the button below to join the class:

@component('mail::button', ['url' => $inviteLink])
Join Class
@endcomponent

If the button doesn't work, copy and paste this link into your browser:  
[{{ $inviteLink }}]({{ $inviteLink }})

Thanks,  
{{ config('app.name') }} Team
@endcomponent
