<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiException;
use App\User;
use Closure;
use Firebase\JWT\JWT;

class CpRegisterAuth
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
        if (!$request->header('Authorization'))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'Plz check your Authorization'
            );
        $token = JWT::decode($request->header('Authorization'), config("jwt.secret"), array('HS256'));
        if (!User::where(['id' => $token->user_id])->exists())
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'کاربر گرامی لطفا لاگین کنید.'
            );
        $input['user_id'] = $token->user_id;
        $input['agent'] = $token->agent;
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
