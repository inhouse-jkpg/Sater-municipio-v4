@if ($posts)
    <div class="o-grid">
        @foreach ($posts as $post)
            <div class="{{ $gridColumnClass }}">
                @php
                    $eventPt = method_exists($post, 'getPostType') ? (string) $post->getPostType() : '';
                    $isEventPost = !is_admin()
                        && in_array($eventPt, ['events', 'event'], true);

                    // For events anywhere (archive, front page lists, etc.): end timestamp for Card.components.date range.
                    $endRaw = $isEventPost ? (string) get_field('slut_datum', $post->id) : '';
                    $endTs  = $endRaw !== '' ? apply_filters('sater_events_event_datetime_to_timestamp', null, $endRaw) : null;
                @endphp

                @card([
                    'link' => $post->permalink,
                    'image' => $post->imageContract ?? $post->images['thumbnail16:9'],
                    'heading' => $post->postTitle,
                    'classList' => ['t-archive-card', 'u-height--100', 'u-display--flex', 'u-level-2'],
                    'content' => \Municipio\Helper\Sanitize::sanitizeATags($post->excerptShort),
                    'tags' => $post->termsUnlinked,
                    'meta' => $displayReadingTime ? $post->readingTime : '',
                    'date' => [
                        'timestamp' => $post->getArchiveDateTimestamp(),
                        'format'    => $post->getArchiveDateFormat(),
                        'endTimestamp' => $isEventPost ? $endTs : null,
                    ],
                    'dateBadge' => $post->getArchiveDateFormat() == 'date-badge',
                    'context' => ['archive', 'archive.list', 'archive.list.card'],
                    'containerAware' => true,
                    'hasPlaceholder' => $anyPostHasImage && empty($post->images['thumbnail16:9']['src'])
                ])
                @endcard
            </div>
        @endforeach
    </div>
@endif

