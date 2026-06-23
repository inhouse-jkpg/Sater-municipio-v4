import LayerGroupData from "../createLayerGroup/layerGroupData";

class OptionCreateImageOverlay {
    protected condition: string = 'create_image_overlay';

    constructor(
        private container: HTMLElement,
        private imageOverlayFactoryInstance: ImageOverlayFactoryInterface
    ) {
        this.addListener();
    }

    private addListener(): void {

        this.container.querySelector(`[data-js-value="${this.condition}"]`)?.addEventListener('click', (e) => {
            e.preventDefault();
            const imageOverlayData = this.imageOverlayFactoryInstance.create();

            if (LayerGroupData.getActiveLayerGroup()?.getId()) {
                imageOverlayData.setLayerGroup(LayerGroupData.getActiveLayerGroup()!.getId());
            }

            imageOverlayData.editImageOverlay();
        });
    }
}

export default OptionCreateImageOverlay;