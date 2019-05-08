<?php

namespace App\Http\Controllers\Api\V1;

use App\ContactUs;
use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use App\Inside\Helpers;
use Illuminate\Http\Request;

class ContactUsController extends ApiController
{
    protected $help;

    public function __construct()
    {
        $this->help = new Helpers();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
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
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!$request->input("name"))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                "plz check your name"
            );
        if (!filter_var($request->input("email"), FILTER_VALIDATE_EMAIL))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                "plz check your email"
            );
        if (!$request->input("phone"))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                "plz check your phone"
            );
        if (!$request->input("message"))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                "plz check your message"
            );
        $phone = $this->help->phoneChecker($request->input("phone"));
        ContactUs::create([
            "name" => $request->input("name"),
            "email" => $request->input("email"),
            "phone" => $phone,
            "message" => $request->input("message"),
            "ipAddress" => $request->ip()
        ]);
        return $this->respond(["status" => "success"], null);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
