import { SavedLayerGroup } from "../../types";
import { LoadOptionDataInterface } from "../optionFeature";
import { LayerGroupFactoryInterface } from "./layerGroupFactoryInterface";

class LoadLayerGroups implements LoadOptionDataInterface {
    constructor(
        private layerGroupFactory: LayerGroupFactoryInterface
    ) {}

    public load(savedLayerGroups: SavedLayerGroup): void {
        if (!savedLayerGroups) {
            return;
        }

        for (let savedLayerGroup of savedLayerGroups) {
            const layerGroupData = this.layerGroupFactory.create(savedLayerGroup.id);
            layerGroupData.setTitle(savedLayerGroup.title ?? '');
            layerGroupData.setColor(savedLayerGroup.color ?? '');
            layerGroupData.setLayerGroup(savedLayerGroup.layerGroup ?? '');
            layerGroupData.setIcon(savedLayerGroup.icon ?? '');
            layerGroupData.setPreselected(savedLayerGroup.preselected ?? false);
            layerGroupData.createLayerGroup();
        }
    }
}

export default LoadLayerGroups;