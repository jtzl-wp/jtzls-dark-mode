<?php
/**
 * Main Plugin Class
 *
 * Handles plugin initialization, dependency injection, and WordPress integration.
 *
 * @package JTZL\Inherited_Dark
 */

declare(strict_types=1);

namespace JTZL\Inherited_Dark;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use DI\Container;
use JTZL\Inherited_Dark\Services\FilterService;
use JTZL\Inherited_Dark\Services\StyleService;

/**
 * Main Plugin Class
 *
 * Handles plugin initialization, dependency injection, and WordPress integration.
 */
final class Plugin {

	/**
	 * The singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static ?Plugin $instance = null;

	/**
	 * The dependency injection container instance.
	 *
	 * @var Container
	 */
	private Container $container;

	/**
	 * Get the singleton instance.
	 *
	 * @return Plugin The plugin instance.
	 */
	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor to enforce singleton pattern.
	 */
	private function __construct() {
		$this->container = ContainerFactory::create();
		$this->register_hooks();
	}

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	private function register_hooks(): void {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_styles' ] );
	}

	/**
	 * Enqueue frontend styles.
	 *
	 * Only enqueues styles on the frontend, not in admin.
	 *
	 * @return void
	 */
	public function enqueue_frontend_styles(): void {
		// Only on frontend, not admin.
		if ( is_admin() ) {
			return;
		}

		$filter_service = $this->container->get( FilterService::class );

		if ( ! $filter_service->is_enabled() ) {
			return;
		}

		$style_service = $this->container->get( StyleService::class );
		$style_service->enqueue_styles();
	}

	/**
	 * Get the dependency injection container.
	 *
	 * @return Container The container instance.
	 */
	public function get_container(): Container {
		return $this->container;
	}

	/**
	 * Reset the singleton instance (for testing purposes).
	 *
	 * @internal This method is intended for testing only.
	 *
	 * @return void
	 */
	private static function reset_instance(): void {
		if ( self::$instance instanceof self ) {
			remove_action( 'wp_enqueue_scripts', [ self::$instance, 'enqueue_frontend_styles' ] );
		}
		self::$instance = null;
	}
}
