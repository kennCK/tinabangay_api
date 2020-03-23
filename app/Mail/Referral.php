<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Carbon\Carbon;

class Referral extends Mailable
{
    use Queueable, SerializesModels;
    public $user;
    public $date;
    public $content;
    public $toEmail;
    public $details;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $content, $toEmail, $details, $timezone)
    {
        $this->user = $user;
        $this->content = $content;
        $this->toEmail = $toEmail;
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
        return $this->subject('Referral notification')->from(env('MAIL_FROM_ADDRESS'), env('APP_NAME'))->view('email.referral');
    }
}
