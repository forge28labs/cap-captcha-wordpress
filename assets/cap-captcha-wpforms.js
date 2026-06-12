(function () {
	var i18n = typeof capCaptchaWpforms !== 'undefined' && capCaptchaWpforms.msg
		? capCaptchaWpforms.msg
		: 'Please complete the captcha.';

	function getRow(widget) {
		return widget.closest('.wpforms-field-row');
	}

	function getContainer(widget) {
		return widget.closest('.wpforms-field');
	}

	function getFieldId(container) {
		var m = container.id && container.id.match(/wpforms-(\d+)-field_(\d+)/);
		return m ? m[2] : null;
	}

	function showError(widget) {
		var c = getContainer(widget);
		if (!c) return;
		c.classList.add('wpforms-has-error');

		widget.style.setProperty('--cap-border-color', '#f04438');

		var fid = getFieldId(c);
		if (fid && !c.querySelector('.wpforms-error')) {
			var err = document.createElement('div');
			err.className = 'wpforms-error';
			err.id = 'wpforms-field_' + fid + '-error';
			err.textContent = i18n;

			var row = getRow(widget);
			if (row && row.nextSibling) {
				row.parentNode.insertBefore(err, row.nextSibling);
			} else if (row) {
				row.parentNode.appendChild(err);
			} else {
				c.appendChild(err);
			}
		}
	}

	function clearError(widget) {
		var c = getContainer(widget);
		if (!c) return;
		c.classList.remove('wpforms-has-error');

		widget.style.removeProperty('--cap-border-color');

		var err = c.querySelector('.wpforms-error');
		if (err) err.remove();
	}

	document.addEventListener('submit', function (e) {
		var form = e.target;
		if (!form.classList.contains('wpforms-form')) return;

		var blocked = false;
		form.querySelectorAll('cap-widget').forEach(function (w) {
			if (!w.token) {
				showError(w);
				blocked = true;
			}
		});

		if (blocked) {
			e.preventDefault();
			e.stopPropagation();

			var first = form.querySelector('.wpforms-has-error');
			if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
		}
	}, true);

	document.addEventListener('solve', function (e) {
		var widget = e.target;
		if (widget.tagName !== 'CAP-WIDGET') return;
		clearError(widget);
	}, true);

	document.addEventListener('wpformsAjaxSubmitFailed', function () {
		document.querySelectorAll('cap-widget').forEach(function (w) { w.reset(); });
	});
})();
