/*global window,jQuery,wp */
var MediaModal = function (settings, options) {
  'use strict';
  this.settings = {
    calling_selector: false,
    cb: function (attachment) {}
  };
  var that = this,
  frame = wp.media.frames.file_frame;

  this.attachEvents = function attachEvents() {
    jQuery(this.settings.calling_selector).on('click', this.openFrame);
  };

  this.openFrame = function openFrame(e) {
    e.preventDefault();

    // Create the media frame.
    frame = wp.media.frames.file_frame = wp.media(
      jQuery.extend(true, {
        title: jQuery(this).data('uploader_title'),
          button: {
            text: jQuery(this).data('uploader_button_text')
          }
      }, that.options)
	);

    // Set filterable state to uploaded to get select to show (setting this
    // when creating the frame doesn't work)
    frame.on('toolbar:create:select', function(){
      frame.state().set('filterable', 'uploaded');
    });

    // When an image is selected, run the callback.
    frame.on('select', function () {
      // We set multiple to false so only get one image from the uploader
      var attachment = frame.state().get('selection').first().toJSON();
      that.settings.cb(attachment);
    });

    frame.on('open activate', function() {
      // Get the link/button/etc that called us
      var $caller = jQuery(that.settings.calling_selector);

      // Select the thumbnail if we have one
      if ($caller.data('thumbnail_id')) {
        var Attachment = wp.media.model.Attachment;
        var selection = frame.state().get('selection');
        selection.add(Attachment.get($caller.data('thumbnail_id')));
      }
    });
        
    frame.open();
  };

  this.init = function init() {
    jQuery.extend(this.settings, settings);
    this.options = options;
    this.attachEvents();
  };
  this.init();

  return this;
};