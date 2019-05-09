<?php

namespace App\Http\Middleware;

use App\Exceptions\ApiException;
use Closure;

class AppCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
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
        if (!$request->header('app'))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'Plz check your app header'
            );
        if (!$request->header('type_app'))
            throw new ApiException(
                ApiException::EXCEPTION_BAD_REQUEST_400,
                'Plz check your type_app header'
            );

        $token = JWT::decode($request->header('Authorization'), config("jwt.secret"), array('HS256'));
        $input['agent'] = $token->agent;
        $input['agency_id'] = $token->agency_id;
        $input['agency_agent_id'] = $token->agency_agent_id;
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
