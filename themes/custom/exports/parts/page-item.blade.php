<div class="page-break"></div>

@if (isset($chapter))
    <div class="chapter-hint">{{$chapter->name}}</div>
@endif

<h1 id="page-{{$page->id}}" style="font-size: 1.6em">{{ $page->name }}</h1>
{!! $page->html !!}