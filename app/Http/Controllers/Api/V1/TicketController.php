<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\ApiException;
use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;

class TicketController extends ApiController
{
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
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $shopping_id)
    {
        if (!$request->file('pdf'))
            throw new ApiException(
                ApiException::EXCEPTION_NOT_FOUND_404,
                'plz check your pdf'
            );
        \Storage::disk('upload')->makeDirectory('/ticket/');
        $pdf = $shopping_id . '.ticket' . $request->file('pdf')->getClientOriginalExtension();
        $exists = \Storage::disk('upload')->has('/ticket/' . $pdf);
        if ($exists == null)
            \Storage::disk('upload')->put('/ticket/' . $pdf, \File::get($request->file('pdf')->getRealPath()));
        return $this->respond(['status' => "success"]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
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
    public function destroy($id)
    {
        //
    }
}
