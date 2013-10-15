;(function ( $, window, document, undefined ) {

    var pluginName = "PostMetaMedia",
        defaults   = {
            propertyName: "value"
        }
    ;

    function PostMetaMedia ( element, options ) {
        this.$element = $(element);
        //this.settings = $.extend( {}, defaults, options );
        //this._defaults = defaults;
        //this._name = pluginName;
        this.init();
    }

    PostMetaMedia.prototype = {

        init: function () {
            this.listen();
        },

        listen: function() {
            _this = this;
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
            _this = this;

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
        $('.vpm-media').PostMetaMedia({});
    });

})( jQuery, window, document );