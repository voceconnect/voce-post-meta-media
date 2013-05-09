window.VocePostMetaMedia = {
        
	/**
	* Display the selected media in the post meta box
    * 
    * @method setThumbnailHTML
    * @param string html
    * @param integer id
    * @param string post_type
    */
	setThumbnailHTML: function(thumb_url, id, post_type){
		jQuery('#set-'+ post_type +'-'+ id +'-thumbnail').html('<img src="'+unescape(thumb_url)+'" />');
		jQuery('#remove-'+ post_type +'-'+ id +'-thumbnail').show();
	},
        
	/**
	* Populate the selected media ID in the hidden meta field
	*
    * @method setThumbnailID
    * @param integer thumb_id
    * @param integer id
    */
	setThumbnailID: function(thumb_id, id, post_type){
		jQuery('#set-'+ post_type +'-'+ id +'-thumbnail').data('thumbnail_id', thumb_id);
		var field = jQuery('input#'+id+'.hidden');
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
	remove: function(id, post_type, label){
		var field = jQuery('input#' + id + '.hidden');
		if ( field.size() > 0 ) {
			jQuery(field).val('');
		}
		jQuery("#set-" + post_type + "-" + id + "-thumbnail").html('Set ' + label).data('thumbnail_id', '');
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
	setAsThumbnail: function(thumb_id, thumb_url, id, post_type){
		var win = window.dialogArguments || opener || parent || top;
		win.tb_remove();
		win.VocePostMetaMedia.setThumbnailID(thumb_id, id, post_type);
		win.VocePostMetaMedia.setThumbnailHTML(escape(thumb_url), id, post_type);
	}
        
}