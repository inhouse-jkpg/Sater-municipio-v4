<!-- pagination.blade.php -->
@if($list)
<{{$componentElement}} class="{{ $class }}" aria-label="Paginering" {!! $attribute !!}>
    <{{$listElement}} class="{{$baseClass}}__list">

        <{{$listItem}} class="{{$baseClass}}__item--previous {{$baseClass}}__item">
            @button([
            'style' => $buttonStyle,
            'size' => $buttonSize,
            'color' => 'default',
            'icon' => 'chevron_left',
            'ariaLabel' => 'Föregående sida',
            'attributeList' => [
                'disabled' => $previousDisabled,
                'data-js-pagination-prev' => ''
            ],
            'href' => $previous,
            ])
            @endbutton
        </{{$listItem}}>

        @includeWhen($firstItem, 'Pagination.Partials.less_indicator')

        <{{$listItem}} class="{{$baseClass}}__page-wrapper">
            <{{$listElement}} class="{{$baseClass}}__pages" js-table-pagination--links>
                @foreach($list as $key => $item)
                    @php($pageNumber = $key + 1)
                    @php($isCurrentPage = $pageNumber === (int) $current)
                    <{{$listItem}} class="{{$baseClass}}__item" data-js-pagination-index="{{$pageNumber}}">
                        @button([
                            'style' => $buttonStyle,
                            'size' => $buttonSize,
                            'color' => $isCurrentPage ? 'primary' : 'default',
                            'href' => $item['href'],
                            'classList' => [
                                $baseClass . '__link',
                                $isCurrentPage ? $baseClass.'__item' . $currentClass : ''
                            ],
                            'text' => $pageNumber,
                            'ariaLabel' => 'Sida ' . $pageNumber,
                            'attributeList' => array_filter([
                                'aria-current' => $isCurrentPage ? 'page' : null,
                            ]),
                        ])
                        @endbutton
                    </{{$listItem}}>
                @endforeach
            </{{$listElement}}>
        </{{$listItem}}>

        @includeWhen($lastItem, 'Pagination.Partials.more_indicator')

        <{{$listItem}} class="{{$baseClass}}__item--next {{$baseClass}}__item">
            @button([
                'style' => $buttonStyle,
                'size' => $buttonSize,
                'color' => 'default',
                'icon' => 'chevron_right',
                'ariaLabel' => 'Nästa sida',
                'attributeList' => [
                    'disabled' => $nextDisabled,
                    'data-js-pagination-next' => ''
                ],
                'href' => $next
            ])
            @endbutton
        </{{$listItem}}>

    </{{$listElement}}>
</{{$componentElement}}>
@else
<!-- No pagination data -->
@endif
