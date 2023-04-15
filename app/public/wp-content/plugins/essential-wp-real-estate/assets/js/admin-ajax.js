


jQuery(document).ready(function ($) {

	var ajax_url = ajax_obj.ajax_url;

    $("#add_custom_field").on('click',function(){

        var field_label = $('[name="field_label[]"]').val();
        var field_default_value = $('[name="field_default_value[]"]').val();
        var field_type = $('[name="field_type[]"]').val();

        $.ajax({
            type: 'post', 
            url: ajax_url, 
            dataType: 'html',
            data: {
                action: 'ccl_add_custom_field',
                field_label: field_label,
                field_default_value: field_default_value,
                field_type: field_type,
            }, 
            success: function( data ) {
               $('.added-field-list').append(data);
            }
        });

    }); 

    $(".delete-field").on('click',function(){
        var field_id = $(this).data('field_id');
        var field_name = $(this).data('field_name');

        $(this).parent('.custom-field-item').remove();
        $.ajax({
            type: 'post', 
            url: ajax_url, 
            dataType: 'html',
            data: {
                action: 'ccl_delete_custom_field',
                field_id: field_id,
                field_name: field_name,
            }, 
            success: function( data ) {

            }
        })
    }); 

});