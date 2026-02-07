<?php
/**
 * Filter Service
 *
 * Manages WordPress filter hooks for dark mode customization.
 *
 * @package JTZL\Inherited_Dark\Services
 */

declare(strict_types=1);

namespace JTZL\Inherited_Dark\Services;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use JTZL\Inherited_Dark\Contracts\FilterServiceInterface;

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
		'--id-bg-primary'     => '#1a1a1a',
		'--id-bg-secondary'   => '#2d2d2d',
		'--id-text-primary'   => '#e0e0e0',
		'--id-text-secondary' => '#a0a0a0',
		'--id-border-color'   => '#404040',
		'--id-link-color'     => '#6db3f2',
		'--id-link-hover'     => '#8ec5f5',
	];

	/**
	 * Check if dark mode should be enabled for current context.
	 *
	 * Filter: inherited_dark_enabled
	 * Allows disabling dark mode on specific pages.
	 *
	 * @return bool Whether dark mode is enabled.
	 */
	public function is_enabled(): bool {
		$enabled = apply_filters( 'inherited_dark_enabled', true );
		// Ensure boolean return even if filter returns non-boolean.
		return (bool) $enabled;
	}

	/**
	 * Get filtered CSS variables.
	 *
	 * Filter: inherited_dark_css_variables
	 * Allows customizing CSS variables.
	 *
	 * @return array<string, string> CSS variable name => value pairs.
	 */
	public function get_filtered_css_variables(): array {
		$variables = apply_filters( 'inherited_dark_css_variables', self::DEFAULT_CSS_VARIABLES );
		// Ensure array return even if filter returns non-array.
		return is_array( $variables ) ? $variables : self::DEFAULT_CSS_VARIABLES;
	}

	/**
	 * Get additional custom CSS rules.
	 *
	 * Filter: inherited_dark_custom_css
	 * Allows adding custom CSS rules.
	 *
	 * @return string Custom CSS rules.
	 */
	public function get_custom_css(): string {
		$css = apply_filters( 'inherited_dark_custom_css', '' );
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
