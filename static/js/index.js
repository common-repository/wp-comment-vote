jQuery(document).on("click", ".comment--like a",
function() {
    var $this = jQuery(this);
    var comment_id = $this.parent().data("commentid");
    var event = $this.data("event");
    var count = $this.children(".count");
    if ($this.parent().hasClass("rated")) {
        alert("you've rated");
        return false;
    } else {
        var ajax_data = {
            action: "do_comment_rate",
            comment_id: comment_id,
            event: event
        };
		$this.parent().addClass("rated");
        jQuery.ajax({
            url: wcv_ajax_url,
            type: "POST",
            data: ajax_data,
            dataType: "json",
            success: function(data) {
                if (data.status == 200) {
                    if (event == "up") {
                        count.html(data.data._comment_up);
                    } else {
                        count.html(data.data._comment_down);
                    }
                    
                } else {
                    console.log(data.data)
                }
            }
        });
    }
    return false;
});
jQuery(document).on("click", ".displayratingcmt",
function() {
    jQuery(this).parent().next().toggle(400);
    return false;
});