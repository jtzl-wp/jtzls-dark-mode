<?php
/**
 * Style Service Interface
 *
 * Defines the contract for CSS enqueueing and generation.
 *
 * @package JTZL\Simple_Dark_Mode\Contracts
 */

namespace JTZL\Simple_Dark_Mode\Contracts;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Style Service Interface
 *
 * Defines the contract for CSS enqueueing and generation.
 */
interface StyleServiceInterface {

	/**
	 * Register and enqueue dark mode styles.
	 *
	 * @return void
	 */
	public function enqueue_styles(): void;

	/**
	 * Get the CSS custom properties for dark mode.
	 *
	 * @return array<string, string> CSS variable name => value pairs.
	 */
	public function get_css_variables(): array;

	/**
	 * Generate inline CSS with filtered variables.
	 *
	 * @return string The generated inline CSS.
	 */
	public function get_inline_css(): string;
}
