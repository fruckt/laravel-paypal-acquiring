<?php
/**
 * Created by PhpStorm.
 * User: fruckt
 * Date: 09.12.2016
 * Time: 9:59
 */

namespace App\Http\Controllers;


use GuzzleHttp\Client;

class HomeController extends Controller
{
    public function index()
    {
        $params = [
            'languages' => \App\Language::getArray(),
            'json' => '{}',
        ];

        $client = new Client();
        try {
            $response = $client->get(env('CERTIFICATE_SITE') . '/api/faceValues', [
                'headers' => [
                    'X-Requested-With' => 'XMLHttpRequest',
                ]
            ]);

            $params['json'] = $response->getBody()->getContents();

        } catch (\Exception $e) {

        }

        return view('pages.home', $params);
    }
}