<style>
    @keyframes pulseate {
        0% {
            transform: scale(1);
        }

        40% {
            transform: scale(3);
            opacity: 0;
        }

        100% {
            opacity: 0;
        }
    }

    @keyframes fadeIn {
        0% {
            opacity: 0;
            transform: scale(.5);
        }

        100% {
            transform: scale(1);
            opacity: 1;
        }
    }

    @keyframes fadeInSimple {
        0% {
            opacity: 0;
        }
        100% {
            opacity: 1;
        }
    }
</style>
<style scoped>
    .mod-interactive-map-wrapper ul li::before {
        display: none;
    }

    .mod-interactive-map-container {
        position: relative;
    }

    .mod-interactive-map-pin {
        opacity: 0;
        transform: translate(-50%, -50%) scale(0);
        transition: opacity 300ms,
                    transform 300ms;
        position: absolute;
        min-width: 12px;
        min-height: 12px;
        max-width: 20px;
        max-height: 20px;
        cursor: pointer;
        margin: 0px !important;
        width: 1vw;
        height: 1vw;
        color: #fff;
        pointer-events: all;
    }

    .mod-interactive-map-pin svg {
        width: 100%;
        display: block;
    }

    .mod-interactive-map-pin::after {
        content: '';
        display: block;
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        transform: scale(1);
        border-radius: 50%;
        border: 1px solid currentColor;
        box-shadow: 0 0 10px #000;
        animation: pulseate 2000ms infinite ease-in-out;
    }

    .mod-interactive-map-pin.pin-visible {
        transform: translate(-50%, -50%) scale(1);
        opacity: 1;
    }

    .zoomlevel-2 .mod-interactive-map-pin {
        min-width: 8px;
        min-height: 8px;
        max-width: 12px !important;
        max-height: 12px !important;
    }

    .zoomlevel-3 .mod-interactive-map-pin,
    .zoomlevel-4 .mod-interactive-map-pin,
    .zoomlevel-5 .mod-interactive-map-pin,
    .zoomlevel-6 .mod-interactive-map-pin {
        min-width: 6px;
        min-height: 6px;
        max-width: 8px !important;
        max-height: 8px !important;
    }

    .mod-interactive-map-pin.mod-interactive-map-pin-active .mod-interactive-map-pin-info {
        display: block;
    }

    .mod-interactive-map-pin-info.mod-interactive-map-pin-info-hidden {
        visibility: hidden;
        opacity: 0;
        transform: translateY(0px);
    }

    .mod-interactive-map-pin-info {
        z-index: 3;
        position: absolute;
        width: 0;
        height: 0;
        visibility: visible;
        overflow: visible;
        opacity: 1;
        transform: translateY(10px);
        transition: opacity 300ms ease-in-out,
                    transform 300ms ease-in-out,
                    visibility 301ms;
    }

    @media (max-width: 600px) {
        .mod-interactive-map-pin-info {
            width: auto !important;
            left: 0 !important;
            top: 0 !important;
        }

        .mod-interactive-map-pin-info .mod-interactive-map-pin-wrapper {
            width: 90% !important;
            margin-left: 5%;
            margin-right: 5%;
            transform: none !important;
        }

        .mod-interactive-map-pin-info::after {
            display: none !important;
        }
    }

    .mod-interactive-map-pin-info .mod-interactive-map-pin-wrapper {
        position: relative;
        width: 350px;
        background-color: #fff;
        text-align: center;
        padding: 20px;
        border-radius: 5px;
        cursor: default;
        border-radius: 2px;
        box-shadow: 0 0 3px rgba(0,0,0,.2);
        margin-top: -10px;
        margin-left: 10px;
        transform: translate(-50%, -100%);
    }

    .mod-interactive-map-pin-info::after {
        content: '';
        display: block;
        position: absolute;
        top: calc(100% - 10px);
        left: calc(50% + 10px);
        transform: translateX(-50%);
        z-index: 99;
        width: 0;
        height: 0;
        border-left: 10px solid transparent;
        border-right: 10px solid transparent;
        border-top: 10px solid #fff;
    }

    [data-interactive-map-close-tooltip] {
        position: absolute;
        right: 10px;
        top: 10px;
        border: 0;
        background-color: transparent;
        font-size: 20px;
        cursor: pointer;
    }

    [data-interactive-map-close-tooltip]:hover {
        color: #ff0000;
    }

    .mod-interactive-map-categories {
        margin-bottom: 60px;
        animation: fadeInSimple 300ms ease-in;
    }

    .mod-interactive-map-categories ul {
        text-align: center;
    }

    .mod-interactive-map-categories li {
        display: inline-block;
        padding: 5px 10px;
        padding-right: 15px;
        border-radius: 3px;
        background-color: rgba(0,0,0,.1);
        text-align: left;
    }

    .mod-interactive-map-category-color-indicator {
        display: inline-block;
        width: 15px;
        height: 15px;
    }

    .mod-interactive-map-category-color-indicator svg {
        width: 100%;
        display: block;
    }

    .mod-interactive-map-pin-info a.btn {
        margin-top: 10px;
    }

    .mod-iteractive-map-buttons {
        display: block;
        position: absolute;
        right: 0;
        bottom: 10px;
        padding: 7px 15px;
    }

    .mod-interactive-map-reset-button {
        position: absolute;
        right: 0;
        margin: 7px 15px;
        top: 10px;
    }

    .mod-iteractive-map-buttons.mod-interactive-map-pin-info-hidden {
        display: none;
    }

    .mod-iteractive-map-buttons > button {
    }

    .mod-iteractive-map-buttons > button:hover,
    .mod-iteractive-map-buttons > button:hover i {
        opacity: 1;
        cursor: hand;
        cursor: pointer;
    }

    .mod-iteractive-map-buttons > button + button {
    }

    .mod-iteractive-map-buttons > button > i {
    }

    .mod-interactive-map-overflower {
        pointer-events: none;
        position: relative;
    }

    .mod-interactive-map-overflower img:not(:first-of-type) {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        opacity: 0;
        transform: translateY(-10px);
        transition: opacity 100ms, transform 100ms;
    }

    .mod-interactive-map-overflower img.pin-visible {
        opacity: 1;
        transform: translateY(0px);
    }

    .mod-interactive-map-zoomable .image-layer {
        width: 100%;
    }

