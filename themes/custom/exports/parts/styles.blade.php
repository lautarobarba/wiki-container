{{-- Fetch in our standard export styles --}}
<style>
    @if (!app()->runningUnitTests())
        {!! file_get_contents(public_path('/dist/export-styles.css')) !!}
    @endif
</style>

{{-- Apply any additional styles that can't be applied via our standard SCSS export styles --}}
@if ($format === 'pdf')
    <style>
        /* Patches for CSS variable colors within PDF exports */
        a {
            color: {{ setting('app-link') }};
        }

        blockquote {
            border-left-color: {{ setting('app-color') }};
        }

        body.export-engine-dompdf {
            counter-reset: page;
        }

        body.export-engine-dompdf::before {
            /* content: counter(page); */
            content: "{{ trim($__env->yieldContent('shelf')) }} - {{ trim($__env->yieldContent('title')) }}";
            position: fixed;
            top: 0;
            right: 0;
            font-size: 10px;
            color: #222;
            counter-increment: 1;
        }

        body.export-engine-dompdf::after {
            content: "Página " . counter(page);
            position: fixed;
            bottom: 0;
            right: 0;
            font-size: 10px;
            color: #222;
            counter-increment: 1;
        }
    </style>
@endif