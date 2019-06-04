<?php

namespace App\Console\Commands;

use App\Inside\Constants;
use App\Shopping;
use App\Supplier;
use App\SupplierWallet;
use App\SupplierWalletInvoice;
use Illuminate\Console\Command;

class CheckIncomeSupplier extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'checkIncome:supplier';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Supplier Income';

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
        $shopping = Shopping::where([
            ['date', '<', date('Y-m-d', strtotime('-2 days', strtotime('now')))],
            'status' => Constants::SHOPPING_STATUS_SUCCESS,
            'payment_status' => Constants::SHOPPING_STATUS_PENDING
        ])->get();
        if ($shopping)
            foreach ($shopping as $value)
                switch (explode('-', $value->shopping_id)[0]) {
                    case Constants::APP_NAME_HOTEL:
                        $this->hotel($value);
                        break;
                }
    }

    private function hotel($shopping)
    {
        $supplier = Supplier::where(['id' => $shopping->supplier_id])->first();
        $incomeSupplier = 0;
        if ($supplier->type == Constants::TYPE_PRICE)
            $incomeSupplier = $shopping->price_all - $shopping->income - $supplier->price;
        elseif ($supplier->type == Constants::TYPE_PERCENT)
            if ($supplier->percent < 100) {
                $incomeSupplier = ($supplier->percent / 100) * ($shopping->price_all);
                $incomeSupplier = $shopping->price_all - $incomeSupplier - $shopping->income;
            }
        $wallet = SupplierWallet::where('supplier_id', $supplier->id)->first();
        $walletPaymentTokenSupplierCount = SupplierWalletInvoice::count();
        $walletPaymentTokenSupplier = "SW-" . ++$walletPaymentTokenSupplierCount;
        SupplierWalletInvoice::create([
            'supplier_id' => $supplier->id,
            'wallet_id' => $wallet->id,
            'price_before' => $supplier->income,
            'price' => $incomeSupplier,
            'price_after' => intval($supplier->income + $incomeSupplier),
            'price_all' => $incomeSupplier,
            'type_status' => Constants::INVOICE_TYPE_STATUS_INCOME,
            'status' => Constants::INVOICE_STATUS_SUCCESS,
            'type' => Constants::INVOICE_TYPE_INCOME_SUPPLIER,
            'invoice_status' => Constants::INVOICE_INVOICE_STATUS_INCOME_SUPPLIER . " - " . Constants::INVOICE_INVOICE_STATUS_INCREMENT,
            'payment_token' => $walletPaymentTokenSupplier,
            'market' => Constants::INVOICE_MARKET_INCOME_SUPPLIER,
            'info' => ['wallet' => $wallet, "shopping" => $shopping],
        ]);
        Supplier::where('id', $supplier->id)->update([
            'income' => intval($supplier->income + $incomeSupplier)
        ]);
        Shopping::where([
            'id' => $shopping->id,
        ])->update(['payment_status' => Constants::SHOPPING_STATUS_PAYMENT]);
        echo "shoppingId=> $shopping->id \n";
    }
}
