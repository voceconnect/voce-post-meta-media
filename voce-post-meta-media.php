<?php
/*
  Plugin Name: Voce Meta Media
  Plugin URI: http://vocecommunications.com
  Description: Extends Voce Post Meta with a media picker field
  Version: 0.1
  Author: markparolisi, voceplatforms
  Author URI: http://vocecommunications.com
  License: GPL2
 */

class Voce_Post_Meta_Media {

	public static function initialize() {
		add_filter( 'meta_type_mapping', array( __CLASS__, 'meta_type_mapping' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'action_admin_enqueue_scripts' ) );
		add_filter( 'attachment_fields_to_edit', array( __CLASS__, 'add_attachment_field' ), 20, 2 );
	}

	/** Enqueue admin JavaScripts
	 *
	 * @return void
	 */
	public static function action_admin_enqueue_scripts( $hook ) {
		// only load on select pages
		if ( !in_array( $hook, array( 'post-new.php', 'post.php', 'media-upload-popup' ) ) ) {
			return;
		}
		add_thickbox();
		wp_enqueue_script( "featured-image-custom", self::plugins_url( 'voce-post-meta-media.js', __FILE__ ), array( 'jquery', 'media-upload' ) );
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

	/**
	 * Throw this in the media attachment fields
	 *
	 * @param string $form_fields
	 * @param string $post
	 * @return void
	 */
	public static function add_attachment_field( $form_fields, $post ) {
		$calling_post_id = 0;
		if ( isset( $_GET['post_id'] ) )
			$calling_post_id = absint( $_GET['post_id'] );
		elseif ( isset( $_POST ) && count( $_POST ) ) // Like for async-upload where $_GET['post_id'] isn't set
			$calling_post_id = $post->post_parent;
		if ( !$calling_post_id ) {
			return $form_fields;
		}

		$referer = wp_get_referer();
		$query_vars = wp_parse_args( parse_url( $referer, PHP_URL_QUERY ) );
		$meta_id = $_REQUEST['meta_id'];		
		if ( (isset( $_REQUEST['context'] ) && $_REQUEST['context'] != $meta_id) || (isset( $query_vars['context'] ) && $query_vars['context'] != $meta_id) ) {
			return $form_fields;
		}
		$post_type = get_post_type( $calling_post_id );
		$mime_type = $post->post_mime_type;
		$icon = (strpos( $mime_type, 'image' )) ? false : true;
		$label = $_REQUEST['meta_label'];
		$img_html = esc_attr( wp_get_attachment_image( $post->ID, 'medium', $icon ) );
		$link = sprintf( '<a id="%4$s-%1$s-thumbnail-%2$s" class="%1$s-thumbnail" href="#" onclick="VocePostMetaMedia.setAsThumbnail(\'%2$s\', \'%1$s\', \'%4$s\', \'%5$s\');return false;">Set as %3$s</a>', $meta_id, $post->ID, $label, $post_type, $img_html );
		$form_fields["{$post_type}-{$meta_id}-thumbnail"] = array(
			'label' => $label,
			'input' => 'html',
			'html' => $link );
		return $form_fields;
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

}

if ( class_exists( 'Voce_Meta_API' ) ) {
	Voce_Post_Meta_Media::initialize();

	function voce_media_field_display( $field, $value, $post_id ) {
		global $content_width, $_wp_additional_image_sizes, $post;
		$post_id = (!empty($post_id)) ? $post_id : $post->ID;
		$post_type = get_post_type( $post_id );
		$image_library_url = get_upload_iframe_src( 'image' );
		// if TB_iframe is not moved to end of query string, thickbox will remove all query args after it.
		$image_library_url = add_query_arg( array( 'context' => $field->id, 'meta_id'=>$field->id, 'meta_label'=>$field->label, 'TB_iframe' => 1 ), remove_query_arg( 'TB_iframe', $image_library_url ) );
		$value_post = get_post( $value );
		$mime_type = $value_post->post_mime_type;
		$icon = (strpos( $mime_type, 'image' )) ? false : true;
		if ( !isset( $_wp_additional_image_sizes["{$field->post_type}-{$field->id}-thumbnail"] ) ) {
			$thumbnail_html = wp_get_attachment_image( $value, array( $content_width, $content_width ), $icon );
		} else {
			$thumbnail_html = wp_get_attachment_image( $value, "{$this->post_type}-{$this->id}-thumbnail", $icon );
		}
		$edit_media_anchor = ($value) ? $thumbnail_html : "Add Media";
		$set_id = "set-$post_type-$field->id-thumbnail";
		$remove_id = "remove-$post_type-$field->id-thumbnail";
		?>
		<p class="hide-if-no-js">
			<?php voce_field_label_display( $field ); ?>
		</p>
		<p class="hide-if-no-js">
			<a title="<?php echo $field->id; ?>" href="<?php echo $image_library_url; ?>" id="<?php echo $set_id; ?>" class="thickbox">
				<?php echo $edit_media_anchor ?>
			</a>
		</p>
		<p class="hide-if-no-js">
			<?php $hidden = ($value && get_post( $value )) ? " " : " hidden "; ?>
			<a href="#" id="<?php echo $remove_id; ?>" class="<?php echo $hidden; ?>" onclick="VocePostMetaMedia.remove('<?php echo $field->id; ?>', '<?php echo $post_type; ?>'); return false;">
				Remove Media
			</a>
		</p>
		<input class="hidden" type="hidden" id="<?php echo $field->id; ?>" name="<?php echo $field->id; ?>" value="<?php echo esc_attr( $value ); ?>"  />
		<?php
	}

}