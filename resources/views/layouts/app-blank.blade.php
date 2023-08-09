<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="apple-touch-icon" sizes="180x180" href="{{ URL::asset('apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ URL::asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ URL::asset('favicon-16x16.png') }}">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('../node_modules/bootstrap/dist/css/bootstrap.min.css') }}" crossorigin="anonymous" />
    <link rel="stylesheet" href="{{ asset('../node_modules/@fortawesome/fontawesome-free/css/all.min.css') }}" crossorigin="anonymous" />
{{--    <link rel="stylesheet" href="{{ asset('../node_modules/@tailwindcss/forms/dist/tailwind.css') }}" />--}}

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

</head>
<body>
<div id="app">

    @yield('content')

</div>

<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="{{ asset('../node_modules/jquery/dist/jquery.min.js') }}" crossorigin="anonymous"></script>
{{--<script src="{{ asset('../node_modules/@popperjs/core/dist/cjs/popper.js') }}" crossorigin="anonymous"></script>--}}
<script src="{{ asset('../node_modules/bootstrap/dist/js/bootstrap.min.js') }}" crossorigin="anonymous"></script>
</body>
</html>
