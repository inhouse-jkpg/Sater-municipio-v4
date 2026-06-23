import { MapInterface, LatLngBoundsObject, LatLngObject } from "@helsingborg-stad/openstreetmap";
import { ImageOverlayBoundsAndRatioCalculatorInterface } from "./imageOverlayBoundsAndRatioCalculatorInterface";

class ImageOverlayBoundsAndRatioCalculator implements ImageOverlayBoundsAndRatioCalculatorInterface {
    constructor(
        private mapInstance: MapInterface
    ) {}

    public calculateBounds(imageUrl: string, center: LatLngObject): Promise<[LatLngBoundsObject, number]> {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.src = imageUrl;
    
            img.onload = () => {
                const aspectRatio = img.naturalWidth / img.naturalHeight;
                const width = this.getScaledWidth(this.mapInstance.getZoom());
                const height = width / aspectRatio;
                const lngOffset = (width / 2) / Math.cos(center.lat * (Math.PI / 180));

                const bounds: LatLngBoundsObject = {
                    southWest: {
                        lat: center.lat - height / 2,
                        lng: center.lng - lngOffset
                    },
                    northEast: {
                        lat: center.lat + height / 2,
                        lng: center.lng + lngOffset
                    }
                };

                resolve([bounds, aspectRatio]);
            };
            img.onerror = () => {
                reject(new Error("Failed to load image"))
            };
        });
    }

    private getScaledWidth(zoom: number): number {
        const baseWidth = 0.1;

        return baseWidth / Math.pow(2, zoom - 10);
    }
}

export default ImageOverlayBoundsAndRatioCalculator;