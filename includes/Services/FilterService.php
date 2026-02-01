<?php
/**
 * Filter Service
 *
 * Manages WordPress filter hooks for dark mode customization.
 *
 * @package JTZL\Simple_Dark_Mode\Services
 */

namespace JTZL\Simple_Dark_Mode\Services;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use JTZL\Simple_Dark_Mode\Contracts\FilterServiceInterface;

/**
 * Filter Service
 *
 * Manages WordPress filter hooks for dark mode customization.
 */
class FilterService implements FilterServiceInterface {

	/**
	 * Default CSS variables for dark mode.
	 *
	 * @var array<string, string>
	 */
	private const DEFAULT_CSS_VARIABLES = [
		'--sdm-bg-primary'     => '#1a1a1a',
		'--sdm-bg-secondary'   => '#2d2d2d',
		'--sdm-text-primary'   => '#e0e0e0',
		'--sdm-text-secondary' => '#a0a0a0',
		'--sdm-border-color'   => '#404040',
		'--sdm-link-color'     => '#6db3f2',
		'--sdm-link-hover'     => '#8ec5f5',
	];

	/**
	 * Check if dark mode should be enabled for current context.
	 *
	 * Filter: simple_dark_mode_enabled
	 * Allows disabling dark mode on specific pages.
	 *
	 * @return bool Whether dark mode is enabled.
	 */
	public function is_enabled(): bool {
		$enabled = apply_filters( 'simple_dark_mode_enabled', true );
		// Ensure boolean return even if filter returns non-boolean.
		return (bool) $enabled;
	}

	/**
	 * Get filtered CSS variables.
	 *
	 * Filter: simple_dark_mode_css_variables
	 * Allows customizing CSS variables.
	 *
	 * @return array<string, string> CSS variable name => value pairs.
	 */
	public function get_filtered_css_variables(): array {
		$variables = apply_filters( 'simple_dark_mode_css_variables', self::DEFAULT_CSS_VARIABLES );
		// Ensure array return even if filter returns non-array.
		return is_array( $variables ) ? $variables : self::DEFAULT_CSS_VARIABLES;
	}

	/**
	 * Get additional custom CSS rules.
	 *
	 * Filter: simple_dark_mode_custom_css
	 * Allows adding custom CSS rules.
	 *
	 * @return string Custom CSS rules.
	 */
	public function get_custom_css(): string {
		$css = apply_filters( 'simple_dark_mode_custom_css', '' );
		// Ensure string return even if filter returns non-string.
		return is_string( $css ) ? $css : '';
	}

	/**
	 * Get default CSS variables.
	 *
	 * @return array<string, string> Default CSS variable name => value pairs.
	 */
	public function get_default_css_variables(): array {
		return self::DEFAULT_CSS_VARIABLES;
	}
}
