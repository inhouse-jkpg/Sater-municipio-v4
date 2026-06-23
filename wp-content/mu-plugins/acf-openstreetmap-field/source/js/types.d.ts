import { LatLngObject, LatLngBoundsObject } from "@helsingborg-stad/openstreetmap";
import MapStyle from "./options/settings/mapStyle";

type BlockSettings = {
    blockId: string;
    fieldName: string;
}

type SavedLayerGroup = {
    title: string;
    color: string;
    layerGroup: string;
    id: string;
    icon: string;
    preselected: boolean;
}[];

type SavedMarkerData = {
    title: string;
    description: string;
    url: string;
    position: LatLngObject;
    layerGroup: string;
    image: string;
}[];

type SavedImageOverlayData = {
    title: string;
    image: string;
    position: LatLngBoundsObject;
    layerGroup: string;
    aspectRatio: number;
}[];

type SavedStartPosition = {
    latlng: LatLngObject;
    zoom: number;
};

type SaveData = {
    layerGroups: SavedLayerGroup;
    markers: SavedMarkerData;
    imageOverlays: SavedImageOverlayData;
    startPosition: SavedStartPosition;
    mapStyle: string;
    layerFilter: "true"|"false";
    layerFilterDefaultOpen: "true"|"false";
    layerFilterTitle: string;
}
