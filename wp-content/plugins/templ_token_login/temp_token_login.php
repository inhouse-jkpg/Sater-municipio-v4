<?php
/**
 * Plugin Name: Templ Token Login
 * Description: Enables passwordless, 1-click login from the Templ Panel.
 * Version: 1.1.0
 * Update URI: false
 * Author: Templ
 * Author URI: https://templ.io/
 * Text Domain: templio-token-login
 * Domain Path: /languages
 * License: Unknown
 *
 * Copyright Wootemple AB
 * 
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'init', 'templ_token_login_load_textdomain' );
function templ_token_login_load_textdomain() {
	load_plugin_textdomain( 'templio-token-login', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}

add_action( 'init', 'templio_token_check_login' );
function templio_token_check_login(){
	if( isset( $_GET['wt-check'] ) && isset( $_GET['wt-launcher']) && isset($_GET['wt-login'])) {
		$login=$_GET['wt-login'];
		$user=wp_get_current_user();
		if ($user->ID!=0 && ($user->user_login==$login || $user->user_email==$login)) {
			$arr_params = array( 'wt-check', 'wt-launcher', 'wt-login' );
			$cur_url=$_SERVER["REQUEST_URI"];
			$current_page_url = remove_query_arg( $arr_params, $cur_url );
			wp_redirect( $current_page_url );
			exit;
		}
		//echo($user->ID." ". $user->user_login." ".$login);
		//exit;

		$url=$_GET['wt-launcher'];
		$data=stripslashes($_GET['wt-check']);
		//echo ($data);
		//echo ('Encoded: '.$url."encoded-data=".urlencode($data));
		wp_redirect( $url."encoded-data=".urlencode($data) );
		exit;
	}
}

/**
 * Automatically logs in a user with the correct token
 *
 * @since v.1.0
 *
 * @return string
 */
add_action( 'init', 'templio_token_auto_login' );
function templio_token_auto_login(){
	if( isset( $_GET['wt-token'] ) && (isset( $_GET['wt-uid']) || isset($_GET['wt-login']))) {
		$arr_params = array( 'wt-uid', 'wt-token', 'wt-login' );
		$cur_url=$_SERVER["REQUEST_URI"];
		$current_page_url = remove_query_arg( $arr_params, $cur_url );

		$token  =  sanitize_key( $_REQUEST['wt-token'] );
		//echo('Whats this '.$token);

		if( ! isset( $_GET['wt-uid'] ) ) {
			$login = $_GET['wt-login'];
			//echo('Whats this '.$login);

			$user=get_user_by('login',$login);
			if( ! $user ) {
				$user=get_user_by('email',$login);
			}
			if( ! $user ) {
				//echo('Failed to get user '.$login);
				wp_redirect( $current_page_url );
				exit;
			}
			///echo('User id: '.$user->ID);
			$uid=$user->ID;
			//exit;
		} else {
			$uid = sanitize_key( $_GET['wt-uid'] );
		}

		$hash_meta = get_user_meta( $uid, 'wootemple_login_token', true);
		$hash_meta_expiration = get_user_meta( $uid, 'wootemple_login_expiration', true);

		$hash_string = hash('sha256', $token . $hash_meta_expiration);
		$time = time();

		if ( $hash_string != $hash_meta || $hash_meta_expiration < $time ){
			//echo("Failed to use ".$token." for ".$uid." since ".$hash_string." not equal to ".$hash_meta." or ".$hash_meta_expiration." is less than ".$time);
			//echo("Failed to use ".$token);
			//wp_redirect( '/wp-login.php' );
			wp_redirect( $current_page_url );
			exit;
		} else {
			wp_set_auth_cookie( $uid );
			//delete_user_meta($uid, 'wootemple_login_token' );
			//delete_user_meta($uid, 'wootemple_login_expiration');

			$total_logins = get_option( 'templio_login_total', 0);
			update_option( 'templio_login_total', $total_logins + 1);
			wp_redirect( $current_page_url );
			exit;
		}
	}
}
