<div class="dynamic-guide">
@if(empty($outcome))
    @includeWhen($backgroundImage, 'partials.background-image')
    <div class="dynamic-guide__guide">
        @paper([
        'classList' => ['u-padding--6', 'dynamic-guide__guide-container']
        ])
            @includeWhen($startPage, 'guide.start-page')
            @includeWhen(!empty($steps), 'guide.steps')
            @include('guide.end-page')
            @button([
                'style'             => 'basic',
                'color'             => 'default',
                'text'              => $lang['previousStep'],
                'icon'              => 'arrow_back',
                'reversePositions'  => 'true',
                'classList'         => ['u-display--none', 'u-margin__right--auto', 'u-margin__top--3'],
                'attributeList'     => ['data-js-dynamic-guide-back-button' => '']
            ])
            @endbutton
        @endpaper
    </div>
    @else
        @include('guide.results-page')
    @endif
</div>
