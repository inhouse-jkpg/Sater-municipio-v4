import { MarkerDataInterface } from "../markerDataInterface";
import { EditMarkerDataInterface } from "./editMarkerDataInterface";
import { hideSidebar, showSidebar } from "../../../helper/gutenbergSidebar";

class EditMarkerData implements EditMarkerDataInterface, Editable {
    constructor(
        private markerData: MarkerDataInterface,
        private fieldValidatorInstance: FieldValidatorInterface,
        private editInstance: EditInterface,
        private overlayInstance: OverlayInterface,
        private titleInstance: Field,
        private urlInstance: Field,
        private descriptionInstance: Field,
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
        this.titleInstance.setValue(this.markerData.getTitle());
        this.urlInstance.setValue(this.markerData.getUrl());
        this.descriptionInstance.setValue(this.markerData.getDescription());
        this.layerInstance.setValue(this.markerData.getLayerGroup());
        this.imageInstance.setValue(this.markerData.getImage());
    }

    public save() {
        if (!this.fieldValidatorInstance.validateUrl((this.urlInstance.getValue() ?? '') as string)) {
            return;
        }

        this.markerData.setTitle(this.titleInstance.getValue() as string);
        this.markerData.setUrl(this.urlInstance.getValue() as string);
        this.markerData.setDescription(this.descriptionInstance.getValue() as string);
        this.markerData.setLayerGroup(this.layerInstance.getValue() as string);
        this.markerData.setImage(this.imageInstance.getValue() as string);
        this.markerData.updateMarker();
        this.editInstance.setActiveEditable(null);
        this.hideFields();
    }

    public cancel() {
        this.editInstance.setActiveEditable(null);
        this.hideFields();
    }

    public delete() {
        this.markerData.deleteMarker();
        this.editInstance.setActiveEditable(null);
        this.hideFields();
    }

    public hideFields() {
        this.layerInstance.hideField();
        this.titleInstance.hideField();
        this.urlInstance.hideField();
        this.descriptionInstance.hideField();
        this.imageInstance.hideField();
        this.overlayInstance.hideOverlay();
        showSidebar();
    }

    public showFields() {
        this.layerInstance.showField();
        this.titleInstance.showField();
        this.urlInstance.showField();
        this.descriptionInstance.showField();
        this.imageInstance.showField();
        this.overlayInstance.showOverlay();
        hideSidebar();
    }
}

export default EditMarkerData;