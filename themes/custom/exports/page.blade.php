@extends('layouts.export')

@section('shelf', $page->book->shelves->first()?->name ?? '')
@section('title', $page->name)

@section('content')
    @include('pages.parts.page-display')
@endsection