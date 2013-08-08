Voce Post Meta Media
==================

Contributors: markparolisi, voceplatforms  
Tags: post, meta, media  
Requires at least: 3.3  
Tested up to: 3.6  
Stable tag: 1.0  
License: GPLv2 or later  
License URI: http://www.gnu.org/licenses/gpl-2.0.html


## Description
Extend Voce Post Meta with media fields

Works on both the old and new (3.5+) media modals

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

#### Example

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

**1.0**  
*Initial version.*