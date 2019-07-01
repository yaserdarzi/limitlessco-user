<?php

namespace App\Console\Commands;

use App\Agency;
use App\Commission;
use App\Inside\Constants;
use App\Supplier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckCommission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:commission';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'fix commission';

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
        $agency = Agency::all();
        foreach ($agency as $agencyVal) {
            $customer_id = Constants::SALES_TYPE_AGENCY . "-" . $agencyVal->id;
            ////////////////////ROOM////////////////////
            $room = DB::connection(Constants::CONNECTION_HOTEL)
                ->table(Constants::APP_HOTEL_DB_ROOM_DB)
                ->select(
                    Constants::APP_HOTEL_DB_ROOM_DB . '.id as room_id',
                    '*'
                )
                ->get();
            foreach ($room as $roomVal) {
                $shopping_id = Constants::APP_NAME_HOTEL . "-" . $roomVal->hotel_id . "-" . $roomVal->room_id;
                if (!Commission::where(['customer_id' => $customer_id, 'shopping_id' => $shopping_id])->exists()) {
                    Commission::create([
                        'customer_id' => $customer_id,
                        'shopping_id' => $shopping_id,
                        'type' => $agencyVal->type,
                        'percent' => $agencyVal->percent,
                        'price' => $agencyVal->price,
                        'award' => $agencyVal->award,
                        'income' => $agencyVal->income,
                    ]);
                    echo "$customer_id =>>>>>>>>>>>>>>>>>> $shopping_id \n";
                }

            }
            ////////////PRODUCT////////////////////
            $product = DB::connection(Constants::CONNECTION_ENTERTAINMENT)
                ->table(Constants::APP_ENTERTAINMENT_DB_PRODUCT_DB)
                ->get();
            foreach ($product as $productVal) {
                $shopping_id = Constants::APP_NAME_ENTERTAINMENT . "-" . $productVal->id;
                if (!Commission::where(['customer_id' => $customer_id, 'shopping_id' => $shopping_id])->exists()) {
                    Commission::create([
                        'customer_id' => $customer_id,
                        'shopping_id' => $shopping_id,
                        'type' => $agencyVal->type,
                        'percent' => $agencyVal->percent,
                        'price' => $agencyVal->price,
                        'award' => $agencyVal->award,
                        'income' => $agencyVal->income,
                    ]);
                    echo "$customer_id =>>>>>>>>>>>>>>>>>> $shopping_id \n";
                }

            }
        }
    }
}
