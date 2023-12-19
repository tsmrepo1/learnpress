if (typeof TrustindexJsLoaded === 'undefined') {
	var TrustindexJsLoaded = {};
}

TrustindexJsLoaded.unique = true;

jQuery(document).ready(function($) {
	$('.btn-connect-public').click(function(event) {
		event.preventDefault();

		let button = $(this);
		let token = $('#ti-noreg-connect-token').val();

		button.addClass('ti-btn-loading').blur();

		let dontRemoveLoading = false;

		// get url params
		let params = new URLSearchParams({
			type: 'Google',
			referrer: 'public',
			webhook_url: $('#ti-noreg-webhook-url').val(),
			email: $('#ti-noreg-email').val(),
			token: token,
			version: $('#ti-noreg-version').val()
		});

		let tiWindow = window.open('https://admin.trustindex.io/source/edit2?' + params.toString(), 'trustindex', 'width=850,height=850,menubar=0' + popupCenter(850, 850));

		window.addEventListener('message', function(event) {
			if (event.origin.startsWith('https://admin.trustindex.io/'.replace(/\/$/,'')) && event.data.id) {
				dontRemoveLoading = true;

				tiWindow.close();
				$('#ti-connect-info').removeClass('ti-d-none');

				$('#ti-noreg-page-details').val(JSON.stringify(event.data));
				$('#ti-noreg-review-download').val(token);

				button.closest('form').submit();
			}
		});

		$('#ti-connect-info').addClass('ti-d-none');
		let timer = setInterval(function() {
			if (tiWindow.closed) {
				$('#ti-connect-info').removeClass('ti-d-none');

				if (!dontRemoveLoading) {
					button.removeClass('ti-btn-loading');
				}

				clearInterval(timer);
			}
		}, 1000);
	});

	// try reply again
	jQuery(document).on('click', '.btn-try-reply-again', function(event) {
		event.preventDefault();

		let btn = jQuery(this);
		let replyBox = btn.closest('td').find('.ti-reply-box');

		replyBox.attr('data-state', btn.data('state'));
		replyBox.find('.state-'+ btn.data('state') +' .btn-post-reply').attr('data-reconnect', 1).trigger('click');
	});
});