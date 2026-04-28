@extends('templates.single')
@section('hero-top-sidebar')
    @if (!empty($featuredImage->src[0]))
        @hero([
            'image' => $featuredImage->src[0]
        ])
        @endhero
        @if (!$placeQuicklinksAfterContent)
            @include('partials.navigation.fixed')
        @endif
    @endif