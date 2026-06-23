class Preselected implements Field {
    preselectedContainer: HTMLElement|null;
    preselected: HTMLInputElement|null;
    constructor(private overlayInstance: OverlayInterface) {
        this.preselectedContainer = this.overlayInstance.getOverlay()?.querySelector('[data-js-field-edit-preselected]') ?? null;
        this.preselected = this.getContainer()?.querySelector('input') ?? null;
    }

    public getContainer(): HTMLElement|null {
        return this.preselectedContainer;
    }

    public getField(): HTMLInputElement|null {
        return this.preselected;
    }

    public setValue(value: boolean): void {
        if (!this.getField()) {
            return;
        }

        this.getField()!.checked = value;
    }

    public getValue(): boolean {
        if (!this.getField()) {
            return false;
        }

        return this.getField()!.checked;
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

export default Preselected;