type ImageOverlaysStorage = {
    [key: string]: {imageOverlay: ImageOverlayDataInterface, listItem: HTMLLIElement}
};

interface ImageOverlaysListInterface {
    addItem(imageOverlayData: ImageOverlayDataInterface): void;
    removeItem(imageOverlayData: ImageOverlayDataInterface): void;
    updateItem(imageOverlayData: ImageOverlayDataInterface): void;
}