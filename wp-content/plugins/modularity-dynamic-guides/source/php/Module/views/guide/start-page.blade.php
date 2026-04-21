<div class="dynamic-guide__content" data-js-dynamic-guide-step>
    @if($startPage['heading'])
        @typography([
            'element' => 'h2',
            'variant' => 'h1',
            'classList' => ['u-margin__bottom--4']
        ])
        {{ $startPage['heading'] }}
        @endtypography
    @endif
    @if($startPage['preamble'])
        @typography([
            'classList' => ['u-margin__y--2'],
            'attributeList' => ['style' => 'max-width: unset;']
        ]) 
        {{ $startPage['preamble'] }}
        @endtypography
    @endif
    @button([
        'text' => $startPage['button_label'],
        'color' => 'primary',
        'attributeList' => ['data-js-dynamic-guide-button' => '']
    ])
    @endbutton
</div>