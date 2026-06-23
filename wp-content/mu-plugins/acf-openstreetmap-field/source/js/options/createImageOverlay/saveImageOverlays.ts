import { SavedImageOverlayData } from "../../types";
import { SaveOptionDataInterface } from "../optionFeature";
import ImageOverlayData from "./imageOverlayData";

class SaveImageOverlays implements SaveOptionDataInterface {
    constructor() {}

    public save(): SavedImageOverlayData {
        let data = [];
        for (let imageOverlay of Object.values(ImageOverlayData.getImageOverlays())) {
            if (!imageOverlay.getImageOverlay() || !imageOverlay.getImageAspectRatio()) {
                continue;
            }

            data.push({
                title: imageOverlay.getTitle(),
                image: imageOverlay.getImage(),
                layerGroup: imageOverlay.getLayerGroup(),
                position: imageOverlay.getImageOverlay()!.getPosition(),
                aspectRatio: imageOverlay.getImageAspectRatio()!
            });
        }

        return data;
    }
}

export default SaveImageOverlays;