@extends('template')

@section('body')
<?php
    $languages = \App\Language::getArray();
    $crypted_languages = json_encode($languages);
    $current_language = Cookie::get('language');
    $current_language = $current_language ? $current_language : Config::get('app.locale');
?>
    <span class='lines'></span>
        <span class='lines'></span>
        <span class='lines'></span>
        <span class='lines'></span>
        <div class="sert"> 
            <h1>@lang('acquiring.cert')</h1>
            <h1>@lang('acquiring.site')</h1>
            <p><span>@lang('acquiring.face_value')</span><span id="sum"><span>-</span> <span>-</span></span></p>
            <p><span>@lang('acquiring.price2')</span><span id="price"><span>-</span> UAH</span></p>
        </div>
        <div class="center">
            <div class="title">
                <h1>@lang('acquiring.title')</h1>
                <div class="lang">
                    @foreach($languages as $language)
                        <a value="{{ $language['label'] }}" class="{{ $language['label'] == $current_language ? 'active' : '' }}">{{ $language['displayed'] }}</a>
                    @endforeach
                </div>
            </div>

                {!! Form::open(array('action' => 'AcquiringController@postOrder',
                    'method' => 'post',
                    'class' => '',
                    'role' => 'form')) !!}
                        <div class="selectw">
                           <label class="selectlabel" for="currency">@lang('acquiring.currency'):</label>
                            {!! Form::select('currency', ['1'=>'UAH'], null, ['class' => 'dropdown',  'onchange' => 'showselect2()']) !!}
                            {!! Form::hidden('currency_name', '') !!}
                            <label class="selectlabel" for="face_value">@lang('acquiring.face_value'):</label>
                            {!! Form::select('face_value', ['1000'=>1000], null, ['class' => 'dropdown', 'onchange' => 'showdesr()']) !!}
                            <p class="selecterror col-md-12 col-xs-12 help-block">{!! $errors->first('currency') !!}</p>
                            <p class="selecterror col-md-12 col-xs-12 help-block">{!! $errors->first('face_value') !!}</p>                     
                        </div>
                        {!! Form::text('price', null, ['class' => 'form-control hidden', 'readonly', 'id' => 'priceinput']) !!}
                        {{--<p class="col-md-12 col-xs-12 help-block">{!! $errors->first('price') !!}</p>--}}
                        <p class="descr">@lang('acquiring.msg1') <span></span></p>
                        <div>
                            <label class="strange transform" for="phone">@lang('acquiring.phone'):</label>
                            {!! Form::text('phone', '', ['class' => 'form-control transform', 'placeholder' => trans('acquiring.placeholder.phone'), 'required']) !!}
                            <p class="col-md-12 col-xs-12 help-block">{!! $errors->first('phone') !!}</p>
                        </div>
                        <div> 
                        <label class="strange transform" for="email">@lang('acquiring.email'):</label>
                        {!! Form::email('email', '', ['class' => 'form-control transform', 'placeholder' => trans('acquiring.placeholder.email'), 'required']) !!}
                        <p class="col-md-12 col-xs-12 help-block">{!! $errors->first('email') !!}</p>
                        </div>
                    
                        {!! Form::submit(trans('acquiring.button.order'), ['id' => 'send']) !!}
                    
                {!! Form::close() !!}
           </div>
        </div>
@stop

@section('scripts')
<script>

    var globalJson = '';
    var faceValues;

    // design
