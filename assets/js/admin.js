(function($) {
    'use strict';

    $(document).ready(function() {
        // Hide other plugin notices and popups on EventEule pages
        if (window.location.href.indexOf('page=eventeule') > -1) {
            // Remove Elementor Pro and other plugin popups
            const removeElements = setInterval(function() {
                // Elementor Pro popups
                $('.dialog-lightbox-widget, .dialog-widget, #elementor-connect-pro-banner').remove();
                $('.e-notice:not(.eventeule-message)').remove();
                $('.elementor-message').remove();
                
                // Other plugin notices (but keep EventEule's own)
                $('.notice:not(.eventeule-message)').each(function() {
                    if (!$(this).closest('.eventeule-dashboard').length) {
                        $(this).remove();
                    }
                });
            }, 100);
            
            // Stop checking after 5 seconds
            setTimeout(function() {
                clearInterval(removeElements);
            }, 5000);
        }
        
        // Color Picker Synchronisation
        const colorInputs = document.querySelectorAll('.eventeule-color-input input[type="color"]');
        
        colorInputs.forEach(function(colorInput) {
            const textInput = colorInput.nextElementSibling;
            
            // Update text input when color changes
            colorInput.addEventListener('input', function() {
                textInput.value = this.value;
            });
            
            colorInput.addEventListener('change', function() {
                textInput.value = this.value;
            });
        });

        // Smooth tab transitions
        $('.eventeule-tab').on('click', function(e) {
            const $tab = $(this);
            
            // Visual feedback
            $('.eventeule-tab').removeClass('active');
            $tab.addClass('active');
        });

        // Auto-dismiss success messages after 5 seconds (only saved settings message)
        setTimeout(function() {
            $('.notice.is-dismissible').not('.eventeule-message').fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);

        console.log('EventEule Admin loaded successfully');
    });

})(jQuery);