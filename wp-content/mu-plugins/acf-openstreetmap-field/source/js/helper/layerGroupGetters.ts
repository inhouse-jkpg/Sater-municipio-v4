import LayerGroupData from "../options/createLayerGroup/layerGroupData";

export function getColorFromLayerGroup(id: string) {
    const layerGroups = LayerGroupData.getLayerGroups();

    let color = '#E04A39';
    if (layerGroups[id]) {
        color = layerGroups[id].getColor();
    }

    return color;
}

export function getIconFromLayerGroup(id: string): string|null {
    const layerGroups = LayerGroupData.getLayerGroups();
    let icon = null;
    if (layerGroups[id]) {
        icon = layerGroups[id].getIcon();
    }

    return icon;
}