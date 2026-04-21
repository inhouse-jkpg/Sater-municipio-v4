<div class="dynamic-guide__content u-display--none" data-js-dynamic-guide-step>

    @typography([
        'element' => 'h2',
        'variant' => 'h1',
        'classList' => ['u-margin__bottom--4']
    ])
        {{$endPage['heading']}}
    @endtypography

    <ul class="dynamic-guide__answers u-unlist u-rounded" data-js-dynamic-guide-answers></ul>
<div>
    @button([
        'text' => $endPage['result_button_label'],
        'color' => 'primary',
        'icon' => 'arrow_forward',
        'classList' => ['u-margin__top--3'],
        'attributeList' => ['data-js-dynamic-guide-button' => '']
    ])
    @endbutton
</div>

@button([
    'style' => 'basic',
    'color' => 'default',
    'text' => $endPage['restart_button_label'],
    'reversePositions' => 'true',
    'classList' => ['dynamic-guide-restart'],
    'attributeList' => ['data-js-dynamic-guide-restart-button' => '']
])
@endbutton
</div>