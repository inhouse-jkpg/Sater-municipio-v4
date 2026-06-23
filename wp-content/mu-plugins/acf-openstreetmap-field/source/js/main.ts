import { CreateMarker, CreateLayerGroup, CreateImageOverlay, CreateMap, CreateTileLayer, TilesHelper, CreateAttribution, MapInterface } from '@helsingborg-stad/openstreetmap';
import OptionCreateMarker from './options/createMarker/optionCreateMarker';
import OptionSetStartPosition from './options/startPosition/optionSetStartPosition';
import MarkerFactory from './options/createMarker/markerFactory';
import SaveHiddenField from './save';
import SaveMarkers from './options/createMarker/saveMarkers';
import SaveStartPostion from './options/startPosition/saveStartPosition';
import LoadMarkers from './options/createMarker/loadMarkers';
import LoadStartPosition from './options/startPosition/loadStartPosition';
import FieldValidator from './edit/fields/fieldValidator';
import LoadHiddenField from './load';
import Title from './edit/fields/title';
import Url from './edit/fields/url';
import Overlay from './edit/overlay';
import Description from './edit/fields/description';
import MarkersList from './options/createMarker/markersList';
import OptionCreateLayerGroup from './options/createLayerGroup/optionCreateLayerGroup';
import EditMarkerDataFactory from './options/createMarker/edit/editMarkerDataFactory';
import Edit from './edit/actions/edit';
import LayerGroupFactory from './options/createLayerGroup/layerGroupFactory';
import EditLayerGroupDataFactory from './options/createLayerGroup/edit/editLayerGroupDataFactory';
import LayerGroupsList from './options/createLayerGroup/layerGroupsList';
import LoadLayerGroups from './options/createLayerGroup/loadLayerGroups';
import SaveLayerGroups from './options/createLayerGroup/saveLayerGroups';
import Color from './edit/fields/color';
import Layer from './edit/fields/layer';
import Icon from './edit/fields/icon';
import OptionCreateImageOverlay from './options/createImageOverlay/optionCreateImageOverlay';
import ImageOverlayFactory from './options/createImageOverlay/imageOverlayFactory';
import EditImageOverlayFactory from './options/createImageOverlay/edit/editImageOverlayDataFactory';
import Image from './edit/fields/image';
import ImageOverlaysList from './options/createImageOverlay/imageOverlaysList';
import ImageOverlayResize from './options/createImageOverlay/imageFunctionality/imageOverlayResize';
import ImageOverlayBoundsAndRatioCalculator from './options/createImageOverlay/helper/imageOverlayBoundsAndRatioCalculator';
import ImageOverlayMove from './options/createImageOverlay/imageFunctionality/imageOverlayMove';
import LoadImageOverlays from './options/createImageOverlay/loadImageOverlays';
import SaveImageOverlays from './options/createImageOverlay/saveImageOverlays';
import MapStyle from './options/settings/mapStyle';
import LayerFilter from './options/settings/layerFilter';
import IconFactoryResolver from './icons/iconFactoryResolver';
import ListItemHelper from './helper/createListItem';
import Preselected from './edit/fields/preselected';
import LayerFilterTitle from './options/settings/layerFilterTitle';
import LayerFilterDefaultOpen from './options/settings/layerFilterDefaultOpen';
import { BlockSettings } from './types';


declare const acf: any;
declare const language: any;

class Main {
    private mapInstance!: MapInterface;

