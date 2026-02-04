<?php
/**
 * Dependency Injection Container Factory
 *
 * Configures and builds the PHP-DI container for the plugin.
 *
 * @package JTZL\Simple_Dark_Mode
 */

declare(strict_types=1);

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
	 * Get the cache directory path.
	 *
	 * @internal This method is intended for internal use only.
	 *
	 * @return string The cache directory path.
	 */
	private static function get_cache_dir(): string {
		return WP_CONTENT_DIR . '/cache/simple-dark-mode';
	}

	/**
	 * Configure compilation for the container builder.
	 *
	 * Uses wp-content/cache directory instead of plugin directory for security.
	 * Includes version in class name for automatic cache invalidation on updates.
	 *
	 * @param ContainerBuilder $builder The container builder instance.
	 * @return void
	 */
	private static function configure_compilation( ContainerBuilder $builder ): void {
		$cache_dir = self::get_cache_dir();

		if ( ! self::ensure_cache_directory( $cache_dir ) ) {
			return;
		}

		self::write_protection_file( $cache_dir . '/index.php', "<?php\n// Silence is golden.\n" );

		// Include version in class name for automatic invalidation on plugin updates.
		$version_suffix = preg_replace( '/[^a-zA-Z0-9_]/', '_', SIMPLE_DARK_MODE_VERSION );
		$compiled_class = 'CompiledContainer_' . $version_suffix;

		$builder->enableCompilation( $cache_dir, $compiled_class );
	}

	/**
	 * Ensure the cache directory exists.
	 *
	 * @param string $cache_dir The cache directory path.
	 * @return bool True if directory exists or was created, false on failure.
	 */
	private static function ensure_cache_directory( string $cache_dir ): bool {
		if ( is_dir( $cache_dir ) ) {
			return true;
		}

		if ( ! wp_mkdir_p( $cache_dir ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging for cache directory issues.
			error_log( sprintf( 'Simple Dark Mode: failed to create cache directory %s', $cache_dir ) );
			return false;
		}

		return true;
	}

	/**
	 * Write a protection file if it doesn't exist.
	 *
	 * @param string $file_path The file path to write.
	 * @param string $content   The content to write.
	 * @return bool True on success or file exists, false on failure.
	 */
	private static function write_protection_file( string $file_path, string $content ): bool {
		if ( is_file( $file_path ) ) {
			return true;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Non-critical hardening file.
		$result = file_put_contents( $file_path, $content );
		if ( false === $result ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Intentional debug logging for cache directory issues.
			error_log( sprintf( 'Simple Dark Mode: failed to write protection file %s', $file_path ) );
			return false;
		}

		return true;
	}

	/**
	 * Clear the compiled container cache.
	 *
	 * Should be called on plugin activation/deactivation to ensure fresh container.
	 *
	 * @return void
	 */
	public static function clear_cache(): void {
		$cache_dir = self::get_cache_dir();

		if ( ! is_dir( $cache_dir ) ) {
			return;
		}

		$files = (array) glob( $cache_dir . '/CompiledContainer*.php' );

		foreach ( $files as $file ) {
			if ( is_file( $file ) ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink -- Direct filesystem access for cache cleanup.
				unlink( $file );
			}
		}
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
