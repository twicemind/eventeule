(function($) {
    'use strict';

    $(document).ready(function() {
        // Hide other plugin notices and popups on EventEule pages - AGGRESSIVE MODE
        if (window.location.href.indexOf('page=eventeule') > -1) {
            // Immediate removal
            $('.dialog-lightbox-widget, .dialog-widget, #elementor-connect-pro-banner').remove();
            $('.dialog-lightbox-message').remove();
            $('body').removeClass('elementor-has-popup');
            
            // Repeated checking every 100ms for 10 seconds
            let checkCount = 0;
            const maxChecks = 100; // 10 seconds
            
            const removeElements = setInterval(function() {
                checkCount++;
                
                // Elementor Pro popups - multiple selectors
                $('.dialog-lightbox-widget, .dialog-widget, #elementor-connect-pro-banner').remove();
                $('.dialog-lightbox-message, .dialog-lightbox-widget-content').remove();
                $('.e-notice:not(.eventeule-message)').remove();
                $('.elementor-message').remove();
                $('body').removeClass('elementor-has-popup');
                $('#elementor-pro-notice').remove();
                
                // Remove dialog overlay
                $('.dialog-widget-content, .dialog-buttons-wrapper').closest('.dialog-widget').remove();
                
                // Other plugin notices (but keep EventEule's own)
                $('.notice:not(.eventeule-message)').each(function() {
                    if (!$(this).closest('.eventeule-dashboard').length) {
                        $(this).remove();
                    }
                });
                
                // Stop after max checks
                if (checkCount >= maxChecks) {
                    clearInterval(removeElements);
                }
            }, 100);
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