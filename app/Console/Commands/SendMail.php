<?php

namespace App\Console\Commands;

use App\Agency;
use App\Inside\Constants;
use App\Shopping;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Morilog\Jalali\CalendarUtils;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

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
        $connection = new AMQPStreamConnection(config("rabbitmq.server"), config("rabbitmq.port"), config("rabbitmq.user"), config("rabbitmq.password"), '/');
        $channel = $connection->channel();
        list(, $consumerCount,) = $channel->queue_declare(Constants::QUEUE_MAIL_TICKET, false, false, false, false);
        if ($consumerCount == 0)
            exit();
        echo " [*] Waiting for messages. To exit press CTRL+C\n";
        $callback = function ($msg) {
            echo ' [x] Received ', $msg->body, "\n";
            $data = json_decode($msg->body);
            $data->shopping = Shopping::where(['id' => $data->shopping_id])->first();
            $data->agency = Agency::where(['id' => explode('-', $data->shopping->customer_id)[1]])->first();
            $config = [
                'driver' => 'smtp',
                'host' => 'smtp.yandex.com',
                'port' => '465',
                'encryption' => 'ssl',
                'username' => Constants::MAIL_USER . $data->base_url,
                'password' => Constants::MAIL_PASSWORD
            ];
            Config::set('mail', $config);
            $data->emailSend = Constants::MAIL_USER . $data->base_url;
            $data->name = $data->shopping->name;
            $data->shopping->date_persian = CalendarUtils::strftime('Y-m-d', strtotime($data->shopping->date));
            $data->shopping->created_at_persian = CalendarUtils::strftime('Y-m-d', strtotime($data->shopping->created_at));
            $data = (array)$data;
            Mail::send('emails.ticket', ['data' => $data], function ($m) use ($data) {
                $m->from($data['emailSend'], $data['agency']['name']);
                $m->to($data['email'], $data['name'])
                    ->subject('بلیط ' . $data['shopping']['title'] . ' ' . $data['shopping']['title_more'] . ' ' . $data['name']);
            });
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            exit();
        };
        $channel->basic_consume(Constants::QUEUE_MAIL_TICKET, '', false, false, false, false, $callback);
        $channel->wait();
        $channel->close();
        $connection->close();


    }
}
