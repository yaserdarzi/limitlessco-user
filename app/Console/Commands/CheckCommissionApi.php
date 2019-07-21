<?php

namespace App\Console\Commands;

use App\Api;
use App\ApiApp;
use App\Commission;
use App\Exceptions\ApiException;
use App\Inside\Constants;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckCommissionApi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:commission:api {api_id} {type} {id} {commission}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'fix commission Api api_id(0=All) type(hotel,entertainment,...) id(room,product) commission';

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
        if ($this->argument('api_id') == 0) {
            $api = Api::all();

            foreach ($api as $apiVal) {
                switch ($this->argument('type')) {
                    case Constants::APP_NAME_HOTEL:
                        $shopping_id = $this->hotel();
                        break;
                    case Constants::APP_NAME_ENTERTAINMENT:
                        $shopping_id = $this->entertainment();
                        break;
                }
                $customer_id = Constants::SALES_TYPE_API . "-" . $apiVal->id;
                if (!Commission::where(['customer_id' => $customer_id, 'shopping_id' => $shopping_id])->exists()) {
                    Commission::create([
                        'customer_id' => $customer_id,
                        'shopping_id' => $shopping_id,
                        'type' => Constants::TYPE_PERCENT,
                        'percent' => $this->argument('commission'),
                    ]);
                    echo "$customer_id =>>>>>>>>>>>> $shopping_id =>>>>>>>>>>>> " . $this->argument('commission') . " \n";
                }
            }
        } else {
            $api = Api::where('id', $this->argument('api_id'))->first();
            if (!$api)
                throw new ApiException(
                    ApiException::EXCEPTION_NOT_FOUND_404,
                    'plz check your api_id'
                );
            $apiApp = ApiApp::join(Constants::APP_DB, Constants::API_APP_DB . '.app_id', '=', Constants::APP_DB . '.id')
                ->where('api_id', $this->argument('api_id'))
                ->where('app', $this->argument('type'))
                ->first();
            if (!$apiApp)
                throw new ApiException(
                    ApiException::EXCEPTION_NOT_FOUND_404,
                    'plz check your app hotel or entertainment'
                );
            switch ($this->argument('type')) {
                case Constants::APP_NAME_HOTEL:
                    $shopping_id = $this->hotel();
                    break;
                case Constants::APP_NAME_ENTERTAINMENT:
                    $shopping_id = $this->entertainment();
                    break;
            }
            $customer_id = Constants::SALES_TYPE_API . "-" . $this->argument('api_id');
            if (!Commission::where(['customer_id' => $customer_id, 'shopping_id' => $shopping_id])->exists()) {
                Commission::create([
                    'customer_id' => $customer_id,
                    'shopping_id' => $shopping_id,
                    'type' => Constants::TYPE_PERCENT,
                    'percent' => $this->argument('commission'),
                ]);
                echo "$customer_id =>>>>>>>>>>>> $shopping_id =>>>>>>>>>>>> " . $this->argument('commission') . " \n";
            }
        }
    }

    private function hotel()
    {
        $room = DB::connection(Constants::CONNECTION_HOTEL)
            ->table(Constants::APP_HOTEL_DB_ROOM_DB)
            ->where('id', $this->argument('id'))
            ->first();
        if (!$room)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'plz check your room_id'
            );
        return Constants::APP_NAME_HOTEL . "-" . $room->hotel_id . "-" . $room->id;
    }

    private function entertainment()
    {
        $product = DB::connection(Constants::CONNECTION_ENTERTAINMENT)
            ->table(Constants::APP_ENTERTAINMENT_DB_PRODUCT_DB)
            ->where('id', $this->argument('id'))
            ->first();
        if (!$product)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'plz check your product_id'
            );
        return $shopping_id = Constants::APP_NAME_ENTERTAINMENT . "-" . $product->id;
    }
}