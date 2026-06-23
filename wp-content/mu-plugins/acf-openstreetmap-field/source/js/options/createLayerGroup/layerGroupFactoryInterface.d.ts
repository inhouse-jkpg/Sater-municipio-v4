import { LayerGroupDataInterface } from "./layerGroupDataInterface";

interface LayerGroupFactoryInterface {
    create(id?: string): LayerGroupDataInterface;
}