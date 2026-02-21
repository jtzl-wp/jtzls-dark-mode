<?php
/**
 * Filter Service Interface
 *
 * Defines the contract for filter hook management.
 *
 * @package JTZL\Inherited_Dark_Mode\Contracts
 */

declare(strict_types=1);

namespace JTZL\Inherited_Dark_Mode\Contracts;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Filter Service Interface
 *
 * Defines the contract for filter hook management.
 */
interface FilterServiceInterface {

	/**
	 * Check if dark mode should be enabled for current context.
	 *
	 * @return bool Whether dark mode is enabled.
	 */
	public function is_enabled(): bool;

	/**
	 * Get filtered CSS variables.
	 *
	 * @return array<string, string> CSS variable name => value pairs.
	 */
	public function get_filtered_css_variables(): array;

	/**
	 * Get additional custom CSS rules.
	 *
	 * @return string Custom CSS rules.
	 */
	public function get_custom_css(): string;

	/**
	 * Get default CSS variables.
	 *
	 * @return array<string, string> Default CSS variable name => value pairs.
	 */
	public function get_default_css_variables(): array;
}
