import { ImageOverlayInterface, MarkerInterface, LatLngObject } from "@helsingborg-stad/openstreetmap";

interface ImageOverlayMoveInterface {
    createMove(
        imageOverlay: ImageOverlayInterface,
        position: LatLngObject,
        resizeHandle: MarkerInterface,
        layerGroup?: LayerGroupInterface|null
    ): MarkerInterface;
    addMarkerToMap(layerGroup?: LayerGroupInterface|null): void;
    isDragging(): boolean;
}