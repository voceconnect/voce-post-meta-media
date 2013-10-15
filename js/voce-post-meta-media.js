;(function ( $, window, document, undefined ) {

    var pluginName = "PostMetaMedia",
        defaults   = {
            parentContainer: false,
            imageContainer:  false,
            inputField:      false
        }
    ;

    function PostMetaMedia ( element, options ) {
        this.$element = $(element);
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
            this.$element.on( 'click', function(e) {
                e.preventDefault();
                if (_this.hasImage) {
                    _this.removeImage();
                } else {
                    _this.openModal();
                }
            } );
        },

        openModal: function() {
            var frameOptions = {
                title: 'TITLE HERE',
                button: {
                  text: 'BUTTON TEXT'
                }
            };
            this.modal = wp.media.frames.file_frame = wp.media(frameOptions);
            this.modalListen();
            this.modal.open();
        },

        modalListen: function() {
            var _this = this;
            this.modal.on('toolbar:create:select', function() {
                return _this.modal.state().set('filterable', 'uploaded');
            });
            this.modal.on('select', function() {
                var attachments;
                attachments = [];
                $.each(_this.modal.state().get('selection').models, function() {
                    return attachments.push(this.toJSON());
                });
                return _this.attachImage(attachments);
            });
            this.modal.on('open activate', function() {
                if (_this.$element.data('attachment_ids')) {
                    return _this.$element.data('attachment_ids', '');
                }
            });
        },

        attachImage: function( attachments ) {
            var attachment = attachments[0];
            this.setThumbID(attachment.id);
            this.setThumbHTML(attachment.sizes.full.url);
            this.hasImage = true;
        },

        removeImage: function() {
            this.setThumbID('');
            this.setThumbHTML('');
            this.$element.text('Set Image');
            this.hasImage = false;
        },

        setThumbID: function( id ) {
            var input = this.settings.inputField;
            var parent = this.settings.parentContainer;
            if ( input && parent ) {
                var $parent = this.$element.parents(parent).eq(0);
                var $input = $parent.find(input);
                if ( $input.length ) {
                    $input.eq(0).val(id);
                }
            }
        },

        setThumbHTML: function( url ) {
            var container = this.settings.imageContainer;
            var parent = this.settings.parentContainer;
            if ( container && parent ) {
                var $parent = this.$element.parents(parent).eq(0);
                var $container = $parent.find(container);
                if ( $container.length ) {
                    var content = '';
                    if ( url ) {
                        var $img = $('<img>');
                        $img.css({'max-width':'100%'});
                        $img.attr('src', url);
                        content = $img;
                    }
                    $container.eq(0).html(content);
                }
            }
            this.$element.text('Remove Image');
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
        $('.vpm-media').PostMetaMedia({
            parentContainer: '.meta-media-field',
            imageContainer:  '.image-container',
            inputField:      '.thumb-id'
        });
    });

})( jQuery, window, document );