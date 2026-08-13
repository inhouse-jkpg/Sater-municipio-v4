{{--
  Manual Input "list" appearance: same WCAG 1.3.1 fix as Inlay List.
  Item titles are not headings; items are wrapped in ul/li.
--}}
@card([
    'context' => $context,
])
    @if ((empty($hideTitle) && !empty($postTitle)) || !empty($titleIcon))
    <div class="c-card__header">
        @include('partials.post-title',
        [
            'variant' => 'h4', 'classList' => [],
            'titleIcon' => $titleIcon ?? null
        ])
    </div>
    @endif

    @if (!empty($manualInputs))
        @collection([
            'sharpTop' => true,
            'bordered' => true,
            'componentElement' => 'ul',
            'classList' => [
                'sater-a11y-inlaylist',
            ],
        ])
            @foreach ($manualInputs as $input)
                <li class="sater-a11y-inlaylist__item">
                    @collection__item([
                        'icon' => $input['icon'] ?? 'arrow_forward',
                        'link' => $input['link'],
                        'classList' => $input['classList'] ?? [],
                        'attributeList' => [
                            ...($input['attributeList'] ?? []),
                            ...($input['link'] ? ['aria-labelledby' => $input['id']] : [])
                        ]
                    ])
                        @typography([
                            'element' => 'span',
                            'variant' => 'h4',
                            'id'      => $input['id'],
                            'classList' => [
                                'sater-a11y-inlaylist-item-title',
                            ],
                        ])
                            {{ $input['title'] }}
                        @endtypography
                    @endcollection__item
                </li>
            @endforeach
        @endcollection
    @endif
@endcard
