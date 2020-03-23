<?php

namespace App\Jobs;

use App\Jobs\Email;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class Payment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $requestList = app('App\Http\Controllers\RequestMoneyController')->billingSchedule();
        // echo json_encode($details[0]['account']['email']);
        $i = 0;
        foreach ($requestList as $key) {
            if($requestList[$i]['send_billing_flag'] == true){
                Email::dispatch($requestList[$i]); 
            }
            $i++;
        }
    }
}
