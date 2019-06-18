<?php

namespace App\Console\Commands;

use App\Inside\Constants;
use App\ShoppingBag;
use App\ShoppingBagExpire;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ShoppingBagExpireTimeCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checkExpire:shoppingBag';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'ShoppingBagExpireTimeCheck';

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
        $shoppingBagExpire = ShoppingBagExpire::where('expire_time', '<', date('Y-m-d H:i:s', strtotime('now')))
            ->where([
                'status' => Constants::SHOPPING_STATUS_SHOPPING
            ])->first();
        if ($shoppingBagExpire) {
            ShoppingBagExpire::where([
                'id' => $shoppingBagExpire->id
            ])->update(['status' => Constants::SHOPPING_STATUS_DELETE]);
            $shoppingBag = ShoppingBag::where([
                'customer_id' => $shoppingBagExpire->customer_id
            ])->get();
            if (sizeof($shoppingBag))
                foreach ($shoppingBag as $value)
                    switch (explode('-', $value->shopping_id)[0]) {
                        case Constants::APP_NAME_HOTEL:
                            $this->hotelCheck($value);
                            $this->deleteShoppingBagExpire($shoppingBagExpire->id);
                            break;
                        case Constants::APP_NAME_ENTERTAINMENT:
                            $this->entertainmentCheck($value);
                            $this->deleteShoppingBagExpire($shoppingBagExpire->id);
                            break;
                    }
        }
    }

    ///////////////////////private function/////////////////////////////


    private function deleteShoppingBagExpire($id)
    {
        ShoppingBagExpire::where([
            'id' => $id
        ])->delete();
    }

    //
    //Hotel
    //
    private function hotelCheck($shoppingBag)
    {
        echo "hotel \n";
        foreach ($shoppingBag->shopping->roomEpisode as $value) {
            echo "episode= $value->id \n";
            DB::connection(Constants::CONNECTION_HOTEL)
                ->table(Constants::APP_HOTEL_DB_ROOM_EPISODE_DB)
                ->where('id', $value->id)
                ->decrement('capacity_filled', $shoppingBag->count);
            DB::connection(Constants::CONNECTION_HOTEL)
                ->table(Constants::APP_HOTEL_DB_ROOM_EPISODE_DB)
                ->where('id', $value->id)
                ->increment('capacity_remaining', $shoppingBag->count);
        }
        ShoppingBag::where('id', $shoppingBag->id)->delete();
        echo "room= $shoppingBag->id \n";
    }

    //
    //Entertainment
    //
    private function entertainmentCheck($shoppingBag)
    {
        echo "entertainment \n";
        DB::connection(Constants::CONNECTION_ENTERTAINMENT)
            ->table(Constants::APP_ENTERTAINMENT_DB_PRODUCT_EPISODE_DB)
            ->where('id', $shoppingBag->shopping->productEpisode->id)
            ->decrement('capacity_filled', $shoppingBag->count);
        DB::connection(Constants::CONNECTION_ENTERTAINMENT)
            ->table(Constants::APP_ENTERTAINMENT_DB_PRODUCT_EPISODE_DB)
            ->where('id', $shoppingBag->shopping->productEpisode->id)
            ->increment('capacity_remaining', $shoppingBag->count);
        ShoppingBag::where('id', $shoppingBag->id)->delete();
        echo "episode= $shoppingBag->id \n";
    }
}
