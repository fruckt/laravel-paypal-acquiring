<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Crypt;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class AcquiringController extends Controller
{
    public function postOrder(Request $request)
    {
        $input = Input::all();
        $rules = [
            'currency' => 'required|integer',
            'face_value' => 'required|integer',
            'price' => 'integer',
            'phone' => 'required|numeric',
            'email' => 'required|email',
        ];

        $validator = Validator::make($input, $rules);

        if ($validator->fails()) {
            return Redirect::back()
                ->withInput()
                ->withErrors($validator);
        }

        $phone = $input['phone'];
        $email = $input['email'];
        $face_value = $input['face_value'];
        $currency_name = $input['currency_name'];

        $data = [
            'phone' => $phone,
            'email' => $email,
            'currency' => $input['currency'],
            'face_value' => $face_value,
        ];

        $ch = $this->buildCurlObject('POST', $data);

        $result = curl_exec($ch);
        $error_no = curl_errno($ch);
        $error_msg = curl_error($ch);
        curl_close($ch);

        if (!$result) {
            return Redirect::back()->withErrors($error_no . ':' . $error_msg);
        }

        $json = json_decode($result);
        if ($json->status != 'ok') {
            return Redirect::back()->withErrors($json['msg']);
        }

        $code = $json->code;

        //Переменные
        $p_OrderId = $code;
        $p_OrderDate = strftime('%d%m%Y%H%M%S');
        $p_SaleDetal = array( //Описание товара
            $code
        );
        $p_SalePrice = array( //Цена за товар
            $input['price'].'.00'
        );
        $p_SaleCurr = $input['currency_name'];
        $p_SaleSum = $input['price'].'.00';
        $p_ReturnUrl = env('PAYMENT_ANSWER_LINK');
        $p_MerchantId = '9999999';
        $p_TerminalId = 'E9999999';
        $p_hashKey = 'ProdFidoBank@EquPlace2016';
        $signature = gen_HashCode($p_OrderId,
            $p_OrderDate,
            $p_SaleDetal,
            $p_SalePrice,
            $p_SaleCurr,
            $p_SaleSum,
            $p_ReturnUrl,
            $p_MerchantId,
            $p_TerminalId,
            $p_hashKey);

        \Log::info('-------Order parameters-------');
        \Log::info('p_OrderId       '.$p_OrderId);
        \Log::info('p_OrderDate     '.$p_OrderDate);
        \Log::info('p_SaleDetal     '.implode(',', $p_SaleDetal));
        \Log::info('p_SalePrice     '.implode(',', $p_SalePrice));
        \Log::info('p_SaleCurr      '.$p_SaleCurr);
        \Log::info('p_SaleSum       '.$p_SaleSum);
        \Log::info('p_ReturnUrl     '.$p_ReturnUrl);
        \Log::info('p_MerchantId    '.$p_MerchantId);
        \Log::info('p_TerminalId    '.$p_TerminalId);
        \Log::info('p_hashKey       '.$p_hashKey);
        \Log::info('signature       '.$signature);
        \Log::info('email           '.$email);
        \Log::info('phone           '.$phone);
        \Log::info('face_value      '.$face_value);
        \Log::info('currency_name   '.$currency_name);
        \Log::info('------------------------------');

        return Redirect::route('acquiring.payment')
            ->with(compact('p_OrderId',
                'p_OrderDate',
                'p_SaleDetal',
                'p_SalePrice',
                'p_SaleCurr',
                'p_SaleSum',
                'p_ReturnUrl',
                'p_MerchantId',
                'p_TerminalId',
                'p_hashKey',
                'signature',
                'phone',
                'email',
                'face_value',
                'currency_name'
            ));
    }

    public function getPayment(Request $request)
    {
        $p_OrderId = Session::get('p_OrderId');
        if (!$p_OrderId) {
            return Redirect::to('/');
        }

        return view('pages.payment', [
            'p_OrderId' => $p_OrderId,
            'p_OrderDate' => Session::get('p_OrderDate'),
            'p_SaleDetal' => Session::get('p_SaleDetal'),
            'p_SalePrice' => Session::get('p_SalePrice'),
            'p_SaleCurr' => Session::get('p_SaleCurr'),
            'p_SaleSum' => Session::get('p_SaleSum'),
            'p_ReturnUrl' => Session::get('p_ReturnUrl'),
            'p_MerchantId' => Session::get('p_MerchantId'),
            'p_TerminalId' => Session::get('p_TerminalId'),
            'p_hashKey' => Session::get('p_hashKey'),
            'signature' => Session::get('signature'),
            'phone' => Session::get('phone'),
            'email' => Session::get('email'),
            'face_value' => Session::get('face_value'),
            'currency_name' => Session::get('currency_name'),
        ]);
    }

    public function postAnswer(Request $request)
    {
        //Сообщение
        $ResultMsg = $request->has('ResultMsg') ? $request->get('ResultMsg') : null;
        //Результат
        $ResultComm = $request->has('ResultComm') ? $request->get('ResultComm') : null;
        //Код результата
        $ResultCode = $request->has('ResultCode') ? $request->get('ResultCode') : null;
        //Номер заказа
        $OrderId = $request->has('OrderID') ? $request->get('OrderID') : null;
        //Код продавца
        $MerchantId = $request->has('MerchantId') ? $request->get('MerchantId') : null;
        //Контрольная сумма
        $HashOut = $request->has('HashOut') ? $request->get('HashOut') : null;
        //Ключ подписи ответа
        $HashKeyResult = 'DVSGROUP258';

        $tmpStr = '';
        $tmpStr .= mb_strlen($ResultMsg,'utf8').$ResultMsg;
        $tmpStr .= mb_strlen($ResultComm,'utf8').$ResultComm;
        $tmpStr .= mb_strlen($ResultCode,'utf8').$ResultCode;
        $tmpStr .= mb_strlen($OrderId,'utf8').$OrderId;
        $tmpStr .= mb_strlen($MerchantId,'utf8').$MerchantId;
        $Signature = hash_hmac('sha1', $tmpStr, $HashKeyResult);

        \Log::info('--------Order answer---------');
        \Log::info('ResultMsg       '.$ResultMsg);
        \Log::info('ResultComm      '.$ResultComm);
        \Log::info('ResultCode      '.$ResultCode);
        \Log::info('OrderId         '.$OrderId);
        \Log::info('MerchantId      '.$MerchantId);
        \Log::info('HashOut         '.$HashOut);
        \Log::info('$Signature      '.$Signature);
        \Log::info('------------------------------');

        if ($Signature === $HashOut && $ResultCode === '000'){
            $data = [
                'code' => Crypt::encrypt($OrderId),
            ];

            $ch = $this->buildCurlObject('PUT', http_build_query($data));

            $result = curl_exec($ch);
            $error_no = curl_errno($ch);
            $error_msg = curl_error($ch);
            curl_close($ch);

            \Log::info('--------Karman answer---------');
            \Log::info('result          '.$result);
            \Log::info('error_no        '.$error_no);
            \Log::info('error_msg       '.$error_msg);
            \Log::info('------------------------------');

            if (!$result) {
                return Redirect::route('acquiring.status')
                    ->with(compact('error_no',
                        'error_msg',
                        'ResultCode',
                        'OrderId'
                    ));
            } else if (json_decode($result)->status !== 'ok') {
                $error_no = 999;
                $error_msg = 'Ошибка подтверждения оплаты сертификата';
                return Redirect::route('acquiring.status')
                    ->with(compact('error_no',
                        'error_msg',
                        'ResultCode',
                        'OrderId'
                    ));
            }
        }

        return Redirect::route('acquiring.status')
            ->with(compact('ResultMsg',
                'ResultComm',
                'ResultCode',
                'OrderId',
                'HashOut',
                'Signature'
            ));
    }

    public function getStatus(Request $request)
    {
        $OrderId = Session::get('OrderId');
        $ResultCode = Session::get('ResultCode');
        if (!$OrderId || $ResultCode) {
            return Redirect::to('/');
        }

        return view('pages.payment', [
            'OrderId' => $OrderId,
            'ResultCode' => $ResultCode,
            'ResultComm' => Session::get('ResultComm'),
            'ResultMsg' => Session::get('ResultMsg'),
            'HashOut' => Session::get('HashOut'),
            'Signature' => Session::get('Signature'),
        ]);
    }

    private function buildCurlObject($method = 'POST', $data)
    {
        $ch = curl_init(env("CERTIFICATE_SITE") . '/api/certificate');
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_COOKIESESSION, false);

        return $ch;
    }
}

