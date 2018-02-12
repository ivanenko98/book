<?php

namespace App\Http\Controllers;

use App\Http\Traits\FormatResponse;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, FormatResponse;

    public function arrayResponse($status, $message = null, $data = null){
        $response = array();
        $response['status']     = $status;
        $response['message']    = $message;
        $response['data']       = $data;

        return $response;
    }
}
