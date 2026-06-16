<span>
    @button([
        'text' => $number,
        'ariaLabel' => 'Ring ' . $number,
        'color' => 'default',
        'style' => 'basic',
        'href' => 'tel:' . $number,
        'icon' => $type == 'smartphone' ? 'smartphone' : 'call',
        'reversePositions' => 'true',
        'attributeList' => [
            'itemprop' => 'telephone'
        ],
        'classList' => ['c-button--phone', 'c-button--' . $type, 'u-margin--0']
    ])
    @endbutton
</span>