//Функция хеширования по алгоритму
function gen_HashCode ( $f_OrderId,
                        $f_OrderDate,
                        $f_SaleDetal,
                        $f_SalePrice,
                        $f_SaleCurr,
                        $f_SaleSum,
                        $f_ReturnUrl,
                        $f_MerchantId,
                        $f_TerminalId,
                        $f_hashKey) {
    $str = '';
    $tmpStr = '';
    $resMsg = null;
    if ($f_SalePrice !== null)
        for ($i = 0; $i < count($f_SaleDetal); $i++) {
            $tmpStr .= mb_strlen($f_SaleDetal[$i] ,'utf8').'_'.$f_SaleDetal[$i];
            $tmpStr .= mb_strlen($f_SalePrice[$i] ,'utf8').'_'.$f_SalePrice[$i];
        }
    if ($f_SaleCurr == null) $resMsg .= '[валюта] ';
    $tmpStr .= mb_strlen($f_SaleCurr ,'utf8').'_'.$f_SaleCurr;
    if ($f_SaleSum == null) $resMsg .= '[сумма] ';
    $tmpStr .= mb_strlen($f_SaleSum ,'utf8').'_'.$f_SaleSum;
    if ($f_OrderId == null) $resMsg .= '[номер заказа] ';
    $tmpStr .= mb_strlen($f_OrderId ,'utf8').'_'.$f_OrderId;
    if ($f_OrderDate == null) $resMsg .= '[дата заказа] ';
    $tmpStr .= mb_strlen($f_OrderDate ,'utf8').'_'.$f_OrderDate;
    if ($f_MerchantId == null) $resMsg .= '[код продавца] ';
    $tmpStr .= mb_strlen($f_MerchantId ,'utf8').'_'.$f_MerchantId;
    if ($f_TerminalId == null) $resMsg .= '[код терминала] ';
    $tmpStr .= mb_strlen($f_TerminalId ,'utf8').'_'.$f_TerminalId;
    if ($f_hashKey == null) $resMsg .= '[ключ продавца]';
    if (mb_strlen($tmpStr ,'utf8')>3000)
        $str = substr($tmpStr,0,3000);
    else
        $str = $tmpStr;

    if ($resMsg !== null) {
        $resMsg = 'ERR: Не указаны параметры: '.$resMsg;
        echo "$resMsg <br>";
    }

    $resMsg = hash_hmac('sha1', $str,$f_hashKey);

    return $resMsg;
}

