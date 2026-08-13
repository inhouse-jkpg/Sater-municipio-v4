{{--
  WCAG 1.3.1: Item labels must not be headings. Keep module title as h2.
  Links live in a real ul/li; Collection item stays the clickable row.
--}}
@card([
    'attributeList' => [
        ...(!$hideTitle && !empty($postTitle) ? ['aria-labelledby' => 'mod-inlaylist-' . $ID . '-label'] : []),
    ],
    'context' => 'module.inlay.list'
])

    @if (!$hideTitle && !empty($postTitle))
        <div class="c-card__header">
            @typography([
                'id'        => 'mod-inlaylist-' . $ID . '-label',
                'element'   => 'h2',
                'variant'   => 'h4',
                'classList' => []
            ])
                {!! $postTitle !!}
            @endtypography
        </div>
    @endif

    @if (!empty($items))
        @collection([
            'sharpTop' => true,
            'componentElement' => 'ul',
            'classList' => [
                'sater-a11y-inlaylist',
            ],
        ])
            @foreach($items as $item)
                <li class="sater-a11y-inlaylist__item">
                    @collection__item([
                        'icon' => 'arrow_forward',
                        'link' => $item['href']
                    ])
                        @typography([
                            'element' => 'span',
                            'variant' => 'h4',
                            'classList' => [
                                'sater-a11y-inlaylist-item-title',
                            ],
                        ])
                            {{$item['label']}}
                        @endtypography
                    @endcollection__item
                </li>
            @endforeach
        @endcollection
    @endif
@endcard
