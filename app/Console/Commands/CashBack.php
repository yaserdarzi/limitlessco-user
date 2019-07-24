<?php

namespace App\Console\Commands;

use App\AgencyWallet;
use App\AgencyWalletInvoice;
use App\Inside\Constants;
use App\Shopping;
use Illuminate\Console\Command;

class CashBack extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cash:back {shopping_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'cash back for agency';

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
        $shopping = Shopping::where('id', $this->argument('shopping_id'))->first();
        $wallet = AgencyWallet::where('agency_id', explode('-', $shopping->customer_id)[1])->first();
        $walletPaymentTokenAgencyCount = AgencyWalletInvoice::count();
        $walletPaymentTokenAgency = "a-" . ++$walletPaymentTokenAgencyCount;
        AgencyWalletInvoice::create([
            'agency_id' => explode('-', $shopping->customer_id)[1],
            'wallet_id' => $wallet->id,
            'price_before' => $wallet->price,
            'price' => intval((1 / 100) * $shopping->price_payment),
            'price_after' => intval($wallet->price + intval((1 / 100) * $shopping->price_payment)),
            'price_all' => intval((1 / 100) * $shopping->price_payment),
            'type_status' => Constants::INVOICE_TYPE_STATUS_PRICE,
            'status' => Constants::INVOICE_STATUS_SUCCESS,
            'type' => Constants::INVOICE_TYPE_SHOPPING_CASH_BACK,
            'invoice_status' => Constants::INVOICE_INVOICE_STATUS_SHOPPING . " - " . Constants::INVOICE_INVOICE_STATUS_INCREMENT . " - " . Constants::INVOICE_INVOICE_STATUS_CASH_BACK,
            'payment_token' => $walletPaymentTokenAgency,
            'market' => Constants::INVOICE_MARKET_WALLET,
            'info' => ['wallet' => $wallet],
        ]);
        AgencyWallet::where('agency_id', explode('-', $shopping->customer_id)[1])->update([
            'price' => intval($wallet->price + intval((1 / 100) * $shopping->price_payment))
        ]);
        echo "success \n";
    }
}
