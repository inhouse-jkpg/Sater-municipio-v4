<?php
// Update 

if ( get_option( 'cacsp_version' ) < '2.27' ) {
    update_option( 'cacsp_version', cacsp_get_plugin_version(), true );
    cacsp_save_error_message_js();
}
