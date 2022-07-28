define(['jquery'], function ($) {
    $(".increase-qty").click(function(e){
        e.preventDefault();
        var qty = $(this).closest("div.control").find("input");
        var newqty = parseInt(qty.val())+parseInt(1);
        qty.val(newqty).change();
        return false;
    });
    $(".decrease-qty").click(function(){
        var qty = $(this).closest("div.control").find("input");
        var newqty = parseInt(qty.val())-parseInt(1);
        if(newqty < 1){
            return false;
        }
        qty.val(newqty).change();
        return false;
    });
    $(".tocart").click(function(){
        var qty = $(this).parent().find("input.qty").val();
        if(qty <= 0) {
            $(this).parent().find("p.qty-error").show();
            return false;
        }
        return true;
    });
});
