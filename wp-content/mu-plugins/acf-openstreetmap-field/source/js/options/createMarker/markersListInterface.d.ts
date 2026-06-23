import { MarkerDataInterface } from "./markerDataInterface";

type MarkersListDataStorage = {
    [key: string]: {marker: MarkerDataInterface, listItem: HTMLLIElement};
}
interface MarkersListInterface {
    addItem(markerData: MarkerDataInterface): void;
    removeItem(markerData: MarkerDataInterface): void;
    updateItem(markerData: MarkerDataInterface): void;
}