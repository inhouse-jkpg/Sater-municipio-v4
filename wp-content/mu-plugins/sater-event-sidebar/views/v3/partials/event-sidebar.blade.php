{{-- Evenemang ACF meta cards (start_datum, slut_datum, plats, pris, arrangor) --}}
<div class="o-grid single-event-sidebar-right">
    @if (!empty($eventData['start_datum']) || !empty($eventData['slut_datum']))
        <div class="c-card">
            @if (!empty($eventData['start_datum']))
                <div class="c-card__body">
                    <h2 class="c-typography c-card__heading c-typography__variant--h3">
                        Startdatum
                    </h2>
                    {{ $eventData['start_datum'] }}
                </div>
            @endif
            @if (!empty($eventData['slut_datum']))
                <div class="c-card__body">
                    <h2 class="c-typography c-card__heading c-typography__variant--h3">
                        Slutdatum
                    </h2>
                    {{ $eventData['slut_datum'] }}
                </div>
            @endif
        </div>
    @endif

    @if (!empty($eventData['plats']))
        <div class="c-card">
            <div class="c-card__body">
                <h2 class="c-typography c-card__heading c-typography__variant--h3">
                    Plats
                </h2>
                {{ $eventData['plats'] }}
            </div>
        </div>
    @endif

    @if (!empty($eventData['pris']))
        <div class="c-card">
            <div class="c-card__body">
                <h2 class="c-typography c-card__heading c-typography__variant--h3">
                    Pris
                </h2>
                {{ $eventData['pris'] }}
            </div>
        </div>
    @endif

    @if (!empty($eventData['arrangor']))
        <div class="c-card">
            <div class="c-card__body">
                <h2 class="c-typography c-card__heading c-typography__variant--h3">
                    Arrangör
                </h2>
                {{ $eventData['arrangor'] }}
            </div>
        </div>
    @endif
</div>
