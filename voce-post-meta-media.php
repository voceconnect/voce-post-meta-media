<?php
/*
  Plugin Name: Voce Meta Media
  Plugin URI: http://vocecommunications.com
  Description: Extends Voce Post Meta with a media picker field
  Version: 1.0
  Author: markparolisi, voceplatforms
  Author URI: http://vocecommunications.com
  License: GPL2
 */

class Voce_Post_Meta_Media {

	/**
	 * setup plugin
	 * @global string $wp_version
	 */
	public static function initialize() {
		global $wp_version;

		add_filter( 'meta_type_mapping', array( __CLASS__, 'meta_type_mapping' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'action_admin_enqueue_scripts' ) );
	}

	/**
	 * @method meta_type_mapping
	 * @param type $mapping
	 * @return array
	 */
	public static function meta_type_mapping( $mapping ) {
		$mapping['media'] = array(
			'class' => 'Voce_Meta_Field',
			'args' => array(
				'display_callbacks' => array( 'voce_media_field_display' )
			)
		);
		return $mapping;
	}

	/** Enqueue admin JavaScripts
	 *
	 * @return void
	 */
	public static function action_admin_enqueue_scripts( $hook ) {
		global $wp_version;

		if ( ! in_array( $hook, array( 'post-new.php', 'post.php', 'media-upload-popup' ) ) )
			return;

		wp_enqueue_media();
		wp_enqueue_script( 'voce-post-meta-media-js', self::plugins_url( 'js/voce-post-meta-media.js', __FILE__ ), array( 'jquery', 'set-post-thumbnail' ) );
		wp_enqueue_style( 'voce-post-meta-media-css', self::plugins_url( 'css/voce-post-meta-media.css', __FILE__ ) );
	}

	/**
	 * @method plugins_url
	 * @param type $relative_path
	 * @param type $plugin_path
	 * @return string
	 */
	public static function plugins_url( $relative_path, $plugin_path ) {
		$template_dir = get_template_directory();

		foreach (array( 'template_dir', 'plugin_path' ) as $var) {
			$$var = str_replace( '\\', '/', $$var ); // sanitize for Win32 installs
			$$var = preg_replace( '|/+|', '/', $$var );
		}
		if ( 0 === strpos( $plugin_path, $template_dir ) ) {
			$url = get_template_directory_uri();
			$folder = str_replace( $template_dir, '', dirname( $plugin_path ) );
			if ( '.' != $folder ) {
				$url .= '/' . ltrim( $folder, '/' );
			}
			if ( !empty( $relative_path ) && is_string( $relative_path ) && strpos( $relative_path, '..' ) === false ) {
				$url .= '/' . ltrim( $relative_path, '/' );
			}
			return $url;
		} else {
			return plugins_url( $relative_path, $plugin_path );
		}
	}

}


Voce_Post_Meta_Media::initialize();

/**
 *
 * @global type $content_width
 * @global type $_wp_additional_image_sizes
 * @global type $wp_version
 * @param type $field
 * @param type $value
 * @param type $post_id
 * @return type
 */
function voce_media_field_display( $field, $value, $post_id ) {
	if ( ! class_exists( 'Voce_Meta_API' ) ) {
		return;
	}

	$field_id     = $field->get_input_id();
	$field_name   = $field->get_name();
	$label        = 'Set ' . $field->label;
	$link_content = esc_html($label);
	$hide_remove  = true;
	$data_vars    = array();
	$field_data   = array(
		'uploader_title'       => $label,
		'uploader_button_text' => $label
	);

	// Prepare data attributes
	foreach ( $field_data as $key => $val ) {
		if ( $val )
			$data_vars[] = sprintf( 'data-%s="%s"', $key, esc_attr( $val ) );
	}

	// If value is set get thumbnail to display and show remove button
	if ( $value && get_post( $value ) ) {
		$thumbnail_html = wp_get_attachment_image( $value, 'medium' );
		if ( ! empty( $thumbnail_html ) ) {
			$link_content = $thumbnail_html;
			$hide_remove = false;
		}
	}

?>

	<div class="vpm-media-field hide-if-no-js" <?php echo implode(' ', $data_vars); ?>>
		<p><?php voce_field_label_display( $field ); ?></p>
		<p>
			<input class="hidden vpm-id" type="hidden" id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( $value ); ?>" />
			<a title="<?php esc_attr( $label ); ?>" href="#" class="vpm-add"><?php echo $link_content; ?></a>
		</p>
		<p><a href="#" class="vpm-remove <?php echo ( $hide_remove ) ? 'hidden' : ''; ?>">Remove <?php echo esc_html( $field->label ); ?></a></p>
	</div>

<?php

}
