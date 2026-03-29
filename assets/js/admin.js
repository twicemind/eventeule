(function($) {
    'use strict';

    $(document).ready(function() {
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

        // Auto-dismiss success messages after 3 seconds
        setTimeout(function() {
            $('.notice.is-dismissible').fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);

        console.log('EventEule Admin loaded successfully');
    });

})(jQuery);