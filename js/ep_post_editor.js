jQuery(document).ready( function() {    // ON DOCUMENT READY

    jQuery('div#ep_metabox_overview_epaper').click(function()   //META BOX
    {
        if ( jQuery('div#ep_metabox_overview_content').css('display') != 'block' )
        {
            jQuery('div#ep_metabox_overview_help_content').css('display', 'none');
            jQuery('div#ep_metabox_overview_content').css('display', 'block');
        }
    });

    jQuery('div#ep_metabox_overview_help').click(function() // toggle display attribute of metabox divs
    {
        if ( jQuery('div#ep_metabox_overview_help_content').css('display') != 'block' )
        {
            jQuery('div#ep_metabox_overview_content').css('display', 'none');
            jQuery('div#ep_metabox_overview_help_content').css('display', 'block');
        }
    });
       
    jQuery('#ep_metabox_overview_navi').css('cursor', 'pointer');   // hover effect for metabox navi
    
    jQuery('#ep_metabox_overview_epaper').click(function(){ // toggle css of metabox navi
        jQuery(this).css('color', '#D54E21');
        jQuery('#ep_metabox_overview_help').css('color', '#464646');
    });
    
    jQuery('#ep_metabox_overview_help').click(function(){
        jQuery(this).css('color', '#D54E21');
        jQuery('#ep_metabox_overview_epaper').css('color', '#464646');
    });
       

    jQuery('.add_epaper_button').click(function() { //ADD EPAPER TO TINYMCE
                
        var ep_title = "title=\"" + jQuery(this).parent().find('.ep_meta_info_title').html() + "\"";  
            
        //http://stackoverflow.com/questions/1253303/whats-the-best-way-to-set-cursor-caret-position 
        if ( jQuery.browser.msie ){
            tinyMCE.activeEditor.execCommand("mceInsertRawHTML", false, "[epaper " + ep_title +  "]");
        } else {
            tinyMCE.activeEditor.selection.setContent("[epaper " + ep_title +  "] <br />");
        }          
    });
    
    jQuery('.add_link_tag').click(function() {  //ADD LINK TEXT ATTRIBUTE TO TINYMCE
        
        if ( jQuery.browser.msie ){
            tinyMCE.activeEditor.execCommand("mceInsertRawHTML", false, " link_text=\"\"");
        } else {
            tinyMCE.activeEditor.selection.setContent(" link_text=\"\"");     
        }
    });
    
    jQuery('.add_css_class').click(function() { //ADD CSS CLASS TO TINYMCE
           
        if ( jQuery.browser.msie ){
            tinyMCE.activeEditor.execCommand("mceInsertRawHTML", false, " css_class=\"\"");
        } else {
            tinyMCE.activeEditor.selection.setContent(" css_class=\"\"");    
        }        
    });
    
}); // END DOCUMENT READY