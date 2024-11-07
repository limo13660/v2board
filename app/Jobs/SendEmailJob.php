<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use App\Models\MailLog;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $params;

    public $tries = 3;
    public $timeout = 10;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($params, $queue = 'send_email')
    {
        $this->onQueue($queue);
        $this->params = $params;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (config('daotech.email_host')) {
            Config::set('mail.host', config('daotech.email_host', env('mail.host')));
            Config::set('mail.port', config('daotech.email_port', env('mail.port')));
            Config::set('mail.encryption', config('daotech.email_encryption', env('mail.encryption')));
            Config::set('mail.username', config('daotech.email_username', env('mail.username')));
            Config::set('mail.password', config('daotech.email_password', env('mail.password')));
            Config::set('mail.from.address', config('daotech.email_from_address', env('mail.from.address')));
            Config::set('mail.from.name', config('daotech.app_name', 'DaoTech'));
        }
        $params = $this->params;
        $email = $params['email'];
        $subject = $params['subject'];
        $params['template_name'] = 'mail.' . config('daotech.email_template', 'default') . '.' . $params['template_name'];
        try {
            sleep(2); 
            Mail::send(
                $params['template_name'],
                $params['template_value'],
                function ($message) use ($email, $subject) {
                    $message->to($email)->subject($subject);
                }
            );
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        $log = [
            'email' => $params['email'],
            'subject' => $params['subject'],
            'template_name' => $params['template_name'],
            'error' => isset($error) ? $error : NULL
        ];

        MailLog::create($log);
        $log['config'] = config('mail');
        return $log;
    }
}
