@if ($posts)
    <div class="o-grid">
        @foreach ($posts as $post)
            @php
                $isEventsArchive = !is_admin() && (is_post_type_archive('events') || is_post_type_archive('event'));
                $resolvedGridColumnClass = $isEventsArchive ? 'o-grid-12 o-grid-6@md o-grid-6@lg' : $gridColumnClass;
            @endphp
            <div class="{{ $resolvedGridColumnClass }}">
                @php
                    // Hämta start_datum från postens metadata
                    $startDatum = get_field('start_datum', $post->id);
                    $timestamp = $startDatum ? strtotime($startDatum) : $post->getArchiveDateTimestamp();

                    // Block/Image reads top-level imageAlt, not image['alt'] (component-library Block).
                    $blockImageAlt = apply_filters('sater_events_post_grid_block_image_alt', null, $post);

                    $resolvedRatio = $isEventsArchive ? '16:9' : ($archiveProps->format == 'tall' ? '12:16' : '1:1');
                @endphp
                @block([
                    'link' => $post->permalink,
                    'heading' => $post->postTitle,
                    'ratio' => $resolvedRatio,
                    'meta' => $post->termsUnlinked,
                    'secondaryMeta' => $displayReadingTime ? $post->readingTime : '',
                    'imageAlt' => $blockImageAlt,
                    'image' => $post->imageContract ?? null ? [
                        'src' => $post->imageContract,
                        'backgroundColor' => 'secondary'
                    ] : [
                        'src' => ($archiveProps->format == 'tall' && !$isEventsArchive)
                            ? ($post->images['thumbnail3:4']['src'] ?? false)
                            : ($post->images['thumbnail16:9']['src'] ?? false),
                        'alt' => $post->images['thumbnail16:9']['alt'] ?? '' ? $post->images['thumbnail16:9']['alt'] ?? '' : $post->postTitle,
                        'backgroundColor' => 'secondary'
                    ],
                    'date' => [
                        'timestamp' => $timestamp,
                        'format'    => $post->getArchiveDateFormat(),
                    ],
                    'dateBadge' => $post->getArchiveDateFormat() == 'date-badge',
                    'classList' => ['t-archive-block'],
                    'context' => ['archive', 'archive.list', 'archive.list.block'],
                ])
                @endblock
            </div>
        @endforeach
    </div>
@endif
