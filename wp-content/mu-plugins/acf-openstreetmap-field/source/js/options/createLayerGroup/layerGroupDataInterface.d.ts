import { LayerGroupInterface } from "@helsingborg-stad/openstreetmap";
import { MarkerDataInterface } from "../createMarker/markerDataInterface";

type LayerGroupsDataStorage = { [id: string]: LayerGroupDataInterface };

interface LayerGroupDataInterface {
    createLayerGroup(): LayerGroupInterface;
    deleteLayerGroup(): void;
    editLayerGroup(): void;
    updateLayerGroup(): void;
    setTitle(title: string): void;
    getTitle(): string;
    setColor(color: string): void;
    getColor(): string;
    setLayerGroup(layerGroup: string): void;
    getLayerGroup(): string;
    setIcon(icon: string): void;
    getLayer(): LayerGroupInterface|null;
    showLayerGroup(): void;
    hideLayerGroup(): void;
    // static setActiveLayerGroup(layerGroup: LayerGroupDataInterface|null): void;
    // static getActiveLayerGroup(): LayerGroupDataInterface|null;
    // static getLayerGroups(): LayerGroupsDataStorage;
    getIcon(): string;
    getId(): string;
    getPreselected(): boolean;
    setPreselected(preselected: boolean): void;
}