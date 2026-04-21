<?php

namespace ModularityInteractiveMap\Module;

use ModularityInteractiveMap\Helper\CacheBust;

class InteractiveMap extends \Modularity\Module
{
    public $slug = 'interactive-map';
    public $icon = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0Ij48cGF0aCBkPSJNMjMuOTYxIDguNDI5Yy0uODMxLjk4Mi0xLjYxNCAxLjkxOC0xLjk2MSAzLjc3NXY2LjY4M2wtNCAyLjQ3OXYtOS4xNjFjLS4zNDctMS44NTctMS4xMy0yLjc5My0xLjk2MS0zLjc3NS0uOTA4LTEuMDc1LTIuMDM5LTIuNDExLTIuMDM5LTQuNjI5bC4wMTktLjM0NS0yLjAxOS0xLjQ1Ni01LjU0NSA0LTYuNDU1LTR2MThsNi40NTUgNCA1LjU0NS00IDUuNTQ1IDQgNi40NTUtNHYtMTEuNjE4bC0uMDM5LjA0N3ptLTEyLjk2MSA5LjgyNmwtNCAyLjg4NXYtMTMuMDY3bDQtMi44ODZ2MTMuMDY4em05LTE4LjI1NWMtMi4xIDAtNCAxLjcwMi00IDMuODAxIDAgMy4xMjEgMy4xODggMy40NTEgNCA4LjE5OS44MTItNC43NDggNC01LjA3OCA0LTguMTk5IDAtMi4wOTktMS45LTMuODAxLTQtMy44MDF6bTAgNS41Yy0uODI4IDAtMS41LS42NzEtMS41LTEuNXMuNjcyLTEuNSAxLjUtMS41IDEuNS42NzEgMS41IDEuNS0uNjcyIDEuNS0xLjUgMS41eiIvPjwvc3ZnPg==';
    public $supports = array();
    //public $isLegacy = true;

    //public $templateDir = MODULARITY_INTERACTIVE_MAP_TEMPLATE_PATH;

    public function init()
    {
        $this->nameSingular = __('Interactive Map', 'modularity-interactive-map');
        $this->namePlural = __('Interactive Map', 'modularity-interactive-map');
        $this->description = __('Create interactive image maps', 'modularity-interactive-map');

        add_action('add_meta_boxes', array($this, 'addMetaboxes'));
        add_action('save_post', array($this, 'save'), 11, 2);
    }

    public function data() : array
    {
        $data = array();
        $data['layers'] = $this->getLayers();
        $data['pins'] = $this->getPins();
        $data['categories'] = $this->getCategories();

        return $data;
    }

    /**
     * Blade Template
     * @return string
     */
    public function template() : string
    {
        return 'interactive-map.blade.php';
    }

    public function getCategories()
    {
        $categories = get_post_meta($this->ID, 'interactive_map_categories', true);

        if (!$categories) {
            $categories = array();
        }

        foreach ($categories as $key => $category) {
            if (!empty($category['icon'])) {
                $svg = \Municipio\Helper\Svg::extract($category['icon']);
                $svg = str_replace('<svg', '<svg style="fill:' . $category['color'] . ';"', $svg);
            } else {
                $svg = '<svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg" style="fill:' . $category['color'] . ';"><circle cx="100" cy="100" r="100"/></svg>';
            }

            $categories[$category['name']] = $category;
            $categories[$category['name']]['svg'] = $svg;
            unset($categories[$key]);
        }

        return $categories;
    }

    public function getPins()
    {
        $pins = get_post_meta($this->ID, 'interactive_map_pins', true);

        if (!$pins) {
            $pins = array();
        }

        return $pins;
    }

    public function getLayers()
    {
        $layers = get_post_meta($this->ID, 'interactive_map_layers', true);

        if (!$layers) {
            $layers = array(
                'id' => get_post_meta($this->ID, 'interactive_map_image_id', true),
                'name' => 'base',
                'category' => null
            );
        }

        return $layers;
    }

    /**
     * Enqueue your scripts and/or styles with wp_enqueue_script / wp_enqueue_style
     * @return
     */
    public function script()
    {

        wp_register_script('panzoom', MODULARITY_INTERACTIVE_MAP_URL . '/source/js/vendor/panzoom.js', null, '3.0.0', true);
        wp_register_script('modularity-interative-map', MODULARITY_INTERACTIVE_MAP_URL . '/dist/' . CacheBust::name('js/modularity-interactive-map.js'), null, '3.0.0', false);
        wp_enqueue_script('panzoom');
        wp_enqueue_script('modularity-interative-map');
    }

    public function addMetaboxes()
    {
        // Map metabox
        add_meta_box(
            'map-image',
            __('Map image', 'modularity-interactive-map'),
            function () {
                global $post;

                $current = array(
                    'id' => get_post_meta($post->ID, 'interactive_map_image_id', true),
                    'layers' => get_post_meta($post->ID, 'interactive_map_layers', true),
                    'pins' => get_post_meta($post->ID, 'interactive_map_pins', true)
                );

                include MODULARITY_INTERACTIVE_MAP_TEMPLATE_PATH . '/admin/map-image.php';
            },
            'mod-interactive-map',
            'advanced',
            'high'
        );

        add_meta_box(
            'map-pin-categories',
            __('Pin categories', 'modularity-interactive-map'),
            function () {
                global $post;
                $categories = get_post_meta($post->ID, 'interactive_map_categories', true);
                include MODULARITY_INTERACTIVE_MAP_TEMPLATE_PATH . '/admin/map-categories.php';
            },
            'mod-interactive-map',
            'side',
            'default'
        );
    }

    public function save($postId, $post)
    {
        if ($post->post_type !== 'mod-interactive-map') {
            return;
        }

        // Save layers
        if (isset($_POST['interactive-map-layers']) && is_array($_POST['interactive-map-layers'])) {
            update_post_meta($postId, 'interactive_map_layers', $_POST['interactive-map-layers']);
        }

        // Save map id (deprecated)
        if (isset($_POST['interactive-map-image-id']) && !empty($_POST['interactive-map-image-id'])) {
            update_post_meta($postId, 'interactive_map_image_id', $_POST['interactive-map-image-id']);
        }

        // Save map pins
        if (isset($_POST['interactive-map-pin']) && !empty($_POST['interactive-map-pin'])) {
            update_post_meta($postId, 'interactive_map_pins', $_POST['interactive-map-pin']);
        }

        // Save map categories
        if (isset($_POST['interactive-map-categories']) && !empty($_POST['interactive-map-categories'])) {
            update_post_meta($postId, 'interactive_map_categories', $_POST['interactive-map-categories']);
        }
    }

    /**
     * Available "magic" methods for modules:
     * init()            What to do on initialization
     * data()            Use to send data to view (return array)
     * style()           Enqueue style only when module is used on page
     * script            Enqueue script only when module is used on page
     * adminEnqueue()    Enqueue scripts for the module edit/add page in admin
     * template()        Return the view template (blade) the module should use when displayed
     */

}