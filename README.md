Voce Post Meta Media
==================

Contributors: markparolisi, garysmirny, kevinlangleyjr, curtisloisel, voceplatforms  
Tags: post, meta, media  
Requires at least: 3.5  
Tested up to: 3.8.1  
Stable tag: 1.1.2  
License: GPLv2 or later  
License URI: http://www.gnu.org/licenses/gpl-2.0.html


## Description
Extend Voce Post Meta with media fields

Only works on the new (WP 3.5+) Media Modal
For support with the old thickbox media handling, use the [1.0.0](https://github.com/voceconnect/voce-post-meta-media/tree/1.0.0) tag.

## Installation

### As standard plugin:
> See [Installing Plugins](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins).

### As theme or plugin dependency:
> After dropping the plugin into the containing theme or plugin, add the following:
```php
if( ! class_exists( 'Voce_Post_Meta_Media' ) ) {
	require_once( $path_to_voce_post_meta_media . '/voce-post-meta-media.php' );
}
```

## Usage

#### Basic example for inserting an image

```php
<?php
add_action('init', function(){
	add_metadata_group( 'demo_meta', 'Page Options', array(
		'capability' => 'edit_posts'
	));
	add_metadata_field( 'demo_meta', 'demo_media', 'Demo Media', 'media' );
	add_post_type_support( 'page', 'demo_meta' );
});
?>
```

#### Example using a custom mime type (in this example, a PDF)

```php
<?php
add_action('init', function(){
	add_metadata_group( 'demo_meta', 'Page Options', array(
		'capability' => 'edit_posts'
	));
	add_metadata_field( 'demo_meta', 'demo_media', 'Demo Media', 'media', array(
		'mime_types' => array( 'application/pdf' )
	));
	add_post_type_support( 'page', 'demo_meta' );
});
?>
```



**1.1.2**  
*Added check for Voce_Meta_API*

**1.1.0**  
*Removed support for all versions lower than WordPress 3.5*

**1.0.0**  
*Initial version.*
