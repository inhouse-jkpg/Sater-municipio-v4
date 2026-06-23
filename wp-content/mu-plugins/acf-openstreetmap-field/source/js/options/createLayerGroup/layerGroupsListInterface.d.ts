import { LayerGroupDataInterface } from "./layerGroupDataInterface";

type LayerGroupsStorage = {
    [key: string]: {layerGroup: LayerGroupDataInterface, listItem: HTMLLIElement}
};

interface LayerGroupsListInterface {
    addItem(layerGroupData: LayerGroupDataInterface): void;
    removeItem(layerGroupData: LayerGroupDataInterface): void;
    updateItem(layerGroupData: LayerGroupDataInterface): void;
    setStyleHtml(layerGroupData: LayerGroupDataInterface): void;
}