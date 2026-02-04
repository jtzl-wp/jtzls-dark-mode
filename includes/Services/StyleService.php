<?php
/**
 * Style Service
 *
 * Handles CSS enqueueing and generation for dark mode.
 *
 * @package JTZL\Simple_Dark_Mode\Services
 */

declare(strict_types=1);

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
	 * Callable to check if current theme is a block theme.
	 *
	 * @var callable
	 */
	private $is_block_theme_callback;

	/**
	 * Constructor.
	 *
	 * @param FilterServiceInterface $filter_service          The filter service instance.
	 * @param callable|null          $is_block_theme_callback Optional callback to check block theme status.
	 */
	public function __construct( FilterServiceInterface $filter_service, ?callable $is_block_theme_callback = null ) {
		$this->filter_service          = $filter_service;
		$this->is_block_theme_callback = $is_block_theme_callback ?? fn() => function_exists( 'wp_is_block_theme' ) && wp_is_block_theme();
	}

	/**
	 * Check if the current theme is a block theme.
	 *
	 * @return bool True if block theme, false otherwise.
	 */
	public function is_block_theme(): bool {
		return (bool) ( $this->is_block_theme_callback )();
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
		// Only for block themes - CSS variables don't work with classic theme's inversion strategy.
		if ( $this->is_block_theme() ) {
			$inline_css = $this->get_inline_css();
			if ( ! empty( $inline_css ) ) {
				wp_add_inline_style( self::HANDLE, $inline_css );
			}
		}
	}

	/**
	 * Get the CSS file URL, preferring hashed version for cache busting.
	 *
	 * Uses block theme CSS for FSE themes, classic theme CSS (with inversion) for others.
	 *
	 * @return string The CSS file URL.
	 */
	private function get_css_url(): string {
		$build_dir  = SIMPLE_DARK_MODE_PATH . 'build/css/';
		$plugin_url = plugin_dir_url( SIMPLE_DARK_MODE_FILE ) . 'build/css/';

		// Determine which CSS file to use based on theme type.
		$css_base = $this->is_block_theme() ? 'dark-mode-block' : 'dark-mode-classic';

		// Try hashed version first (pattern: dark-mode-{type}.[hash].min.css).
		$css_files = glob( $build_dir . $css_base . '.*.min.css' );

		if ( ! empty( $css_files ) ) {
			$css_file = basename( $css_files[0] );
			return $plugin_url . $css_file;
		}

		// Fallback to non-hashed version.
		return $plugin_url . $css_base . '.min.css';
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
		// Trim first, then sanitize using shared logic.
		$value = trim( $value );
		return $this->perform_css_sanitization( $value );
	}

	/**
	 * Sanitize arbitrary CSS to prevent XSS via style tag breakout.
	 *
	 * @param string $css The CSS to sanitize.
	 * @return string The sanitized CSS.
	 */
	private function sanitize_css( string $css ): string {
		// Just delegate to shared logic (which returns untrimmed if passed untrimmed, but here we can return sanitized string).
		// Note: The original sanitize_css did NOT trim. I will keep it consistent or just let perform_css_sanitization handle it.
		// However, perform_css_sanitization uses wp_strip_all_tags which might not trim.
		// Let's implement perform_css_sanitization to be robust.
		return $this->perform_css_sanitization( $css );
	}

	/**
	 * Perform sanitization on a CSS string.
	 *
	 * @param string $css_string The string to sanitize.
	 * @return string The sanitized string.
	 */
	private function perform_css_sanitization( string $css_string ): string {
		// Strip HTML tags and remove any potential style tag breakouts.
		$css_string = wp_strip_all_tags( $css_string );
		$css_string = preg_replace( '#<\s*/\s*style\s*>#i', '', $css_string );
		$css_string = str_replace( [ '<', '>' ], '', $css_string );

		// Block potential protocol-based XSS (matches "javascript:", "vbscript:", "data:" at word boundaries).
		// This also covers url("javascript:...") cases as the protocol is preceded by non-word characters.
		if ( preg_match( '/\b(javascript|vbscript|data):/i', $css_string ) ) {
			return '';
		}

		// Block expression (IE legacy XSS).
		if ( stripos( $css_string, 'expression' ) !== false ) {
			return '';
		}

		return $css_string;
	}
}
