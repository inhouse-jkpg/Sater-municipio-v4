import DashIconFactory from "./dashIconFactory";
import MaterialSymbolFactory from "./materialSymbolFactory";
import NullIconFactory from "./nullIconFactory";

class IconFactoryResolver implements IconFactoryResolverInterface {
    public resolve(): IconFactoryInterface {
        if (this.materialSymbolsAvailable()) {
            return new MaterialSymbolFactory();
        }

        if (this.dashiconsAvailable()) {
            return new DashIconFactory();
        }

        return new NullIconFactory();
    }

    private dashiconsAvailable(): boolean {
        return document.fonts.check('16px dashicons');
    }

    private materialSymbolsAvailable(): boolean {
        return document.fonts.check('16px Material Symbols Outlined') ||
            document.fonts.check('16px Material Symbols Rounded') ||
            document.fonts.check('16px Material Symbols Sharp');
    }
}

export default IconFactoryResolver;