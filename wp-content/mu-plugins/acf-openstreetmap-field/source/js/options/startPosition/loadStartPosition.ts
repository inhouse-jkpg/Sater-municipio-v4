import { SavedStartPosition } from "../../types";
import { LoadOptionDataInterface } from "../optionFeature";
import { OptionSetStartPositionInterface } from "./optionSetStartPositionInterface";

class LoadStartPosition implements LoadOptionDataInterface {
    constructor(private optionSetStartPositionInstance: OptionSetStartPositionInterface) {}

    public load(startPosition: SavedStartPosition): void {
        if (startPosition) {
            this.optionSetStartPositionInstance.setStartPosition(startPosition);
        }
    }
}

export default LoadStartPosition;