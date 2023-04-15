!function ($) {
    "use strict";
    jQuery( document ).ready(function( $ ) {
        let clearcache = new ClearCache("listing_clear_cache");
        clearcache.clickEvent();
        let emailupload = new EmailUpload();
        emailupload.clickEvent();


        var cl_datepicker = $( '.cl_datepicker' );
        if ( cl_datepicker.length > 0 ) {
            var dateFormat = 'mm/dd/yy';
            cl_datepicker.datepicker( {
                dateFormat: dateFormat
            } );
        }
    });


class EmailUpload {
    constructor() {
        this.clicktarget='.cl_admin_settings_upload_button';
        this.title='Choose Media';
        this.buttontext='Use this image';
        this.inputfieldobj=$('.cl_admin_settings_upload_input');
    }
    clickEvent() {
       $(document).on("click",this.clicktarget,this.callUploadProcess.bind(this));
    }
    callUploadProcess(e) {
       e.preventDefault();
       var cl_mb_img_id = $(this).attr("id");
       var inputfieldobj=this.inputfieldobj
       var aw_uploader = wp.media({
           title:  this.title,
           library : {
            //    uploadedTo : wp.media,
               type : 'image'
           },
           button: {
               text:this.buttontext
           },
           multiple: true
       }).on( 'select', function() {
           var selection = aw_uploader.state().get('selection');
           var attachment_ids = $('#' + cl_mb_img_id).val();
           selection.map( function( attachment ) {
               attachment = attachment.toJSON();
               if ( attachment.id ) {
                   attachment_ids   = attachment_ids ? attachment_ids + ',' + attachment.id : attachment.id;
                   var attachment_image = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
                   inputfieldobj.val(attachment_image);
               }
           });
       })
        .open();
    }
}

class ClearCache {
    constructor(target) {
        this.clicktarget="."+target ;
        this.action=target;
        this.ajax = $.ajax;
    }
    clickEvent() {
        $(document).on("click",this.clicktarget,this.callAjaxPost.bind(this));
    }
    callAjaxPost(e) {
        this.ajax({
            type: "POST",
            url: ajaxurl,
            data: {action:this.action},
            success: function(res)
            {
            },
            error: function()
            {
            }
        });
    }
}


}(jQuery);