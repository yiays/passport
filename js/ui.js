$('.tile-cover').on('click', function(e){
	$('.tile.active').removeClass('active');
	$(this).parent().addClass('active');
	setTimeout(function(e){
		e.focus();
	}, 100, $(this).parent().find('input[autofocus]').get(0));
});
$('.tile-content button[data-cancel]').on('click', function(e){
	$(this).parent().parent().parent().removeClass('active');
});