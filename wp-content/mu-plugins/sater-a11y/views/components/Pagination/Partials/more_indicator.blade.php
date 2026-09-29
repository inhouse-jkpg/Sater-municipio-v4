@php($pageNumber = $lastItem['key'] + 1)
<{{$listItem}} class="{{$baseClass}}__item u-display--none@xs">
    @icon([
        'icon' => 'more_horiz',
        'size' => $buttonSize,
        'decorative' => true,
    ])
    @endicon
</{{$listItem}}>

<{{$listItem}} class="{{$baseClass}}__item u-display--none@xs">
    @button([
        'style' => $buttonStyle,
        'size' => $buttonSize,
        'color' => 'default',
        'href' => $lastItem['href'],
        'classList' => [
            $baseClass . '__link'
        ],
        'text' => $pageNumber,
        'ariaLabel' => 'Sida ' . $pageNumber,
    ])
    @endbutton
</{{$listItem}}>
