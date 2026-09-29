<section class="c-accordion__section" itemprop="address">
    <div class="c-accordion__button">
        <div class="c-accordion__button-wrapper" tabindex="-1">
            <span class="c-accordion__button-column">Besöksadress</span>
        </div>
    </div>

    <div class="c-accordion__content" aria-hidden="false" style="display: block; height: auto; opacity: 1; visibility: visible;">
        @typography([
            "element"       => "p",
            'variant'       => 'meta',
            'classList'     => [
                'u-margin__top--0',
                'u-color__text--darker'
            ]
        ])
            {!! $contact['visiting_address'] !!}
        @endtypography
    </div>
</section>
