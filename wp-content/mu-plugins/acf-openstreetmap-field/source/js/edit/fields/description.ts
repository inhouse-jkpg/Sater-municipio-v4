class Description implements Field {
    descriptionContainer: HTMLElement|null;
    description: HTMLTextAreaElement|null;
    constructor(private overlayInstance: OverlayInterface) {
        this.descriptionContainer = this.overlayInstance.getOverlay()?.querySelector('[data-js-field-edit-description]') ?? null;
        this.description = this.getContainer()?.querySelector('textarea') ?? null;
    }

    public getContainer(): HTMLElement|null {
        return this.descriptionContainer;
    }

    public getField(): HTMLTextAreaElement|null {
        return this.description;
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

export default Description;