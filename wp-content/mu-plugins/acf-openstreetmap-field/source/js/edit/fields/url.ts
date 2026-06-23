class Url implements Field {
    private urlContainer: HTMLElement|null;
    private url: HTMLInputElement|null;
    constructor(private overlayInterface: OverlayInterface) {
        this.urlContainer = this.overlayInterface.getOverlay()?.querySelector('[data-js-field-edit-url]') ?? null;
        this.url = this.getContainer()?.querySelector('input') ?? null;
    }

    public getContainer(): HTMLElement|null {
        return this.urlContainer;
    }

    public getField(): HTMLInputElement|null {
        return this.url;
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

export default Url;