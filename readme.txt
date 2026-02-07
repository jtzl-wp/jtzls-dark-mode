=== Inherited Dark ===
Contributors: jtzl
Tags: dark mode, dark theme, prefers-color-scheme, accessibility, automatic
Requires at least: 6.9
Tested up to: 6.9
Requires PHP: 8.2
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automatic dark mode styling based on visitor OS preference using CSS prefers-color-scheme media query.

== Description ==

Inherited Dark automatically applies dark mode styling to your WordPress site based on your visitors' operating system preferences. No configuration required - it just works.

**Key Features:**

* Automatic detection using CSS `prefers-color-scheme: dark` media query
* Zero configuration - works out of the box
* Privacy-respecting - all detection happens client-side
* Theme-agnostic - works with any WordPress theme
* Media preservation - images, videos, and embeds display correctly
* Developer-friendly - extensible via WordPress filter hooks

**How It Works:**

The plugin uses the CSS `prefers-color-scheme` media query to detect when a visitor's operating system is set to dark mode. When detected, dark mode styles are automatically applied without any JavaScript or server-side processing.

**Technical Features:**

* Modern PHP 8.2+ architecture with dependency injection (PHP-DI)
* Service-oriented design with PSR-4 autoloading
* Separate styling strategies for Block themes (CSS variables) and Classic themes (filter inversion)
* Comprehensive test coverage with PHPUnit

**Developer Hooks:**

Customize the plugin behavior using these filter hooks:

* `inherited_dark_enabled` - Enable/disable dark mode on specific pages
* `inherited_dark_css_variables` - Customize dark mode colors
* `inherited_dark_custom_css` - Add custom CSS rules

== Installation ==

1. Upload the `inherited-dark` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. That's it! Dark mode will automatically apply based on visitor preferences

== Frequently Asked Questions ==

= Does this plugin require any configuration? =

No. Inherited Dark works automatically without any settings or configuration.

= How does dark mode detection work? =

The plugin uses the CSS `prefers-color-scheme: dark` media query, which detects your operating system's color scheme preference. This is a privacy-respecting, client-side-only approach.

= Will this affect my images and videos? =

No. The plugin includes media preservation rules that ensure images, videos, iframes, and embedded content display without color distortion.

= Can I disable dark mode on specific pages? =

Yes. Use the `inherited_dark_enabled` filter hook:

`
add_filter( 'inherited_dark_enabled', function( $enabled ) {
    if ( is_page( 'landing-page' ) ) {
        return false;
    }
    return $enabled;
} );
`

= Can I customize the dark mode colors? =

Yes. Use the `inherited_dark_css_variables` filter hook to override default CSS variables:

`
add_filter( 'inherited_dark_css_variables', function( $variables ) {
    $variables['--id-bg-primary'] = '#0d1117';
    $variables['--id-text-primary'] = '#f0f0f0';
    return $variables;
} );
`

Available CSS variables:
* `--id-bg-primary` - Primary background color
* `--id-bg-secondary` - Secondary background color
* `--id-text-primary` - Primary text color
* `--id-text-secondary` - Secondary text color
* `--id-border-color` - Border color
* `--id-link-color` - Link color
* `--id-link-hover` - Link hover color

Note: CSS variables only apply to Block themes. Classic themes use filter inversion.

= Does this work with Full Site Editing (FSE) themes? =

Yes. Inherited Dark uses different styling strategies optimized for each theme type:

* Block themes (FSE): Uses CSS custom properties for precise color control
* Classic themes: Uses CSS filter inversion for broad compatibility

Both approaches ensure proper dark mode rendering without theme modifications.

= Does this affect the WordPress admin area? =

No. Dark mode styling is applied only to the public-facing frontend of your site.

= Can I add custom CSS rules? =

Yes. Use the `inherited_dark_custom_css` filter hook:

`
add_filter( 'inherited_dark_custom_css', function( $css ) {
    return $css . '@media (prefers-color-scheme: dark) { .my-element { color: #fff; } }';
} );
`

= What are the system requirements? =

* PHP 8.2 or higher
* WordPress 6.9 or higher
* Composer (for development only)

== Screenshots ==

1. Dark mode automatically applied based on OS preference

== Changelog ==

= 1.0.0 =
* Initial release
* Automatic dark mode detection via CSS prefers-color-scheme
* Theme-agnostic styling with CSS custom properties
* Media preservation for images, videos, and embeds
* Developer filter hooks for customization

== Upgrade Notice ==

= 1.0.0 =
Initial release of Inherited Dark.
