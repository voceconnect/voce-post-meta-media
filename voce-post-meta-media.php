<?php

class Voce_Post_Meta_Media {

	protected static $initialized = false;

	/**
	 * setup plugin
	 */
	public static function initialize() {
		if( !self::$initialized ) {
			add_filter( 'meta_type_mapping', array( __CLASS__, 'meta_type_mapping' ) );
			add_action( 'admin_enqueue_scripts', array( __CLASS__, 'action_admin_enqueue_scripts' ) );
			add_action( 'admin_init', array( __CLASS__, 'check_voce_meta_api' ) );
			self::$initialized = true;
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
				'display_callbacks' => array( array( __CLASS__, 'display_media_field' ) ),
				'sanitize_callbacks' => array( array( __CLASS__, 'sanitize_media_field' ) ),
				'default_value' => array()
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
		if( isset( $field->args['multiple_select'] ) && $field->args['multiple_select'] ){
			$values = array_map( 'intval', explode(',', $new_value) );
			return array_filter( $values );
		} else {
			return intval($new_value);
		}
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

		extract( wp_parse_args( $field->args, array(
			'mime_types' => array( 'image' ),
			'multiple_select' => false,
			'thumb_size' => 'medium'
		) ) );

		// Html content vars
		$label_add    = 'Set ' . $field->label;
		$label_remove = 'Remove ' . $field->label;
		$link_content = '';
		$value_string = '';
		$hide_remove  = true;

		// If value is set get thumbnails to display and show remove button
		if ( $value ) {
			if ( !is_array( $value ) ) {
				$value = array( $value );
			}
			$value_string = implode(',', $value);
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
					$link_content .= '<br>' . get_the_title( $value_post->ID );
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
				<input class="hidden vpm-id" type="hidden" id="<?php echo esc_attr( $field->get_input_id() ); ?>" name="<?php echo esc_attr( $field->get_name() ); ?>" value="<?php echo esc_attr( $value_string ); ?>" />
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

	/**
	 * Check if Voce Post Meta is loaded
	 * @method check_voce_meta_api
	 * @return void
	 */
	public static function check_voce_meta_api() {
		if ( !class_exists('Voce_Meta_API')) {
	  		add_action('admin_notices', array( __CLASS__, 'voce_meta_api_not_loaded' ) );
	  	}
	}
	
	/**
	 * Display message if Voce_Meta_API class (or Voce Post Meta plugin, more likely) is not available
	 * @method voce_meta_api_not_loaded
	 * @return void
	 */
	public static function voce_meta_api_not_loaded() {
	    printf(
	      '<div class="error"><p>%s</p></div>',
	      __('Voce Meta Media Plugin cannot be utilized without the <a href="https://github.com/voceconnect/voce-post-meta" target="_BLANK">Voce Post Meta</a> plugin.')
	    );
	}		


}