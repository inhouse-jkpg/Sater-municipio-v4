import { LatLngBoundsObject, ImageOverlayInterface, LayerGroupInterface } from "@helsingborg-stad/openstreetmap";

type ImageOverlaysDataStorage = { [id: string]: ImageOverlayDataInterface };

interface ImageOverlayDataInterface {
    editImageOverlay(): void;
    setTitle(title: string): void;
    getTitle(): string;
    setLayerGroup(layerGroup: string): void;
    getLayerGroup(): string;
    setImage(image: string, bounds?: LatLngBoundsObject): void;
    getImage(): string;
    updateImageOverlay(): void;
    deleteImageOverlay(): void;
    addImageOverlayToMap(layerGroup?: LayerGroupInterface|null): void;
    getId(): string;
    getImageOverlay(): ImageOverlayInterface|null;
    getImageAspectRatio(): number|null;
}