<?php
/**
 * Dependency Injection Container Factory
 *
 * Configures and builds the PHP-DI container for the plugin.
 *
 * @package JTZL\Simple_Dark_Mode
 */

namespace JTZL\Simple_Dark_Mode;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

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
	 * Uses wp-content/cache directory instead of plugin directory for security.
	 *
	 * @param ContainerBuilder $builder The container builder instance.
	 * @return void
	 */
	private static function configure_compilation( ContainerBuilder $builder ): void {
		$cache_dir = WP_CONTENT_DIR . '/cache/simple-dark-mode';

		if ( ! file_exists( $cache_dir ) ) {
			wp_mkdir_p( $cache_dir );
		}

		$index_file = $cache_dir . '/index.php';
		if ( ! file_exists( $index_file ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Non-critical hardening file.
			$result = file_put_contents( $index_file, "<?php\n// Silence is golden.\n" );
			if ( false === $result ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging for cache directory issues.
				error_log( sprintf( 'Simple Dark Mode: failed to write index.php to %s', $index_file ) );
			}
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
