jQuery(document).ready(function($){
	// $(".nt-app-rev").click(function(e){
	// 	var app_btn = $(this);
	// 	e.preventDefault();
	// 	var id = $(this).data("ntrev-id");
	// 	jQuery.ajax({
	// 		type: 'POST',
	// 		url: nt_list_table_params.ajaxurl,
	// 		data: {"action": "nt_app_rev", "id": id},
	// 		success: function (data) {
	// 			$(app_btn).fadeOut(200);
	// 			$(app_btn).closest('tr').find('.column-status').html("Approved");
	// 		}
	// 	});
	// });

	function ntcolorStar(){
				$(this).prevAll().andSelf().addClass('nt-hover-rating');
				$(this).nextAll().removeClass('nt-hover-rating');
				$('.nt-current-rating[name="'+$(this).closest(".nt-stars").data("str-target")+'"]').val($(this).data("rating"));
			}

			function ntdecolorStar(){
				$(this).prevAll().andSelf().removeClass('nt-hover-rating');
				$(".nt-current-rating").val("0.0");
			}

			$(".nt-stars-half").hover(ntcolorStar,ntdecolorStar);
			$(".nt-stars-half").click(function(){
				ntcolorStar();
				$(this).off("mouseleave");
			});

	

});

