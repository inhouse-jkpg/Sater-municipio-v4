import { LayerGroupDataInterface } from "../layerGroupDataInterface";
import EditLayerGroupData from "./editLayerGroupData";

class EditLayerGroupDataFactory {
    constructor(
        private editInstance: EditInterface,
        private overlayInstance: OverlayInterface,
        private titleInstance: Field,
        private colorInstance: Field,
        private iconInstance: Field,
        private layerInstance: Field,
        private preselectedInstance: Field,
        private language: any
    ) {}

    public create(layerGroupData: LayerGroupDataInterface): EditLayerGroupDataInterface {
        return new EditLayerGroupData(
            layerGroupData,
            this.editInstance,
            this.overlayInstance,
            this.titleInstance,
            this.colorInstance,
            this.iconInstance,
            this.layerInstance,
            this.preselectedInstance,
            this.language
        );
    }
}

export default EditLayerGroupDataFactory;