class Layer implements Field {
    layerContainer: HTMLElement|null;
    layer: HTMLSelectElement|null;
    constructor(private overlayInstance: OverlayInterface) {
        this.layerContainer = this.overlayInstance.getOverlay()?.querySelector('[data-js-field-edit-layer]') ?? null;
        this.layer = this.getContainer()?.querySelector('select') ?? null;
    }

    public getContainer(): HTMLElement|null {
        return this.layerContainer;
    }

    public getField(): HTMLSelectElement|null {
        return this.layer;
    }

    public setValue(value: string): void {
        if (!this.getField()) {
            return;
        }

        this.getField()!.value = value;
    }

    public getValue(): string {
        if (!this.getField()) {
            return '';
        }

        return this.getField()!.value;
    }

    public showField(): void {
        if (!this.getContainer()) {
            return;
        }

        this.getContainer()!.classList.add('is-visible');
    }

    public hideField(): void {
        if (!this.getContainer()) {
            return;
        }

        this.getContainer()!.classList.remove('is-visible');
    }
}

export default Layer;