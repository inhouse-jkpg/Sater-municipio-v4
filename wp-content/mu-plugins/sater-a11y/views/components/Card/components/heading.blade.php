@php
    // E-tjänster link cards: keep h2+h3 visual mix but avoid announcing "heading level 2" with the link.
    $isEtjansterLinkCard = !empty($GLOBALS['sater_a11y_etjanster_page_cards']) && !empty($link);
    $headingElement = $isEtjansterLinkCard ? 'span' : 'h2';
    $headingClassList = [
        $baseClass . '__heading',
        'u-margin__y--0',
    ];
    if ($isEtjansterLinkCard) {
        $headingClassList[] = 'sater-a11y-etjanster-card-title';
    }
@endphp
@group([
    'classList' => [$baseClass."__heading-container"],
    'justifyContent' => 'space-between',
    'alignItems' => 'center',
])
    @group([
        'direction' => 'vertical'
    ])

        @if($heading)
            @typography([
                'element'   => $headingElement,
                'variant'   => 'h3',
                'classList' => $headingClassList,
            ])
                {!! $heading !!}
            @endtypography
        @endif

        @if($subHeading)
            @typography([
                'element' => 'span',
                'variant' => 'h6',
                'classList' => [
                    $baseClass . '__sub-heading',
                    'u-margin__y--0'
                ]
            ])
                {!! $subHeading !!}
            @endtypography
        @endif

        @includeWhen($meta && !$metaFirst, 'Card.components.meta')
    @endgroup
    @includeWhen($collapsible, 'Card.components.collapsiableButton')
    @includeWhen($icon, 'Card.components.icon')
@endgroup
