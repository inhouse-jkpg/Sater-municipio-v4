<?php
defined('ABSPATH') || die;

function templ_extend_cdn_enabler() {

    if( ! class_exists('CDN_Enabler_CLI') ) {
        return;
    }

    class Templ_CDN_Enabler_CLI_Extension extends CDN_Enabler_CLI {

        /**
         * Updates CDN Enabler's hostname setting.
         *
         * ## OPTIONS
         *
         * <hostname>
         * : Hostname to update to.
         *
         * ## EXAMPLES
         *
         *     wp cdn-enabler hostname cdn1337.templcdn.com
         */
        public function hostname( $args ) {
            if( ! isset($args[0]) ) {
                return WP_CLI::error('Missing hostname argument.');
            }
            $host = sanitize_text_field($args[0]);
            $settings = get_option('cdn_enabler');
            if( is_array( $settings ) ) {
                if( $host == $settings['cdn_hostname'] ) {
                    return WP_CLI::log('Nothing to update.');
                }
                $settings['cdn_hostname'] = $host;
                if( update_option('cdn_enabler', $settings) ) {
                    return WP_CLI::success('CDN hostname successfully updated.');
                }
            }
            return WP_CLI::error('An unknown error occured.');
        }

    }

    WP_CLI::add_command( 'cdn-enabler', 'Templ_CDN_Enabler_CLI_Extension' );

}
add_action('plugins_loaded', 'templ_extend_cdn_enabler');