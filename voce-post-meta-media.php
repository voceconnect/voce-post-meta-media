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

		if ( version_compare( $wp_version, '3.5', '<' ) ) {
			add_filter( 'attachment_fields_to_edit', array( __CLASS__, 'add_attachment_field' ), 20, 2 );
		}
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

		if ( version_compare( $wp_version, '3.5', '<' ) ) {
			add_thickbox();
			wp_enqueue_script( 'vpm-featured-image', self::plugins_url( 'js/voce-post-meta-media.js', __FILE__ ), array( 'jquery', 'media-upload' ) );
		} else { // 3.5+ media modal
			wp_enqueue_media();
			wp_enqueue_script( 'vpm-featured-image', self::plugins_url( 'js/voce-post-meta-media.js', __FILE__ ), array( 'jquery', 'set-post-thumbnail' ) );
			wp_enqueue_script( 'vpm-featured-image-modal', self::plugins_url( 'js/media-modal.js', __FILE__ ), array( 'jquery', 'media-models' ) );
		}
	}

	/**
	* Throw this in the media attachment fields
	*
	* @param string $form_fields
	* @param string $post
	* @return void
	*/
	public static function add_attachment_field( $form_fields, $post ) {
		$calling_post_id = 0;
		if ( isset( $_GET['post_id'] ) ) {
			$calling_post_id = absint( $_GET['post_id'] );
		}
		elseif ( isset( $_POST ) && count( $_POST ) ) { // Like for async-upload where $_GET['post_id'] isn't set
			$calling_post_id = $post->post_parent;
		}

		if ( ! $calling_post_id ) {
			return $form_fields;
		}

		$referer = wp_get_referer();
		$query_vars = wp_parse_args( parse_url( $referer, PHP_URL_QUERY ) );
		$meta_id = ( isset($_REQUEST['meta_id']) ? $_REQUEST['meta_id'] : null );
		if ( ( isset( $_REQUEST['context'] ) && $_REQUEST['context'] != $meta_id ) || ( isset( $query_vars['context'] ) && $query_vars['context'] != $meta_id ) ) {
			return $form_fields;
		}
		$post_type = get_post_type( $calling_post_id );
		$mime_type = $post->post_mime_type;
		$icon = ( strpos( $mime_type, 'image' ) ) ? false : true;
		$label = isset( $_REQUEST['meta_label'] ) ? $_REQUEST['meta_label'] : null;
		$img_html = wp_get_attachment_image_src( $post->ID, 'medium', $icon );

		$format_string = '<a id="set-%4$s-%1$s-thumbnail" class="%1$s-thumbnail" href="#" onclick="VocePostMetaMedia.setAsThumbnail(\'%2$s\', \'%5$s\', \'%1$s\', \'%4$s\');return false;">Set as %3$s</a>';
		$link = sprintf( $format_string, $meta_id, $post->ID, $label, $post_type, esc_attr( $img_html[0] ) );
		$form_fields["{$post_type}-{$meta_id}-thumbnail"] = array(
			'label' => $label,
			'input' => 'html',
			'html' => $link );
		return $form_fields;
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

	global $content_width, $_wp_additional_image_sizes, $wp_version;
	$post_type = get_post_type( $post_id );

	$value_post = get_post( $value );

	$url_class = '';

	if ( version_compare( $wp_version, '3.5', '<' ) ) {
		// Use the old thickbox for versions prior to 3.5
		$image_library_url = get_upload_iframe_src( 'image' );
		// if TB_iframe is not moved to end of query string, thickbox will remove all query args after it.
		$image_library_url = add_query_arg( array( 'context' => $field->get_input_id( ), 'meta_id' => $field->get_input_id( ), 'meta_label' => $field->label, 'TB_iframe' => 1 ), remove_query_arg( 'TB_iframe', $image_library_url ) );
		$url_class = 'thickbox';
	} else {
		// Use the media modal for 3.5 and up
		$image_library_url = "#";
		$modal_js = sprintf(
			'var mm_%3$s = new MediaModal({
				calling_selector : "#set-%1$s-%2$s-thumbnail",
				cb : function(attachments){
					var attachment = attachments[0];
					var img_url = "";
					if (typeof attachment.sizes != "object") {
						img_url = attachment.icon;
					}
					else if (typeof attachment.sizes.medium != "undefined") {
						img_url = attachment.sizes.medium.url;
					}
					else if (typeof attachment.sizes.thumbnail != "undefined") {
						img_url = attachment.sizes.thumbnail.url;
					}
					else {
						img_url = attachment.sizes.full.url;
					}
					VocePostMetaMedia.setAsThumbnail(attachment.id, img_url, "%2$s", "%1$s");
				}
			});',
			$post_type, $field->get_input_id( ), md5( $field->get_input_id( ) )
		);
	}

	// Get icon for type
	$mime_type = $value_post->post_mime_type;
	$icon = ( strpos( $mime_type, 'image' ) ) ? false : true;

	$format_string = '
	<p class="hide-if-no-js">%1$s</p>
	<p class="hide-if-no-js">
		<input class="hidden" type="hidden" id="%4$s" name="%8$s" value="%7$s"  />
		<a title="%6$s" href="%2$s" id="set-%3$s-%4$s-thumbnail" class="%5$s" data-attachment_ids="%7$s" data-uploader_title="%6$s" data-uploader_button_text="%6$s">%%s</a>
	</p>';
	$set_thumbnail_link = sprintf( $format_string, voce_field_label_display( $field ), $image_library_url, $post_type, $field->get_input_id( ), $url_class, sprintf( esc_attr( 'Set %s' ), $field->label ), $value, $field->get_name( ) );
	$content = sprintf( $set_thumbnail_link, sprintf( esc_html( 'Set %s' ), $field->label ) );
	$hide_remove = true;

	if ( $value && get_post( $value ) ) {
		$old_content_width = $content_width;
		$content_width = 266;
		if ( ! isset( $_wp_additional_image_sizes["{$post_type}-{$field->get_input_id( )}-thumbnail"] ) ) {
			$thumbnail_html = wp_get_attachment_image( $value, array( $content_width, $content_width ), $icon );
		}
		else {
			$thumbnail_html = wp_get_attachment_image( $value, "{$post_type}-{$field->get_input_id( )}-thumbnail", $icon );
		}

		if ( ! empty( $thumbnail_html ) ) {
			$content = sprintf( $set_thumbnail_link, $thumbnail_html );
			$hide_remove = false;
		}
		$content_width = $old_content_width;
	}

	$format_string = '<p class="hide-if-no-js"><a href="#" id="remove-%1$s-%2$s-thumbnail" class="%4$s" onclick="VocePostMetaMedia.remove(\'%2$s\', \'%1$s\', \'%3$s\');return false;">Remove %3$s</a></p>';
	$content .= sprintf( $format_string, $post_type, $field->get_input_id( ), esc_html( $field->label ), $hide_remove ? 'hidden' : '' );
	$content .= !empty( $field->description ) ? ('<br><span class="description">' . wp_kses( $field->description, Voce_Meta_API::GetInstance()->description_allowed_html ) . '</span>') : '';

	if ( version_compare( $wp_version, '3.5', '>=' ) ) {
		$content .= sprintf( '<script>%s</script>', $modal_js );
	}

	echo $content;
}
