;(function ( $, window, document, undefined ) {

    var defaults   = {
        addSelector:    '.vpm-add',
        removeSelector: '.vpm-remove',
        inputSelector:  '.vpm-id',
        thumbSize:      'medium',
        modalOptions:   {}
    };

    function PostMetaMedia ( element, options ) {
        this.$element = $(element);
        this.settings = $.extend( {}, defaults, options );
        this._defaults = defaults;
        this.init();
    }

    PostMetaMedia.prototype = {

        init: function () {
            this.$addLink    = this.$element.find(this.settings.addSelector);
            this.$removeLink = this.$element.find(this.settings.removeSelector);
            this.$inputField = this.$element.find(this.settings.inputSelector);
            this.listen();
        },

        listen: function() {
            var _this = this;
            this.$addLink.on( 'click', function(e) {
                e.preventDefault();
                _this.openModal();
            } );
            this.$removeLink.on( 'click', function(e) {
                e.preventDefault();
                _this.removeImage();
                $(this).hide();
            } );
        },

        openModal: function() {
            var defaultOptions = {
                title: 'Select Media'
            };
            var options = $.extend( true, {}, defaultOptions, this.settings.modalOptions );
            this.modal = wp.media.frames.file_frame = wp.media(options);
            this.modalListen();
            this.modal.open();
        },

        modalListen: function() {
            var _this = this;
            this.modal.on('toolbar:create:select', function() {
                _this.modal.state().set('filterable', 'uploaded');
            });
            this.modal.on('select', function() {
                _this.getModalAttachment();
            });
            this.modal.on('open activate', function() {
                _this.attachmentToModal();
            });
        },

        getModalAttachment: function() {
            var attachments = [];
            var selections  = this.modal.state().get('selection').models;
            $.each( selections, function() {
                attachments.push(this.toJSON());
            });
            this.attachImage(attachments);
        },

        attachmentToModal: function() {
            var attachments = this.$inputField.val().split(',');
            if ( attachments ) {
                var Attachment = wp.media.model.Attachment;
                var selection = this.modal.state().get('selection');
                if (typeof attachments == 'object') {
                    $.each(attachments, function(){
                        selection.add(Attachment.get(this));
                    });
                }
                else {
                    selection.add(Attachment.get(attachments));
                }
            }
        },

        attachImage: function( attachments ) {
            var ids   = [];
            var urls  = [];
            var _this = this;
            $.each( attachments, function(i, attachment){
                var url = _this.getThumbUrl(attachment);
                ids.push(attachment.id);
                urls.push(url);
            } );
            this.$inputField.val(ids);
            this.setThumbHTML(urls);
            this.hasImage = true;
            this.$removeLink.show();
        },

        getThumbUrl: function ( attachment ) {
            var img_url = "";
            if ( typeof attachment.sizes != "object" ) {
                img_url = attachment.icon;
            }
            else if ( typeof attachment.sizes[this.settings.thumbSize] != "undefined" ) {
                img_url = attachment.sizes[this.settings.thumbSize].url;
            }
            else if (typeof attachment.sizes.medium != "undefined") {
                img_url = attachment.sizes.medium.url;
            }
            else {
                img_url = attachment.sizes.full.url;
            }
            return img_url;
        },

        removeImage: function() {
            this.$inputField.val('');
            this.$addLink.html(this.$addLink.attr('title'));
            this.hasImage = false;
        },

        setThumbHTML: function( urls ) {
            _this = this;
            this.$addLink.html('');
            $.each( urls, function(i, url){
                var $img = $('<img>');
                $img.attr('src', url);
                _this.$addLink.append($img);
            } );
        }

    };

    $(document).ready(function(){
        $('.vpm-media-field').each(function(i, e){
            new PostMetaMedia( this, $(this).data('field-settings') );
        });
    });

})( jQuery, window, document );