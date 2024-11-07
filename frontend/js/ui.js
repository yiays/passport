$('.tile-cover').on('click', (e) => {
	$('.tile.active').removeClass('active');
	$(e.target).parent().addClass('active');
	setTimeout((element) => {
		element.focus();
	}, 100, $(e.target).parent().find('input[autofocus]').get(0));
});

$('.tile-content *[data-close]').on('click', (e) => {
	e.stopImmediatePropagation();
	e.preventDefault();
	$(e.target).parents('.tile').removeClass('active');
	return false;
});

$('button[data-cancel]').on('click', (e) => {
	history.back();
});

$('a[data-cancel]').on('click', (e) => {
	e.preventDefault();
	return false;
});

$('button[data-href]').on('click', (e) => {
	window.location = e.target.dataset.href;
});

$('form').on('submit', function(e) {
	e.preventDefault();
	const form = e.target;
	if($(form).find('input[type=submit]').prop('disabled'))
		return;
	const formData = new FormData(form);
	$(form).parent().find('.success,.error').remove();
	$(form).parent().find('input').prop('disabled', true);
	if(form.reportValidity()) {
		$.ajax(form.action, {
			method: form.method,
			data: formData,
			processData: false,
			contentType: false,
			statusCode: {
				400: () => {
					alert("This login method isn't working correctly right now. Please try again later.");
				}
			},
			success: (data) => {
				if(data.success) {
					const message = data.message || "Logged in successfully";
					$(form).append(`<span class=success>${message}</span>`);
					if(data.token) {
						passport_storeToken(data.token);
						passport_storeProfile(data.profile);
						if(redirect)
							window.location = redirect;
					}else{
						$(form).hide();
						$(`#${form.id}-stage2`).show();
						$(`#${form.id}-stage2 input`).prop('disabled', false);
					}
				}
				else {
					$(form).append(`<span class=error>${data.message}</span>`);
					setTimeout(() => {
						$(form).parent().find('input').filter(':visible').prop('disabled', false);
					}, 1000);
				}
			}
		});
	}
	return false;
});