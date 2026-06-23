import { LatLngBoundsObject, LatLngObject } from "@helsingborg-stad/openstreetmap";

interface ImageOverlayBoundsAndRatioCalculatorInterface {
    calculateBounds(imageUrl: string, center: LatLngObject): Promise<[LatLngBoundsObject, number]>;
}