<?php

namespace App\Console\Commands;

use App\Inside\Constants;
use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class SendSMSRegisterAgency extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:sms:register:agency';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send sms for register agency';

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
        $connection = new AMQPStreamConnection(config("rabbitmq.server"), config("rabbitmq.port"), config("rabbitmq.user"), config("rabbitmq.password"), '/');
        $channel = $connection->channel();
        list(, $consumerCount,) = $channel->queue_declare(Constants::QUEUE_SMS_REGISTER_AGENCY, false, false, false, false);
        if ($consumerCount == 0)
            exit();
        echo " [*] Waiting for messages. To exit press CTRL+C\n";
        $callback = function ($msg) {
            echo ' [x] Received ', $msg->body, "\n";
            $data = json_decode($msg->body);
            try {
//                $sender = "10009000700777";
                $sender = "10004346";
                $message = "به سامانه خرید هتل سان رایز کیش خوش آمدید
جهت خرید تلفنی:
۰۲۱-۹۵۱۱۱۲۷۴
جهت خرید آنلاین:
Sunrisevip.ir
Username: " . $data->username . "
Pass: " . $data->password . "
با تقدیم احترامات شایسته
اسکندری
واحد فروش";
                $receptor = array($data->phone);
                $result = \Kavenegar::Send($sender, $receptor, $message);
                if ($result) {
                    foreach ($result as $r) {
                        echo "messageid = $r->messageid \n";
                        echo "message = $r->message \n";
                        echo "status = $r->status \n";
                        echo "statustext = $r->statustext \n";
                        echo "sender = $r->sender \n";
                        echo "receptor = $r->receptor \n";
                        echo "date = $r->date \n";
                        echo "cost = $r->cost \n";
                    }
                }
            } catch (\Kavenegar\Exceptions\ApiException $e) {
                // در صورتی که خروجی وب سرویس 200 نباشد این خطا رخ می دهد
                echo $e->errorMessage();
            } catch (\Kavenegar\Exceptions\HttpException $e) {
                // در زمانی که مشکلی در برقرای ارتباط با وب سرویس وجود داشته باشد این خطا رخ می دهد
                echo $e->errorMessage();
            }
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            exit();
        };
        $channel->basic_consume(Constants::QUEUE_SMS_REGISTER_AGENCY, '', false, false, false, false, $callback);
        $channel->wait();
        $channel->close();
        $connection->close();
    }
}
