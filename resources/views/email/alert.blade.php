@component('email.header')
@endcomponent
<span class="text">
    <h3>{{env('APP_NAME')}} Invitation</h3>
    Hello {{$toEmail}}!
    <br>
    <br>
</span>
@component('email.footer')
@endcomponent