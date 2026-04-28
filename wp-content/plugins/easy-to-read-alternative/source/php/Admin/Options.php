<?php

namespace EasyReading\Admin;

class Options
{
    public function __construct()
    {
        if (function_exists('acf_add_options_sub_page')) {
            acf_add_options_sub_page(array(
                'page_title'    => _x('Easy reading settings', 'ACF', 'easy-reading'),
                'menu_title'    => _x('Easy reading', 'Easy reading settings', 'easy-reading'),
                'menu_slug'     => 'easy-reading-options',
                'parent_slug'   => 'options-general.php',
                'capability'    => 'manage_options'
            ));

        // Create custom ACF location rule. Adds 'easy reading' field to selected post types
        add_filter('acf/location/rule_types', array($this, 'acfLocationRulesTypes'));
        add_filter('acf/location/rule_values/settings', array($this, 'acfLocationRuleValues'));
        add_filter('acf/location/rule_match/settings', array($this, 'acfLocationRulesMatch'), 10, 3);

        }
    }

    /**
     * Add new location rule type 'Easy reading'
     * @param  array $choices Location rule types
     * @return array
     */
    public function acfLocationRulesTypes($choices)
    {
        $choices['Easy reading']['settings'] = 'Post types';
        return $choices;
    }

    /**
     * Location rule type choices
     * @param  array $choices Location rule choices
     * @return array
     */
    public function acfLocationRuleValues($choices)
    {
        return $choices['post_types'] = "Selected";
    }

    /**
     * Matching custom location rule
     * @param  boolean $match   If rule match or not
     * @param  array   $rule    Current rule that to match against
     * @param  array   $options Data about the current edit screen
     * @return boolean
     */
    public function acfLocationRulesMatch($match, $rule, $options)
    {
        $post_types = get_field('easy_reading_posttypes', 'option');

        if ($post_types) {
            if ($rule['operator'] == "==") {
                $match = (isset($options['post_type']) && in_array($options['post_type'], $post_types) && $options['post_id'] > 0);
            } elseif ($rule['operator'] == "!=") {
                $match = (isset($options['post_type']) && !in_array($options['post_type'], $post_types) && $options['post_id'] > 0);
            }
        }

        return $match;
    }
}
