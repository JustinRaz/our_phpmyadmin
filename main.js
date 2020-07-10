
$(document).ready(function() {
    console.log("Document is ready")

    prev_db_hover = undefined;

    $("#db-list > li").mouseover(function () { 
        prev_db_hover = $("#db-list > #"+this.id+" > #drop-btn");
        prev_db_hover.show();
    }).mouseleave(() => {
        prev_db_hover.hide();
    });
});