import { AttributionInterface, MapStyle as Style, TileLayerInterface, TilesHelperInterface } from "@helsingborg-stad/openstreetmap";
import { Setting } from "./setting";

class MapStyle implements Setting {
    container: HTMLElement|null;
    setting: HTMLSelectElement|undefined|null;

    constructor(
        private tileLayerInstance: TileLayerInterface,
        private attributionInstance: AttributionInterface,
        private tilesHelperInstance: TilesHelperInterface,
        container: HTMLElement
    ) {
        this.container = container.querySelector('[data-js-setting-map-style]');
        this.setting = this.container?.querySelector('select');

        this.setListener();
    }

    public getValue(): Style {    
        return this.getSanitizedValue(this.setting?.value);
    }

    private setListener(): void {
        if (!this.setting) {
            return;
        }

        this.setting.addEventListener('input', (e) => {
            const tiles = this.tilesHelperInstance.getDefaultTiles(this.getValue());
            this.tileLayerInstance.setUrl(tiles.url);
            this.attributionInstance.setPrefix(tiles.attribution);
        });
    }

    public save(): string {
        return this.getSanitizedValue(this.setting?.value);
    }

    public load(value: string): void {
        if (!this.setting) {
            return;
        }

        this.setting.value = this.getSanitizedValue(value);
    }

    private getSanitizedValue(value: string|undefined|null): Style {
        const allowedValues: Style[] = ["default", "dark", "pale", "color"];

        if (!value || !allowedValues.includes(value as Style)) {
            return "default";
        }

        return value as Style;
    }
}

export default MapStyle;