import { LoadOptionDataInterface } from "./options/optionFeature";
import MapStyle from "./options/settings/mapStyle";
import { Setting } from "./options/settings/setting";
import { BlockSettings, SaveData, SavedImageOverlayData, SavedLayerGroup, SavedMarkerData, SavedStartPosition } from "./types";

declare const wp: any;
class LoadHiddenField {
    data: SaveData;

    constructor(
        private hiddenField: HTMLInputElement,
        private loadLayerGroupsInstance: LoadOptionDataInterface,
        private loadMarkersInstance: LoadOptionDataInterface,
        private loadImageOverlaysInstance: LoadOptionDataInterface,
        private loadStartPositionInstance: LoadOptionDataInterface,
        private mapStyleInstance: Setting,
        private layerFilterInstance: Setting,
        private layerFilterTitleInstance: Setting,
        private layerFilterDefaultOpenInstance: Setting,
        private blockSettings: BlockSettings|null
    ) {
        if (this.blockSettings) {
            this.loadDataFromBlock();
        }

        let json = this.hiddenField.value || '{}';

        this.data = JSON.parse(json);
        if (!json) {
            return;
        }
        
        this.loadLayerGroupsInstance.load(this.data.layerGroups as SavedLayerGroup);
        this.loadMarkersInstance.load(this.data.markers as SavedMarkerData);
        this.loadImageOverlaysInstance.load(this.data.imageOverlays as SavedImageOverlayData);
        this.loadStartPositionInstance.load(this.data.startPosition as SavedStartPosition);
        this.mapStyleInstance.load(this.data.mapStyle);
        this.layerFilterTitleInstance.load(this.data.layerFilterTitle);
        this.layerFilterDefaultOpenInstance.load(this.data.layerFilterDefaultOpen);
        this.layerFilterInstance.load(this.data.layerFilter);
    }

    private loadDataFromBlock() {
        const blockAttributes = wp.data.select('core/block-editor').getBlockAttributes(this.blockSettings!.blockId);

        if (!blockAttributes || !blockAttributes.data) {
            this.hiddenField.value = '{}';
            return;
        }

        this.hiddenField.value = blockAttributes.data[this.blockSettings!.fieldName] || '{}';
    }
}

export default LoadHiddenField;