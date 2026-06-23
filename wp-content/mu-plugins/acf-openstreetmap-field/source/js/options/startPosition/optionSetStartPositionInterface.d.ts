import { MapInterface, CreateMarkerInterface, MarkerInterface, LatLngObject } from "@helsingborg-stad/openstreetmap";

type StartPosition = {
    latlng: LatLngObject;
    zoom: number;
}

interface OptionSetStartPositionInterface {
    setStartPosition(startPosition: StartPosition): void;
    getStartPosition(): StartPosition;
}