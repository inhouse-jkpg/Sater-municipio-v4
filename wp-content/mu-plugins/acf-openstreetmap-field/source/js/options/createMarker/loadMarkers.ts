import { SavedMarkerData } from "../../types";
import { LoadOptionDataInterface } from "../optionFeature";
import { MarkerFactoryInterface } from "./markerFactoryInterface";

class LoadMarkers implements LoadOptionDataInterface {
    constructor(
        private markerFactoryInstance: MarkerFactoryInterface
    ) {}

    public load(savedMarkers: SavedMarkerData): void {
        if (!savedMarkers) {
            return;
        }

        for (let savedMarker of savedMarkers) {
            const markerData = this.markerFactoryInstance.create();
            markerData.setTitle(savedMarker.title ?? '');
            markerData.setUrl(savedMarker.url ?? '');
            markerData.setDescription(savedMarker.description ?? '');
            markerData.setLayerGroup(savedMarker.layerGroup ?? '');
            markerData.createMarker(savedMarker.position);
            markerData.setImage(savedMarker.image ?? '');
        }
    }
}

export default LoadMarkers;