jQuery(document).ready(function ($) {

    //map
    
    // Select2 enable for select_advance meta fields
    $(".select-adv").each(function () {
        $('#' + $(this).attr("id")).select2({
            placeholder: "Select an item",
            allowClear: true,
            debug: true
        });
    });
    
    // Upload images.
    $('body').on('click', '.mb_img_upload_btn', function(e){
        e.preventDefault();
        if (aw_uploader) {
            aw_uploader.open();
            return;
        }
        var cl_mb_img_id    = $(this).attr("id");
        var cl_mb_img_name  = $(this).data("name");
        var product_images  = $('#' + cl_mb_img_id + '_cont') ;
        var button = $(this),
        aw_uploader = wp.media({
            title: 'Choose Media',
            library : {
                type : 'image'
            },
            button: {
                text: 'Use this image'
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
					$('.cl_mb_placeholder#' + cl_mb_img_id).remove();
                    product_images.append(
                        '<div id="' + attachment.id + '" class="single_img"><input type="hidden" name="' + cl_mb_img_name + '[]" value="' + attachment.id + '"><img id="' + attachment.id + '" src="' + attachment_image + '" width="150" height="150"/><a id="' + cl_mb_img_id + '" data-img_id="' + attachment.id + '" class="cl-remove" href="javascript:void(0)">X</a></div>'
                    );
				}
            });
		});
        aw_uploader.open();
    });

    // Remove images.
    $('body').on('click', 'a.cl-remove', function (e) {
        e.preventDefault();
        var cl_mb_img_id        = $(this).attr("id");
        var data_placeholder    = $('.cl_mb_clear_btn').data("placeholder");
        $(this).parent().remove();
        if (!$.trim($('#' + cl_mb_img_id +'_cont').html()).length) {
            $('#' + cl_mb_img_id + '_cont').replaceWith(
                '<div id="' + cl_mb_img_id + '_cont" class="components-responsive-wrapper"><img id="' + cl_mb_img_id + '" class="cl_mb_placeholder" src="' + data_placeholder + '"></div>'
            );
        }
		return false;
	});

    // Clear images.
    $('body').on('click', '.cl_mb_clear_btn', function(e){
        e.preventDefault();
        var data_placeholder    = $(this).data("placeholder");
        var cl_mb_remove_id     = $(this).attr("id");
        
        $('#' + cl_mb_remove_id + '_cont').replaceWith(
            '<div id="' + cl_mb_remove_id + '_cont" class="components-responsive-wrapper"><img id="' + cl_mb_remove_id + '" class="cl_mb_placeholder" src="' + data_placeholder + '"></div>'
        );
        $('.mb_img_upload_btn#' + cl_mb_remove_id).attr("src", data_placeholder);
    });

    // Images Sortable
    $('body').on('mouseenter', '.components-responsive-wrapper', function (e){
        e.preventDefault();

        /* Enable Sortable */
        $(function(){
            $('.components-responsive-wrapper').sortable({
                items: "> div",
                placeholder: 'ui-state-highlight',
                over: function(event, ui) {
                        var cl = ui.item.attr('class');
                        $('.ui-state-highlight').addClass(cl);
                    }
            }).disableSelection();
        });

    });

    // LOCATION MAP Leaflet | Openstreet
    $(document).ready(function () {

        let lat = $('#cl-meta-text-input-lat').val();
        let lon = $('#cl-meta-text-input-lon').val();

        var Lmap = L.map('map-ed').setView([lat, lon], 4);
        // SET COPYRIGHT MARK
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> | <a href="https://smartdatasoft.com/">Smartdatasoft</a>',
            maxZoom: 14,
            // noWrap: true
        }).addTo(Lmap);

        var mapMarker = L.marker([lat, lon], {draggable: true}).addTo(Lmap).on('dragend', function() {
			var coord = String(mapMarker.getLatLng()).split(',');
            var lat = coord[0].split('(');
			var lng = coord[1].split(')');
            $('#cl-meta-text-input-lat').val(lat[1]);
            $('#cl-meta-text-input-lon').val(lng[0]);
        });

        function updateMarker() {
            let lat = $('#cl-meta-text-input-lat').val();
            let lon = $('#cl-meta-text-input-lon').val();
            var newLatLng = new L.LatLng(lat, lon);
            mapMarker.setLatLng(newLatLng); 
        }
        
        $("#cl-meta-text-input-lat").on("change", function () {
            updateMarker();
        })

        $("#cl-meta-text-input-lon").on("change", function () {
            updateMarker();
        })

    });


    // Single Meta Field Clone
    function mb_cl_clone(container) {
        // Clone the last child of cl_mb_clone_single class
        let last = container.children('.cl_mb_clone_single').last();
        let clone = last.clone();
		// Insert clone.
        clone.insertAfter(last);
    }
    

    // Group Meta Field Clone
    function mb_cl_group_clone(container) {
        
        // Generete Random ID
        let unique_key = (Math.random() + 1).toString(36).substring(2);

        // Clone the last child of cl_mb_clone_single class
        let group_id = container.data("group_id");
        let last = container.children('.cl_mb_clone_single').last();
        let clone = last.clone();

		// Insert clone.
        clone.insertAfter(last);

        // Insert Random ID on cloned fields
        let cloned = container.children('.cl_mb_clone_single').last().children('.column');
        cloned.each(function () {
            let group_field_id = $(this).data("group_field_id");
            let name = group_id + '[' + unique_key + '][' + group_field_id + ']';
            let group_key = $(this).children().eq(1).data("key");
            let target_input = $(this).children().last();
            if (target_input.is("input") || target_input.is("select")|| target_input.is("textarea")) {
                $(this).children().last().attr('name', name);
            } else {
                var value = new RegExp(group_key,"g");
                $(this).html($(this).html().replace(value,unique_key));
            }
        })
        
    }
    
    function addClone( e ) {
		e.preventDefault();
		var container = $( this ).closest( '.column' );
		mb_cl_clone( container );
    }
    
    function addGroupClone(e) {
		e.preventDefault();
		var container = $( this ).closest( '.column' );
		mb_cl_group_clone( container );
    }
    
    function removeClone(e) {
        e.preventDefault();
        var mb_col = $(this).closest('.column');
        if ($(mb_col).find('.cl_mb_clone_single').length !== 1) { /* Check if the clone div is the only child exist. */
            $(this).parent().remove();
        }
    }

    $(document).on("click", 'button.clone_btn', addClone);
    $(document).on("click", 'button.clone_group_btn', addGroupClone);
    $(document).on("click", 'button.remove_clone_btn', removeClone);
    
    // -- Clone Remove Button hover effect
    function add_mouse_hover(e) {
        e.preventDefault();
        $(this).parent().addClass('mouse_hover');
    }
    function remove_mouse_hover(e) {
        e.preventDefault();
        $(this).parent().removeClass('mouse_hover');
    }
    $(document).on("mouseover", 'button.remove_clone_btn', add_mouse_hover);
    $(document).on("mouseout", 'button.remove_clone_btn', remove_mouse_hover);



    // Group Sortable
    $(document).on('mouseenter', '.cl-meta-sortable', function (e){
        e.preventDefault();

        /* Enable Sortable */
        $(function(){
            $('.cl-meta-sortable').sortable({
                items: "> div",
                placeholder: 'sortable-state-highlight',
                over: function(event, ui) {
                    var cl = ui.item.attr('class');
                    $('.sortable-state-highlight').addClass(cl);
                }
            }).disableSelection();
        });

    });


});