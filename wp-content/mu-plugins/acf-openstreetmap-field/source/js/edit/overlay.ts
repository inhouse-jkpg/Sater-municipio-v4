class Overlay implements OverlayInterface {
    private overlay: HTMLElement|null;
    constructor(private container: HTMLElement) {
        this.overlay = this.container.querySelector('[ data-js-field-edit-overlay]');
    }

    public getOverlay(): HTMLElement|null {
        return this.overlay;
    }

    public showOverlay(): void {
        if (!this.overlay) {
            return;
        }

        this.overlay.classList.add('is-open');
    }

    public hideOverlay(): void {
        if (!this.overlay) {
            return;
        }

        this.overlay.classList.remove('is-open');
    }
}

export default Overlay;