    constructor(
        id: string,
        container: HTMLElement,
        map: HTMLElement,
        blockSettings: BlockSettings|null
    ) {
        const hiddenField = container.querySelector('[data-js-hidden-field]') as HTMLInputElement;

        if (!acf) {
            console.error('ACF not found');
            return;
        }

        if (!hiddenField) {
            console.error('Hidden field not found');
            return;
        }

        this.mapInstance = new CreateMap({
            id: id
        }).create();

        // Icons
        const iconFactoryInstance = new IconFactoryResolver().resolve();

        // Helpers
        const tilesHelperInstance = new TilesHelper();
        const listItemHelper      = new ListItemHelper(language ?? {});

        // MapTiles
        const tileLayerInstance    = new CreateTileLayer().create();
        const attributionInstance  = new CreateAttribution().create();

        // Settings
        const mapStyleInstance         = new MapStyle(tileLayerInstance, attributionInstance, tilesHelperInstance, container);
        const layerFilterTitleInstance = new LayerFilterTitle(container);
        const layerFilterDefaultOpen   = new LayerFilterDefaultOpen(container);
        const layerFilterInstance      = new LayerFilter(container, layerFilterTitleInstance, layerFilterDefaultOpen);

        // General
        const createMarkerInstance       = new CreateMarker();
        const createLayerGroupInstance   = new CreateLayerGroup();
        const createImageOverlayInstance = new CreateImageOverlay();

        // Fields and Edit
        const fieldValidatorInstance = new FieldValidator(language ?? {});
        const overlayInstance        = new Overlay(container);
        const editInstance           = new Edit(container);
        const titleInstance          = new Title(overlayInstance);
        const urlInstance            = new Url(overlayInstance);
        const descriptionInstance    = new Description(overlayInstance);
        const colorInstance          = new Color(overlayInstance);
        const layerInstance          = new Layer(overlayInstance);
        const iconInstance           = new Icon(overlayInstance);
        const imageInstance          = new Image(overlayInstance);
        const preselectedInstance    = new Preselected(overlayInstance);

        // Create layer group
        const editLayerGroupDataFactory = new EditLayerGroupDataFactory(
            editInstance,
            overlayInstance,
            titleInstance,
            colorInstance,
            iconInstance,
            layerInstance,
            preselectedInstance,
            language ?? {}
        );

        const layerGroupsList = new LayerGroupsList(container, listItemHelper, language);

        const layerGroupFactoryInstance = new LayerGroupFactory(
            this.mapInstance,
            createLayerGroupInstance,
            editLayerGroupDataFactory,
            layerGroupsList,
            iconFactoryInstance
        );

        // Create marker
        const editMarkerDataFactoryInstance = new EditMarkerDataFactory(
            fieldValidatorInstance,
            editInstance,
            overlayInstance,
            titleInstance,
            urlInstance,
            descriptionInstance,
            layerInstance,
            imageInstance
        );

        const markerFactoryInstance  = new MarkerFactory(
            this.mapInstance,
            createMarkerInstance,
            editMarkerDataFactoryInstance,
            new MarkersList(container, this.mapInstance, listItemHelper, language),
            iconFactoryInstance
        );

        // Create image overlay
        const editImageOverlayFactoryInstance = new EditImageOverlayFactory(
            editInstance,
            overlayInstance,
            titleInstance,
            layerInstance,
            imageInstance
        );

        const imageOverlayResize = new ImageOverlayResize(this.mapInstance, createMarkerInstance, iconFactoryInstance);
        const imageOverlayMove = new ImageOverlayMove(this.mapInstance, createMarkerInstance, iconFactoryInstance);

        const imageOverlayFactoryInstance = new ImageOverlayFactory(
            this.mapInstance,
            createImageOverlayInstance,
            editImageOverlayFactoryInstance,
            new ImageOverlaysList(container, this.mapInstance, listItemHelper, language),
            new ImageOverlayBoundsAndRatioCalculator(this.mapInstance),
            imageOverlayResize,
            imageOverlayMove
        );

        // Main
        const OptionCreateLayerGroupInstance = new OptionCreateLayerGroup(
            container,
            layerGroupsList,
            layerGroupFactoryInstance
        );

        const OptionCreateImageOverlayInstance = new OptionCreateImageOverlay(
            container,
            imageOverlayFactoryInstance
        );

        const OptionSetStartPositionInstance = new OptionSetStartPosition(
            this.mapInstance,
            container
        );

        const OptionCreateMarkerInstance = new OptionCreateMarker(
            this.mapInstance,
            markerFactoryInstance,
            imageOverlayResize,
            imageOverlayMove
        );

        // Save and Load
        new LoadHiddenField(
            hiddenField,
            new LoadLayerGroups(layerGroupFactoryInstance),
            new LoadMarkers(markerFactoryInstance),
            new LoadImageOverlays(imageOverlayFactoryInstance),
            new LoadStartPosition(OptionSetStartPositionInstance),
            mapStyleInstance,
            layerFilterInstance,
            layerFilterTitleInstance,
            layerFilterDefaultOpen,
            blockSettings
        );

        new SaveHiddenField(
            hiddenField,
            new SaveLayerGroups(),
            new SaveMarkers(),
            new SaveImageOverlays(),
            new SaveStartPostion(OptionSetStartPositionInstance),
            mapStyleInstance,
            layerFilterInstance,
            layerFilterTitleInstance,
            layerFilterDefaultOpen,
            blockSettings
        );

        // After data loaded
        const currentTiles = tilesHelperInstance.getDefaultTiles(mapStyleInstance.getValue());
        tileLayerInstance.setUrl(currentTiles.url).addTo(this.mapInstance);
        attributionInstance.setPrefix(currentTiles.attribution).addTo(this.mapInstance);

        const startPosition = OptionSetStartPositionInstance.getStartPosition();
        this.mapInstance.setView(startPosition.latlng ?? { lat: 59.32932, lng: 18.06858 }, startPosition.zoom ?? 16);

        if (blockSettings) {
            this.invalidateSize();
        }
    }

    public invalidateSize() {
        setTimeout(() => {
            this.mapInstance.invalidateSize();
        }, 300);
    }
}

export default Main;