@component('email.header')
@endcomponent
<?php
$amount = $data['amount'] + $data['interest'];
?>
<span class="text">
    <h3>Payment Due Reminder</h3>
    Hello {{$data['account']['username']}}!
    <br>
    <br>
    Here is your billing notification on {{env('APP_NAME')}}:
    <br>
    <br>
    <br>
    <table style="width:50%">
    <tr>
    <th>Amount to pay</th>
    <th>Due Date</th> 
    </tr>
    <tr>
    <td align="center">PHP {{$amount}}</td>
    <td align="center">{{$data['next_billing_date_human']}}</td> 
    </tr>
    </table>
    <br>
    <br>
    Penalty will occur if bill has not been paid in 3 days. Thank you!
</span>
@component('email.footer')
@endcomponent