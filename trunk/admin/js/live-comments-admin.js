"use strict";
(function( $ ) {
 
    // Add Color Picker to all inputs that have 'color-field' class
    $(function() {
        $('.color-field').wpColorPicker();
        
        $('.toggle').hide();
        $('.pop-up').on('click', function(){
            $(this).siblings('.toggle').toggle('slow');
        })
    });
     
})( jQuery );