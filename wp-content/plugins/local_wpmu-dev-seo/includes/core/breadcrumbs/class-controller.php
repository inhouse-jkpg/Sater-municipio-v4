<?php
/**
 * Initializes breadcrumbs functionality.
 *
 * @since   3.5.0
 * @package SmartCrawl
 */

namespace SmartCrawl\Breadcrumbs;

use SmartCrawl\Admin\Settings\Admin_Settings;
use SmartCrawl\Settings;
use SmartCrawl\Singleton;
use SmartCrawl\Controllers;

/**
 * Breadcrumbs class.
 */
class Controller extends Controllers\Controller {

	use Singleton;

	/**
	 * Should this module run?.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function should_run() {
		$adv_tools_options = Settings::get_component_options( Settings::COMP_AUTOLINKS, array() );

		return ( ! isset( $adv_tools_options['disable-adv-tools'] ) || ! $adv_tools_options['disable-adv-tools'] ) &&
			Admin_Settings::is_tab_allowed( Settings::TAB_AUTOLINKS );
	}

	/**
	 * Initialize the class.
	 *
	 * @since 3.5.0
	 *
	 * @return void
	 */
	protected function init() {
		add_action( 'init', array( $this, 'init_hooks' ) );
	}

	/**
	 * Initialize hooks.
	 *
	 * @since 3.5.0
	 *
	 * @return void
	 */
	public function init_hooks() {
		if ( function_exists( '\add_shortcode' ) ) {
			add_shortcode( 'smartcrawl_breadcrumbs', array( $this, 'render_shortcode' ) );
			// keeping old shortcode for backward compatibility.
			add_shortcode( 'smartcrawl_breadcrumb', array( $this, 'render_shortcode' ) );
		}

		if ( Helper::is_active() && Helper::get_option( 'disable_woo' ) ) {
			remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0 );
		}

		add_filter( 'wds-known_macros', array( $this, 'replace_macros' ), 10, 2 );
	}

	/**
	 * Callback for shortcode function.
	 *
	 * @since 3.5.0
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return string
	 */
	public function render_shortcode( $atts = array() ) {
		$atts = shortcode_atts(
			array(
				'before' => '',
				'after'  => '',
			),
			$atts,
			'smartcrawl_breadcrumbs'
		);

		return $this->render_breadcrumb( $atts['before'], $atts['after'] );
	}

	/**
	 * Render breadcrumb for current page.
	 *
	 * @since 3.5.0
	 *
	 * @param string $before What to show before the breadcrumb.
	 * @param string $after  What to show after the breadcrumb.
	 *
	 * @return string
	 */
	public function render_breadcrumb( $before = '', $after = '' ) {
		// Front page doesn't need a breadcrumb.
		if ( Helper::is_active() ) {
			$builder = $this->get_current_builder();

			// If breadcrumb class is found.
			if ( is_object( $builder ) && method_exists( $builder, 'render' ) ) {
				return $builder->render( $before, $after );
			}
		}

		return '';
	}

	/**
	 * Get current page builder.
	 *
	 * Get the breadcrumb builder class instance for the
	 * current page.
	 *
	 * @since 3.5.0
	 *
	 * @return Builders\Builder|Builders\No
	 */
	public function get_current_builder() {
		static $builder = null;

		if ( null === $builder ) {
			// Default no breadcrumb builder.
			$builder = Builders\No::get();

			if ( function_exists( '\is_woocommerce' ) && \is_woocommerce() ) {
				// WooCommerce shop, product, category or tag.
				$builder = Builders\Woocommerce::get();
			} elseif ( is_page() || is_home() ) {
				// Normal page.
				$builder = Builders\Pages::get();
			} elseif ( is_single() || is_post_type_archive() ) {
				// Single post or post type archive.
				$builder = Builders\Posts::get();
			} elseif ( is_404() ) {
				// 404 page.
				$builder = Builders\Error_404::get();
			} elseif ( is_search() ) {
				// Search results page.
				$builder = Builders\Search::get();
			} elseif ( is_category() || is_tag() || is_tax() ) {
				// Taxonomy archive pages.
				$builder = Builders\Taxonomies::get();
			} elseif ( is_archive() ) {
				// Post archive pages.
				$builder = Builders\Archives::get();
			}
		}

		return $builder;
	}

	/**
	 * Modify pagination macro values for breadcrumbs.
	 *
	 * If there are no pages, display it as page 1.
	 * See https://incsub.atlassian.net/browse/SMA-1403
	 *
	 * @since 3.5.0
	 * @todo  Improve this method.
	 *
	 * @param array  $macros Macros.
	 * @param string $module Module name.
	 *
	 * @return array
	 */
	public function replace_macros( $macros, $module ) {
		global $wp_query;
		// Only for breadcrumbs.
		if ( 'breadcrumb' === $module ) {
			/* translators: 1: Current page number, 2: Total page number */
			$page_x_of_y = esc_html__( 'Page %1$s of %2$s', 'wds' );
			$max_pages   = isset( $wp_query->max_num_pages ) ? $wp_query->max_num_pages : 1;
			if ( empty( $macros['%%pagenumber%%'] ) || empty( $macros['%%pagetotal%%'] ) ) {
				$macros['%%pagenumber%%'] = 1;
				$macros['%%pagetotal%%']  = $max_pages;
			}

			if ( empty( $macros['%%spell_pagenumber%%'] ) || empty( $macros['%%spell_pagetotal%%'] ) ) {
				$macros['%%spell_pagenumber%%'] = \smartcrawl_spell_number( 1 );
				$macros['%%spell_pagetotal%%']  = \smartcrawl_spell_number( $max_pages );
			}
			if ( isset( $macros['%%page%%'] ) && empty( $macros['%%page%%'] ) ) {
				$macros['%%page%%'] = sprintf( $page_x_of_y, 1, $max_pages );
			}
			if ( isset( $macros['%%spell_page%%'] ) && empty( $macros['%%spell_page%%'] ) ) {
				// translators: %1$s Page number, %2$ total pages.
				$macros['%%spell_page%%'] = sprintf( $page_x_of_y, \smartcrawl_spell_number( 1 ), \smartcrawl_spell_number( $max_pages ) );
			}

			// Use custom separator.
			if ( isset( $macros['%%sep%%'] ) ) {
				// translators: %s separator.
				$macros['%%sep%%'] = sprintf(
					'<span class="smartcrawl-breadcrumb-separator">%s</span>',
					esc_attr( Helper::get_separator() )
				);
			}
		}

		return $macros;
	}
}