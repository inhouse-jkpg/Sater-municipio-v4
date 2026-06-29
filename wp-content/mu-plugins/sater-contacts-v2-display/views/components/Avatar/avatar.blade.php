<!-- avatar.blade.php -->
@if($displayAvatar)

    <div class="{{ $class }}" {!! $attribute !!}>
        
        {{-- If the avatar has an image --}}
        @if($image)
            {{-- Decorative: alt="" avoids duplicate name in screen readers; aria-hidden hides image from AT. --}}
            @image(
                [
                    'src' => $image,
                    'classList' => [$baseClass.'__image'],
                    'alt' => '',
                    'attributeList' => [
                        'aria-hidden' => 'true',
                        'data-decorative-avatar' => 'true',
                    ],
                    'cover' => true
                ]
            )
            @endimage
        @endif

        {{-- If the avatar has an icon --}}
        @if($icon)
            <span class="{{$baseClass}}__icon" aria-hidden="true">
                @icon(
                    [
                        'icon' => $icon['name'],
                        'decorative' => true,
                        'classList' => ["c-icon--size-".$icon['size']]
                    ]
                )
                @endicon
            </span>
        @endif

        {{-- If the avatar has initials --}}
        @if($initials)
            <svg class="{{$baseClass}}__initials" aria-hidden="true" viewBox="0 0 1000 1000" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                <text font-size="380" y="50%" x="50%" fill="#fff" dominant-baseline="middle" text-anchor="middle" alignment-baseline="central">{{$initials}}</text>
            </svg>
        @endif
        
    </div>

@endif
