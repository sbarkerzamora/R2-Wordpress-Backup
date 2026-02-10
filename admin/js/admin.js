/**
 * R2 WordPress Backup – Admin scripts
 *
 * @package R2_WordPress_Backup
 */

(function ($) {
	'use strict';

	$('.r2wb-start-backup').on('click', function () {
		var $btn = $(this);
		var $progress = $('.r2wb-backup-progress');
		$btn.prop('disabled', true);
		$progress.show().removeClass('error').text('Starting backup…');

		$.post(r2wbAdmin.ajaxUrl, {
			action: 'r2wb_start_backup',
			nonce: r2wbAdmin.nonce
		}).done(function (res) {
			if (res.success) {
				$progress.removeClass('error').html('<strong>' + (res.data && res.data.message ? res.data.message : 'Backup completed.') + '</strong>');
			} else {
				$progress.addClass('error').html('<strong>Error:</strong> ' + (res.data && res.data.message ? res.data.message : 'Backup failed.'));
			}
		}).fail(function () {
			$progress.addClass('error').html('<strong>Error:</strong> Request failed.');
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
				alert(res.data && res.data.message ? res.data.message : 'Connection successful.');
			} else {
				alert('Error: ' + (res.data && res.data.message ? res.data.message : 'Connection failed.'));
			}
		}).fail(function () {
			alert('Request failed.');
		}).always(function () {
			$btn.prop('disabled', false);
		});
	});

	$('.r2wb-reset-options').on('click', function () {
		if (!confirm('Reset plugin options and schedules? R2 credentials will be kept.')) {
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
				alert('Error: ' + (res.data && res.data.message ? res.data.message : 'Reset failed.'));
				$btn.prop('disabled', false);
			}
		}).fail(function () {
			alert('Request failed.');
			$btn.prop('disabled', false);
		});
	});

	$('.r2wb-delete-backup').on('click', function () {
		var key = $(this).data('key');
		if (!key || !confirm('Delete this backup from R2? This cannot be undone.')) {
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
			} else {
				alert('Error: ' + (res.data && res.data.message ? res.data.message : 'Delete failed.'));
			}
		}).fail(function () {
			alert('Request failed.');
		});
	});

	$('.r2wb-restore-backup').on('click', function () {
		var key = $(this).data('key');
		if (!key || !confirm('Restore this site from the selected backup? Current database and files will be replaced. This cannot be undone.')) {
			return;
		}
		var $btn = $(this);
		var $progress = $('.r2wb-restore-progress');
		$btn.prop('disabled', true);
		$progress.show().text('Restoring…');
		$.post(r2wbAdmin.ajaxUrl, {
			action: 'r2wb_restore_backup',
			nonce: r2wbAdmin.nonce,
			key: key
		}).done(function (res) {
			if (res.success) {
				$progress.html('<strong>' + (res.data && res.data.message ? res.data.message : 'Restore completed.') + '</strong>');
			} else {
				$progress.html('<span class="error">Error: ' + (res.data && res.data.message ? res.data.message : 'Restore failed.') + '</span>');
			}
		}).fail(function () {
			$progress.html('<span class="error">Request failed.</span>');
		}).always(function () {
			$btn.prop('disabled', false);
		});
	});

})(jQuery);
