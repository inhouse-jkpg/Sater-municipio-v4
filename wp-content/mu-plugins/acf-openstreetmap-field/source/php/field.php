<?php

namespace AcfOpenStreetMap;

class Field extends \acf_field
{
    public $name;
    public $label;
    public $category;
    public $settings;
    private $mapId = null;
    private static $mapIndex = 0;
    private array $lang;

    public function __construct() {
        $this->name = 'openstreetmap';
        $this->label = 'OpenStreetMap';
        $this->category = 'basic';
        $this->settings = array(
            'path' => plugin_dir_path(__FILE__),
            'dir' => plugin_dir_url(__FILE__),
        );

        $this->lang = \AcfOpenStreetMap\Lang::getLang();

        parent::__construct();
    }

    /**
     * Render the field input
     */
    public function render_field($field) {
        $id = $this->getMapId() . '-' . self::$mapIndex++;

        ?>
        <div class="acf-openstreetmap openstreetmap" data-js-openstreetmap-field>
            <input type="hidden" name="<?php echo esc_attr($field['name']); ?>" data-js-hidden-field value="<?php echo esc_attr($field['value']); ?>" id="acf-openstreetmap-hidden-<?php echo $id; ?>"></input>
            <?php $this->addSettings($id) ?>
            <style data-js-style></style>

            <?php $this->addEditOverlay($id) ?>
            <?php $this->addMap($id) ?>
            <?php $this->addOptions() ?>
        </div>
        <?php

        self::$mapIndex++;
    }

    private function getMapId() {
        if ($this->mapId) {
            return $this->mapId;
        }

        $this->mapId = uniqid();
        return $this->mapId;
    }

    private function addMap($id = '')
    {
        ?>
            <div 
                class="acf-openstreetmap__map" 
                data-js-openstreetmap-map 
                id="map-<?php echo $id; ?>"
                style="position: unset; height: 700px; background: #f0f0f0;">
            </div>
        <?php
    }

    private function addSettings($id = '') 
    {
        ?>
        <h2 style="padding: .5rem 0; font-weight: bold;"><?php echo $this->lang['generalSettings'] ?></h2>
            <div class="acf-openstreetmap__settings">
                <div class="acf-openstreetmap__setting" data-js-setting-map-style>
                    <label class="title" for="setting-map-style-<?php echo $id; ?>"><?php echo $this->lang['mapStyle'] ?></label>
                    <select id="setting-map-style-<?php echo $id; ?>" name="map-style">
                        <option value="default"><?php echo $this->lang['default'] ?></option>
                        <option value="dark"><?php echo $this->lang['dark'] ?></option>
                        <option value="pale"><?php echo $this->lang['pale'] ?></option>
                        <option value="color"><?php echo $this->lang['color'] ?></option>
                    </select>
                </div>
                <div class="acf-openstreetmap__setting acf-openstreetmap__option-start-position">
                    <div class="acf-openstreetmap__option-start-position-label"><label for="setting-start-position-<?php echo $id; ?>"><?php echo $this->lang['startPosition'] ?></label><span role="button" data-js-map-start-position><?php echo $this->lang['seeStartPosition'] ?></span></div>
                    <div style="text-align: center" class="button button-primary" acf-openstreetmap-set-start-position role="button" data-js-value="set_start_position" id="setting-start-position-<?php echo $id; ?>"><?php echo $this->lang['setStartPosition'] ?></div>
                </div>
            </div>

            <h2 style="margin-top: 2rem; padding: .5rem 0; font-weight: bold;"><?php echo $this->lang['filterSettings'] ?></h2>
            <div class="acf-openstreetmap__settings">
                <div class="acf-openstreetmap__setting" data-js-setting-layer-filter>
                    <span class="title"><?php echo $this->lang['allowLayerFilter'] ?></span>
                    <label class="switch" for="setting-layer-filter-<?php echo $id; ?>" title="<?php echo $this->lang['allowLayerFilter'] ?>">
                        <input type="checkbox" id="setting-layer-filter-<?php echo $id; ?>">
                        <span class="slider round"></span>
                    </label>
                </div>
                <div class="acf-openstreetmap__setting" data-js-setting-layer-filter-default-open>
                    <span class="title"><?php echo $this->lang['layerFilterOpenAsDefault'] ?></span>
                    <label class="switch" for="setting-layer-filter-default-open-<?php echo $id; ?>" title="<?php echo $this->lang['layerFilterOpenAsDefault'] ?>">
                        <input type="checkbox" id="setting-layer-filter-default-open-<?php echo $id; ?>">
                        <span class="slider round"></span>
                    </label>
                </div>
                <div class="acf-openstreetmap__setting" data-js-setting-layer-filter-title style="display: none;">
                    <label class="title" for="setting-layer-filter-title-<?php echo $id; ?>"><?php echo $this->lang['layerFilterTitle'] ?></label>
                    <input type="text" id=setting-layer-filter-title-<?php echo $id; ?>"></input>
                </div>
            </div>
        <?php
    }

