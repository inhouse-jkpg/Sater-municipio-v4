@extends('templates.master')

@section('before-layout')
@stop

@section('helper-navigation')
    @includeWhen($helperNavBeforeContent, 'partials.navigation.helper')
@stop

@section('hero-top-sidebar')
    @includeIf('partials.hero')
    @includeIf('partials.sidebar', ['id' => 'top-sidebar'])
@stop

@section('above')
    @include('partials.sidebar', ['id' => 'above-columns-sidebar', 'classes' => ['o-grid']])
@stop

@section('sidebar-left')
    @if ($showSidebars)

        @include('partials.sidebar', [
            'id' => 'left-sidebar',
            'classes' => ['o-grid'],
        ])

        @if ($customizer->secondaryNavigationPosition == 'left')
            @if (!empty($secondaryMenu['items']))
                <div class="u-margin__bottom--4 u-display--none@xs u-display--none@sm u-display--none@md">
                    @paper()
                        @includeIf('partials.navigation.sidebar', ['menuItems' => $secondaryMenu['items']])
                    @endpaper
                </div>
            @endif
        @endif

        @include('partials.sidebar', [
            'id' => 'left-sidebar-bottom',
            'classes' => ['o-grid'],
        ])

    @endif
@stop

@section('content')

    {!! $hook->loopStart !!}

    @includeIf('partials.sidebar', ['id' => 'content-area-top', 'classes' => ['o-grid']])

    @section('loop')
        @includeIf('partials.loop')
    @show

    @includeIf('partials.sidebar', ['id' => 'content-area', 'classes' => ['o-grid']])

    @includeWhen($quicklinksPlacement === 'below_content', 'partials.navigation.fixed')

    {!! $hook->loopEnd !!}

@stop

@section('sidebar-right')
@if ($showSidebars)
    @if ($customizer->secondaryNavigationPosition == 'right')
        @if (!empty($secondaryMenu['items']))
            <div class="u-margin__bottom--4 u-display--none@xs u-display--none@sm u-display--none@md">
                @paper()
                    @includeIf('partials.navigation.sidebar', ['menuItems' => $secondaryMenu['items']])
                @endpaper
            </div>
        @endif
    @endif
@endif

@if ($eventData)
    <div class="o-grid single-event-sidebar-right">
        @if($eventData['start_datum'] || $eventData['slut_datum'])
            <div class="c-card c-card--ratio-16-9" data-uid="681dbf56501a2">
                <div class="c-card__body">
                    @if($eventData['start_datum'])
                        <h2 class="c-typography c-card__heading c-typography__variant--h3" data-uid="681dbf56500eb">
                        Startdatum
                        </h2>
                        {{ $eventData['start_datum'] }}
                    @endif
                </div>
                @if($eventData['slut_datum'])
                    <div class="c-card__body">
                        <h2 class="c-typography c-card__heading c-typography__variant--h3" data-uid="681dbf56500eb">
                        Slutdatum
                        </h2>
                        {{ $eventData['slut_datum'] }}
                    </div>
                @endif
            </div>
        @endif

        @if($eventData['plats'])
            <div class="c-card c-card--ratio-16-9" data-uid="681dbf56501a2">
                <div class="c-card__body">
                    
                    <h2 class="c-typography c-card__heading c-typography__variant--h3" data-uid="681dbf56500eb">
                    Plats
                    </h2>
                    {{ $eventData['plats'] }}
                </div>
            </div>
        @endif

        @if($eventData['pris'])
            <div class="c-card c-card--ratio-16-9" data-uid="681dbf56501a2">
                <div class="c-card__body">
                    
                    <h2 class="c-typography c-card__heading c-typography__variant--h3" data-uid="681dbf56500eb">
                    Pris
                    </h2>
                    {{ $eventData['pris'] }}
                </div>
            </div>
        @endif

        @if($eventData['arrangor'])
            <div class="c-card c-card--ratio-16-9" data-uid="681dbf56501a2">
                <div class="c-card__body">
                    
                    <h2 class="c-typography c-card__heading c-typography__variant--h3" data-uid="681dbf56500eb">
                    Arrangör
                    </h2>
                    {{ $eventData['arrangor'] }}
                </div>
            </div>
        @endif
    </div>
@endif

@includeIf('partials.sidebar', ['id' => 'right-sidebar', 'classes' => ['o-grid']])
@stop

@section('below')
@includeIf('partials.sidebar', ['id' => 'content-area-bottom', 'classes' => ['o-grid']])

<!-- Comments -->
@section('article.comments.before')@show
@includeIf('partials.comments')
@section('article.comments.after')@show

@stop
