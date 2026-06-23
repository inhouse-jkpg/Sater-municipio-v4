class Color implements Field {
    colorContainer: HTMLElement|null;
    color: HTMLInputElement|null;
    constructor(private overlayInstance: OverlayInterface) {
        this.colorContainer = this.overlayInstance.getOverlay()?.querySelector('[data-js-field-edit-color]') ?? null;
        this.color = this.getContainer()?.querySelector('input') ?? null;
    }

    public getContainer(): HTMLElement|null {
        return this.colorContainer;
    }

    public getField(): HTMLInputElement|null {
        return this.color;
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

export default Color;