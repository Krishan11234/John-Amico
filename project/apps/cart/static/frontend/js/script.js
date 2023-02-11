jQuery.fn.extend({
    scrollToMe: function(){
        if(jQuery(this).length){
            var top = jQuery(this).offset().top - 100;
            jQuery('html,body').animate({scrollTop: top}, 300);
        }
    },
    scrollToJustMe: function(){
        if(jQuery(this).length){
            var top = jQuery(this).offset().top;
            jQuery('html,body').animate({scrollTop: top}, 300);
        }
    }
});