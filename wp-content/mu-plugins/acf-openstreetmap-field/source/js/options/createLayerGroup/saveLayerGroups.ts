import { SavedLayerGroup } from "../../types";
import { SaveOptionDataInterface } from "../optionFeature";
import LayerGroupData from "./layerGroupData";

class SaveLayerGroups implements SaveOptionDataInterface {
    constructor() {}

    public save(): SavedLayerGroup {
        let data = [];
        for (let layerGroup of Object.values(LayerGroupData.getLayerGroups())) {
            data.push({
                title: layerGroup.getTitle(),
                color: layerGroup.getColor(),
                layerGroup: layerGroup.getLayerGroup(),
                id: layerGroup.getId(),
                icon: layerGroup.getIcon(),
                preselected: layerGroup.getPreselected()
            });
        }

        return data;
    }
}

export default SaveLayerGroups;