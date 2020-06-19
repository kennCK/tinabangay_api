@component('email.header')
@endcomponent
<span class="text">
    <h3>{{env('APP_NAME')}} Alert!</h3>
    <br>
    <br>
        New scanned user was exposed!
    <br>
    <br>
        Scanned on {{$date}}
</span>
@component('email.footer')
@endcomponent