    private function addOptions()
    {
        ?>
            <div class="acf-openstreetmap__options">
                <div class="acf-openstreetmap__option acf-openstreetmap__option-layer-group">
                    <ul class="acf-openstreetmap__option-list acf-openstreetmap__option-list-layer-group" data-js-layer-group-list>
                        <li class="button button-primary" default-layer-group><?php echo $this->lang['defaultLayer'] ?><div class="line-horizontal arrow-right"></div>
                        </li>
                    </ul>
                    <span class="acf-openstreetmap__button-add" acf-openstreetmap-option role="button" data-js-value="create_layer_group"><?php echo $this->lang['addLayer'] ?> [+]</span>
                </div>
                <div class="acf-openstreetmap__markers-and-image-overlay-container">
                    <div class="acf-openstreetmap__option acf-openstreetmap__option-marker">
                        <ul class="acf-openstreetmap__option-list acf-openstreetmap__option-list-marker" data-js-markers-list>
                        </ul>
                        <span>*<?php echo $this->lang['toAddAPin'] ?>: <?php echo $this->lang['clickOnTheMap'] ?></span>
                    </div>
                    <div class="acf-openstreetmap__option acf-openstreetmap__option-image-overlay">
                        <span class="button button-large acf-openstreetmap__option-image-overlay-button" acf-openstreetmap-option role="button" data-js-value="create_image_overlay"><?php echo $this->lang['addImageOverlay'] ?> <span>[+]</span></span>
                        <ul data-js-image-overlay-list class="acf-openstreetmap__option-list acf-openstreetmap__option-list-image-overlay" data-js-image-overlay-list></ul>
                    </div>
                </div>
            </div>
        <?php
    }

    private function addEditOverlay($id = '')
    {
        ?>
            <div class="acf-openstreetmap__field-edit-overlay" data-js-field-edit-overlay>
                <div class="acf-openstreetmap__field" data-js-field-edit-title>
                    <label for="field-text-<?php echo $id; ?>"><?php echo $this->lang['title'] ?></label>
                    <input type="text" id="field-text-<?php echo $id; ?>" name="title"></input>
                </div>
                <div class="acf-openstreetmap__field" data-js-field-edit-url>
                    <label for="field-url-<?php echo $id; ?>"><?php echo $this->lang['url'] ?></label>
                    <input type="url" id="field-url-<?php echo $id; ?>" name="url"></input>
                </div>
                <div class="acf-openstreetmap__field" data-js-field-edit-description>
                    <label for="field-description-<?php echo $id; ?>"><?php echo $this->lang['description'] ?></label>
                    <textarea name="description" id="field-description-<?php echo $id; ?>" cols="30" rows="10"></textarea>
                </div>
                <div class="acf-openstreetmap__field" data-js-field-edit-color>
                    <label for="field-color-<?php echo $id; ?>"><?php echo $this->lang['color'] ?></label>
                    <input type="color" id="field-color-<?php echo $id; ?>" name="color"></input>
                </div>
                <div class="acf-openstreetmap__field" data-js-field-edit-icon>
                    <label for="field-icon-<?php echo $id; ?>"><?php echo $this->lang['icon'] ?></label>
                    <span><?php echo $this->lang['addAnIconNameFromYourLibrary'] ?>. <?php echo $this->lang['ex'] ?> (<a target="_blank" href="https://fonts.google.com/icons">Material Symbols</a>)</span>
                    <input type="text" id="field-icon-<?php echo $id; ?>" name="icon"></input>
                </div>
                <div class="acf-openstreetmap__field" data-js-field-edit-layer>
                    <label for="field-layer-<?php echo $id; ?>"><?php echo $this->lang['layer'] ?></label>
                    <select id="field-layer-<?php echo $id; ?>" name="layer">
                        <option value=""><?php echo $this->lang['default'] ?> (<?php echo $this->lang['onTheMap'] ?>)</option>
                    </select>
                </div>
                <div class="acf-openstreetmap__field acf-openstreetmap__setting" style="flex: unset;"  data-js-field-edit-preselected>
                    <span class="title"><?php echo $this->lang['showAsDefaultWhenFiltering'] ?></span>
                    <label class="switch" for="field-preselected-<?php echo $id; ?>">
                        <input type="checkbox" id="field-preselected-<?php echo $id; ?>" name="preselected">
                        <span class="slider round"></span>
                    </label>
                </div>
                
                <div class="acf-openstreetmap__field" data-js-field-edit-image>
                    <label for="field-icon-<?php echo $id; ?>"><?php echo $this->lang['image'] ?></label>
                    <div style="text-align: center;" class="button button-secondary" data-js-field-edit-image-button role="button" id="field-icon-<?php echo $id; ?>"><?php echo $this->lang['setImage'] ?></div>
                    <div data-js-field-edit-image-preview></div>
                    <input style="display: none;" type="url" name="icon"></input>
                </div>
                <div class="acf-openstreetmap__field-edit-buttons">
                    <div class="acf-openstreetmap__field-edit-buttons-cancel" data-js-field-edit-cancel role="button"><?php echo $this->lang['cancel'] ?> &#10005;</div>
                    <div class="button button-primary button-large" data-js-field-edit-save role="button"><?php echo $this->lang['save'] ?></div>
                    <div class="button button-secondary button-large" data-js-field-edit-delete role="button"><?php echo $this->lang['delete'] ?></div>
                </div>
            </div>
        <?php
    }
    /**
     * Add custom field settings for latitude and longitude
     */
    public function render_field_settings($field) {
        acf_render_field_setting($field, array(
            'label'        => __('Default Latitude', 'acf'),
            'instructions' => __('Set a default latitude', 'acf'),
            'type'         => 'text',
            'name'         => 'default_lat',
        ));
    
        acf_render_field_setting($field, array(
            'label'        => __('Default Longitude', 'acf'),
            'instructions' => __('Set a default longitude', 'acf'),
            'type'         => 'text',
            'name'         => 'default_lng',
        ));
    }
}

new Field();