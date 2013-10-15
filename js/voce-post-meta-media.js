;(function ( $, window, document, undefined ) {

    var pluginName = "PostMetaMedia",
        defaults   = {
            addSelector:    '.vpm-add',
            removeSelector: '.vpm-remove',
            inputSelector:  '.thumb-id',
        }
    ;

    function PostMetaMedia ( element, options ) {
        this.$element = $(element);
        this.$addLink = this.$element.find('.vpm-add');
        this.$removeLink = this.$element.find('.vpm-remove');
        this.$inputField = this.$element.find('.thumb-id');
        this.settings = $.extend( {}, defaults, options );
        this._defaults = defaults;
        this._name = pluginName;
        this.init();
    }

    PostMetaMedia.prototype = {

        init: function () {
            this.listen();
        },

        listen: function() {
            var _this = this;
            this.$addLink.on( 'click', function(e) {
                e.preventDefault();
                _this.openModal();
            } );

            if ( this.$removeLink.length ) {
                this.$removeLink.on( 'click', function(e) {
                    e.preventDefault();
                    _this.removeImage();
                    $(this).hide();
                } );
            }
        },

        openModal: function() {
            var frameOptions = {
                title: this.$element.data('uploader_title'),
                button: {
                  text: this.$element.data('uploader_button_text')
                }
            };
            this.modal = wp.media.frames.file_frame = wp.media(frameOptions);
            this.modalListen();
            this.modal.open();
        },

        modalListen: function() {
            var _this = this;
            this.modal.on('toolbar:create:select', function() {
                _this.modal.state().set('filterable', 'uploaded');
            });
            this.modal.on('select', function() {
                var attachments;
                attachments = [];
                $.each(_this.modal.state().get('selection').models, function() {
                    attachments.push(this.toJSON());
                });
                _this.attachImage(attachments);
            });
            this.modal.on('open activate', function() {
                var attachments = _this.$element.data('attachment_ids');
                if ( attachments ) {
                    var Attachment = wp.media.model.Attachment;
                    var selection = _this.modal.state().get('selection');
                    if (typeof attachments == 'object') {
                        jQuery.each(attachments, function(){
                            selection.add(Attachment.get(this));
                        });
                    }
                    else {
                        selection.add(Attachment.get(attachments));
                    }
                }
            });
        },

        attachImage: function( attachments ) {
            var attachment = attachments[0];
            this.setThumbID(attachment.id);
            this.setThumbHTML(attachment.sizes.full.url);
            this.hasImage = true;
            this.$removeLink.show();
        },

        removeImage: function() {
            this.setThumbID('');
            this.setThumbHTML('');
            this.$addLink.html(this.$element.data('uploader_button_text'));
            this.hasImage = false;
        },

        setThumbID: function( id ) {
            if ( this.$inputField.length ) {
                this.$inputField.eq(0).val(id);
            }
            this.$element.data('attachment_ids', id);
        },

        setThumbHTML: function( url ) {
            var $img = $('<img>');
            $img.css({'max-width':'100%'});
            $img.attr('src', url);
            this.$addLink.html($img);
        }

    };

    $.fn[ 'PostMetaMedia' ] = function ( options ) {
        return this.each(function() {
            if ( !$.data( this, 'PostMetaMedia' ) ) {
                $.data( this, 'PostMetaMedia', new PostMetaMedia( this, options ) );
            }
        });
    };

    $(document).ready(function(){
        $('.vpm-media-field').PostMetaMedia({});
    });

})( jQuery, window, document );