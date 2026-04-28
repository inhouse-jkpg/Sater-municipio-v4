<?php

namespace SmartCrawl\Controllers;

use SmartCrawl\Admin\Settings\Admin_Settings;
use SmartCrawl\Settings;
use SmartCrawl\Singleton;

class Robots extends Controller {

	use Singleton;

	public function should_run() {
		$adv_tools_options = Settings::get_component_options( Settings::COMP_AUTOLINKS, array() );

		return ( ! isset( $adv_tools_options['disable-adv-tools'] ) || ! $adv_tools_options['disable-adv-tools'] ) &&
			Admin_Settings::is_tab_allowed( Settings::TAB_AUTOLINKS );
	}

	protected function init() {
		add_action( 'template_redirect', array( $this, 'hook_robots' ), 999 );

		return true;
	}

	public function hook_robots() {
		if ( $this->robots_enabled() && ! $this->file_exists() ) {
			remove_all_actions( 'do_robots' );
			add_action( 'do_robots', array( $this, 'serve_robots_file' ) );
		}
	}

	public function robots_enabled() {
		return Settings::get_setting( 'robots-txt' );
	}

	public function robots_active() {
		return $this->robots_enabled() && ! $this->file_exists() && $this->is_rootdir_install();
	}

	public function serve_robots_file() {
		$file_contents = $this->get_robot_file_contents();
		$this->output_text( $file_contents );
	}

	public function file_exists() {
		return file_exists( ABSPATH . 'robots.txt' );
	}

	public function is_rootdir_install() {
		$home_path = wp_parse_url( home_url() );
		return empty( $home_path['path'] ) || '/' === $home_path['path'];
	}

	private function output_text( $text ) {
		if ( ! headers_sent() ) {
			status_header( 200 );
			header( 'Content-Type: text/plain; charset=UTF-8' );

			die( $text ); // phpcs:ignore
		}
	}

	private function get_options() {
		return Settings::get_specific_options( 'wds_robots_options' );
	}

	public function get_final_sitemap_url() {
		$options  = $this->get_options();
		$disabled = (bool) \smartcrawl_get_array_value( $options, 'sitemap_directive_disabled' );
		if ( $disabled ) {
			return '';
		}

		$sc_sitemap_enabled = \SmartCrawl\Sitemaps\Utils::sitemap_enabled();
		if ( $sc_sitemap_enabled ) {
			return \smartcrawl_get_sitemap_url();
		}

		$custom_url = trim( (string) \smartcrawl_get_array_value( $options, 'custom_sitemap_url' ) );
		if ( empty( $custom_url ) ) {
			return '';
		}

		return strpos( $custom_url, 'http' ) === 0
			? $custom_url
			: home_url( $custom_url );
	}

	public function get_custom_directives() {
		$options      = $this->get_options();
		$option_value = \smartcrawl_get_array_value( $options, 'custom_directives' );
		if ( ! empty( $option_value ) ) {
			return $option_value;
		}

		return "User-agent: *\nDisallow:";
	}

	public function get_robot_file_contents() {
		$contents = $this->get_custom_directives();

		$sitemap_url = $this->get_final_sitemap_url();
		if ( $sitemap_url ) {
			$contents = sprintf( "%s\n\nSitemap: %s", $contents, $sitemap_url );
		}

		return apply_filters( 'wds_robots_txt_content', $contents );
	}
}