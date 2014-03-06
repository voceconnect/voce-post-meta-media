=== Voce Post Meta Media ===
Contributors: markparolisi, garysmirny, kevinlangleyjr, curtisloisel, voceplatforms  
Requires at least: 3.5.0
Tested up to: 3.6.1
Stable tag: 1.1.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Extend Voce Post Meta with media fields

== Description ==

Only works on the new (WP 3.5+) Media Modal
For support with the old thickbox media handling, use the [pre_wp35_media_modal](https://github.com/voceconnect/voce-post-meta-media/tree/pre_wp35_media_modal) tag.

== Installation ==


1. Upload `voce-post-meta-media` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Create post meta fields like this
```
add_action('init', function(){
	add_metadata_group( 'demo_meta', 'Page Options', array(
		'capability' => 'edit_posts'
	));
	add_metadata_field( 'demo_meta', 'demo_media', 'Demo Media', 'media' );
	add_post_type_support( 'page', 'demo_meta' );
});
```

== Changelog ==

= 1.1.2 =
* Added check for Voce_Meta_API 

= 1.1.0 =
* Removed support for all versions lower than WordPress 3.5

= 1.0.0 =
* Initial release

