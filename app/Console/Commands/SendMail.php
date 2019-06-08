<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class SendMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:mail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send Email Queue';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $config = [
            'driver' => 'smtp',
            'host' => 'smtp.yandex.com',
            'port' => '465',
            'username' => 'sales@limitlessco.ir',
            'password' => 'yaserdarzi'
        ];
        Config::set('mail', $config);
        $data = [
            "email" => "yaser.darzi@gmail.com",
            "name" => "yaser.darzi",
        ];
        Mail::send('emails.ticket', ['email' => "yaser.darzi@gmail.com",], function ($m) use ($data) {
            $m->from('sales@limitlessco.ir', 'limitless');
            $m->to($data['email'], $data['name'])
                ->subject('کد فعالسازی حساب کاربری برای' . date('Y-m-d H:i:s'));
        });

    }
}
