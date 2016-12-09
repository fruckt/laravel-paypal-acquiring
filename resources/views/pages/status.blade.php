@extends('template')

@section('body')

    <div class="center">
        <div class="title">
        @if ($errors->any())
            <div class="row">
                <div class="col-xs-12">
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li><h1>{{ $error }}</h1></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @else
            <h1>@lang('acquiring.msg7')</h1>
        @endif
        <h1><a href="{!! url('/') !!}" class="btn btn-default">@lang('acquiring.button.back')</a></h1>
    </div>
@stop

@section('scripts')
@stop