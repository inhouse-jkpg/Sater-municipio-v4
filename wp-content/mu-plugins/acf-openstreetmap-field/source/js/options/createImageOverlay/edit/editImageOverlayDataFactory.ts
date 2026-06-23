import { ImageOverlayDataInterface } from "../imageOverlayDataInterface";
import EditImageOverlayData from "./editImageOverlayData";

class EditImageOverlayFactory {
    constructor(
        private editInstance: EditInterface,
        private overlayInstance: OverlayInterface,
        private titleInstance: Field,
        private layerInstance: Field,
        private imageInstance: Field
    ) {
    }

    public create(imageOverlayDataInstance: ImageOverlayDataInterface): EditImageOverlayDataInterface {
        return new EditImageOverlayData(
            imageOverlayDataInstance,
            this.editInstance,
            this.overlayInstance,
            this.titleInstance,
            this.layerInstance,
            this.imageInstance
        );
    }
}

export default EditImageOverlayFactory;