import { MarkerInterface, LatLngObject } from "@helsingborg-stad/openstreetmap";
import { LayerGroupsDataStorage } from "../createLayerGroup/layerGroupDataInterface";

type MarkersDataStorage = { [id: string]: MarkerDataInterface };

interface MarkerDataInterface {
    createMarker(latlng: LatLngObject): MarkerInterface;
    editMarker(): void;
    deleteMarker(): void;
    updateMarker(): void;
    setTitle(title: string): void;
    getTitle(): string;
    setDescription(content: string): void;
    getDescription(): string;
    setLayerGroup(layerGroup: string): void;
    getLayerGroup(): string;
    setUrl(url: string): void;
    getUrl(): string;
    setImage(image: string): void;
    getImage(): string;
    getId(): string;
    getMarker(): MarkerInterface|null;
}