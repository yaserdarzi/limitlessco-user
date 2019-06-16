<?php

namespace App\Http\Controllers\Api\V1\CP\Supplier;

use App\Agency;
use App\Api;
use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Shopping;
use App\ShoppingInvoice;
use App\Supplier;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use Morilog\Jalali\CalendarUtils;
use Morilog\Jalali\Jalalian;

class ReportController extends ApiController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        //
    }

    ///////////////////public function///////////////////////

    public function sales(Request $request)
    {
        if ($request->input('role') != Constants::ROLE_ADMIN)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
        if (!$request->input('from'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی وارد کردن تاریخ شروع اجباری می باشد.'
            );
        if (!$request->input('to'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی وارد کردن تاریخ پایان اجباری می باشد.'
            );
        $arrayStartDate = explode('/', $request->input('from'));
        $arrayEndDate = explode('/', $request->input('to'));
        $toDate = \Morilog\Jalali\CalendarUtils::toGregorian($arrayEndDate[0], $arrayEndDate[1], $arrayEndDate[2]);
        $fromDate = \Morilog\Jalali\CalendarUtils::toGregorian($arrayStartDate[0], $arrayStartDate[1], $arrayStartDate[2]);
        $toDate = date('Y-m-d', strtotime($toDate[0] . '-' . $toDate[1] . '-' . $toDate[2]));
        $fromDate = date('Y-m-d', strtotime($fromDate [0] . '-' . $fromDate [1] . '-' . $fromDate [2]));
        $data['countAll'] = 0;
        $data['shopping'] = Shopping::
        where(['supplier_id' => $request->input('supplier_id')])
            ->whereBetween(
                'created_at', [$fromDate, $toDate]
            )->get();
        $supplier = Supplier::where(['id' => $request->input('supplier_id')])->first();
        foreach ($data['shopping'] as $key => $value) {
            $incomeSupplier = 0;
            if ($supplier->type == Constants::TYPE_PRICE)
                $incomeSupplier = $value->price_all - $value->income - $supplier->price;
            elseif ($supplier->type == Constants::TYPE_PERCENT)
                if ($supplier->percent < 100) {
                    $incomeSupplier = ($supplier->percent / 100) * ($value->price_all);
                    $incomeSupplier = $value->price_all - $incomeSupplier - $value->income;
                }
            $value->price_supplier = $incomeSupplier ;
            $data['countAll'] = $data['countAll'] + $value->count;
            $value->date_persian = CalendarUtils::strftime('Y-m-d', strtotime($value->date));
            $value->date_end_persian = null;
            $value->created_at_persian = CalendarUtils::strftime('Y-m-d', strtotime($value->created_at));
            if ($value->date_end)
                $value->date_end_persian = CalendarUtils::strftime('Y-m-d', strtotime($value->date_end));
        }
        return $this->respond($data);
    }

    public function income(Request $request)
    {
        if ($request->input('role') != Constants::ROLE_ADMIN)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
        if (!$request->input('from'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی وارد کردن تاریخ شروع اجباری می باشد.'
            );
        if (!$request->input('to'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی وارد کردن تاریخ پایان اجباری می باشد.'
            );
        $arrayStartDate = explode('/', $request->input('from'));
        $arrayEndDate = explode('/', $request->input('to'));
        $toDate = \Morilog\Jalali\CalendarUtils::toGregorian($arrayEndDate[0], $arrayEndDate[1], $arrayEndDate[2]);
        $fromDate = \Morilog\Jalali\CalendarUtils::toGregorian($arrayStartDate[0], $arrayStartDate[1], $arrayStartDate[2]);
        $toDate = date('Y-m-d', strtotime($toDate[0] . '-' . $toDate[1] . '-' . $toDate[2]));
        $fromDate = date('Y-m-d', strtotime($fromDate [0] . '-' . $fromDate [1] . '-' . $fromDate [2]));
        $data['countAll'] = 0;
        $data['shopping'] = Shopping::
        where([
            'supplier_id' => $request->input('supplier_id'),
            'status' => Constants::SHOPPING_STATUS_FINISH
        ])->whereBetween(
            'created_at', [$fromDate, $toDate]
        )->get();
        $supplier = Supplier::where(['id' => $request->input('supplier_id')])->first();
        foreach ($data['shopping'] as $key => $value) {
            if ($supplier->type == Constants::TYPE_PRICE)
                $value->price_supplier = $value->price_payment - $value->income - $supplier->price;
            elseif ($supplier->type == Constants::TYPE_PERCENT) {
                if ($supplier->percent < 100)
                    $value->price_supplier = ($supplier->percent / 100) * ($value->price_payment - $value->income);
                else
                    $value->price_supplier = 0;
            }
            $data['countAll'] = $data['countAll'] + $value->count;
            $value->date_persian = CalendarUtils::strftime('Y-m-d', strtotime($value->date));
            $value->date_end_persian = null;
            $value->created_at_persian = CalendarUtils::strftime('Y-m-d', strtotime($value->created_at));
            if ($value->date_end)
                $value->date_end_persian = CalendarUtils::strftime('Y-m-d', strtotime($value->date_end));
        }
        return $this->respond($data);
    }

    public function cancel(Request $request)
    {
        if ($request->input('role') != Constants::ROLE_ADMIN)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
        if (!$request->input('from'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی وارد کردن تاریخ شروع اجباری می باشد.'
            );
        if (!$request->input('to'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی وارد کردن تاریخ پایان اجباری می باشد.'
            );
        $arrayStartDate = explode('/', $request->input('from'));
        $arrayEndDate = explode('/', $request->input('to'));
        $toDate = \Morilog\Jalali\CalendarUtils::toGregorian($arrayEndDate[0], $arrayEndDate[1], $arrayEndDate[2]);
        $fromDate = \Morilog\Jalali\CalendarUtils::toGregorian($arrayStartDate[0], $arrayStartDate[1], $arrayStartDate[2]);
        $toDate = date('Y-m-d', strtotime($toDate[0] . '-' . $toDate[1] . '-' . $toDate[2]));
        $fromDate = date('Y-m-d', strtotime($fromDate [0] . '-' . $fromDate [1] . '-' . $fromDate [2]));
        $data['countAll'] = 0;
        $data['shopping'] = Shopping::
        where([
            'supplier_id' => $request->input('supplier_id'),
            'status' => Constants::SHOPPING_STATUS_RETURN
        ])->whereBetween(
            'created_at', [$fromDate, $toDate]
        )->get();
        $supplier = Supplier::where(['id' => $request->input('supplier_id')])->first();
        foreach ($data['shopping'] as $key => $value) {
            if ($supplier->type == Constants::TYPE_PRICE)
                $value->price_supplier = $value->price_payment - $value->income - $supplier->price;
            elseif ($supplier->type == Constants::TYPE_PERCENT) {
                if ($supplier->percent < 100)
                    $value->price_supplier = ($supplier->percent / 100) * ($value->price_payment - $value->income);
                else
                    $value->price_supplier = 0;
            }
            $data['countAll'] = $data['countAll'] + $value->count;
            $value->date_persian = CalendarUtils::strftime('Y-m-d', strtotime($value->date));
            $value->date_end_persian = null;
            $value->created_at_persian = CalendarUtils::strftime('Y-m-d', strtotime($value->created_at));
            if ($value->date_end)
                $value->date_end_persian = CalendarUtils::strftime('Y-m-d', strtotime($value->date_end));
        }
        return $this->respond($data);
    }

    public function manifest(Request $request)
    {
        if ($request->input('role') != Constants::ROLE_ADMIN)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
        if (!$request->input('date'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی وارد کردن تاریخ پایان اجباری می باشد.'
            );
        $arrayDate = explode('/', $request->input('date'));
        $date = \Morilog\Jalali\CalendarUtils::toGregorian($arrayDate [0], $arrayDate [1], $arrayDate [2]);
        $date = date('Y-m-d', strtotime($date[0] . '-' . $date[1] . '-' . $date[2]));
        $data['countAll'] = 0;
        $shopping_id = Constants::APP_NAME_HOTEL . "-";
        $data['shopping'] = Shopping::where([
            'supplier_id' => $request->input('supplier_id'),
            'date' => $date
        ])->where('shopping_id', 'like', "%{$shopping_id}%")->get();
        foreach ($data['shopping'] as $key => $value) {
            $value->room = DB::connection(Constants::CONNECTION_HOTEL)
                ->table(Constants::APP_HOTEL_DB_ROOM_DB)
                ->where('id', explode('-', $value->shopping_id)[1])
                ->select('id', 'title')->first();
            switch (explode('-', $value->customer_id)[0]) {
                case Constants::SALES_TYPE_AGENCY:
                    $value->sales = Agency::where('id', explode('-', $value->customer_id)[1])->select('id', 'name')->first();
                    break;
                case Constants::SALES_TYPE_API:
                    $value->sales = Api::where('id', explode('-', $value->customer_id)[1])->select('id', 'name')->first();
                    break;
            }
            $data['countAll'] = $data['countAll'] + $value->count;
            $value->date_persian = CalendarUtils::strftime('Y-m-d', strtotime($value->date));
            $value->date_end_persian = null;
            $value->created_at_persian = CalendarUtils::strftime('Y-m-d', strtotime($value->created_at));
            if ($value->date_end)
                $value->date_end_persian = CalendarUtils::strftime('Y-m-d', strtotime($value->date_end));
        }
        return $this->respond($data);
    }

    ///////////////////private function///////////////////////


}
