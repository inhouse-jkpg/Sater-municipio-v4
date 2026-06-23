import { LayerGroupFactoryInterface } from "./layerGroupFactoryInterface";
import { LayerGroupsListInterface } from "./layerGroupsListInterface";

class OptionCreateLayerGroup {
    protected condition: string = 'create_layer_group';
    constructor(
        private container: HTMLElement,
        private layerGroupsList: LayerGroupsListInterface,
        private layerGroupFactoryInstance: LayerGroupFactoryInterface,
    ) {
        this.addListener();
    }

    private addListener() {
        this.container.querySelector(`[data-js-value="${this.condition}"]`)?.addEventListener('click', (e) => {
            e.preventDefault();
            
            const layerGroupDataInstance = this.layerGroupFactoryInstance.create();
            layerGroupDataInstance.createLayerGroup();
            layerGroupDataInstance.editLayerGroup();
            this.layerGroupsList.setStyleHtml(layerGroupDataInstance);
            this.layerGroupsList.updateItem(layerGroupDataInstance);
        });
    }
}

export default OptionCreateLayerGroup;