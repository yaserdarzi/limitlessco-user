<?php

namespace App\Http\Controllers\Api\V1\CP\Crm;

use App\Agency;
use App\Http\Controllers\ApiController;
use App\Inside\Constants;
use Illuminate\Http\Request;

class AgencyController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
//        agency name name phone username password text ersal sms note
        $agency = Agency::select(
            Constants::AGENCY_DB . '.id',
            Constants::AGENCY_DB . '.name as agency_name',
            Constants::USERS_DB . '.name as user_name',
            Constants::USERS_DB . '.phone',
            Constants::USERS_DB . '.username',
            Constants::USERS_DB . '.password_username'
        )->join(Constants::AGENCY_USERS_DB, Constants::AGENCY_DB . '.id', '=', Constants::AGENCY_USERS_DB . '.agency_id')
            ->join(Constants::USERS_DB, Constants::AGENCY_USERS_DB . '.user_id', '=', Constants::USERS_DB . '.id');
        if ($request->input('search')) {
            $agency = $agency->orWhere([
                [Constants::AGENCY_DB . '.name', 'like', '%' . $request->input("search") . '%']
            ]);
            $agency = $agency->orWhere([
                [Constants::USERS_DB . '.name', 'like', '%' . $request->input("search") . '%']
            ]);
            $agency = $agency->orWhere([
                [Constants::USERS_DB . '.phone', 'like', '%' . $request->input("search") . '%']
            ]);
        }
        $agency = $agency->orderByDesc(Constants::AGENCY_DB . '.created_at')->take(10)->get();
        return $this->respond($agency);

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
    public function show($id, Request $request)
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
}
