@if ($posts)
    <div class="o-grid">
        @foreach ($posts as $post)
            <div class="{{ $gridColumnClass }}">
                @php
                    // Hämta start_datum från postens metadata
                    $startDatum = get_field('start_datum', $post->id);
                    $timestamp = $startDatum ? strtotime($startDatum) : $post->getArchiveDateTimestamp();

                    // Block/Image reads top-level imageAlt, not image['alt'] (component-library Block).
                    $blockImageAlt = apply_filters('sater_events_post_grid_block_image_alt', null, $post);
                @endphp
                @block([
                    'link' => $post->permalink,
                    'heading' => $post->postTitle,
                    'ratio' => $archiveProps->format == 'tall' ? '12:16' : '1:1',
                    'meta' => $post->termsUnlinked,
                    'secondaryMeta' => $displayReadingTime ? $post->readingTime : '',
                    'imageAlt' => $blockImageAlt,
                    'image' => $post->imageContract ?? null ? [
                        'src' => $post->imageContract,
                        'backgroundColor' => 'secondary'
                    ] : [
                        'src' => $archiveProps->format == 'tall' ? $post->images['thumbnail3:4']['src'] ?? false : $post->images['thumbnail16:9']['src'] ?? false,
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
