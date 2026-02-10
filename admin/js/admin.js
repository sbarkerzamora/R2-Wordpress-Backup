/**
 * R2 Cloud Backup – Admin scripts
 *
 * @package R2_WordPress_Backup
 */

(function ($) {
	'use strict';

	var strings = (typeof r2wbAdmin !== 'undefined' && r2wbAdmin.strings) ? r2wbAdmin.strings : {};
	function s(key, fallback) {
		return strings[key] || fallback || key;
	}

	function getToastRoot() {
		var root = document.getElementById('r2wb-toast-root');
		if (!root) {
			root = document.createElement('div');
			root.id = 'r2wb-toast-root';
			root.className = 'r2wb-toast-root';
			root.setAttribute('aria-live', 'polite');
			document.body.appendChild(root);
		}
		return root;
	}

	/**
	 * Show a toast message (success, error, info). Auto-dismiss after 5s or on click.
	 */
	function showToast(type, message) {
		type = type || 'info';
		var root = getToastRoot();
		var toast = document.createElement('div');
		toast.className = 'r2wb-toast r2wb-toast--' + type;
		toast.textContent = message;
		toast.setAttribute('role', 'alert');
		root.appendChild(toast);

		var hide = function () {
			toast.style.opacity = '0';
			toast.style.transform = 'translateX(100%)';
			setTimeout(function () {
				if (toast.parentNode) {
					toast.parentNode.removeChild(toast);
				}
			}, 200);
		};

		toast.addEventListener('click', hide);
		setTimeout(hide, 5000);
	}

	/**
	 * Update a progress block with type (info, success, error) and message.
	 */
	function showProgress(selector, type, message) {
		var $el = $(selector);
		if (!$el.length) return;
		$el.show()
			.removeClass('r2wb-alert--error r2wb-alert--success error')
			.addClass('r2wb-alert r2wb-alert--info');
		if (type === 'error') {
			$el.addClass('r2wb-alert--error error').removeClass('r2wb-alert--info');
		} else if (type === 'success') {
			$el.addClass('r2wb-alert--success').removeClass('r2wb-alert--info');
		}
		$el.html(message);
	}

	$('.r2wb-start-backup').on('click', function () {
		var $btn = $(this);
		var $progress = $('.r2wb-backup-progress');
		$btn.prop('disabled', true);
		showProgress('.r2wb-backup-progress', 'info', s('startingBackup', 'Starting backup…'));

		$.post(r2wbAdmin.ajaxUrl, {
			action: 'r2wb_start_backup',
			nonce: r2wbAdmin.nonce
		}).done(function (res) {
			if (res.success) {
				var msg = (res.data && res.data.message) ? res.data.message : s('backupSuccess', 'Backup completed successfully.');
				showProgress('.r2wb-backup-progress', 'success', '<span class="r2wb-alert__title">' + msg + '</span>');
			} else {
				var msg = (res.data && res.data.message) ? res.data.message : s('backupFailed', 'Backup failed.');
				showProgress('.r2wb-backup-progress', 'error', '<span class="r2wb-alert__title">' + msg + '</span>');
			}
		}).fail(function () {
			showProgress('.r2wb-backup-progress', 'error', '<span class="r2wb-alert__title">' + s('requestFailed', 'Request failed.') + '</span>');
		}).always(function () {
			$btn.prop('disabled', false);
		});
	});

	$('.r2wb-test-connection').on('click', function () {
		var $btn = $(this);
		$btn.prop('disabled', true);
		$.post(r2wbAdmin.ajaxUrl, {
			action: 'r2wb_test_connection',
			nonce: r2wbAdmin.nonce
		}).done(function (res) {
			if (res.success) {
				showToast('success', (res.data && res.data.message) ? res.data.message : s('connectionOk', 'Connection successful.'));
			} else {
				showToast('error', (res.data && res.data.message) ? res.data.message : s('connectionFailed', 'Connection failed.'));
			}
		}).fail(function () {
			showToast('error', s('requestFailed', 'Request failed.'));
		}).always(function () {
			$btn.prop('disabled', false);
		});
	});

	$('.r2wb-reset-options').on('click', function () {
		if (!confirm(s('confirmReset', 'Reset plugin options and schedules? R2 credentials will be kept.'))) {
			return;
		}
		var $btn = $(this);
		$btn.prop('disabled', true);
		$.post(r2wbAdmin.ajaxUrl, {
			action: 'r2wb_reset_options',
			nonce: r2wbAdmin.nonce
		}).done(function (res) {
			if (res.success) {
				location.reload();
			} else {
				showToast('error', (res.data && res.data.message) ? res.data.message : s('resetFailed', 'Reset failed.'));
				$btn.prop('disabled', false);
			}
		}).fail(function () {
			showToast('error', s('requestFailed', 'Request failed.'));
			$btn.prop('disabled', false);
		});
	});

	$('.r2wb-delete-backup').on('click', function () {
		var key = $(this).data('key');
		if (!key || !confirm(s('confirmDelete', 'Delete this backup from R2? This cannot be undone.'))) {
			return;
		}
		var $row = $(this).closest('tr');
		$.post(r2wbAdmin.ajaxUrl, {
			action: 'r2wb_delete_backup',
			nonce: r2wbAdmin.nonce,
			key: key
		}).done(function (res) {
			if (res.success) {
				$row.fadeOut(function () { $(this).remove(); });
				showToast('success', (res.data && res.data.message) ? res.data.message : '');
			} else {
				showToast('error', (res.data && res.data.message) ? res.data.message : s('deleteFailed', 'Delete failed.'));
			}
		}).fail(function () {
			showToast('error', s('requestFailed', 'Request failed.'));
		});
	});

	$('.r2wb-restore-backup').on('click', function () {
		var key = $(this).data('key');
		if (!key || !confirm(s('confirmRestore', 'Restore this site from the selected backup? Current database and files will be replaced. This cannot be undone.'))) {
			return;
		}
		var $btn = $(this);
		var $progress = $('.r2wb-restore-progress');
		$btn.prop('disabled', true);
		showProgress('.r2wb-restore-progress', 'info', s('restoring', 'Restoring…'));

		$.post(r2wbAdmin.ajaxUrl, {
			action: 'r2wb_restore_backup',
			nonce: r2wbAdmin.nonce,
			key: key
		}).done(function (res) {
			if (res.success) {
				var msg = (res.data && res.data.message) ? res.data.message : s('restoreSuccess', 'Restore completed.');
				showProgress('.r2wb-restore-progress', 'success', '<span class="r2wb-alert__title">' + msg + '</span>');
			} else {
				var msg = (res.data && res.data.message) ? res.data.message : s('restoreFailed', 'Restore failed.');
				showProgress('.r2wb-restore-progress', 'error', '<span class="r2wb-alert__title">' + msg + '</span>');
			}
		}).fail(function () {
			showProgress('.r2wb-restore-progress', 'error', '<span class="r2wb-alert__title">' + s('requestFailed', 'Request failed.') + '</span>');
		}).always(function () {
			$btn.prop('disabled', false);
		});
	});

})(jQuery);
