<?php
/**
 * Style Service
 *
 * Handles CSS enqueueing and generation for dark mode.
 *
 * @package JTZL\Simple_Dark_Mode\Services
 */

namespace JTZL\Simple_Dark_Mode\Services;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use JTZL\Simple_Dark_Mode\Contracts\FilterServiceInterface;
use JTZL\Simple_Dark_Mode\Contracts\StyleServiceInterface;

/**
 * Style Service
 *
 * Handles CSS enqueueing and generation for dark mode.
 */
class StyleService implements StyleServiceInterface {

	/**
	 * Style handle for WordPress.
	 *
	 * @var string
	 */
	private const HANDLE = 'simple-dark-mode';

	/**
	 * The filter service instance.
	 *
	 * @var FilterServiceInterface
	 */
	private FilterServiceInterface $filter_service;

	/**
	 * Constructor.
	 *
	 * @param FilterServiceInterface $filter_service The filter service instance.
	 */
	public function __construct( FilterServiceInterface $filter_service ) {
		$this->filter_service = $filter_service;
	}

	/**
	 * Register and enqueue dark mode styles.
	 *
	 * @return void
	 */
	public function enqueue_styles(): void {
		$css_url = $this->get_css_url();

		wp_enqueue_style(
			self::HANDLE,
			$css_url,
			[],
			SIMPLE_DARK_MODE_VERSION
		);

		// Add inline CSS for filtered variables and custom rules.
		$inline_css = $this->get_inline_css();
		if ( ! empty( $inline_css ) ) {
			wp_add_inline_style( self::HANDLE, $inline_css );
		}
	}

	/**
	 * Get the CSS file URL, preferring hashed version for cache busting.
	 *
	 * @return string The CSS file URL.
	 */
	private function get_css_url(): string {
		$build_dir  = SIMPLE_DARK_MODE_PATH . 'build/css/';
		$plugin_url = plugin_dir_url( SIMPLE_DARK_MODE_FILE ) . 'build/css/';

		// Try hashed version first (pattern: dark-mode.[hash].min.css).
		$css_files = glob( $build_dir . 'dark-mode.*.min.css' );

		if ( ! empty( $css_files ) ) {
			$css_file = basename( $css_files[0] );
			return $plugin_url . $css_file;
		}

		// Fallback to non-hashed version.
		return $plugin_url . 'dark-mode.min.css';
	}

	/**
	 * Get the CSS custom properties for dark mode.
	 *
	 * @return array<string, string> CSS variable name => value pairs.
	 */
	public function get_css_variables(): array {
		return $this->filter_service->get_default_css_variables();
	}

	/**
	 * Generate inline CSS with filtered variables.
	 *
	 * @return string The generated inline CSS.
	 */
	public function get_inline_css(): string {
		$variables  = $this->filter_service->get_filtered_css_variables();
		$custom_css = $this->filter_service->get_custom_css();

		$css = '';

		if ( ! empty( $variables ) ) {
			$var_declarations = [];
			foreach ( $variables as $name => $value ) {
				$sanitized_name  = $this->sanitize_css_variable_name( $name );
				$sanitized_value = $this->sanitize_css_value( $value );

				if ( null !== $sanitized_name && '' !== $sanitized_value ) {
					$var_declarations[] = sprintf( '%s: %s', $sanitized_name, $sanitized_value );
				}
			}
			if ( ! empty( $var_declarations ) ) {
				$css .= '@media (prefers-color-scheme: dark) { :root { ' . implode( '; ', $var_declarations ) . '; } }';
			}
		}

		if ( ! empty( $custom_css ) ) {
			$sanitized_custom_css = $this->sanitize_css( $custom_css );
			if ( '' !== $sanitized_custom_css ) {
				$css .= "\n" . $sanitized_custom_css;
			}
		}

		return $css;
	}

	/**
	 * Sanitize a CSS custom property name.
	 *
	 * Only allows valid CSS custom property names (--prefix-name format).
	 *
	 * @param string $name The CSS variable name to sanitize.
	 * @return string|null The sanitized name, or null if invalid.
	 */
	private function sanitize_css_variable_name( string $name ): ?string {
		$name = trim( $name );

		if ( preg_match( '/^--[a-zA-Z0-9_-]+$/', $name ) ) {
			return $name;
		}

		return null;
	}

	/**
	 * Sanitize a CSS value to prevent XSS via style tag breakout.
	 *
	 * @param string $value The CSS value to sanitize.
	 * @return string The sanitized value.
	 */
	private function sanitize_css_value( string $value ): string {
		$value = trim( $value );

		// Strip HTML tags and remove any potential style tag breakouts.
		$value = wp_strip_all_tags( $value );
		$value = preg_replace( '#<\s*/\s*style\s*>#i', '', $value );
		$value = str_replace( [ '<', '>' ], '', $value );

		return $value;
	}

	/**
	 * Sanitize arbitrary CSS to prevent XSS via style tag breakout.
	 *
	 * @param string $css The CSS to sanitize.
	 * @return string The sanitized CSS.
	 */
	private function sanitize_css( string $css ): string {
		// Strip HTML tags and remove any potential style tag breakouts.
		$css = wp_strip_all_tags( $css );
		$css = preg_replace( '#<\s*/\s*style\s*>#i', '', $css );
		$css = str_replace( [ '<', '>' ], '', $css );

		return $css;
	}
}
