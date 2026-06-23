<?php

/*
Plugin Name:    WPMU Focus Point
Description:    Sets focus point in media library.
Version:        1.0.0
Author:         Niclas Norin
*/

namespace WPMUFocusPoint;


if (! defined('WPINC')) {
    die;
}


define('WPMUFOCUSPOINT_PATH', plugin_dir_path(__FILE__));
define('WPMUFOCUSPOINT_URL', plugins_url('', __FILE__));

require_once WPMUFOCUSPOINT_PATH . 'CacheBust.php';

class WPMUFocusPoint
{
    private $cacheBust;
    private string $metaKey = '_focus_point';
    public function __construct()
    {
        $this->cacheBust = new CacheBust();
        add_filter('attachment_fields_to_edit', array($this, 'addFocusPointFields'), 10, 2);
        add_filter('attachment_fields_to_save', array($this, 'saveFocusPointFields'), 10, 2);
        add_action('admin_enqueue_scripts', array($this, 'enqueueAdminScripts'));
        add_filter('attachment_focus_point', array($this, 'calculateValue'), 10, 2);
    }

    /**
     * Enqueue admin scripts and styles.
     */
    public function enqueueAdminScripts()
    {
        wp_register_script(
            'js-wpmu-focus-point',
            WPMUFOCUSPOINT_URL . '/dist/' .
            $this->cacheBust->name('js/wpmu-focus-point.js')
        ); 

        wp_register_style(
            'css-wpmu-focus-point',
            WPMUFOCUSPOINT_URL . '/dist/' .
            $this->cacheBust->name('css/wpmu-focus-point.css')
        );

        wp_enqueue_style('css-wpmu-focus-point');
        wp_enqueue_script('js-wpmu-focus-point');
    }

    /**
     * Add focus point fields to the media library attachment edit screen.
     *
     * @param array $fields The existing fields.
     * @param object $post The current post object.
     * @return array The modified fields.
     */
    public function addFocusPointFields($fields, $post) 
    {
        $focusPoint = json_decode(get_post_meta($post->ID, '_focus_point', true), true);

        // Default values if not set
        $focusX = $focusPoint['left'] ?? 50;
        $focusY = $focusPoint['top'] ?? 50;

        $fields['focusX'] = $this->generateFocusPointField('x', $post->ID, $focusX);
        $fields['focusY'] = $this->generateFocusPointField('y', $post->ID, $focusY);

        return $fields;
    }

    /**
     * Generates a focus point hidden input field.
     *
     * @param string $axis Either 'x' or 'y'
     * @param int $postId Attachment ID
     * @param string $value Current focus point value
     * @return array Field configuration array
     */
    function generateFocusPointField(string $axis, int $postId, string $value): array {
        return [
            'input' => 'html',
            'html'  => sprintf(
                '<input type="hidden" 
                        id="focus-point-%s-%d" 
                        class="focus-point-input" 
                        name="attachments[%d][focus%s]" 
                        value="%s" 
                        data-js-focus-axis="%s" 
                        data-attachment-id="%d" />',
                strtolower($axis),
                $postId,
                $postId,
                strtoupper($axis), // For name="attachments[%d][focusX]"
                esc_attr($value),
                strtolower($axis),
                $postId
            )
        ];
    }

    /**
     * Save the focus point fields when the attachment is saved.
     *
     * @param array $post The post data.
     * @param array $attachment The attachment data.
     * @return array The modified post data.
     */
    public function saveFocusPointFields($post, $attachment)
    {
        if (isset($attachment['focusX']) && isset($attachment['focusY'])) {
            $focusX = max(0, min(100, (int) $attachment['focusX']));
            $focusY = max(0, min(100, (int) $attachment['focusY']));
            
            $focusPoint = [
                'left' => $focusX,
                'top' => $focusY
            ];

            update_post_meta($post['ID'], $this->metaKey, wp_json_encode($focusPoint));
        }

        return $post;
    }

    /**
     * Calculate the focus point value.
     *
     * @param int $id The attachment ID.
     * @return array The focus point values.
     */
    public function calculateValue(array $currentFocusPoint, int $id)
    {
        $jsonFocusPoint = get_post_meta($id, $this->metaKey, true);
        $decodedFocusPoint = json_decode($jsonFocusPoint, true);

        if (is_array($decodedFocusPoint) && isset($decodedFocusPoint['left'], $decodedFocusPoint['top'])) {
            return $decodedFocusPoint;
        }

        return $currentFocusPoint;
    }
}

new \WPMUFocusPoint\WPMUFocusPoint();
