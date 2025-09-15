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
    
    <style>
        * {
        font-family: "Times New Roman", Times, serif !important;
        }

        h1 {
            font-size: 1.8em;
        }
        h2 {
            font-size: 1.6em;
        }
        h3 {
            font-size: 1.4em;
        }
    </style>
</head>
<body class="export export-format-{{ $format }} export-engine-{{ $engine ?? 'none' }}">

@include('layouts.parts.export-body-start')

<div class="page-content" dir="auto">
    @yield('content')
</div>

@include('layouts.parts.export-body-end')

</body>
</html>