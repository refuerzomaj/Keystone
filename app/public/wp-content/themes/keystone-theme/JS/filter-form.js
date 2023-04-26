/*jQuery('#').change(function(){
    jQuery('#').submit();
});*/
jQuery(document).ready(function($) {
    var typeVal,priceRangeVal,bedroomVal;
    function setMinMaxVal(min,max){
        $("#min-price").val(min);
        $("#max-price").val(max);
    }
    $("#property-type").click(function() {
        $("#property-type #dropdown-content").toggleClass('showDropdown');
        $("#property-price-range #dropdown-content, #property-bed-room #dropdown-content").removeClass('showDropdown');
    });
    $("#property-price-range").click(function(e){
        $("#property-price-range #dropdown-content").toggleClass('showDropdown');
        $("#property-type #dropdown-content, #property-bed-room #dropdown-content").removeClass("showDropdown");
    });
    $("#property-bed-room").click(function(e){
        $("#property-bed-room #dropdown-content").toggleClass('showDropdown');
        $("#property-type #dropdown-content, #property-price-range #dropdown-content").removeClass('showDropdown');
    });
    $(".property-searchfield-box #property-searchfield").click(function(e){
        $("#property-price-range #dropdown-content, #property-type #dropdown-content, #property-price-range #dropdown-content").removeClass('showDropdown');
    });
    //For Property Type
    $("#property-type .dropdown-content a").click(function(e){
        typeVal = $(this).attr('id');
        if(typeVal == "default"){
            $("#property-type p").text("Property Type");
        }else{
            $("#property-type p").text(typeVal).css('text-transform','capitalize');
        }
        $("#property-type-value").val(typeVal);
    });
    //For Property Price Range
    $("#property-price-range .dropdown-content a").click(function(e){
        priceRangeVal = $(this).attr('id');
        switch(priceRangeVal){
            case "default":
                $("#property-price-range p").text("Price Range");
                setMinMaxVal('','');
                break;
            case "1":
                $("#property-price-range p").text("£0 - £10,000");
                setMinMaxVal('0','10,000');
                break;
            case "2":
                $("#property-price-range p").text("£20,000 - £50,000");
                setMinMaxVal('20,000','50,000');
                break;
            case "3":
                $("#property-price-range p").text("£60,000 - £100,000");
                setMinMaxVal('60,000','100,000');
                break;
            case "4":
                $("#property-price-range p").text("£110,000 - £150,000");
                setMinMaxVal('110,000','150,000');
                break;
            case "5":
                $("#property-price-range p").text("£160,000 - £200,000");
                setMinMaxVal('160,000','200,000');
                break;
            case "6":
                $("#property-price-range p").text("£210,000 - £250,000");
                setMinMaxVal('210,000','250,000');
                break;
            case "7":
                $("#property-price-range p").text("£260,000 - £300,000");
                setMinMaxVal('260,000','300,000');
                break;
            case "8":
                $("#property-price-range p").text("£310,000 - £350,000");
                setMinMaxVal('310,000','350,000');
                break;
            case "9":
                $("#property-price-range p").text("£360,000 - £400,000");
                setMinMaxVal('360,000','400,000');
                break;
            case "10":
                $("#property-price-range p").text("£410,000 - £450,000");
                setMinMaxVal('410,000','450,000');
                break;
            case "11":
                $("#property-price-range p").text("£460,000 - £500,000");
                setMinMaxVal('460,000','500,000');
                break;
            case "12":
                $("#property-price-range p").text("£510,000 - £560,000");
                setMinMaxVal('510,000','560,000');
                break;
        }
        $("#property-type-value").val(typeVal);
    });
    //For Property Bedroom
    $("#property-bed-room .dropdown-content a").click(function(e){
        bedroomVal = $(this).attr('id');
        if(bedroomVal == "default"){
            $("#property-bed-room p").text("Bedrooms");
        }else{
            $("#property-bed-room p").text(bedroomVal);
        }
        $("#property-bedrooms-value").val(bedroomVal);
    });
    $("#search-btn").click(function(e){
        //alert(click);
        $("#filter-form").submit();
    });
    //This function toggle the dropdown
});