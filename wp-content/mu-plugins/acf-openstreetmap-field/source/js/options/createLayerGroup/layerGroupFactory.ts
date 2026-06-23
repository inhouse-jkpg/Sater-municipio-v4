import { MapInterface, CreateLayerGroupInterface } from "@helsingborg-stad/openstreetmap";
import EditLayerGroupDataFactory from "./edit/editLayerGroupDataFactory";
import LayerGroupData from "./layerGroupData";
import { LayerGroupsListInterface } from "./layerGroupsListInterface";

class LayerGroupFactory {
    private static idCounter: number = 0;

    constructor(
        private mapInstance: MapInterface,
        private createLayerGroupInstance: CreateLayerGroupInterface,
        private editLayerGroupDataFactoryInstance: EditLayerGroupDataFactory,
        private layerGroupsListInstance: LayerGroupsListInterface,
        private iconFactoryInstance: IconFactoryInterface
    ) {

    }

    public create(id?: string) {
        if (!id || id === '') {
            id = 'layer-group-' + Date.now() + LayerGroupFactory.idCounter++;
        }

        return new LayerGroupData(
            id,
            this.mapInstance,
            this.createLayerGroupInstance,
            this.editLayerGroupDataFactoryInstance,
            this.layerGroupsListInstance,
            this.iconFactoryInstance
        );
    }
}

export default LayerGroupFactory;