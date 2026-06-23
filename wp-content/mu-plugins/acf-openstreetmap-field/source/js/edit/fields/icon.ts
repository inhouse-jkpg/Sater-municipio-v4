class Icon implements Field {
    iconContainer: HTMLElement|null;
    icon: HTMLInputElement|null;
    constructor(private overlayInstance: OverlayInterface) {
        this.iconContainer = this.overlayInstance.getOverlay()?.querySelector('[data-js-field-edit-icon]') ?? null;
        this.icon = this.getContainer()?.querySelector('input') ?? null;
    }

    public getContainer(): HTMLElement|null {
        return this.iconContainer;
    }

    public getField(): HTMLInputElement|null {
        return this.icon;
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

export default Icon;