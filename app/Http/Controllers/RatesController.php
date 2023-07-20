<?php

namespace App\Http\Controllers;

use App\Facades\Error;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RatesController extends Controller
{
    public function proceed ( Request $request )
    {
        $data   = [
            'status' => 'success',
            'code'   => 200,
        ];
        $params = $request->collect();

        switch ( $params[ 'method' ] ?? false ) {
            case 'rates':
            {
                if ( $request->getMethod() === 'GET' ) {
                    $data[ 'data' ] = $this->rates($params[ 'currency' ] ?? false);
                } else {
                    return Error::api_error('unknown_method');
                }
                break;
            }
            case 'convert':
            {
                if ( $request->getMethod() === 'POST' ) {
                    if (
                        ( $currency_from = $params[ 'currency_from' ] ?? false ) &&
                        ( $currency_to = $params[ 'currency_to' ] ?? false ) &&
                        ( $value = $params[ 'value' ] ?? false )
                    ) {
                        $convert_result = $this->convert($currency_from, $currency_to, $value);
                        if ( $convert_result[ 'data' ] ?? false ) {
                            $data[ 'data' ] = $convert_result[ 'data' ];
                        } else {
                            return Error::api_error($convert_result[ 'error' ]);
                        }
                    } else {
                        return Error::api_error('not_enough_data');
                    }
                } else {
                    return Error::api_error('unknown_method');
                }
                break;
            }
            default:
            {
                return Error::api_error('unknown_method');
            }
        }


        return response()->view('main', [ 'data' => $data ])->header('Content-Type', 'application/json');
    }

    private function rates ( $currency = false )
    {
        $result = [];
        if ( $rates = $this->get_rates() ) {
            if ( $currency ) {
                $currency_list = explode(',', $currency);
                foreach ( $currency_list as $currency_name ) {
                    if ( $rates[ $currency_name ] ) {
                        $result[ $currency_name ] = $rates[ $currency_name ] + ( ( $rates[ $currency_name ] / 100 ) * 2 );
                    }
                }
            } else {
                $result = $rates;
            }
        } else {
            return Error::api_error('get_rates');
        }

        return $result ?? [];
    }

    private function convert ( $from, $to, $value )
    {
        $result = [];
        if ( $rates = $this->get_rates() ) {
            $rates[ 'BTC' ] = 1.00;
            $to             = strtoupper($to);
            $from           = strtoupper($from);
            if (
                isset($rates[ $to ]) &&
                isset($rates[ $from ])
            ) {
                $formatted_rates = [];
                foreach ( $rates as $currency_from => $rate_from ) {
                    foreach ( $rates as $currency_to => $rate_to ) {
                        $real_rate = 1;
                        if ( $rate_from > $rate_to ) {
                            $real_rate = $rate_to / $rate_from;
                        } elseif ( $rate_from < $rate_to ) {
                            $real_rate = $rate_from / $rate_to;
                        }
                        $formatted_rates[ $currency_from ][ $currency_to ] = $real_rate;
                    }
                    $formatted_rates[ $currency_from ][ $currency_from ] = 1.00;
                    $formatted_rates[ 'BTC' ][ $currency_from ]          = $rate_from;
                    $formatted_rates[ $currency_from ][ 'BTC' ]          = 1 / $rate_from;
                }
                $rate            = $formatted_rates[ $from ][ $to ];
                $result_value    = $rate * (float)$value;
                $converted_value = ( $result_value + ( $result_value / 100 * 2 ) );

                $result[ 'data' ] = [
                    'currency_from'   => $from,
                    'currency_to'     => $to,
                    'value'           => (float)$value,
                    'converted_value' => max(round($converted_value, 10), 0.01),
                    'rate'            => round($rate, 10),
                ];
            } else {
                $result[ 'error' ] = 'unknown_currency';
            }
        } else {
            $result[ 'error' ] = 'get_rates';
        }

        return $result;
    }

    private function get_rates ()
    {
        $rates = false;
        if ( Cache::has('rates') ) {
            $rates = Cache::get('rates');
        } else {
            $endpoint = 'https://blockchain.info/ticker';
            $client   = new Client();
            $response = $client->request('GET', $endpoint);
            if ( $response->getStatusCode() === 200 ) {
                $call_result = json_decode($response->getBody(), true);
                foreach ( $call_result as $rate_key => $rate_data ) {
                    $rates[ $rate_key ] = round($rate_data[ 'buy' ], 10);
                }
                uasort($rates, [ $this, 'cmp' ]);
                Cache::add('rates', $rates, now()->addMinutes(5));
            }
        }

        return $rates;
    }

    private function cmp ( $a, $b )
    {
        if ( $a == $b ) {
            return 0;
        }

        return ( $a < $b ) ? -1 : 1;
    }
}
