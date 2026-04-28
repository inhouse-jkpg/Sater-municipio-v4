<?php

namespace EasyReading\Posts;

class Content
{
    private $contentModified = false;

    public function __construct()
    {
        $theme = wp_get_theme();
        if ('Municipio' == $theme->name || 'Municipio' == $theme->parent_theme) {
            add_filter('Municipio/Accessibility/Items', array($this, 'addAccessibility'), 11);
        }

        add_filter('the_post', array($this, 'replacePostContent'), 9);
        add_filter('the_lead', array($this, 'easyReadingLead'), 10);
        add_filter('the_content', array($this, 'easyReadingContent'), 10);
    }

    /**
     * Add easy to read link to accessibility nav
     * @param  array $items Default items
     * @return array Modified items
     */
    public function addAccessibility($items): array
    {
        global $wp;
        $current_url = home_url(add_query_arg(array(), $wp->request));
        $postId = $this->getPostId();

        //Define as array, if not set
        if (!is_array($items)) {
            $items = [];
        }

        if (is_post_type_archive()) {
            $postType = get_queried_object();
            if (get_class($postType)  === 'WP_Post_Type') {
                $postId = get_option('page_for_' . $postType->name);
            }
        }

        //Return alternative to show
        if (!isset($_GET['readable']) && get_field('easy_reading_select', $postId) == true) {
            $items[] =  array(
                'href' => add_query_arg('readable', '1', $current_url),
                'text' => __('Easy to read', 'easy-reading')
            );
        } elseif (
            isset($_GET['readable']) && $_GET['readable'] == '1' &&
            get_field('easy_reading_select', $postId) == true
        ) {
            $items[] =  array(
                'href' => remove_query_arg('readable', $current_url),
                'text' => __('Default version', 'easy-reading')
            );
        }

        return $items;
    }

    /**
     * Replaces post content with readable alternative if it exists
     * @param object $post The post object
     * @return void
     */
    public function replacePostContent($post)
    {
        if ($this->shouldDisplay()) {
            $post->post_content = get_field('easy_reading_content', $this->getPostId());
        }

        return $post;
    }

    /**
     * Removes the lead if readable content is showing
     *
     * @param  string $lead Default lead
     * @return string       Modified lead
     */
    public function easyReadingLead($lead)
    {
        global $post;

        if ($this->shouldDisplay($post)) {
            return '';
        }

        return $lead;
    }

    /**
     * Switch content to alternate version
     *
     * @param  string $content Default content
     * @return string       Modified content
     */
    public function easyReadingContent($content)
    {
        
        if ($this->shouldDisplay()) {
            $content = get_field('easy_reading_content', $this->getPostId());
            
            // Apply lead styles to more tag content
            if (strpos($content, '<!--more-->') !== false) {
                $content_parts = explode('<!--more-->', $content);
                $content = '<p class="lead">' . sanitize_text_field($content_parts[0]) . '</p>' . $content_parts[1];
            }

            $this->contentModified = true;
        }

        return $content;
    }

    /**
     * Detect if alternate content should be delivered
     *
     * @return bool   If the easy read text should be displayed
     */
    public function shouldDisplay()
    {

        if($this->contentModified == true) {
            return false;
        }

        if (empty($this->getPostId())) {
            return false;
        }

        if (!(isset($_GET['readable']) && $_GET['readable'] == '1')) {
            return false;
        }

        if (get_field('easy_reading_select', $this->getPostId()) == false) {
            return false;
        }

        return true;
    }

    /**
     * Get the "real" post_id with regards for post as post type plugin.
     *
     * @return int The post id.
     */
    public function getPostId()
    {
        if (is_post_type_archive()) {
            $postType = get_queried_object();
            if (get_class($postType)  === 'WP_Post_Type') {
                $postId = get_option('page_for_' . $postType->name);
                if ($postId) {
                    return $postId;
                }
            }
        }
        
        return get_the_ID();
    }
}
