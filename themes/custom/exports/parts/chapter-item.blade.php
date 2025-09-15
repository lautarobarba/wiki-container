<div class="page-break"></div>

<h1 id="chapter-{{$chapter->id}}" style="font-size: 1.8em">{{ $chapter->name }}</h1>

<div>{!! $chapter->descriptionHtml() !!}</div>

@if(count($chapter->visible_pages) > 0)
    @foreach($chapter->visible_pages as $page)
        @include('exports.parts.page-item', ['page' => $page, 'chapter' => $chapter])
    @endforeach
@endif