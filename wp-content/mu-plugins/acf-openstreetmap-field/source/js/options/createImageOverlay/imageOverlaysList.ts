import { MapInterface } from "@helsingborg-stad/openstreetmap";
import ListItemHelper from "../../helper/createListItem";
import { ImageOverlayDataInterface } from "./imageOverlayDataInterface";

class ImageOverlaysList implements ImageOverlaysListInterface {
    imageOverlaysList: HTMLElement|null;
    listedImageOverlays: ImageOverlaysStorage = {};
    layerAttribute: string = 'data-js-layer-group';
    constructor(
        private container: HTMLElement,
        private mapInstance: MapInterface,
        private listItemHelper: ListItemHelper,
        private language: any
    ) {
        this.imageOverlaysList = this.container.querySelector('[data-js-image-overlay-list]');
    }

    public addItem(imageOverlayData: ImageOverlayDataInterface): void {
        const listItem = this.listItemHelper.createImageOverlayListItem(this.getLayerGroupTitle(imageOverlayData));

        if (imageOverlayData.getLayerGroup()) {
            listItem.setAttribute(this.layerAttribute, imageOverlayData.getLayerGroup());
            listItem.style.order = '2';
        }

        this.imageOverlaysList?.appendChild(listItem);
        this.listedImageOverlays[imageOverlayData.getId()] = {imageOverlay: imageOverlayData, listItem: listItem};
        this.setClickListener(listItem, imageOverlayData);
    }

    public removeItem(imageOverlayData: ImageOverlayDataInterface): void {
        this.listedImageOverlays[imageOverlayData.getId()]?.listItem.remove();
        delete this.listedImageOverlays[imageOverlayData.getId()];
    }

    public updateItem(imageOverlayData: ImageOverlayDataInterface): void {
        if (!this.listedImageOverlays[imageOverlayData.getId()].listItem) {
            return;
        }

        this.listedImageOverlays[imageOverlayData.getId()].listItem.querySelector('[data-js-title]')!.textContent = this.getLayerGroupTitle(imageOverlayData);

        if (imageOverlayData.getLayerGroup()) {
            this.listedImageOverlays[imageOverlayData.getId()].listItem.setAttribute(this.layerAttribute, imageOverlayData.getLayerGroup());
            this.listedImageOverlays[imageOverlayData.getId()].listItem.style.order = '2';
        } else {
            this.listedImageOverlays[imageOverlayData.getId()].listItem.removeAttribute(this.layerAttribute);
            this.listedImageOverlays[imageOverlayData.getId()].listItem.style.order = '';
        }
    }

    private setClickListener(listItem: HTMLLIElement, imageOverlayData: ImageOverlayDataInterface): void {        
        listItem.addEventListener('click', () => {
            if (imageOverlayData.getImageOverlay()) {
                this.mapInstance.flyTo(imageOverlayData.getImageOverlay()!.getCenter());
            }

            imageOverlayData.editImageOverlay();
        });
    }

    private getLayerGroupTitle(imageOverlayData: ImageOverlayDataInterface): string {
        return imageOverlayData.getTitle() ? imageOverlayData.getTitle() : this.language?.unnamedImageOverlay ?? 'Unnamed image overlay';
    }
}

export default ImageOverlaysList;