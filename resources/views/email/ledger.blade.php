@component('email.header')
@endcomponent
<span class="text">
  Hello {{$user->username}}!
  <br>
  {{$title}} on {{$date}}
</span>
<span class="text">
  Transaction id: {{$transactionId}}
  <br>
  <br>
</span>
<span class="text" style="text-align: center;">
    If you did not make this action, please <a href="{{env('APP_FRONT_END_URL')}}/reset_password/{{$user->username}}/{{$user->code}}">reset</a> your password to secure your account and reply to this message to notify us.
</span>
@component('email.footer')
@endcomponent

