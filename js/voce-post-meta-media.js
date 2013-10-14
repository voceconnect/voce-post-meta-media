;(function ( $, window, document, undefined ) {

    var pluginName = "PostMetaMedia",
        defaults   = {
            propertyName: "value"
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
            this.$element.on( 'click', this.openModal );
        },

        openModal: function() {
            var frameOptions = {};
            this.modal = wp.media.frames.file_frame = wp.media(frameOptions);
            this.modalListen();
        },

        modalListen: function() {
            this.modal.on('toolbar:create:select', function() {
                return _this.frame.state().set('filterable', 'uploaded');
            });

            this.modal.on('select', function() {
                var attachments;
                attachments = [];
                $.each(_this.frame.state().get('selection').models, function() {
                    return attachments.push(this.toJSON());
                });
                return _this.attachImage(attachments, $element);
            });

            this.modal.on('open activate', function() {
                if ($element.data('attachment_ids')) {
                    return $element.data('attachment_ids', '');
                }
            });
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