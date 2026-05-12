@php
    $endTs = null;
    if (is_array($date) && isset($date['endTimestamp']) && is_numeric($date['endTimestamp'])) {
        $endTs = (int) $date['endTimestamp'];
    }
    $startTs = null;
    if (is_array($date) && isset($date['timestamp']) && is_numeric($date['timestamp'])) {
        $startTs = (int) $date['timestamp'];
    }
@endphp

@typography(['variant' => 'meta', 'element' => 'span', 'classList' => [$baseClass . '__date']])
    @icon(['icon' => 'date_range', 'size' => 'sm'])
    @endicon

    @if ($startTs && $endTs)
        @php
            $sameDay = wp_date('Y-m-d', $startTs) === wp_date('Y-m-d', $endTs);

            // Svenska skrivregler: kort tankstreck (U+2013), inte bindestreck.
            // - Endags: "6 maj 2026 13.00–16.00" (tider utan mellanrum kring tankstreck)
            // - Flerdags: "6 maj 2026 13.00 – 8 maj 2026 14.00" (mellanrum kring tankstreck)
            $dateOnly  = wp_date('j M Y', $startTs);
            $startTime = wp_date('H.i', $startTs);
            $endTime   = wp_date('H.i', $endTs);

            $formatted = $sameDay
                ? ($dateOnly . ' ' . $startTime . '–' . $endTime)
                : (wp_date('j M Y H.i', $startTs) . ' – ' . wp_date('j M Y H.i', $endTs));
        @endphp

        {{ $formatted }}
    @else
        {{-- Default behavior for non-events or if endTimestamp is missing --}}
        @date($date)
        @enddate
    @endif
@endtypography

