<!doctype html>
<html lang="{{ $locale->htmlLang() }}">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>@yield('title')</title>

    @if($cspContent ?? false)
        <meta http-equiv="Content-Security-Policy" content="{{ $cspContent }}">
    @endif

    @include('exports.parts.styles', ['format' => $format, 'engine' => $engine ?? ''])
    @include('exports.parts.custom-head')
</head>
<body class="export export-format-{{ $format }} export-engine-{{ $engine ?? 'none' }}">
{{-- <h1>TODO: CUSTOM EXPORT LAYOUT!!!</h1> --}}
@include('layouts.parts.export-body-start')
<div class="page-content" dir="auto">
    @yield('content')
</div>
@include('layouts.parts.export-body-end')
</body>
</html>