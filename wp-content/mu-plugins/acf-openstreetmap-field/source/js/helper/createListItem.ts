class ListItemHelper {
    constructor(private language: any) {}

    public createLayerGroupListItem(title: string): HTMLLIElement {
        const li = document.createElement('li');
        li.classList.add('button', 'button-large');
        li.innerHTML = `<span style="display: flex; justify-content: space-between; align-items: center; width: 100%;"><span data-js-title>${title}</span><span class="acf-openstreetmap__edit-layer-group" data-js-edit>${this.language?.edit ?? 'edit'}</span></span><div class="line-horizontal arrow-right"></div>`;
        return li;
    }

    public createMarkerListItem(title: string, color: string): HTMLLIElement {
        const li = document.createElement('li');
        li.classList.add('button');
        li.style.borderRadius = '20px';
        li.innerHTML = `<span style="display: flex; align-items: center; gap: .25rem;"><span style="border-radius: 50%; width: .25rem; height: .25rem; background-color:${color};"></span><span data-js-title>${title}</span></span>`;

        return li;
    }

    public createImageOverlayListItem(title: string): HTMLLIElement {
        const li = document.createElement('li');
        li.classList.add('button');
        li.style.borderRadius = '20px';
        li.innerHTML = `<span style="border-radius: 50%; width: .25rem; height: .25rem;" data-js-title>${title}</span>`;

        return li;
    }
}

export default ListItemHelper;