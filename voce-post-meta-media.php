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

if ( !class_exists( 'Voce_Post_Meta_Media' ) ) {

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
				'display_callbacks' => array( array( __CLASS__, 'display_media_field' ) ),
				'sanitize_callbacks' => array( array( __CLASS__, 'sanitize_media_field' ) )
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

		if ( ! in_array( $hook, array( 'post-new.php', 'post.php' ) ) )
			return;

		wp_enqueue_media();
		wp_enqueue_script( 'voce-post-meta-media-js', self::plugins_url( 'js/voce-post-meta-media.js', __FILE__ ), array( 'jquery' ), false, true );
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

	public static function sanitize_media_field( $field, $old_value, $new_value, $post_id ){
		$values = explode(',', $new_value);
		$values = array_map( 'intval', $values);
		return array_filter( $values );
	}

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
	public static function display_media_field( $field, $value, $post_id ) {
		if ( ! class_exists( 'Voce_Meta_API' ) ) {
			return;
		}

		// Parse args specific to media field display
		$default_args = array(
			'mime_types'      => array( 'image' ),
			'multiple_select' => false,
			'thumb_size'      => 'medium'
		);
		$args = shortcode_atts( $default_args, $field->args );
		extract($args);

		// Html content vars
		$field_id     = $field->get_input_id();
		$field_name   = $field->get_name();
		$label_add    = 'Set ' . $field->label;
		$label_remove = 'Remove ' . $field->label;
		$link_content = '';
		$hide_remove  = true;

		// If value is set get thumbnails to display and show remove button
		if ( $value ) {
			foreach ( $value as $attachment ) {
				$value_post = get_post($attachment);
				if ( $value_post ) {
					$mime_type = $value_post->post_mime_type;
					$icon = ( strpos( $mime_type, 'image' ) ) ? false : true;
					$thumbnail_html = wp_get_attachment_image( $attachment, $thumb_size, $icon );
					if ( ! empty( $thumbnail_html ) ) {
						$link_content .= $thumbnail_html;
						$hide_remove = false;
					}
				}
			}
		}

		// If no thumbnails then use link text and hide remove
		if ( empty($link_content) ) {
			$link_content = esc_html($label_add);
			$hide_remove = true;
		}

		// Settings for the the js object
		$field_settings = array(
			'thumbSize' => $thumb_size,
			'modalOptions' => array(
				'multiple' => $multiple_select,
				'title' => $label_add,
				'button' => array(
					'text' => $label_add
				),
				'library' => array(
					'type' => $mime_types
				)
			)
		);

	?>
		<div class="vpm-media-field hide-if-no-js" data-field-settings="<?php echo esc_attr(json_encode($field_settings)); ?>" >
			<p><?php voce_field_label_display( $field ); ?></p>
			<p>
				<input class="hidden vpm-id" type="hidden" id="<?php echo esc_attr( $field_id ); ?>" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( implode(',', (array) $value) ); ?>" />
				<a title="<?php echo esc_attr( $label_add ); ?>" href="#" class="vpm-add <?php echo ( $hide_remove ) ? 'button' : ''; ?>">
					<?php echo $link_content; ?>
				</a>
			</p>
			<p>
				<a href="#" class="vpm-remove button" <?php echo ( $hide_remove ) ? 'style="display:none;"' : ''; ?>>
					<?php echo esc_html( $label_remove ); ?>
				</a>
			</p>
		</div>
	<?php

	}	

}


Voce_Post_Meta_Media::initialize();



} // End class check