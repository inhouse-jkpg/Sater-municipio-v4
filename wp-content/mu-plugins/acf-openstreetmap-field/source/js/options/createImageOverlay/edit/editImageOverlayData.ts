import { ImageOverlayDataInterface } from "../imageOverlayDataInterface";
import { hideSidebar, showSidebar } from "../../../helper/gutenbergSidebar";

class EditImageOverlayData implements EditImageOverlayDataInterface, Editable {
    constructor(
        private imageOverlayData: ImageOverlayDataInterface,
        private editInstance: EditInterface,
        private overlayInstance: OverlayInterface,
        private titleInstance: Field,
        private layerInstance: Field,
        private imageInstance: Field
    ) {
    }

    public edit(): void {
        this.setDefaultFieldValues();
        this.editInstance.setActiveEditable(this);
        this.showFields();
    }

    private setDefaultFieldValues(): void {
        this.titleInstance.setValue(this.imageOverlayData.getTitle());
        this.layerInstance.setValue(this.imageOverlayData.getLayerGroup());
        this.imageInstance.setValue(this.imageOverlayData.getImage());
    }

    public save() {
        this.imageOverlayData.setTitle(this.titleInstance.getValue() as string);
        this.imageOverlayData.setLayerGroup(this.layerInstance.getValue() as string);
        this.imageOverlayData.setImage(this.imageInstance.getValue() as string);
        this.imageOverlayData.updateImageOverlay();
        this.editInstance.setActiveEditable(null);
        this.hideFields();
    }

    public cancel() {
        this.editInstance.setActiveEditable(null);
        this.hideFields();
    }

    public delete() {
        this.imageOverlayData.deleteImageOverlay();
        this.editInstance.setActiveEditable(null);
        this.hideFields();
    }

    public showFields() {
        this.layerInstance.showField();
        this.titleInstance.showField();
        this.imageInstance.showField();
        this.overlayInstance.showOverlay();
        hideSidebar();

    }

    public hideFields() {
        this.layerInstance.hideField();
        this.titleInstance.hideField();
        this.imageInstance.hideField();
        this.overlayInstance.hideOverlay();
        showSidebar();
    }
}

export default EditImageOverlayData;