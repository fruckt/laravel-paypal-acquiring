@extends('template')

@section('body')
        <span class='lines'></span>
        <span class='lines'></span>
        <span class='lines'></span>
        <span class='lines'></span>
        <div class="sert sert_pay"> 
            <h1>@lang('acquiring.cert')</h1>
            <h1>@lang('acquiring.site')</h1>
            <p><span>@lang('acquiring.face_value')</span><span id="sum"><mark>{!! $face_value.' '.$currency_name !!}</mark></span></p>
            <p><span>@lang('acquiring.price2')</span><span id="price"><mark>{!! explode('.', $p_SaleSum)[0].' '.$p_SaleCurr !!}</mark></span></p>
        </div>
        <div class="center">
    <form name="PayToFido" action="https://pay.fidobank.ua/EquPlace/pay" metod="POST" class="pay_page">
        <div class="text">
            @lang('acquiring.msg2') <mark>{!! $face_value.' '.$currency_name !!}</mark> @lang('acquiring.msg3') <mark>{!! explode('.', $p_SaleSum)[0].' '.$p_SaleCurr !!}</mark>
            <br/>
            @lang('acquiring.msg4') <mark>{!! $phone !!}</mark>
            <br/>
            @lang('acquiring.msg5') <mark>{!! $email !!}</mark>
        </div>
        <input type="hidden" name="OrderID" value="{!! $p_OrderId !!}">
        <input type="hidden" name="OrderDate" value="{!! $p_OrderDate !!}">
        <?php
            for ($i = 0; $i < count($p_SaleDetal); $i++) {
                echo '<input type="hidden" name="SaleDetal" value="'.$p_SaleDetal[$i].'">';
                echo '<input type="hidden" name="SalePrice" value="'.$p_SalePrice[$i].'">';
            }
        ?>
        <input type="hidden" name="SaleCurr" value="{!! $p_SaleCurr !!}">
        <input type="hidden" name="SaleSum" value="{!! $p_SaleSum !!}">
        <input type="hidden" name="ReturnUrl" value="{!! $p_ReturnUrl !!}">
        <input type="hidden" name="HashCode" value="{!! $signature !!}">
        <input type="hidden" name="MerchantId" value="{!! $p_MerchantId !!}">
        <input type="hidden" name="TerminalId" value="{!! $p_TerminalId !!}">
        <div class="form-group">
            <button type="submit" class="but">@lang('acquiring.button.pay')</button>
        </div>
    </form>
    </div>
</div>
@stop

@section('scripts')
@stop