</style>

<div class="mod-interactive-map-wrapper">
    @if (!empty($categories))
    <div class="mod-interactive-map-categories">
        <ul>
            @foreach ($categories as $category)
            <li>
                <label>
                    <input type="checkbox" name="mod-interactive-map-category-checkbox" data-interactive-map-category="{{ $category['name'] }}">
                    {{ $category['name'] }}
                    <span class="mod-interactive-map-category-color-indicator">
                        {!! $category['svg'] !!}
                    </span>
                </label>
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="mod-interactive-map-container">

        <!-- Template for pins -->
        <div class="mod-interactive-map-pin-info mod-interactive-map-pin-info-hidden">
            <div class="mod-interactive-map-pin-wrapper">
                <button type="button" data-interactive-map-close-tooltip>&times;</button>
                <h3>{#title#}</h3>
                <div class="description">{#description#}</div>
                <a href="{#link#}" class="btn btn-primary btn-sm link"><?php _e('Read more', 'modularity-interactive-map'); ?></a>
            </div>
        </div>

        <!-- Zoom area with pins -->
        <div class="mod-interactive-map-overflower">
            <div class="mod-interactive-map-zoomable">
                @foreach ($layers as $layer)
                <img src="{{ wp_get_attachment_url($layer['id']) }}" class="pin-visible image-layer" {!! isset($layer['category']) && is_array($layer['category']) ? 'data-interactive-map-category-name="' . implode('|', $layer['category']) . '"' : '' !!}>
                @endforeach

                @foreach ($pins as $pin)
                <div class="mod-interactive-map-pin pin-visible"
                    data-title="{{ isset($pin['title']) ? $pin['title'] : '' }}"
                    data-description="{{ isset($pin['text']) ? preg_replace('/\s+/', ' ',trim($pin['text'])) : '' }}"
                    data-link="{{ isset($pin['link']) ? $pin['link'] : '' }}"
                    data-interactive-map-category-name="{{ isset($pin['category']) && isset($categories[$pin['category']]) ? $categories[$pin['category']]['name'] : '' }}"
                    style="top: {{ $pin['top'] }};left: {{ $pin['left'] }};color: {{ isset($pin['category']) && isset($categories[$pin['category']]['color']) ? $categories[$pin['category']]['color'] : '' }};">

                    @if(isset($categories[$pin['category']]['svg']))
                        {!! $categories[$pin['category']]['svg'] !!}
                    @else
                        <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" style="fill:#fff";><circle cx="100" cy="100" r="100"/></svg>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
            @button([
                'type' => 'filled',
                'color' => 'default',
                'attributeList' => ['aria-label' => __("Reset", "modularity-interactive-map")],
                'classList' => ['mod-interactive-map-reset-button', 'u-display--none'],
                'icon' => 'refresh'
            ])
            @endbutton
        @group([
            'classList' => ['mod-iteractive-map-buttons'],
        ])
            @button([
                'type' => 'filled',
                'color' => 'default',
                'attributeList' => ['aria-label' => __("Zoom in", "modularity-interactive-map")],
                'classList' => ['zoom-in'],
                'icon' => 'add'
            ])
            @endbutton
            @button([
                'type' => 'filled',
                'color' => 'default',
                'attributeList' => ['aria-label' => __("Zoom out", "modularity-interactive-map")],
                'classList' => ['zoom-out'],
                'icon' => 'remove'
            ])
            @endbutton
        @endgroup
    </div>
</div>

