import { SavedImageOverlayData, SavedLayerGroup, SavedMarkerData, SavedStartPosition } from "../types";
import SaveLayerGroups from "./createLayerGroup/saveLayerGroups";

interface SaveOptionDataInterface {
    save(): SavedMarkerData|SavedStartPosition|SavedImageOverlayData|SavedLayerGroup;
}

interface LoadOptionDataInterface {
    load(data: SavedMarkerData|SavedStartPosition|SavedImageOverlayData|SavedLayerGroup): void;
}