jQuery(document).ready(function($) {
    
    $('.el_mem_checklist li').live('click',function(){
        var checkbox=this; 
        $('.mem_checklist_checkbox',checkbox).toggleClass('mem_checklist_checkbox_checked'); 
                
        if($('.mem_checklist_checkbox',checkbox).hasClass('mem_checklist_checkbox_checked')) {
            $('input',checkbox).attr('checked', true);
        } else {
            $('input',checkbox).attr('checked', false);            
        } 
        var form=$(this).closest('form'); 
        $.post(ajaxurl, 'action=save_element_data&'+form.serialize(), function(data) {
        }); 
    });

});
