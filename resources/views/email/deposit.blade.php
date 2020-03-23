@component('email.header')
@endcomponent
<span class="text" style="text-align: justify;">
  Hello {{$user->username}}!
  <br>
  <br>
  You have created a deposit transaction with the amount of <b>{{$details['currency']}} {{$details['amount']}}</b> via <b>{{$details['bank']}}</b> on {{$date}}.
  <br>
  <br>
  Status is on <b>{{$details['status']}}.</b>
</span>
<span class="text" style="text-align: justify;">
  <br>
  <br>
  To continue with the transaction, Please click on the link below and carefully follow the instructions:
  <br>
  <!-- Link here -->
   <a href="{{env('APP_FRONT_END_URL')}}/paymentConfirmation/{{$user->email}}/{{$user->code}}/{{$details['code']}}">Continue</a>
</span>
<span class="text" style="text-align: justify;">
  <br>
  <br>
  Transaction id: {{$details['code']}}
  <br>
  <br>
</span>
<span class="text" style="text-align: justify;">
  If you did not make this action, please <a href="{{env('APP_FRONT_END_URL')}}/reset_password/{{$user->username}}/{{$user->code}}">reset</a> your password to secure your account and reply to this message to notify us.
</span>
@component('email.footer')
@endcomponent

