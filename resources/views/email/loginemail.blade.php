@component('email.header')
@endcomponent
<span class="text">
    <h3>New login</h3>
    Hello {{$user->username}}!
    <br>
    <br>
    There's a login on {{$date}}.
    <br>
    <br>
</span>
<span class="text">
    If you did not make this action, please <a href="{{env('APP_FRONT_END_URL')}}/reset_password/{{$user->username}}/{{$user->code}}">reset</a> your password to secure your account and reply to this message to notify us.
</span>
@component('email.footer')
@endcomponent
