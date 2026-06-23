declare const wp: any;

class Image implements Field {
    imageContainer: HTMLElement|null;
    image: HTMLInputElement|null;
    button: HTMLButtonElement|null;
    imagePreview: HTMLElement|null;
    constructor(private overlayInstance: OverlayInterface) {
        this.imageContainer = this.overlayInstance.getOverlay()?.querySelector('[data-js-field-edit-image]') ?? null;
        this.button = this.getContainer()?.querySelector('[data-js-field-edit-image-button]') ?? null;
        this.image = this.getContainer()?.querySelector('input') ?? null;
        this.imagePreview = this.getContainer()?.querySelector('[data-js-field-edit-image-preview]') ?? null;
        this.setButtonListener();
    }

    private setButtonListener(): void {
        this.button?.addEventListener('click', () => {
            if (!wp) {
                return;
            }

            const mediaUploader = wp.media({
                title: 'Select Image',
                button: {
                    text: 'Use this image'
                },
                multiple: false
            });

            mediaUploader.on('select', () => {
                const imageUrl = mediaUploader.state().get('selection').first().toJSON().url;
                this.setValue(imageUrl);
            });

            mediaUploader.open();
        });
    }

    private setImagePreview(): void {
        if (!this.imagePreview) {
            return;
        }

        this.imagePreview.innerHTML = `<img src="${this.getValue()}" style="margin-top: 1rem; max-width: 100%; max-height: 100%; border: 1px solid #ccc;">
`;
    }

    public getContainer(): HTMLElement|null {
        return this.imageContainer;
    }

    public getField(): HTMLInputElement|null {
        return this.image;
    }

    public setValue(value: string): void {
        if (!this.getField() || this.getValue() === value) {
            return;
        }

        this.getField()!.value = value;
        this.setImagePreview();
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

export default Image;