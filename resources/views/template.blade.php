<!DOCTYPE html>
<html>
<head>
    <title>@lang('acquiring.title')</title>
    <meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
    <meta name="description" content="@lang('template.description')" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="shortcut icon" href="{!! URL::to('favicon.ico') !!}">

    {!! HTML::style('css/normalize.css') !!}
    {!! HTML::style('css/main.css') !!}

    @yield('link_styles')

    {!! HTML::script('//code.jquery.com/jquery-latest.js') !!}
    {!! HTML::script('js/gsap/TimelineMax.min.js') !!}
    {!! HTML::script('js/gsap/TweenMax.min.js') !!}
    {!! HTML::script('js/jquery.easydropdown.min.js') !!}
    {!! HTML::script('/js/jquery.form.min.js') !!}
    {!! HTML::script('/js/jquery-cookies.js') !!}

    @yield('link_scripts')

</head>
<body>

    <div class="container">
        @yield('body')
    </div>

    @yield('scripts')

</body>
</html>

