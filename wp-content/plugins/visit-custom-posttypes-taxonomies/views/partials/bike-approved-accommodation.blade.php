@if ($description)
    @collection([
        'unbox' => true,
        'classList' => ['u-padding--2', 'u-margin__top--4', 'u-border__top']
    ])
        {!! $description !!}
    @endcollection
@endif
