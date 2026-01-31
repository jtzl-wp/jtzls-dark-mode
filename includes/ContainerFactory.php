<?php
/**
 * Dependency Injection Container Factory
 *
 * Configures and builds the PHP-DI container for the plugin.
 *
 * @package JTZL\Simple_Dark_Mode
 */

namespace JTZL\Simple_Dark_Mode;

use DI\Container;
use DI\ContainerBuilder;
use JTZL\Simple_Dark_Mode\Contracts\FilterServiceInterface;
use JTZL\Simple_Dark_Mode\Contracts\StyleServiceInterface;
use JTZL\Simple_Dark_Mode\Services\FilterService;
use JTZL\Simple_Dark_Mode\Services\StyleService;
use function DI\autowire;
use function DI\get;

/**
 * Dependency Injection Container Factory
 *
 * Configures and builds the PHP-DI container for the plugin.
 */
class ContainerFactory {

	/**
	 * Create and configure the container.
	 *
	 * @param bool|null $enable_compilation Override compilation setting (null = auto-detect).
	 * @return Container The configured container instance.
	 */
	public static function create( ?bool $enable_compilation = null ): Container {
		$builder = new ContainerBuilder();

		// Determine if compilation should be enabled.
		$should_compile = $enable_compilation ?? self::should_enable_compilation();

		if ( $should_compile ) {
			self::configure_compilation( $builder );
		}

		// Enable autowiring for automatic dependency resolution.
		$builder->useAutowiring( true );

		// Add service definitions.
		$builder->addDefinitions( self::get_definitions() );

		return $builder->build();
	}

	/**
	 * Check if compilation should be enabled based on environment.
	 *
	 * @return bool Whether compilation should be enabled.
	 */
	private static function should_enable_compilation(): bool {
		return defined( 'WP_DEBUG' ) && ! WP_DEBUG;
	}

	/**
	 * Configure compilation for the container builder.
	 *
	 * @param ContainerBuilder $builder The container builder instance.
	 * @return void
	 */
	private static function configure_compilation( ContainerBuilder $builder ): void {
		$cache_dir = SIMPLE_DARK_MODE_PATH . 'var/cache';
		if ( ! file_exists( $cache_dir ) ) {
			wp_mkdir_p( $cache_dir );
		}
		$builder->enableCompilation( $cache_dir );
	}

	/**
	 * Get container definitions.
	 *
	 * Defines all service bindings for the plugin.
	 *
	 * @return array<string, mixed> Container definitions.
	 */
	private static function get_definitions(): array {
		return [
			// Interface bindings.
			FilterServiceInterface::class => autowire( FilterService::class ),
			StyleServiceInterface::class  => autowire( StyleService::class )
				->constructor( get( FilterServiceInterface::class ) ),

			// Concrete service bindings.
			FilterService::class          => autowire(),
			StyleService::class           => autowire()
				->constructor( get( FilterServiceInterface::class ) ),
		];
	}
}
