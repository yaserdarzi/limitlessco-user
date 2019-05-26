<?php

namespace App\Http\Middleware;

use App\AgencyApp;
use App\App;
use App\Exceptions\ApiException;
use Closure;

class CpAgencyAppName
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

        if (!$request->header('AppName'))
            throw new ApiException(
                ApiException::EXCEPTION_UNAUTHORIZED_401,
                'Plz check your AppName header'
            );
        $app = App::whereIn(
            'id', $request->input('apps_id')
        )->where('app', $request->header('AppName'))->first();
        if (!$app)
            throw new ApiException(
                ApiException::EXCEPTION_UNAUTHORIZED_401,
                'Plz check your AppName header'
            );
        $agencyApp = AgencyApp::where([
            'agency_id' => $request->input('agency_id'),
            'app_id' => $app->id,
        ])->get();
        if (!sizeof($agencyApp))
            throw new ApiException(
                ApiException::EXCEPTION_UNAUTHORIZED_401,
                'کاربر گرامی شما دسترسی به این قسمت را ندارید.'
            );
        $input['app_id'] = $app->id;
        $input['app_title'] = $app->app;
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