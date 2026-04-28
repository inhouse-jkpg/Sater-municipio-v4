@segment([
    'title'         => $outcome->outcomeTitle,
    'content'       => $outcome->outcomeContent,
    'layout'        => 'split',
    'image'         => !empty($outcome->outcomeImage['src']) ? $outcome->outcomeImage['src'] : false,
    'background'    => 'primary',
    'textColor'     => 'light',
    'textAlignment' => 'center',
    'imageFocus'    => ['top' => '90', 'left' => '100'],
])
 
@if($outcome->outcomeCallToActionUrl && $outcome->outcomeCallToActionLabel)
    @button([
        'variant'       => 'default',
        'text'          => $outcome->outcomeCallToActionLabel,
        'icon'          => 'arrow_forward',
        'href'          => $outcome->outcomeCallToActionUrl
    ])
    @endbutton
@endif
 
@endsegment
 @if ($outcome->outcomePosts)
    <div class="o-grid">
        @foreach($outcome->outcomePosts as $post)    
            <div class="o-grid-12@xs o-grid-6@sm o-grid-4@md u-margin__top--4">
                @block([
                    'heading' => $post->postTitle,
                    'ratio' => '12:16',
                    'filled' => true,
                    'image' => $post->images['thumbnail12:16'],
                    'link' => $post->permalink,
                ])
                @endblock
            </div>
        @endforeach
    </div>
@endif
@button([
    'style' => 'basic',
    'color' => 'default',
    'text' => $resultsPage['restart_button_label'],
    'icon' => 'arrow_back',
    'reversePositions' => 'true',
    'classList' => ['u-margin__right--auto', 'u-margin__top--3'],
    'attributeList' => ['data-js-dynamic-guide-endpage-back-button' => '']
])
@endbutton