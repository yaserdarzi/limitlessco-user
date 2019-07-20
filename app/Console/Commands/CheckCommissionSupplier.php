<?php

namespace App\Console\Commands;

use App\Agency;
use App\AgencyApp;
use App\Commission;
use App\Exceptions\ApiException;
use App\Inside\Constants;
use App\Supplier;
use App\SupplierApp;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckCommissionSupplier extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:commission:supplier {supplier_id} {type} {id} {commission}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'fix commission supplier_id type(hotel,entertainment,...) id(room,product,...) commission';

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
        $supplier = Supplier::where('id', $this->argument('supplier_id'))->first();
        if (!$supplier)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'plz check your supplier_id'
            );
        $supplierApp = SupplierApp::join(Constants::APP_DB, Constants::SUPPLIER_APP_DB . '.app_id', '=', Constants::APP_DB . '.id')
            ->where('supplier_id', $this->argument('supplier_id'))
            ->where('app', $this->argument('type'))
            ->first();
        if (!$supplierApp)
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
        $customer_id = Constants::SALES_TYPE_SUPPLIER . "-" . $this->argument('supplier_id');
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
        $hotel_supplier = DB::connection(Constants::CONNECTION_HOTEL)
            ->table(Constants::APP_HOTEL_DB_HOTEL_SUPPLIER_DB)
            ->where('hotel_id', $room->hotel_id)
            ->where('supplier_id', $this->argument('supplier_id'))
            ->first();
        if (!$hotel_supplier)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'plz check your room_id isn`t this supplier'
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
        $product_supplier = DB::connection(Constants::CONNECTION_ENTERTAINMENT)
            ->table(Constants::APP_ENTERTAINMENT_DB_PRODUCT_SUPPLIER_DB)
            ->where('product_id', $product->id)
            ->where('supplier_id', $this->argument('supplier_id'))
            ->first();
        if (!$product_supplier)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'plz check your product_id isn`t this supplier'
            );
        return $shopping_id = Constants::APP_NAME_ENTERTAINMENT . "-" . $product->id;
    }
}
