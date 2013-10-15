;(function ( $, window, document, undefined ) {

    var pluginName = "PostMetaMedia",
        defaults   = {
            imageContainer: false,
            inputField:     false
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
                _this.openModal();
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
        },

        setThumbID: function( id ) {
            var input = this.settings.inputField;
            if ( input ) {
                var $input = this.$element.siblings(input);
                if ( $input.length ) {
                    $input.eq(0).val(id);
                }
            }
        },

        setThumbHTML: function( url ) {
            var container = this.settings.imageContainer;
            if ( container ) {
                var $container = this.$element.siblings(container);
                if ( $container.length ) {
                    var $img = $('<img>');
                    $img.css({'max-width':'100%'});
                    $img.attr('src', url);
                    $container.eq(0).html($img);
                }
            }
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
            imageContainer: '.image-container',
            inputField: '.thumb-id'
        });
    });

})( jQuery, window, document );