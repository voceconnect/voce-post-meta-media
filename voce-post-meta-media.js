window.VocePostMetaMedia = {
        
	/**
	* Display the selected media in the post meta box
    * 
    * @method setThumbnailHTML
    * @param string html
    * @param integer id
    * @param string post_type
    */
	setThumbnailHTML: function(html, id, post_type){
		jQuery('#set-'+ post_type +'-'+ id +'-thumbnail').html(unescape(html));
		jQuery('#remove-'+ post_type +'-'+ id +'-thumbnail').show();
	},
        
	/**
	* Populate the selected media ID in the hidden meta field
	*
    * @method setThumbnailID
    * @param integer thumb_id
    * @param integer id
    */
	setThumbnailID: function(thumb_id, id){
		var field = jQuery('input#asset_link.hidden');
		if ( field.size() > 0 ) {
			jQuery(field).val(thumb_id);
		}
	},
        
	/**
         * Unset the value in the hidden field
         * Remove the displayed image in the post meta box
         *
         * @method remove
         * @param integer id
         * @param post_type
         */
	remove: function(id, post_type){
		var field = jQuery('input#' + id + '.hidden');
		if ( field.size() > 0 ) {
			jQuery(field).val('');
		}
		jQuery("#set-" + post_type + "-" + id + "-thumbnail").html("Add media");
		jQuery("#remove-" + post_type + "-" + id + "-thumbnail").hide();
	},
        
	/**
         * Signal the selected media contents to the parent window (from TB)
         *
         * @method setAsThumbnail
         * @param integer thumb_id
         * @param integer id
         * @param string post_type
         * @param string img_html
         */
	setAsThumbnail: function(thumb_id, id, post_type, img_html){
		var win = window.dialogArguments || opener || parent || top;
		win.tb_remove();
		win.VocePostMetaMedia.setThumbnailID(thumb_id, id);
		win.VocePostMetaMedia.setThumbnailHTML(escape(img_html), id, post_type);
	}
        
}