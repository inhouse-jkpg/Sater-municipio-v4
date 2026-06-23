import { MapInterface } from "@helsingborg-stad/openstreetmap";
import { OptionSetStartPositionInterface, StartPosition } from "./optionSetStartPositionInterface";

class OptionSetStartPosition implements OptionSetStartPositionInterface {
    private startPosition: StartPosition = {} as StartPosition;
    private startPositionElement: HTMLElement|null;

    constructor(
        private mapInstance: MapInterface,
        private container: HTMLElement,
    ) {
        this.startPositionElement = this.container.querySelector('[data-js-map-start-position]');

        this.startPositionElement?.addEventListener('click', () => {
            this.setStartPositionElementClickListener();
        });

        this.container.querySelector('[acf-openstreetmap-set-start-position]')?.addEventListener('click', () => {
            this.startPosition = {
                latlng: this.mapInstance.getCenter(),
                zoom: this.mapInstance.getZoom(),
            };
        });
    }

    private setStartPositionElementClickListener(): void {
        if (this.startPosition) {
            this.mapInstance.setView(this.startPosition.latlng, this.startPosition.zoom);
        }
    }

    public getStartPosition(): StartPosition {
        return this.startPosition;
    }

    public setStartPosition(startPosition: StartPosition): void {
        if (this.startPositionElement) {
            this.startPositionElement.style.display = 'block';
        }

        this.startPosition = startPosition;
    }
}

export default OptionSetStartPosition;