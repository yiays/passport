let redirectParams = { url: null, includeToken: false, includeProfile: false };

function doRedirect(token, profile) {
	let target = redirectParams.url;
	if(redirectParams.includeProfile || redirectParams.includeToken) {
		target += '?';
		if(redirectParams.includeToken)
			target += 'token=' + token + '&';
		if(redirectParams.includeProfile)
			target += 'profile=' + encodeURIComponent(JSON.stringify(profile));
	}
	window.location = target;
}

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
	const tileContent = form.parentElement;
	if($(form).find('input[type=submit]').prop('disabled'))
		return;
	const formData = new FormData(form);
	$(tileContent).find('.success,.error').remove();
	$(tileContent).find('input').prop('disabled', true);
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
					$(tileContent).append(`<span class=success>${message}</span>`);
					if(data.token) {
						passport_storeToken(data.token);
						passport_storeProfile(data.profile);
						if(redirectParams.url) {
							doRedirect(data.token, data.profile);
						}
					}else{
						$(form).hide();
						$(`#${form.id}-stage2`).show();
						$(`#${form.id}-stage2 input`).prop('disabled', false);
					}
				}
				else {
					$(tileContent).append(`<span class=error>${data.message}</span>`);
					setTimeout(() => {
						$(tileContent).find('input').filter(':visible').prop('disabled', false);
					}, 1000);
				}
			}
		});
	}
	return false;
});