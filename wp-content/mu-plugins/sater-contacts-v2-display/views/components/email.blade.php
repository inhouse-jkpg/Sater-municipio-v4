@php
    $email = $contact['email'] ?? '';
@endphp

<span>
    @button([
        'text' => $email,
        'ariaLabel' => 'Skicka e-post till ' . $email,
        'color' => 'default',
        'style' => 'basic',
        'href' => 'mailto:' . $email,
        'icon' => 'outgoing_mail',
        'reversePositions' => 'true',
        'attributeList' => [
            'itemprop' => 'email'
        ],
        'classList' => ['c-button--email', 'u-margin--0']
    ])
    @endbutton
</span>
