{{-- Buscador limitado al sistema (filtra por el tag [sistema=<nombre del estante>]) --}}
<div class="mb-xl">
    <form id="shelf-scoped-search-form" action="{{ url('/search') }}" method="GET" class="search-box flexible" role="search">
        <input type="text" name="term" aria-label="{{ trans('entities.shelves_search_this') }}" placeholder="{{ trans('entities.shelves_search_this') }}">
        <input type="hidden" id="shelf-sistema-value" value="{{ $shelf->name }}">
        <button tabindex="-1" type="submit" aria-label="{{ trans('common.search') }}">@icon('search')</button>
    </form>
</div>
<script nonce="{{ $cspNonce }}">
    (function () {
        var form = document.getElementById('shelf-scoped-search-form');
        if (!form) return;
        form.addEventListener('submit', function () {
            var termInput = form.querySelector('input[name="term"]');
            var sistema = document.getElementById('shelf-sistema-value').value;
            var filter = '[sistema=' + sistema + ']';
            if (termInput.value.indexOf(filter) === -1) {
                termInput.value = (termInput.value + ' ' + filter).trim();
            }
        });
    })();
</script>

@if($shelf->tags->count() > 0)
    <div id="tags" class="mb-xl">
        @include('entities.tag-list', ['entity' => $shelf])
    </div>
@endif