//------------------------------------------------------------------------------------------
    


    function showselect2()
    {
        onCurrencyChange();

        TweenMax.to($('.selectw label:nth-of-type(1)'),0.2,{x:'0%', ease: Sine.easeIn});
        TweenMax.to($('.selectw .dropdown:nth-of-type(1)'),0.2,{x:'0%', ease: Sine.easeIn, delay:0.2});
        TweenMax.to($('.selectw label:nth-of-type(2)'),0.2,{x:'0%', autoAlpha:1, ease: Sine.easeIn, delay:0.4});
        TweenMax.to($('.selectw .dropdown:nth-of-type(2)'),0.2,{x:'0%', autoAlpha:1, ease: Sine.easeIn, delay:0.6});
    }

    function showdesr()
    {
        setPrice();

        TweenMax.to($('.container form .transform'), 0.2, {y:0, ease: Sine.easeIn});
        TweenMax.to($('.container form .descr'), 0.2, {y:'-90%', autoAlpha:1, ease: Sine.easeIn, delay:0.3});
        var $currency = ($("select[name=currency] :selected").text());
        var $sum = ($("select[name=face_value] :selected").text());
        var $allprice = $("#priceinput").val();
        $('#sum span:nth-of-type(1)').text($sum);
        $('#sum span:nth-of-type(2)').text($currency);
        $('#price span').text($allprice);
        $('.descr span').text($sum + ' ' + $currency);
    }


    $(document).ready(function(){
        $('.lang div').click(function(){
            TweenMax.set($('.lang a'),{className:'-=active'});
            TweenMax.set($(this),{className:'+=active'});
        })

        TweenMax.to($('.container form'),0.4,{autoAlpha:1, delay:0.5, ease: Sine.easeIn});
    });


    // features
//------------------------------------------------------------------------------------------
    function setCurrencyName()
    {
        $('input[name=currency_name]').val($("select[name=currency] :selected").text());
    }

    function setCurrency(currencies)
    {
        var currency_id = '{{ Input::old('currency') }}';
        currency_id = isNaN(parseInt(currency_id)) ? 0 : parseInt(currency_id);

        var select = '<select name="currency" class="dropdown" onchange="showselect2()" required>';
        select += '<option value="">{{ trans('acquiring.currency') }}</option>';
        for(var i in currencies){
            select += '<option value="'+i+'"'+(i == currency_id ? 'selected' : '')+'>'+currencies[i]+'</option>';
        }
        select += '</select>';

        var div = $('select[name=currency]').closest('div.dropdown');
        var label = div.prev();
        div.remove();

        label.after(select);

        $('select[name=currency]').easyDropDown({
            cutOff: 9
        });

        setCurrencyName();

        if (currency_id > 0)
            $('select[name=currency]').trigger('onchange');
        else {
            faceValues = globalJson['face_values'][$('select[name=currency]').val()];
            setFaceValues(faceValues);
        }
    }

    function setFaceValues(faceValues)
    {
        var face_id = '{{ Input::old('face_value') }}';
        face_id = isNaN(parseInt(face_id)) ? 0 : parseInt(face_id);

        var select = '<select name="face_value" class="dropdown" onchange="showdesr()" required>';
        select += '<option value="">{{ trans('acquiring.face_value') }}</option>';
        for(var i in faceValues){
            select += '<option value="'+i+'"'+(i == face_id ? 'selected' : '')+'>'+i+'</option>';
        }
        select += '</select>';

        var div = $('select[name=face_value]').closest('div.dropdown');
        var label = div.prev();
        div.remove();

        label.after(select);

        $('select[name=face_value]').easyDropDown({
            cutOff: 9
        });

        setPrice();

        if (face_id > 0)
            $('select[name=face_value]').trigger('onchange');
    }

    function setPrice()
    {
        var value = $('select[name=face_value]').val();
        $('input[name=price]').val(faceValues[value]);
    }

    function onCurrencyChange()
    {
        setCurrencyName();

        faceValues = globalJson['face_values'][$('select[name=currency]').val()];
        setFaceValues(faceValues);
    }

    $( document ).ready(function()
    {
        $.ajax({
            url: '{!! env('CERTIFICATE_SITE') !!}/api/faceValues',
            xhrFields: {withCredentials: true},
            method: "GET",
            dataType: "json",
            success: function (json) {
                globalJson = json;
                currencies = json['currencies'];
                setCurrency(currencies);
            }
        });

        var languages = JSON.parse('{!! $crypted_languages !!}');
        $('.lang a').on('click', function(){
            Cookies.set('language', languages[$(this).attr('value')]['crypt'], { expires: 365 });
            window.location = location.href;
        });
    });
</script>
@stop