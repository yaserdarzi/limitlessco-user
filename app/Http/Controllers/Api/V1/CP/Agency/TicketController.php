<?php

namespace App\Http\Controllers\Api\V1\CP\Agency;

use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use App\Shopping;
use Illuminate\Http\Request;
use App\Http\Requests;
use Morilog\Jalali\CalendarUtils;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class TicketController extends ApiController
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $customer_id = Constants::SALES_TYPE_AGENCY . "-" . $request->input('agency_id') . "-";
        $skip = 0;
        if ($request->input('page') != null)
            if ($request->input('page') != 0)
                $skip = 10 * $request->input('page');
        $shopping = Shopping::where('customer_id', 'like', "%{$customer_id}%");
        if ($request->input('search')) {
            $search = $request->input('search');
            $shopping = $shopping
                ->where('name', 'LIKE', "%$search%")
                ->orWhere('phone', 'LIKE', "%$search%");
        }
        $shopping = $shopping->take(10)->skip($skip)
            ->orderBy('created_at', 'desc')->get()->map(function ($value) {
                $value->date_persian = CalendarUtils::strftime('Y-m-d', strtotime($value->date));
                $value->created_at_persian = CalendarUtils::strftime('Y-m-d', strtotime($value->created_at));
                return $value;
            });
        return $this->respond($shopping);
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
    public function show(Request $request)
    {
        $customer_id = Constants::SALES_TYPE_AGENCY . "-" . $request->input('agency_id') . "-";
        $shopping = Shopping::where([
            'id' => $request->input('id'),
        ])->where('customer_id', 'like', "%{$customer_id}%")->first();
        if (!$shopping)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
        $shopping->date_persian = CalendarUtils::strftime('Y-m-d', strtotime($shopping->date));
        $shopping->created_at_persian = CalendarUtils::strftime('Y-m-d', strtotime($shopping->created_at));
        return $this->respond($shopping);
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


    public function ticketSendMail(Request $request)
    {
        if (!$request->input('base_url'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن base_url اجباری می باشد.'
            );
        if (!$request->input('email'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن پست الکترونیک اجباری می باشد.'
            );
        if (!$request->input('shopping_id'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی ، وارد کردن shopping_id اجباری می باشد.'
            );
        $customer_id = Constants::SALES_TYPE_AGENCY . "-" . $request->input('agency_id') . "-";
        $shopping = Shopping::where([
            'id' => $request->input('shopping_id'),
        ])->where('customer_id', 'like', "%{$customer_id}%")->first();
        if (!$shopping)
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'کاربر گرامی شما دسترسی به این قسمت ندارید.'
            );
        $connection = new AMQPStreamConnection(config("rabbitmq.server"), config("rabbitmq.port"), config("rabbitmq.user"), config("rabbitmq.password"), '/');
        $channel = $connection->channel();
        $channel->queue_declare(Constants::QUEUE_MAIL_TICKET, false, false, false, false);
        $msg = new AMQPMessage(json_encode([
            'base_url' => $request->input('base_url'),
            'email' => $request->input('email'),
            'shopping_id' => $request->input('shopping_id')
        ]),
            array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
        );
        $channel->basic_publish($msg, '', Constants::QUEUE_MAIL_TICKET);
        $channel->close();
        $connection->close();
        return $this->respond(["status" => "success"]);
    }
}
