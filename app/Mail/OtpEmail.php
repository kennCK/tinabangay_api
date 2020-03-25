<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Carbon\Carbon;
class OtpEmail extends Mailable
{
    use Queueable, SerializesModels;
    public $user;
    public $date;
    public $otpCode;
    public $text;
    public $subject;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject, $user, $otpCode, $text, $timezone)
    {
        $this->user = $user;
        $this->otpCode = $otpCode;
        $this->text = $text;
        $this->date = Carbon::now()->copy()->tz($timezone)->format('F j, Y h:i A');
        $this->subject = $subject;
    }

    /**
     * Build the message.
     *
     * @return $this
     */

    public function build()
    {
        return $this->subject($this->subject)->from(env('MAIL_FROM_ADDRESS'), env('APP_NAME'))->view('email.otpemail');
    }
}
