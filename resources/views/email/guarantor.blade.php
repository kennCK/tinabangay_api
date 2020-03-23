@component('email.header')
@endcomponent
<span class="text">
    <h3>{{env('APP_NAME')}} Invitation</h3>
    Hello {{$email}}!
    <br>
    <br>
    Have a wonderful day!
    <br>
    You have been invited by {{$user->email}} to join PAYHIRAM and be his guarantor.
    <br>
    <br>
    Click the button below to:
    <br>
    <a href="{{env('APP_FRONT_END_URL')}}/signup/{{$email}}/{{$code}}">
        <button class="button">Accept & Register Now!</button>
    </a>
    <br>
</span>
@component('email.footer')
@endcomponent