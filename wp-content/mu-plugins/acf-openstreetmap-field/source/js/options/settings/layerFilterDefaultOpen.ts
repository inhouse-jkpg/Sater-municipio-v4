import { Setting } from "./setting";

class LayerFilterDefaultOpen implements Setting {
    container: HTMLElement|null;
    setting: HTMLInputElement|undefined|null;

    constructor(
        container: HTMLElement
    ) {
        this.container = container.querySelector('[data-js-setting-layer-filter-default-open]');
        this.setting = this.container?.querySelector('input');
    }

    public getValue(): "true"|"false" {    
        return this.setting?.checked ? 'true' : 'false';
    }

    public save(): "true"|"false" {
        return this.getValue();
    }

    public load(value: string): void {
        if (!this.setting) {
            return;
        }

        this.setting.checked = value === "true";
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

        this.container.style.display = 'flex';
    }
}

export default LayerFilterDefaultOpen;