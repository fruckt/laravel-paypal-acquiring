<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Crypt;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use PayPal\Api\Amount;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

class AcquiringController extends Controller
{
    public static function getApiContext()
    {
        $apiContext = new ApiContext(
            new OAuthTokenCredential(
                env('PAYPAL_CLIENT_ID'),
                env('PAYPAL_CLIENT_SECRET')
            )
        );

        $apiContext->setConfig(
            array(
                'mode' => 'sandbox',
                // 'log.LogEnabled' => true,
                // 'log.FileName' => '../PayPal.log',
                // 'log.LogLevel' => 'DEBUG', // PLEASE USE `INFO` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
                // 'cache.enabled' => true,
                // 'http.CURLOPT_CONNECTTIMEOUT' => 30
                // 'http.headers.PayPal-Partner-Attribution-Id' => '123123123'
                //'log.AdapterFactory' => '\PayPal\Log\DefaultLogFactory' // Factory class implementing \PayPal\Log\PayPalLogFactory
            )
        );

        return $apiContext;
    }


    public function postOrder(Request $request)
    {
        $rules = [
            'currency' => 'required|integer',
            'face_value' => 'required|integer',
            'price' => 'integer',
            'phone' => 'required|numeric',
            'email' => 'required|email',
        ];

        $input = $request->all();
        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            return redirect()->back()
                ->withInput()
                ->withErrors($validator);
        }

        $phone = $input['phone'];
        $email = $input['email'];
        $face_value = $input['face_value'];

        $client = new Client();
        try {
            $response = $client->post(env("CERTIFICATE_SITE") . '/api/certificate', [
                'form_params' => [
                    'phone' => $phone,
                    'email' => $email,
                    'currency' => $input['currency'],
                    'face_value' => $face_value,
                ]
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors($e->getCode() . ':' . $e->getMessage());
        }

        $json = \GuzzleHttp\json_decode($response->getBody()->getContents());
        if ($json->status != 'ok') {
            return redirect()->back()->withErrors($json['msg']);
        }

        $code = $json->code;
        $price = $input['price'];
        $currency = $input['currency_name'];

        return redirect()->route('acquiring.payment')
            ->with(compact('code', 'price', 'currency', 'phone', 'email', 'face_value'));
    }

    public function getPayment(Request $request)
    {
        $code = Session::get('code');
        if (empty($code))
            return redirect()->to('/');

        return view('pages.payment', [
            'code' => $code,
            'price' => Session::get('price'),
            'currency' => Session::get('currency'),
            'phone' => Session::get('phone'),
            'email' => Session::get('email'),
            'face_value' => Session::get('face_value'),
        ]);
    }

    public function postPayment(Request $request)
    {
        $rules = [
            'code' => 'required|string|min:16|max:16',
            'price' => 'required|integer',
            'currency' => 'required|string|min:3|max:3',
        ];

        $input = $request->all();
        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            return redirect()->to(route('acquiring.status'))
                ->withInput()
                ->withErrors($validator);
        }

        $payer = new Payer();
        $payer->setPaymentMethod("paypal");

        $item = new Item();
        $item->setName(trans('acquiring.cert'))
            ->setCurrency($input['currency'])
            ->setQuantity(1)
            ->setSku($input['code'])
            ->setPrice($input['price']);

        $itemList = new ItemList();
        $itemList->setItems(array($item));

        $amount = new Amount();
        $amount->setCurrency($input['currency'])
            ->setTotal($input['price']);

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($itemList)
            ->setDescription(trans('acquiring.title'))
            ->setInvoiceNumber($input['code']);

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl(route('acquiring.execute', ['success' => true], true))
            ->setCancelUrl(route('acquiring.execute', ['success' => false], true));

        $payment = new Payment();
        $payment->setIntent("sale")
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions(array($transaction));

        try {
            $payment->create(self::getApiContext());
        } catch (\Exception $e) {
            return redirect()->route('acquiring.status')
                ->withErrors($e->getCode() . ':' . $e->getMessage());
        }

        $request->session()->put($payment->getId(), $input['code']);

        return redirect()->to($payment->getApprovalLink());
    }

    public function getExecute(Request $request)
    {
        $code = '';

        if ($request->get('success')) {
            $paymentId = $request->get('paymentId');
            $payment = Payment::get($paymentId, self::getApiContext());

            $execution = new PaymentExecution();
            $execution->setPayerId($request->get('PayerID'));

            try {
                $result = $payment->execute($execution, self::getApiContext());

                try {
                    $payment = Payment::get($paymentId, self::getApiContext());
                    $code = $request->session()->pull($payment->getId());
                } catch (\Exception $e) {
                    return redirect()->route('acquiring.status')->withErrors($e->getCode() . ':' . $e->getMessage());
                }
            } catch (\Exception $e) {
                return redirect()->route('acquiring.status')->withErrors($e->getCode() . ':' . $e->getMessage());
            }
        } else {
            return redirect()->route('acquiring.status')->withErrors(trans('acquiring.canceled'));
        }

        if ($code){
            $client = new Client();
            try {
                $response = $client->put(env("CERTIFICATE_SITE") . '/api/certificate', [
                    'form_params' => [
                        'code' => Crypt::encrypt($code),
                    ]
                ]);
            } catch (\Exception $e) {
                return redirect()->route('acquiring.status')->withErrors($e->getCode() . ':' . $e->getMessage());
            }

            $json = \GuzzleHttp\json_decode($response->getBody()->getContents());
            if ($json->status != 'ok') {
                return redirect()->route('acquiring.status')->withErrors($json['msg']);
            }
        }

        return redirect()->route('acquiring.status');
    }

    public function getStatus(Request $request)
    {
        $request->session()->reflash();
        return view('pages.status');
    }
}