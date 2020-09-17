$('.tile-cover').on('click', function(e){
	$('.tile.active').removeClass('active');
	$(this).parent().addClass('active');
	setTimeout(function(e){
		e.focus();
	}, 100, $(this).parent().find('input[autofocus]').get(0));
});

$('.tile-content button[data-cancel]').on('click', function(e){
	e.stopImmediatePropagation();
	$(this).parent().parent().parent().removeClass('active');
});

$('button[data-cancel]').on('click', function(e){
	history.back();
});

$('a[data-cancel]').on('click', function(e){
	e.preventDefault();
	return false;
});

$('button[data-target]').on('click', function(e){
	var target = this.dataset.target;
	var method = this.dataset.method || 'GET';
	var params = this.dataset.params || '{}';
	var success = this.dataset.success || 'return';
	params = JSON.parse(params);
	for (const key in params) {
		if(params[key] == '?'){
			params[key] = prompt(`Please provide a ${key}.`, '');
		}
	}
	$.ajax(target, {
		method: method,
		data: params,
		success: function(data){
			alert(data.desc);
			if(data.status == 200){
				eval(success);
			}
		},
		error: function(e){
			alert("Failed to run action. Please check your network.");
		}
	});
});