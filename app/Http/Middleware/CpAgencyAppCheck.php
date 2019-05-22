<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiException;
use App\Inside\Constants;
use App\Agency;
use Closure;
use Firebase\JWT\JWT;

class CpAgencyAppCheck
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     * @throws \App\Exceptions\ApiException
     */
    public function handle($request, Closure $next)
    {
        $input = array_map(function ($input) {
            if (is_array($input)) {
                return array_map(array($this, 'safeTrim'), $input);
            }
            return trim($input);
        }, $request->all());
        if (!$request->header('appToken'))
            throw new ApiException(
                ApiException::EXCEPTION_UNAUTHORIZED_401,
                'Plz check your appToken header'
            );
        $token = JWT::decode($request->header('appToken'), config("jwt.secret"), array('HS256'));
        if (!Agency::where(['id' => $token->agency_id, 'status' => Constants::STATUS_ACTIVE])->exists())
            throw new ApiException(
                ApiException::EXCEPTION_UNAUTHORIZED_401,
                'کاربر گرامی لطفا لاگین کنید.'
            );
        $input['apps_id'] = $token->apps_id;
        $input['agency_id'] = $token->agency_id;
        $request->replace($input);
        return $next($request);
    }


    private function safeTrim($input)
    {
        if (is_array($input)) {
            return array_map(array($this, 'safeTrim'), $input);
        }
        return trim($input);
    }

}
