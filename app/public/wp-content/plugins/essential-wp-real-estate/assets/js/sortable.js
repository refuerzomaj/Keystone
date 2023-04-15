(function($) {
	
	"use strict";

    jQuery( document ).ready(function( $ ) {
        function dand(){
            var $gallery = $( ".cornerarea" );
            $(".div_element_main", $gallery ).draggable({
                revert: "invalid",
                helper: "clone",
                cursor: "move"
            });
            $(".cornerarea").droppable({
                accept:'.div_element_main',
                drop: function (event, ui) {
                    var type= $(ui.draggable).find('._list_blickes').data('type');
                    
                    var position= $(event.target).data('position');
                    var positionpre= $(ui.draggable).closest('.cornerarea').data('position');
                    var area=  $(event.target).data('area');
                    var postr='';
                    if(typeof(position)!="undefined"){
                        var postr="["+position+"]";
                    }
                    if(typeof(positionpre)!="undefined"){
                        var postrpre="["+positionpre+"]";
                    }
                    var name=$(ui.draggable).find('input').attr('name',"archive_setting["+area+"]"+postr+"["+type+"]");
                    $(ui.draggable).find('.active_inactive_input').attr('value',0);
                    $(ui.draggable).find('input').after('<input class="active_inactive_input" type="hidden" name="archive_setting['+area+']'+postr+'['+type+'][active]" value="1">');
                   
                    
                    $(this).append(ui.draggable);

                }

            });
        }

        if($('.faq-filter-item-area').length){
            $('.ac-trigger').on("click", function(){
                $(this).closest('.faq-filter-item-title').next('.faq-filter-item-content-area').slideToggle();
                $(this).toggleClass('current');
                $(this).find("i").map(function(a,b){
                    $(b).toggleClass("hidethis")
                })
            });
            $('.ac-trigger-active').on("click", function(){
                $(this).find("i").map(function(a,b){
                    $(b).toggleClass("hidethis")
                })
                $(this).closest(".faq-filter-item-area").toggleClass("inactive")
                if($(this).closest(".faq-filter-item-area").hasClass("inactive")){
                    $(this).closest(".faq-filter-item-area").find('.activeinactive').val(0)
                }else{
                    $(this).closest(".faq-filter-item-area").find('.activeinactive').val(1)
                }
            });
            $( ".shop-sidebar-content" ).sortable({ handle: '.sortable-handle' } );
            jQuery('#form_pagelayout_archive').on('submit', function(e)
            {
                e.preventDefault();
                $(this).find('input[type=checkbox]:checked').prop('checked', true).val(1);
                $(this).find('input[name=action]').val('cl_save_pagelayout_archive');
                var settings = $(this).serialize();
                $.ajax(
                {
                    type: "POST",
                    url: ajaxurl,
                    data: settings,
                    success: function()
                    {
                        //window.location.reload();
                    },
                    error: function()
                    {
                    }
                });
            });
        }
        //varsion 2

        $( ".sectiontwo" ).sortable({items:".div_element_main",cancel: "span" });
        $( ".sectionone" ).sortable({items:".sectionone_item"});
        $( ".sectionfive" ).sortable({items:".sectionfive_item"});
        $( ".sectionfiveright" ).sortable({items:".sectionfiveright_item",cancel: "span"});
        dand();
        
        $( ".metasection" ).sortable({items:".metasection_item"});
        $( ".sectionthumbnail" ).sortable({items:".sectionthumbnail_item"});
        $( ".leftmeta" ).sortable({items:".div_element_main"});
        $( ".rightmeta" ).sortable({items:".div_element_main"});
        $( ".extrasection" ).sortable({items:".div_element_main"});
        $( ".templatesection" ).sortable({items:".div_element_main"});


        $( ".listing-short-detail-wrap" ).sortable({items:"._card_list_flex"});
        $( ".single-sortable-div" ).sortable({items:"._card_list_flex"});
        $('.plus-icon').on("click", function(){
            if($(this).next('.wperesds-modal-container').is(":visible")){
                $(this).next('.wperesds-modal-container').hide();
            }else{
                $('.wperesds-modal-container:visible').hide()
                $(this).next('.wperesds-modal-container').show();
            }
        })
        $(document).on("click",'.element_inactive', function(){
           
            var nameattr= $(this).closest('.div_element_main').find('input').attr('name');
            $(this).closest('.div_element_main').find('input').next('input').replaceWith('');
            $(this).closest('.div_element_main').find('input').after('<input class="active_inactive_input" type="hidden" name="'+nameattr+'[active]" value="0">');
            var clonehtml=$(this).closest('.div_element_main').prop('outerHTML');
            if($(this).closest('.div_element_main').siblings(".wperesds-modal-container").length){
                $(this).closest('.div_element_main').siblings(".wperesds-modal-container").find('.option-card-body').append(clonehtml);
            }
            if($(this).closest('.div_element_main').parent().siblings(".wperesds-modal-container").length){
                $(this).closest('.div_element_main').parent().siblings(".wperesds-modal-container").find('.option-card-body').append(clonehtml);
            }
            $(this).closest('.div_element_main').replaceWith('')
        })

        $(document).on("click",'.element_active', function(){
            var datadiv=$(this).closest('.div_element_main').data('div');
            var position=$(this).closest('.div_element_main').closest('.cornerarea').data('position');
            var nameattr= $(this).closest('.div_element_main').find('input').attr('name');
            $(".active_inactive_input."+position).replaceWith('')
            $(this).closest('.div_element_main').find(".active_inactive_input").replaceWith('')
            $(this).closest('.div_element_main').find('input').after('<input class="active_inactive_input" type="hidden" name="'+nameattr+'[active]" value="1">');
            var clonehtml=$(this).closest('.div_element_main').prop('outerHTML');
            if(typeof(datadiv) !="undefined"){
                $('.'+datadiv).html(clonehtml)
            }else{
                $(this).closest('.div_element_main').closest('.wperesds-modal-container').prev('.plus-icon').before( clonehtml)
            }
            $(this).closest('.div_element_main').replaceWith('');
            dand()
            
        })

        $(document).on("click",'.cptm-header-action-close', function(event){
            event.preventDefault();
            $(this).closest('.wperesds-modal-container').hide()
        })


        function cl_sortable(c,f){
            
            $(c).sortable({
                connectWith: c,
                items: '> .widget-item',
                placeholder: 'sortable-state-highlight',
                over: function (event, ui) {
                    var cl = ui.item.attr('class');
                    $('.sortable-state-highlight').addClass(cl);
                    var item_type = $(this).data("item_type");
                    var item_val = '[]';
                    if (typeof $(this).data("item_val") !== "undefined") {
                        item_val = "[" + $(this).data("item_val") + "][]";
                    }
                    ui.item.children().attr("name",f + "[" + item_type + "]" + item_val);
                }
            }).disableSelection();
        }

        function cl_ajax(trigger_key,val){
            $(trigger_key).on('submit', function(e)
            {
                e.preventDefault();
                $(this).find('input[name=action]').val(val);
                var settings = $(this).serialize();
                var msg = $(this).find( ".submit" );
                $.ajax(
                {
                    type: "POST",
                    url: ajaxurl,
                    data: settings,
                    success: function()
                    {
                        //window.location.reload();
                        msg.append('<span class="save-msg">Save Successfully</span>');
                        setTimeout(function () {
                            $('.save-msg').remove();
                        }, 1000);
                    },
                    error: function()
                    {
                    }
                });
            });
        }

        // Sortable
        cl_sortable('.archive_builder-section', 'archive_setting');
        cl_sortable('.search_builder-section', 'search_setting');
        cl_sortable('.add_builder-section', 'add_setting');

        // Ajax
        cl_ajax('#form_pagelayout_single','cl_single_settings_layout_save');
        cl_ajax('#form_pagelayout_archive','cl_save_pagelayout_archive');
        cl_ajax('#form_pagelayout_archive_list','cl_save_pagelayout_archive_list');
        cl_ajax('#form_pagelayout_search','cl_save_settings_layout_search');
        cl_ajax('#form_pagelayout_add','cl_save_settings_layout_add');
        cl_ajax('#form_pagelayout_custom_field','cl_save_settings_layout_custom_field');
        cl_ajax('#form_pagelayout_comp_field_list','cl_save_settings_layout_comp_field_list');


        // ---------------------------------------- End Archive List form builder ---------------------------------------- //

        $('#add_builder_sec_btn').on('click', function(e)
        {
            e.preventDefault();
            var name = $('#add_builder_sec_val').val().replace(/ /g, '_');
            if (name == '') {
                var rand = Math.random().toString(36).substring(2,7);
                var name = "Field Group " + rand;
            }
            var html = '<div class="'+name+' add_builder-section enabled ui-sortable" data-item_type="enabled" data-item_val="'+name+'"><span>'+name+'</span></div>';
            $(html).insertBefore(".add_builder-add_section");
            cl_sortable('.add_builder-section', 'add_setting');
            $('#add_builder_sec_val').val("");
        });


        // ---------------------------------------- Hover on remove button ---------------------------------------- //
        
        $(document).on("click",'.archive_builder-section.enabled .widget-item .widget-item-remove', function(e){
            e.preventDefault();
            var widget_item = $(this).closest('.widget-item');
            $(widget_item).remove();
        })

    });

})(jQuery);	