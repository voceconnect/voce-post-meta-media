voce-post-meta-media
====================

Media field for Voce Post Meta plugin

usage:
add_action('init', function(){
  add_metadata_group( 'demo_meta', 'Page Options', array(
	  'capability' => 'edit_posts'
  ) );
  add_metadata_field( 'demo_meta', 'demo_media', 'Demo Media', 'media' );
  add_post_type_support( 'page', 'demo_meta' );
});


eazypeezylemonsqueezy