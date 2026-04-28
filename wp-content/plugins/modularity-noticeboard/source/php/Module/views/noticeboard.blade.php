<div class="{{ $classes }}">
    @typography([
        'variant' => 'h4',
        'element' => 'h4',
        'classList' => ['box-title']
    ])
        {{ $postTitle }}
    @endtypography

    @if (!empty($introductoryText))
        {!! $introductoryText !!}
    @endif

    <ul>
        @forelse ($postItems as $postItem)
            <li>
                @if ($postItem['link'])
                    <a href="{{ $postItem['link'] }}">{{ $postItem['title'] }} {{ $postItem['meetingDate'] }}</a>
                @else
                    {{ $postItem['title'] }} {{ $postItem['meetingDate'] }}
                @endif
                {{ $strings['posted'] }}: {{ $postItem['published'] }}
                @if ($postItem['unpubDate'])
                    {{ $strings['taken_down'] }}: {{ $postItem['unpubDate'] }}
                @endif
            </li>
        @empty
            <li>{{ $strings['no_posts'] }}</li>
        @endforelse
    </ul>
</div>
