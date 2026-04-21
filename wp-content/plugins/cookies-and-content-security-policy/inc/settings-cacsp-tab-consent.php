<?php
/*
https://supporthost.com/wp-list-table-tutorial/
*/

if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
// var_dump(get_current_screen());
class Cacsp_Consent_Table extends WP_List_Table {

    // Define table columns
    function get_columns() {
        $columns = array(
            // 'cb'                => '<input type="checkbox" />', // column_cb table header
            'id'                => __('id', 'cookies-and-content-security-policy'),
            'time'              => __('time', 'cookies-and-content-security-policy'),
            'ip'                => __('ip', 'cookies-and-content-security-policy'),
            'accepted_cookies'  => __('accepted_cookies', 'cookies-and-content-security-policy'),
            'expires'           => __('expires', 'cookies-and-content-security-policy'),
            'site'              => __('site', 'cookies-and-content-security-policy'),
        );
        return $columns;
    }

    // Bind table with columns, data and all
    function prepare_items() {
        //data
        if ( isset($_POST['s']) ) {
            $this->table_data = $this->get_table_data($_POST['s']);
        } else {
            $this->table_data = $this->get_table_data();
        }

        $columns = $this->get_columns();
        //$hidden = array();
        $hidden = ( is_array(get_user_meta( get_current_user_id(), 'managetoplevel_page_supporthost_list_tablecolumnshidden', true)) ) ? get_user_meta( get_current_user_id(), 'managetoplevel_page_supporthost_list_tablecolumnshidden', true) : array();
        //$sortable = array();
        $sortable = $this->get_sortable_columns();
        $primary  = 'id';
        $this->_column_headers = array($columns, $hidden, $sortable, $primary);
        
        usort($this->table_data, array(&$this, 'usort_reorder'));

        /* pagination */
        $per_page = $this->get_items_per_page('elements_per_page', 10);
        $current_page = $this->get_pagenum();
        $total_items = count($this->table_data);

        $this->table_data = array_slice($this->table_data, (($current_page - 1) * $per_page), $per_page);

        $this->set_pagination_args(array(
            'total_items' => $total_items, // total number of items
            'per_page'    => $per_page, // items to show on a page
            'total_pages' => ceil( $total_items / $per_page ) // use ceil to round up
        ));
        
        $this->items = $this->table_data;
    }

    // Get table data
    private function get_table_data( $search = '' ) {
        global $wpdb;

        $table = $wpdb->prefix . 'cacsp_consent';

        if ( !empty($search) ) {
            return $wpdb->get_results(
                "SELECT * from {$table} WHERE ip Like '%{$search}%' OR accepted_cookies Like '%{$search}%' OR expires Like '%{$search}%' OR time Like '%{$search}%'",
                ARRAY_A
            );
        } else {
            return $wpdb->get_results(
                "SELECT * from {$table}",
                ARRAY_A
            );
        }
    }

    // define $table_data property
    private $table_data;

    function column_default($item, $column_name) {
          switch ($column_name) {
                case 'id':
                case 'time':
                case 'ip':
                case 'accepted_cookies':
                case 'expires':
                case 'site':
                default:
                    return $item[$column_name];
          }
    }

    /*
    function column_cb($item) {
        return sprintf(
                '<input type="checkbox" name="element[]" value="%s" />',
                $item['id']
        );
    }
    */

    protected function get_sortable_columns() {
        $sortable_columns = array(
            'id'          => array('id', true),
            'time'         => array('time', true),
            'ip'   => array('ip', true),
            'accepted_cookies'        => array('accepted_cookies', false),
            'expires'        => array('expires', true),
            'site'        => array('site', true),
        );
        return $sortable_columns;
    }

    // Sorting function
    function usort_reorder($a, $b) {
        // If no sort, default to user_login
        $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'id';

        // If no order, default to asc
        $order = (!empty($_GET['order'])) ? $_GET['order'] : 'desc';

        // Determine sort order
        $result = strcmp($a[$orderby], $b[$orderby]);

        // Send final sort direction to usort
        return ($order === 'asc') ? $result : -$result;
    }

}

function cacsp_consent_init() {
    $table = new Cacsp_Consent_Table();
    // var_dump(get_current_screen());
    echo '<div class="wrap">';
        // Prepare table
        $table->prepare_items();
        // Search form
        $table->search_box('search', 'search_id');
        // Display table
        $table->display();
    echo '</div>';
}

// Register the custom bulk action
/*
add_filter( 'bulk_actions-settings_page_cacsp_settings', 'register_cacsp_consent_bulk_actions' );
function register_cacsp_consent_bulk_actions($bulk_actions) {
    $bulk_actions['delete_selected'] = __( 'Delete selected!', 'cookies-and-content-security-policy');
    return $bulk_actions;
}
*/

// Handle the custom bulk action
/*
add_filter( 'handle_bulk_actions-settings_page_cacsp_settings', 'cacsp_consent_bulk_action_handler', 10, 3 );
function cacsp_consent_bulk_action_handler( $redirect_to, $doaction, $post_ids ) {
    error_log( 'Action: ' . $doaction );
    error_log( 'Post IDs: ' . print_r( $post_ids, true ) );

    if ( $doaction == 'delete_selected' && !empty( $post_ids ) ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cacsp_consent';
        
        error_log( 'Table Name: ' . $table_name );

        foreach ( $post_ids as $post_id ) {
            $result = $wpdb->delete( $table_name, array( 'id' => $post_id ) );
            if ( false === $result ) {
                error_log( 'SQL Error: ' . $wpdb->last_error );
            } else {
                error_log( 'Deleted Post ID: ' . $post_id );
            }
        }

        $redirect_to = add_query_arg( 'bulk_deleted', count( $post_ids ), $redirect_to );
    }

    return $redirect_to;
}
*/

cacsp_consent_init();
