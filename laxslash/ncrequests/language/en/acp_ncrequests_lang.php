<?php
/**
 * This file is a part of the Force Style Changes modification by
 * lax.slash for the phpBB 3.1 Forums Software.
 *
 * @copyright (c) lax.slash <https://www.github.com/LaxSlash>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit();
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
	'ACP_LAXSLASH_NCREQUESTS_MANAGE_MODULE_TITLE' => 'Manage name change requests',
	'ACL_U_LAXSLASH_NCREQUESTS_SUBMIT_NCR' => 'Can request a name change',
	'UCP_LAXSLASH_NCREQUESTS_REQUEST_CHANGE_MODULE' => 'Request a username change',
	'UCP_LAXSLASH_NCREQUESTS_EXPLAIN_REQUEST_NAMECHANGE' => 'You can use this form to request a username change from the Board Administrators. Your request will be submitted, and will then be either approved, denied, or altered by an administrator. You will need to login using your new username if approved. Note that you can not request a username that is either already pending in another members request, or is already in use by another member.',
	'UCP_LAXSLASH_NCREQUESTS_NEW_USERNAME' => 'New username requested',
	'UCP_LAXSLASH_NCREQUESTS_VPW_EXPLAIN' => 'In order to request a new username change, you must also verify your password.',
	'UCP_LAXSLASH_NCREQUESTS_CANCEL_NAMECHANGE_REQUEST' => 'Cancel Request',
	'UCP_LAXSLASH_NCREQUESTS_UNABLE_TO_CHANGE_REQUEST_OPEN' => 'You are currently unable to request a new username, as you already have a request open. Your current request is displayed below. In order to cancel it, you may use the "Cancel Request" button. You will then be able to submit a new request.',
	'UCP_LAXSLASH_NCREQUESTS_CURRENT_REQUEST_LBL' => 'Current open username request',
	'UCP_LAXSLASH_NCREQUESTS_VPW_EXPLAIN_FOR_REVOKE' => 'In order to cancel your username change request, you must also verify your password.',
	'UCP_LAXSLASH_NCREQUESTS_BROWSER_TITLE' => 'Request Username Change',
	'LAXSLASH_NCREQUESTS_UCP_REQUEST_CANCELLED_SUCCESS' => 'The request has been cancelled successfully.',
	'LAXSLASH_NCREQUESTS_ERROR_NO_RECORDS_DELETED' => 'Your request was not cancelled, as there is not an outstanding request for you in the database.',
	'LAXSLASH_NCREQUESTS_UCP_REQUEST_SUBMITTED_SUCCESS' => 'Your request has been successfully submitted.',
	'LAXSLASH_NCREQUESTS_UCP_ERR_ENTER_NEW_NAME_REQ' => 'You must enter a new username to request.',
	'LAXSLASH_NCREQUESTS_UCP_ERR_CANT_REQUEST_OWN_NAME' => 'You are unable to submit a request for your own username.',
	'LAXSLASH_NCREQUESTS_NAME_REQUEST_ALREADY_EXISTS' => 'A username request already exists for you. You must cancel that one before submitting another.',
	'LAXSLASH_NCREQUESTS_UCP_ERR_REQUEST_FOR_NAME_ALREADY_EXISTS' => 'A request for this username is already pending, please select an alternative',
	'LAXSLASH_NCREQUESTS_OPEN_REQUESTS_ALERT' => array(
		1 => 'There is currently %d pending username change request.',
		2 => 'There are currently %d pending username change requests.',
	),
	'LAXSLASH_NCREQUESTS_CLICK_TO_VIEW' => 'Click here to view.',
	'LAXSLASH_NCREQUESTS_LOG_USER_REQUESTED_CHANGE' => '<strong>Requested a username change</strong><br />» from "%1$s" to "%2$s"',
	'LAXSLASH_NCREQUESTS_LOG_USER_CANCELLED_CHANGE' => '<strong>Cancelled a username change request</strong><br />» from "%1$s" to "%2$s"',
	'LAXSLASH_NCREQUESTS_ACP_MANAGE_REQUESTS' => 'Manage username change requests',
	'LAXSLASH_NCREQUESTS_ACP_MANAGE_REQUESTS_EXPLAIN' => 'You can use this panel to approve, deny and edit existing username change requests from your users.',
	'LAXSLASH_NCREQUESTS_REQUESTED_BY_USERNAME' => 'Requested By',
	'LAXSLASH_NCREQUESTS_REQUESTED_USERNAME' => 'Requested Username',
	'LAXSLASH_NCREQUESTS_DATE_OF_REQUEST' => 'Date Request Submitted',
	'LAXSLASH_NCREQUESTS_IP_ADDRESS_OF_REQUEST' => 'IP Address',
	'LAXSLASH_NCREQUESTS_PREVIOUS_REQUEST_APPROVED_DATE' => 'Previous Request Approval Date',
	'LAXSLASH_NCREQUESTS_REQUEST_OPTION_APPROVE' => 'Approve',
	'LAXSLASH_NCREQUESTS_REQUEST_OPTION_DENY' => 'Deny',
	'LAXSLASH_NCREQUESTS_ACP_APPROVE_ERROR_PRE' => 'The request for the username "%1$s" from "%2$s" caused the following error:',
	'LAXSLASH_NCREQUESTS_ACP_PERFORM_ACTION_OPTIONS' => 'Options',
	'LAXSLASH_NCREQUESTS_ENTER_ACTION_REASON' => 'Action reason',
	'LAXSLASH_NCREQUESTS_ENTER_ACTION_REASON_EXPLAIN' => 'Enter a reason for the action being performed.',
	'LAXSLASH_NCREQUESTS_LOG_IN_USER_NOTES' => 'Record user note',
	'LAXSLASH_NCREQUESTS_LOG_IN_USER_NOTES_EXPLAIN' => 'Record a user note regarding the name change on the users profile. Only takes effect if the selected action is to approve the request(s).',
	'LAXSLASH_NCREQUESTS_INCLUDE_REASON_IN_NOTIFICATION' => 'Show reason in user notification',
	'LAXSLASH_NCREQUESTS_INCLUDE_REASON_IN_NOTIFICATION_EXPLAIN' => 'Include the reason in the received notification for the requesting user. Only takes effect if the "Send notification" option is selected.',
	'LAXSLASH_NCREQUESTS_SEND_NOTIFICATION' => 'Send notification',
	'LAXSLASH_NCREQUESTS_SEND_NOTIFICATION_EXPLAIN' => 'If selected, this will cause the requesting user(s) to receive a notification regarding this action.',
	'LAXSLASH_NCREQUESTS_PENDING_REQUESTS_APPROVED_SUCCESSFULLY' => 'The selected pending username change requests have been approved successfully.',
	'LAXSLASH_NCREQUESTS_ACP_NO_ACTIVE_REQUESTS' => 'There are currently no active requests.',
	'LAXSLASH_NCREQUESTS_ACP_NAMES_MUST_BE_DIFFERENT' => 'The requested name must not match the target users current name.',
	'LAXSLASH_NCREQUESTS_LOG_ADMIN_APPROVED_REQS' => '<strong>Approved</strong> the following name change requests:<br />» %1$s',
	'LAXSLASH_NCREQUESTS_LOG_ADMIN_DENIED_REQS' => '<strong>Denied</strong> the following name change requests:<br />» %1$s',
	'LAXSLASH_NCREQUESTS_PENDING_REQUESTS_DENIED_SUCCESSFULLY' => 'The selected pending name change requests were successfully denied.',
	'ACL_A_LAXSLASH_NCREQUESTS_MANAGE_NAME_CHANGE_REQUESTS' => 'Manage username change requests',
	'ACL_A_LAXSLASH_NCREQUESTS_EDIT_NAME_CHANGE_REQUESTS' => 'Edit username change requests',
	'LAXSLASH_NCREQUESTS_ACP_ERR_MARK_ONE_REQUEST' => 'You must mark at least one request to be managed.',
	'LAXSLASH_NCREQUESTS_NAME_CHANGES_DISABLED' => 'Requests may not be submitted or cancelled, as this board has name changes turned off.',
	'LAXSLASH_NCREQUESTS_REQUEST_OPTION_EDIT' => 'Edit',
	'LAXSLASH_NCREQUESTS_NO_PERMISSION_TO_EDIT' => 'You can not do that, as you do not have permission to edit username change requests.',
	'LAXSLASH_NCREQUESTS_LOG_ADMIN_EDITED_REQS' => '<strong>Edited</strong> the following name change requests:<br />» %1$s',
	'LAXSLASH_NCREQUESTS_ACP_EDIT_ERROR_PRE' => 'The following error occured trying to edit the username change request for %1$s:',
	'LAXSLASH_NCREQUESTS_MUST_CHANGE_MARKED_REQUESTS' => 'Requests that are selected must have a different request username.',
	'LAXSLASH_NCREQUESTS_PENDING_REQUESTS_EDITED_SUCCESSFULLY' => 'The selected pending username change requests have been edited successfully.',
	'LAXSLASH_NCREQUESTS_ULOG_ACCEPTED_NCREQUEST' => '<strong>Approved</strong> a pending name change request:<br />» from "%1$s" to "%2$s"',
	'LAXSLASH_NCREQUESTS_NOTIFICATION_TYPE_NCR_APPROVE_OPTION' => 'Your username change request is approved',
	'LAXSLASH_NCREQUESTS_NOTIFICATION_TYPE_NCR_APPROVE' => 'Your username change request has been approved.',
	'LAXSLASH_NCREQUESTS_NOTIFICATION_TYPE_NCR_APPROVE_TEXT' => '» "%1$s" to "%2$s"<br /><strong>Approved by:</strong> %3$s',
	'LAXSLASH_NCREQUESTS_NOTIFICATION_TYPE_NCR_DENY_OPTION' => 'Your username change request is denied',
	'LAXSLASH_NCREQUESTS_NOTIFICATION_TYPE_NCR_DENY' => 'Your username change request has been denied.',
	'LAXSLASH_NCREQUESTS_NOTIFICATION_TYPE_NCR_DENY_TEXT' => '» "%1$s" <br /><strong>Denied by:</strong> %2$s',
	'LAXSLASH_NCREQUESTS_NOTIFICATION_TYPE_NCR_DENY_TEXT_W_REASON' => '» "%1$s"<br /><strong>Denied by:</strong> %2$s<br /><strong>Reason:</strong> %3$s',
	'LAXSLASH_NCREQUESTS_NOTIFICATION_TYPE_NCR_EDIT_OPTION' => 'Your username request is edited',
	'LAXSLASH_NCREQUESTS_NOTIFICATION_TYPE_NCR_EDIT' => 'Your username request has been edited.',
	'LAXSLASH_NCREQUESTS_NOTIFICATION_TYPE_NCR_EDIT_TEXT' => '» "%1$s" -> "%2$s"<br /><strong>Edited by:</strong> %3$s',
	'LAXSLASH_NCREQUESTS_NOTIFICATION_TYPE_NCR_EDIT_TEXT_W_REASON' => '» "%1$s" -> "%2$s"<br /><strong>Edited by:</strong> %3$s<br /><strong>Reason:</strong> %4$s',
));