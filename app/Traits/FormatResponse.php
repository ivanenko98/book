<?php

namespace App\Http\Traits;

/**
 * Created by PhpStorm.
 * User: westham
 * Date: 06.02.18
 * Time: 16:00
 */
trait FormatResponse
{
    protected function formatResponse($status, $message = null, $data = null){
        return [
            'status' => $status,
            'data' => $data,
            'message' => $message,
        ];
    }

}