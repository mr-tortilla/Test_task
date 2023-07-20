<?php

namespace App\Services;

class ApiErrorService
{
    public function get_error_data ( $name = false )
    {
        $errors_list = [
            'unknown'          => [
                'code'    => 503,
                'status'  => 'error',
                'message' => 'Unknown error',
            ],
            'invalid_token'    => [
                'code'    => 403,
                'status'  => 'error',
                'message' => 'Invalid token',
            ],
            'no_token'         => [
                'code'    => 403,
                'status'  => 'error',
                'message' => 'Token not found',
            ],
            'unknown_method'   => [
                'code'    => 404,
                'status'  => 'error',
                'message' => 'Unknown method',
            ],
            'get_rates'        => [
                'code'    => 503,
                'status'  => 'error',
                'message' => 'Error while get rates data',
            ],
            'unknown_currency' => [
                'code'    => 403,
                'status'  => 'error',
                'message' => 'Unknown currency',
            ],
            'not_enough_data'  => [
                'code'    => 403,
                'status'  => 'error',
                'message' => 'Not enough data',
            ],
        ];

        return $name ? ( $errors_list[ $name ] ?? $errors_list[ 'unknown' ] ) : $errors_list;
    }

    public function api_error ( $name = false )
    {
        $error_data = $this->get_error_data($name);

        return response()->view('main', [ 'data' => [ $error_data ] ], $error_data[ 'code' ])->header('Content-Type', 'application/json');
    }
}
