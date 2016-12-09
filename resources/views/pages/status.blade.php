@extends('template')

@section('body')

    <div class="row">
        @if (isset($errorno))
            {!! $error_no.':'.$error_msg !!}
        @elseif ($Signature !== $HashOut)
            @lang('acquiring.msg6')
        @elseif ($ResultCode === "000")
            <h1@lang('acquiring.msg7')<h1>
        @else
            <h1>@lang('acquiring.msg8')<h1>
            @lang('acquiring.msg9') {!! $ResultCode !!}<br>
            @lang('acquiring.msg10'){!! $OrderId !!}<br>
            {!! $ResultComm !!}<br>
            {!! $ResultMsg !!}<br>
        @endif
    </div>
    <a href="{!! url('/') !!}" class="btn btn-primary">@lang('acquiring.button.back')</a>
@stop

@section('scripts')
@stop