import { ImageOverlayInterface, MarkerInterface, LatLngObject, LayerGroupInterface } from "@helsingborg-stad/openstreetmap";

interface ImageOverlayResizeInterface {
    createResize(
        imageOverlay: ImageOverlayInterface,
        position: LatLngObject,
        aspectRatio: number,
        layerGroup?: LayerGroupInterface|null
    ): MarkerInterface;
    addMarkerToMap(layerGroup?: LayerGroupInterface|null): void;
    isDragging(): boolean;
}