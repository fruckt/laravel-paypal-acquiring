@extends('template')

@section('body')
    <span class='lines'></span>
    <span class='lines'></span>
    <span class='lines'></span>
    <span class='lines'></span>
    <div class="sert sert_pay">
        <h1>@lang('acquiring.cert')</h1>
        <h1>@lang('acquiring.site')</h1>
        <p><span>@lang('acquiring.face_value')</span><span id="sum"><mark>{!! $face_value.' '.$currency!!}</mark></span></p>
        <p><span>@lang('acquiring.price2')</span><span id="price"><mark>{!! $price.' '.$currency !!}</mark></span></p>
    </div>
    <div class="center">
    {!! Form::open(['url' => '/payment', 'method' => 'post', 'class' => 'pay_page']) !!}
        <div class="text">
            @lang('acquiring.msg2') <mark>{{ $face_value.' '.$currency }}</mark> @lang('acquiring.msg3') <mark>{{ $price.' '.$currency }}</mark>
            <br/>
            @lang('acquiring.msg4') <mark>{{ $phone }}</mark>
            <br/>
            @lang('acquiring.msg5') <mark>{{ $email }}</mark>
        </div>
        {!! Form::hidden('code', $code) !!}
        {!! Form::hidden('price', $price) !!}
        {!! Form::hidden('currency', $currency) !!}
        <div class="form-group">
            {!! Form::submit(trans('acquiring.button.pay'), ['class' => 'but']) !!}
        </div>
    {!! Form::close() !!}
    </div>
</div>
@stop

@section('scripts')
@stop