import { SavedImageOverlayData } from "../../types";
import { LoadOptionDataInterface } from "../optionFeature";

class LoadImageOverlays implements LoadOptionDataInterface {
    constructor(
        private imageOverlayFactory: ImageOverlayFactoryInterface
    ) {}

    public load(savedImageOverlays: SavedImageOverlayData): void {
        if (!savedImageOverlays) {
            return;
        }

        for (let savedImageOverlay of savedImageOverlays) {
            const imageOverlayData = this.imageOverlayFactory.create();
            imageOverlayData.setTitle(savedImageOverlay.title ?? '');
            imageOverlayData.setLayerGroup(savedImageOverlay.layerGroup ?? '');
            imageOverlayData.setImageAspectRatio(savedImageOverlay.aspectRatio ?? null);
            imageOverlayData.setImage(savedImageOverlay.image ?? '', savedImageOverlay.position ?? null);
            imageOverlayData.updateImageOverlay();
        }
    }
}

export default LoadImageOverlays;