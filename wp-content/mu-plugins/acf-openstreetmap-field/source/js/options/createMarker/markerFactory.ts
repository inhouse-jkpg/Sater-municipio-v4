import { MapInterface, CreateMarkerInterface } from "@helsingborg-stad/openstreetmap";
import EditMarkerDataFactory from "./edit/editMarkerDataFactory";
import MarkerData from "./markerData";
import { MarkerDataInterface } from "./markerDataInterface";
import { MarkerFactoryInterface } from "./markerFactoryInterface";
import { MarkersListInterface } from "./markersListInterface";

class MarkerFactory implements MarkerFactoryInterface {
    constructor(
        private mapInstance: MapInterface,
        private createMarkersInstance: CreateMarkerInterface,
        private editMarkerDataFactoryInstance: EditMarkerDataFactory,
        private markersListInstance: MarkersListInterface,
        private iconFactoryInstance: IconFactoryInterface
    ) {}

    public create(): MarkerDataInterface {
        return new MarkerData(
            this.mapInstance,
            this.createMarkersInstance,
            this.editMarkerDataFactoryInstance,
            this.markersListInstance,
            this.iconFactoryInstance
        );
    }
}

export default MarkerFactory;