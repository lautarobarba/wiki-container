@extends('layouts.export')

@section('shelf', $chapter->book->shelves->first()?->name ?? '')
@section('title', $chapter->name)

@section('content')

    <h1 style="font-size: 1.8em">{{$chapter->name}}</h1>
    <div>{!! $chapter->descriptionHtml() !!}</div>

    @include('exports.parts.chapter-contents-menu', ['pages' => $pages])

    @foreach($pages as $page)
        @include('exports.parts.page-item', ['page' => $page, 'chapter' => null])
    @endforeach

@endsection