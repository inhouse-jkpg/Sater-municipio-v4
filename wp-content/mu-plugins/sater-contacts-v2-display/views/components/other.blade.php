@if ($contact['other'])
    @typography([
        'element' => 'div',
        'variant' => 'meta',
        'classList' => [
            'sater-contacts-v2__other',
            'u-color__text--darker'
        ]
    ])
        {!! $contact['other'] !!}
    @endtypography
@endif
