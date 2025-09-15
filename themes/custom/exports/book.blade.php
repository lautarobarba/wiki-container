@extends('layouts.export')

@section('shelf', $book->shelves->first()?->name ?? '')
@section('title', $book->name)

@section('content')

    <h1 style="font-size: 2em">{{$book->name}}</h1>
    <div>{!! $book->descriptionHtml() !!}</div>

    @include('exports.parts.book-contents-menu', ['children' => $bookChildren])

    @foreach($bookChildren as $bookChild)
        @if($bookChild->isA('chapter'))
            @include('exports.parts.chapter-item', ['chapter' => $bookChild])
        @else
            @include('exports.parts.page-item', ['page' => $bookChild, 'chapter' => null])
        @endif
    @endforeach

@endsection