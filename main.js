
$(document).ready(function() {
    console.log("Document is ready")

    prev_hover_drop_btn = undefined;
    prev_hover_query_join_btn = undefined;

    $("#db-list > li").mouseover(function () { 
        prev_hover_query_join_btn = $("#db-list > #"+this.id+" > #query-join-btn");
        prev_hover_drop_btn = $("#db-list > #"+this.id+" > #drop-btn");
        prev_hover_drop_btn.show();
        prev_hover_query_join_btn.show();
    }).mouseleave(() => {
        prev_hover_drop_btn.hide();
        prev_hover_query_join_btn.hide();
    });
});