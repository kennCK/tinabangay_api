<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Carbon\Carbon;
class Deposit extends Mailable
{
    use Queueable, SerializesModels;
    public $user;
    public $date;
    public $details;
    public $amount;
    public $emailSubject;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $details, $subject, $timezone)
    {
        $this->user = $user;
        $this->emailSubject = $subject;
        $this->details = $details;
        $this->date = Carbon::now()->copy()->tz($timezone)->format('F j, Y h:i A');
    }

    /**
     * Build the message.
     *
     * @return $this
     */

    public function build()
    {
        return $this->subject($this->emailSubject)->from(env('MAIL_FROM_ADDRESS'), env('APP_NAME'))->view('email.deposit');
    }
}
