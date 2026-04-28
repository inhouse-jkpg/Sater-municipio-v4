<?php

namespace SaterCustomTypes;

class GlobalFilter
{

    public function __construct()
    {
        // array of filters (field key => field name)
        $GLOBALS['query_filters'] = array(
        	'fa'	=> 'filter_alternatives',
        );

        // action
        add_action('pre_get_posts', array($this, 'query_filters_pre_get_posts'));
    }

    public function query_filters_pre_get_posts( $query ) {

        // bail early if is in admin
        if( is_admin() ) return;

        // bail early if not main query
        // - allows custom code / plugins to continue working
        if( !$query->is_main_query() ) return;

        // get meta query
        $meta_query = $query->get('meta_query');

        // loop over filters
        foreach( $GLOBALS['query_filters'] as $key => $name ) {

            // continue if not found in url
            if( empty($_GET[ $key ]) ) {

                continue;

            }

            // get the value for this filter
            $value = explode(',', $_GET[ $key ]);

            // append meta query
            $meta_query[] = array(
                'key'		=> $name,
                'value'		=> $value,
                'compare'	=> 'IN',
            );

        }

        // update meta query
        $query->set('meta_query', $meta_query);

    }
}
