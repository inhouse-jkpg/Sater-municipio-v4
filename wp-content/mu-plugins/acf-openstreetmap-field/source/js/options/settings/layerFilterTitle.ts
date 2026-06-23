class LayerFilterTitle implements LayerFilterSetting {
    container: HTMLElement|null;
    setting: HTMLInputElement|undefined|null;

    constructor(
        container: HTMLElement
    ) {
        this.container = container.querySelector('[data-js-setting-layer-filter-title]');
        this.setting = this.container?.querySelector('input');
    }

    public getValue(): string{    
        return this.setting?.value ?? "";
    }

    public save(): string {
        return this.getValue();
    }

    public load(value: string): void {
        if (!this.setting) {
            return;
        }

        this.setting.value = value;
    }

    public hide(): void {
        if (!this.container) {
            return;
        }

        this.container.style.display = 'none';
    }

    public show(): void {
        if (!this.container) {
            return;
        }

        this.container.style.display = 'block';
    }
}

export default LayerFilterTitle;