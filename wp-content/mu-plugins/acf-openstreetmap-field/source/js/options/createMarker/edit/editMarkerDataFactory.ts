import { MarkerDataInterface } from "../markerDataInterface";
import EditMarkerData from "./editMarkerData";
import { EditMarkerDataInterface } from "./editMarkerDataInterface";

class EditMarkerDataFactory {
    constructor(
        private fieldValidatorInstance: FieldValidatorInterface,
        private editInstance: EditInterface,
        private overlayInstance: OverlayInterface,
        private titleInstance: Field,
        private urlInstance: Field,
        private descriptionInstance: Field,
        private layerInstance: Field,
        private imageInstance: Field
    ) {}
    public create(markerData: MarkerDataInterface): EditMarkerDataInterface {
        return new EditMarkerData(
            markerData,
            this.fieldValidatorInstance,
            this.editInstance,
            this.overlayInstance,
            this.titleInstance,
            this.urlInstance,
            this.descriptionInstance,
            this.layerInstance,
            this.imageInstance
        );
    }
}

export default EditMarkerDataFactory;