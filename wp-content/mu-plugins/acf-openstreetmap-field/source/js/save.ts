import { MapStyle } from "@helsingborg-stad/openstreetmap";
import { SaveOptionDataInterface } from "./options/optionFeature";
import { Setting } from "./options/settings/setting";
import { BlockSettings, SaveData, SavedImageOverlayData, SavedLayerGroup, SavedMarkerData, SavedStartPosition } from "./types";

declare const acf: any;
declare const wp: any;

class SaveHiddenField {
    data: SaveData = {
        markers: [],
        layerGroups: [],
        imageOverlays: [],
        startPosition: {
            latlng: {
                lat: 56.046467,
                lng: 12.694512
            },
            zoom: 16
        },
        mapStyle: "default",
        layerFilter: "false",
        layerFilterTitle: "",
        layerFilterDefaultOpen: "false"
    };

    constructor(
        private hiddenField: HTMLInputElement,
        private saveLayerGroups: SaveOptionDataInterface,
        private saveMarkers: SaveOptionDataInterface,
        private saveImageOverlays: SaveOptionDataInterface,
        private saveStartPosition: SaveOptionDataInterface,
        private mapStyleInstance: Setting,
        private layerFilterInstance: Setting,
        private layerFilterTitleInstance: Setting,
        private layerFilterDefaultOpenInstance: Setting,
        private blockSettings: BlockSettings|null
    ) {
        if (blockSettings) {
            document.querySelector('.editor-post-publish-button')?.addEventListener('click', () => {
                this.saveDataToBlock();
            });
        } else {
             acf.add_filter('validation_complete', (values: any, form: any) => {
                this.setAndGetData();
                return values;
            });
        }
    }

    private setAndGetData(): string {
        this.data.layerGroups = this.saveLayerGroups.save() as SavedLayerGroup;
        this.data.markers = this.saveMarkers.save() as SavedMarkerData;
        this.data.imageOverlays = this.saveImageOverlays.save() as SavedImageOverlayData;
        this.data.startPosition = this.saveStartPosition.save() as SavedStartPosition;
        this.data.mapStyle = this.mapStyleInstance.save() as MapStyle;
        this.data.layerFilter = this.layerFilterInstance.save() as "true"|"false";
        this.data.layerFilterTitle = this.layerFilterTitleInstance.save() as string;
        this.data.layerFilterDefaultOpen = this.layerFilterDefaultOpenInstance.save() as "true"|"false";
        const json = JSON.stringify(this.data);
        this.hiddenField.value = json;

        return json;
    }

    private saveDataToBlock() {
        const currentAttributes = wp.data.select('core/block-editor').getBlockAttributes(this.blockSettings!.blockId);

        if (!currentAttributes || !currentAttributes.data) {
            console.log('No block attributes found or no data attribute present.');
            return;
        }

        const updatedAttributes = {
            ...currentAttributes,
            data: {
                ...currentAttributes.data,
                [this.blockSettings!.fieldName]: this.setAndGetData() ?? '{}'
            }
        };

        wp.data.dispatch('core/block-editor').updateBlockAttributes(this.blockSettings!.blockId, updatedAttributes);
    }
}

export default SaveHiddenField;