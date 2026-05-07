@if ($posts)
    <div class="o-grid">
        @foreach ($posts as $post)
            <div class="{{ $gridColumnClass }}">
                @php
                    $isEventsArchive = !is_admin() && (is_post_type_archive('events') || is_post_type_archive('event'));

                    // For events: attach an end timestamp so Card.components.date can show a range.
                    $endRaw = $isEventsArchive ? (string) get_field('slut_datum', $post->id) : '';
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
                        'endTimestamp' => $isEventsArchive ? $endTs : null,
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

