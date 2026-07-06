
<div class="{{$baseClass}}__image-container">
    @php
        $decorative = !empty($GLOBALS['sater_a11y_etjanster_decorative_card_images']);
        $alt = $decorative ? '' : (is_array($image) ? ($image['alt'] ?? null) : null);
        $attributeList = $decorative ? [
            'aria-hidden' => 'true',
            'data-decorative-card-image' => 'true',
        ] : [];
    @endphp
    @image([
        'src' => is_array($image) ? ($image['src'] ?? null) : $image,
        'alt' => $alt,
        'cover' => true,
        'classList' => [
            $baseClass . '__image'
        ],
        'placeholderEnabled' => $hasPlaceholder,
        'attributeList' => $attributeList,
    ])
    @endimage
</div>
