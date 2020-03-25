@component('email.header')
@endcomponent
<span class="text">
    <h3>Reset Password Request</h3>
    Hello {{$user->username}}! 
    <br>
    You've requested to reset your account password on {{env('APP_NAME')}}: using this email address at {{$user->email}}.
    <br>
    Click the button below to:
    <br>
    <a href="{{env('APP_FRONT_END_URL')}}/reset_password/{{$user->username}}/{{$user->code}}">
        <button class="button">Reset</button>
    </a>
    <br>
</span>
<span class="text">
    If you did not make this change, please <a href="{{env('APP_FRONT_END_URL')}}/reset_password/{{$user->username}}/{{$user->code}}">reset</a> your password to secure your account and reply to this message to notify us.
</span>
@component('email.footer')
@endcomponent
