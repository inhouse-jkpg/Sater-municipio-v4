import { ImageOverlayInterface, CreateMarkerInterface, MarkerInterface, MapInterface, LatLngBoundsObject, LatLngObject, LayerGroupInterface } from "@helsingborg-stad/openstreetmap";
import { ImageOverlayMoveInterface } from "./imageOverlayMoveInterface";

class ImageOverlayMove implements ImageOverlayMoveInterface {
    private moveHandle: MarkerInterface|null = null;
    private dragging: boolean = false;
    constructor(        
        private mapInstance: MapInterface,
        private createMarkerInstance: CreateMarkerInterface,
        private iconFactoryInstance: IconFactoryInterface
    ) {}

    public createMove(
        imageOverlay: ImageOverlayInterface,
        position: LatLngObject,
        resizeHandle: MarkerInterface,
        layerGroup: LayerGroupInterface|null = null
    ): MarkerInterface {
        this.moveHandle = this.createMarkerInstance.create({
            draggable: true,
            position: position,
            html: this.getMoveIcon(),
        });

        this.addMarkerToMap(layerGroup);

        let startLatLng: null|LatLngObject = null;
        let startBounds: null|LatLngBoundsObject = null;

        this.moveHandle.addListener("dragstart", (event) => {
            if (!event.latLng) {
                return;
            }
            this.dragging = true;
            startLatLng = event.latLng;
            startBounds = imageOverlay.getPosition();
            imageOverlay.setOpacity(0.5);
        });

        this.moveHandle.addListener("drag", (event) => {
            if (!event.latLng || !startLatLng || !startBounds) {
                return;
            }

            const deltaLat = event.latLng.lat - startLatLng.lat;
            const deltaLng = event.latLng.lng - startLatLng.lng;

            const newBounds = {
                southWest: {
                    lat: startBounds.southWest.lat + deltaLat,
                    lng: startBounds.southWest.lng + deltaLng
                },
                northEast: {
                    lat: startBounds.northEast.lat + deltaLat,
                    lng: startBounds.northEast.lng + deltaLng
                }
            };

            this.moveHandle!.setPosition(newBounds.southWest);
            resizeHandle.setPosition(newBounds.northEast);
            imageOverlay.setPosition(newBounds);
        });

        this.moveHandle.addListener("dragend", (event) => {
            setTimeout(() => {
                this.dragging = false;
            }, 100);
            imageOverlay.setOpacity(1);
        });

        return this.moveHandle;
    }

    public isDragging(): boolean {
        return this.dragging;
    }

    public addMarkerToMap(layerGroup: LayerGroupInterface|null = null): void {
        this.moveHandle?.addTo(layerGroup ?? this.mapInstance);
    }

    private getMoveIcon(): string {
        return this.iconFactoryInstance.create('move', '#2271b1', 16);
    }
}

export default ImageOverlayMove;