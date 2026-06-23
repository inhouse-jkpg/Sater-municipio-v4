import { SavedMarkerData } from "../../types";
import { SaveOptionDataInterface } from "../optionFeature";
import MarkerData from "./markerData";

class SaveMarkers implements SaveOptionDataInterface {
    constructor() {}

    public save(): SavedMarkerData {
        let data = [];
        for (let marker of Object.values(MarkerData.getMarkers())) {
            if (!marker.getMarker()) {
                continue;
            }

            data.push({
                title: marker.getTitle(),
                url: marker.getUrl(),
                description: marker.getDescription(),
                position: marker.getMarker()!.getPosition(),
                layerGroup: marker.getLayerGroup(),
                image: marker.getImage()
            });
        }

        return data;
    }
}

export default SaveMarkers;