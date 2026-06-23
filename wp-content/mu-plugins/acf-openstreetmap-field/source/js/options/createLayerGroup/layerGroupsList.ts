import { LayerGroupDataInterface } from "./layerGroupDataInterface";
import ListItemHelper from "../../helper/createListItem";
import { LayerGroupsListInterface, LayerGroupsStorage } from "./layerGroupsListInterface";
import LayerGroupData from "./layerGroupData";

class LayerGroupsList implements LayerGroupsListInterface {
    layerGroupsList: HTMLElement|null;
    styleElement: HTMLStyleElement|null;
    defaultLayerGroup: HTMLElement|null;
    listedLayerGroups: LayerGroupsStorage = {};
    layerAttribute: string = 'data-js-layer-group';
    activeClass: string = 'button-primary';

    constructor(
        private container: HTMLElement,
        private listItemHelper: ListItemHelper,
        private language: any
    ) {
        this.styleElement = this.container.querySelector('[data-js-style]');
        this.layerGroupsList = this.container.querySelector('[data-js-layer-group-list]');
        this.defaultLayerGroup = this.container.querySelector('[default-layer-group]');

        this.handleDefaultLayerGroup();
    }

    private handleDefaultLayerGroup(): void {
        if (!this.defaultLayerGroup) {
            return;
        }

        this.defaultLayerGroup.addEventListener('click', () => {
            this.removeIsActiveClass();
            this.defaultLayerGroup?.classList.add(this.activeClass);
            if (this.styleElement) {
                this.styleElement.innerHTML = '';
            }

            LayerGroupData.setActiveLayerGroup(null);
        });
    }

    public addItem(layerGroupData: LayerGroupDataInterface): void {
        const listItem = this.listItemHelper.createLayerGroupListItem(this.getLayerGroupTitle(layerGroupData));
        this.layerGroupsList?.appendChild(listItem);
        this.listedLayerGroups[layerGroupData.getId()] = {layerGroup: layerGroupData, listItem: listItem};
        this.setClickListener(listItem, layerGroupData);
    }

    public removeItem(layerGroupData: LayerGroupDataInterface): void {
        this.listedLayerGroups[layerGroupData.getId()]?.listItem.remove();
        delete this.listedLayerGroups[layerGroupData.getId()];
    }

    public updateItem(layerGroupData: LayerGroupDataInterface): void {
        if (!this.listedLayerGroups[layerGroupData.getId()].listItem) {
            return;
        }

        this.removeIsActiveClass();
        this.listedLayerGroups[layerGroupData.getId()].listItem.classList.add(this.activeClass);
        LayerGroupData.setActiveLayerGroup(layerGroupData);
        this.listedLayerGroups[layerGroupData.getId()].listItem.querySelector('[data-js-title]')!.textContent = this.getLayerGroupTitle(layerGroupData);
    }

    public setStyleHtml(layerGroupData: LayerGroupDataInterface): void {
        if (this.styleElement) {
            this.styleElement.innerHTML = `[${this.layerAttribute}]:not([${this.layerAttribute}="${layerGroupData.getId()}"]), [data-js-image-overlay-list]:has([${this.layerAttribute}]:not([${this.layerAttribute}="${layerGroupData.getId()}"])):not(:has(li:not([${this.layerAttribute}]))) { display: none; }
             `;
        }
    }

    private setClickListener(listItem: HTMLLIElement, layerGroupData: LayerGroupDataInterface): void {
        listItem.querySelector('[data-js-edit]')?.addEventListener('click', () => {
            listItem.classList.remove(this.activeClass);
            layerGroupData.editLayerGroup();
        });

        listItem.addEventListener('click', () => {
            this.removeIsActiveClass();
            this.setStyleHtml(layerGroupData);
            listItem.classList.add(this.activeClass);
            LayerGroupData.setActiveLayerGroup(layerGroupData);
        });
    }

    private removeIsActiveClass(): void {
        this.defaultLayerGroup?.classList.remove(this.activeClass);

        for (const key in this.listedLayerGroups) {
            this.listedLayerGroups[key].listItem.classList.remove(this.activeClass);
        }
    }

    private getLayerGroupTitle(layerGroupData: LayerGroupDataInterface): string {
        return layerGroupData.getTitle() ? layerGroupData.getTitle() : this.language?.untitledLayer ?? 'Untitled Layer';
    }
}

export default LayerGroupsList;