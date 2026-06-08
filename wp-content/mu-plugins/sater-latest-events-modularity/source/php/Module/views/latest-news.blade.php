@if (($objectType ?? 'events') === 'events')
    <div class="o-grid sater-latest-events__grid" @if (!empty($ID) && empty($hideTitle) && !empty($post_title)) aria-labelledby="mod-latest-news-{{ $ID }}-label" @endif>
        @if (empty($hideTitle) && !empty($post_title))
            <div class="o-grid-12@md">
                <h2 @if (!empty($ID)) id="mod-latest-news-{{ $ID }}-label" @endif>{{ $post_title }}</h2>
            </div>
        @endif

        @if (!empty($posts))
            @foreach ($posts as $post)
                @php
                    $image = class_exists(\Municipio\Helper\Post::class)
                        ? \Municipio\Helper\Post::getFeaturedImage($post->ID, [520, 390])
                        : false;
                    $hasPlaceholder = false;

                    if (empty($image) || empty($image['src'])) {
                        $placeholderUrl = (string) apply_filters('sater_events_placeholder_image_url', '');
                        $placeholderUrl = $placeholderUrl !== '' ? set_url_scheme($placeholderUrl) : '';

                        if ($placeholderUrl !== '') {
                            $image = [
                                'src' => $placeholderUrl,
                                'alt' => (string) $post->post_title,
                            ];
                        } else {
                            $hasPlaceholder = true;
                            $image = null;
                        }
                    }

                    $startRaw = (string) get_field('start_datum', $post->ID);
                    $endRaw   = (string) get_field('slut_datum', $post->ID);
                    $startTs  = $startRaw !== ''
                        ? apply_filters('sater_events_event_datetime_to_timestamp', null, $startRaw)
                        : null;
                    $endTs    = $endRaw !== ''
                        ? apply_filters('sater_events_event_datetime_to_timestamp', null, $endRaw)
                        : null;

                    $excerpt = '';
                    $extended = get_extended($post->post_content);
                    if (!empty($extended['main'])) {
                        $excerpt = wp_trim_words(
                            wp_strip_all_tags(strip_shortcodes($extended['main'])),
                            30,
                            null
                        );
                    }
                @endphp

                <div class="{{ $gridColumnClass }}">
                    @card([
                        'link' => get_permalink($post->ID),
                        'heading' => apply_filters('the_title', $post->post_title),
                        'content' => $excerpt,
                        'image' => $image,
                        'ratio' => '4:3',
                        'classList' => ['u-height--100', 'sater-latest-events__card'],
                        'context' => ['module.latest-news', 'module.latest-news.events'],
                        'containerAware' => true,
                        'hasPlaceholder' => $hasPlaceholder,
                        'date' => $startTs ? [
                            'timestamp' => $startTs,
                            'endTimestamp' => $endTs,
                        ] : null,
                    ])
                    @endcard
                </div>
            @endforeach
        @endif

        <div class="o-grid-12@md">
            <div class="t-read-more-section u-display--flex u-align-content--center u-margin__y--4">
                @button([
                    'text' => __('Evenemangskalender', 'modularity-latest-news'),
                    'href' => get_post_type_archive_link('events'),
                    'color' => 'secondary',
                    'style' => 'filled',
                    'size' => 'md',
                    'classList' => ['u-flex-grow--1@xs', 'u-margin__x--auto'],
                    'attributeList' => [
                        'target' => '_top',
                        'type' => 'button',
                        'aria-label' => __('Visa mer', 'modularity-latest-news'),
                    ],
                ])
                @endbutton
            </div>
        </div>
    </div>
@endif
