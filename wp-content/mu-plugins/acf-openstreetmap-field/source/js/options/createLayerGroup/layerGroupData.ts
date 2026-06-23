import { MapInterface, LayerGroupInterface, CreateLayerGroupInterface, LayerGroup } from "@helsingborg-stad/openstreetmap";
import EditLayerGroupDataFactory from "./edit/editLayerGroupDataFactory";
import { LayerGroupDataInterface, LayerGroupsDataStorage } from "./layerGroupDataInterface";
import { LayerGroupsListInterface } from "./layerGroupsListInterface";

class LayerGroupData implements LayerGroupDataInterface {
    private static layerGroups: LayerGroupsDataStorage = {};
    private static activeLayerGroup: LayerGroupDataInterface|null = null;
    private title: string = '';
    private color: string = '#000000';
    private icon: string = '';
    private preselected: boolean = false;
    private layerGroup: string = '';
    private editor: EditLayerGroupDataInterface;
    private layer: LayerGroupInterface|null = null;
    private layerIsVisible: boolean = true;

    constructor(
        private id: string,
        private mapInstance: MapInterface,
        private createLayerGroupInstance: CreateLayerGroupInterface,
        private editLayerGroupDataFactoryInstance: EditLayerGroupDataFactory,
        private layerGroupsListInstance: LayerGroupsListInterface,
        private iconFactoryInstance: IconFactoryInterface
    ) {
        this.editor = this.editLayerGroupDataFactoryInstance.create(this);
    }

    public createLayerGroup(): LayerGroupInterface {
        if (this.layer) {
            return this.layer;
        }

        this.layer = this.createLayerGroupInstance.create();
        this.layer.addTo(this.mapInstance);
        LayerGroupData.layerGroups[this.getId()] = this;
        this.layerGroupsListInstance.addItem(this);

        return this.layer;
    }

    public editLayerGroup(): void {
        this.editor.edit();
    }

    public getLayer(): LayerGroupInterface|null {
        return this.layer;
    }

    public deleteLayerGroup(): void {
        if (LayerGroupData.layerGroups[this.id]) {
            delete LayerGroupData.layerGroups[this.id];
        }

        this.layer?.removeLayerGroup();
        this.layerGroupsListInstance.removeItem(this);
    }

    public updateLayerGroup(): void {
        this.layerGroupsListInstance.updateItem(this);
    }

    public setTitle(title: string) {
        this.title = title;
        this.editor.setOptionTitle(title);
    }

    public getTitle() {
        return this.title;
    }

    public setLayerGroup(layerGroup: string) {
        this.layerGroup = layerGroup;
    }

    public getLayerGroup(): string {
        return this.layerGroup;
    }

    public setIcon(icon: string) {
        this.icon = this.iconFactoryInstance.format(icon);
    }

    public getIcon(): string {
        return this.icon;
    }

    public setColor(color: string) {
        this.color = color;
    }

    public getColor() {
        return this.color;
    }

    public getId(): string {
        return this.id;
    }

    public setPreselected(preselected: boolean) {
        this.preselected = preselected;
    }

    public getPreselected(): boolean {
        return this.preselected;
    }

    public hideLayerGroup(): void {

        if (!this.layerIsVisible || !this.getLayer()) {
            return;
        }

        this.getLayer()!.removeLayerGroup();
        this.layerIsVisible = false;
    }

    public showLayerGroup(): void {
        if (this.layerIsVisible || !this.getLayer()) {
            return;
        }

        this.getLayer()!.addTo(this.mapInstance);
        this.layerIsVisible = true;
    }

    public static setActiveLayerGroup(layerGroup: LayerGroupDataInterface|null): void {
        if (layerGroup === LayerGroupData.activeLayerGroup) {
            return;
        }

        LayerGroupData.activeLayerGroup = layerGroup;
        
        if (layerGroup === null) {
            for (let layer of Object.values(LayerGroupData.getLayerGroups())) {
                layer.showLayerGroup();
            }
        } else {
            for (let layer of Object.values(LayerGroupData.getLayerGroups())) {
                if (layer.getId() !== layerGroup.getId()) {
                    layer.hideLayerGroup();
                } else {
                    layer.showLayerGroup();
                }
            }
        }
    }

    public static getActiveLayerGroup(): LayerGroupDataInterface|null {
        return LayerGroupData.activeLayerGroup;
    }

    public static getLayerGroups() {
        return LayerGroupData.layerGroups;
    }
}

export default LayerGroupData;