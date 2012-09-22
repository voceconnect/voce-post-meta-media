<?php
/*
  Plugin Name: Voce Meta Media
  Plugin URI: http://vocecommunications.com
  Description: Extends Voce Post Meta with a media picker field
  Version: 0.1
  Author: Mark Parolisi
  Author URI: http://vocecommunications.com
  License: A "Slug" license name e.g. GPL2
 */

class Voce_Post_Meta_Media {
	
	
	public static function initaliaize(){
		add_action('admin_enqueue_scripts', array(__CLASS__, 'action_admin_enqueue_scripts'));
	}
	
	 /** Enqueue admin JavaScripts
     *
     * @return void
     */
    public static function action_admin_enqueue_scripts($hook) {
        // only load on select pages
        if(!in_array($hook, array('post-new.php', 'post.php', 'media-upload-popup'))) {
            return;
        }
        add_thickbox();
        wp_enqueue_script("featured-image-custom", self::plugins_url('voce-post-meta-media.js', __FILE__), array('jquery', 'media-upload'));
    }

	public static function plugins_url($relative_path, $plugin_path) {
        $template_dir = get_template_directory();

        foreach(array('template_dir', 'plugin_path') as $var) {
            $$var = str_replace('\\', '/', $$var); // sanitize for Win32 installs
            $$var = preg_replace('|/+|', '/', $$var);
        }
        if(0 === strpos($plugin_path, $template_dir)) {
            $url = get_template_directory_uri();
            $folder = str_replace($template_dir, '', dirname($plugin_path));
            if('.' != $folder) {
                $url .= '/' . ltrim($folder, '/');
            }
            if(!empty($relative_path) && is_string($relative_path) && strpos($relative_path, '..') === false) {
                $url .= '/' . ltrim($relative_path, '/');
            }
            return $url;
        }
        else {
            return plugins_url($relative_path, $plugin_path);
        }
    }
}
if(class_exists('Voce_Meta_API')){
	Voce_Post_Meta_Media::initialize();
}