<?php
/**
 * This file is a part of the Force Style Changes modification by
 * lax.slash for the phpBB 3.1 Forums Software.
 *
 * @copyright (c) lax.slash <https://www.github.com/LaxSlash>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace laxslash\ncrequests\acp;

class manage_requests_module
{
	public $u_action;
	public $tpl_name;
	public $page_title;

	public function main($id, $mode)
	{
		global $user, $template, $request, $config, $db, $phpbb_log, $phpbb_root_path, $phpEx, $phpbb_log, $auth, $phpbb_container;

		// Create the form key.
		add_form_key('laxslash/ncrequests');

		// If an action was submitted...
		if ($request->is_set_post('submit_action'))
		{
			if (!check_form_key('laxslash/ncrequests'))
			{
				$error[] = $user->lang('FORM_INVALID');
			}

			// Get data.
			$requested_action = $request->variable('requested_action', '');
			$action_reason = $request->variable('action_reason', '');
			$log_usernote = $request->variable('log_usernote', false);
			$incl_reason_in_notification = $request->variable('incl_reason_in_notification', false);
			$send_notification_to_user = $request->variable('send_notification_to_user', false);

			// Push the marked IDs into an array...
			$marked = $request->variable('mark', array(0));

			if (!sizeof($marked))
			{
				unset($marked); // Unset.
				// Error "You must select at least one name request!"
				$error[] = $user->lang('LAXSLASH_NCREQUESTS_ACP_ERR_MARK_ONE_REQUEST');
			}

			if (!sizeof($error))
			{
				// What was the action?
				switch ($requested_action)
				{
					case 'approve':
						// Verify auth.

						// Update each request in the database with the new info.

						// Perform verification for each one of the approved usernames.
						$sql_ary = array(
							'SELECT' => 'r.*, u.username, u.username_clean, u.user_id',
							'FROM' => array(
								LAXSLASH_NCREQUESTS_REQUESTS_TABLE => 'r',
								USERS_TABLE => 'u',
							),
							'WHERE' => $db->sql_in_set('request_id', $marked) . ' AND u.user_id = r.request_user_id AND r.request_status = ' . LAXSLASH_NCREQUESTS_REQUEST_STATUS_PENDING,
						);
						$sql = $db->sql_build_query('SELECT', $sql_ary);
						unset($sql_ary);
						$result = $db->sql_query($sql);
						$pending_changes = $db->sql_fetchrowset($result);

						$db->sql_freeresult($result);
						unset($marked);

						if (!sizeof($pending_changes))
						{
							unset($pending_changes);
							$error[] = $user->lang('LAXSLASH_NCREQUESTS_ACP_ERR_MARK_ONE_REQUEST');
						} else {
							if (!function_exists(validate_data))
							{
								include_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);
							}

							foreach ($pending_changes as $row)
							{
								if ($row['username_clean'] == $row['requested_username_clean'])
								{
									// Don't change requests that are the same as the users current name.
									$error[] = $user->lang('LAXSLASH_NCREQUESTS_ACP_APPROVE_ERROR_PRE', $row['requested_username'], $row['username']) . ' ' . $user->lang('LAXSLASH_NCREQUESTS_ACP_NAMES_MUST_BE_DIFFERENT');
								} else {
									$check_ary = array(
										'requested_username' => array(
											array('string', false, $config['min_name_chars'], $config['max_config_chars']),
											array('username'),
										),
									);
								}

								$pre_errors = validate_data($row, $check_ary);
								unset($check_ary);

								foreach ($pre_errors as $working_error)
								{
									$error[] = $user->lang('LAXSLASH_NCREQUESTS_ACP_APPROVE_ERROR_PRE', $row['requested_username'], $row['username']) . ' ' . $user->lang($working_error . '_USERNAME');
								}

								unset($pre_errors);
								unset($row);
							}
						}

						// Send notifications, and store the log entries.
						if (!sizeof($error))
						{
							// Create a couple of arrays here:
							$insert_arr = array();
							$acp_log_entry_arr = array();
							$update_ids = array();

							// Get the time now to minimize overhead on running it for each insertion.
							$current_time = time();

							foreach ($pending_changes as $row)
							{
								// But only if error free.
								if (!function_exists(user_update_name))
								{
									include_once($phpbb_root_path . 'includes/functions_user.' . $phpEx);
								}

								user_update_name($row['username'], $row['requested_username']);
								$sql_arr = array(
									'username' => $row['requested_username'],
									'username_clean' => $row['requested_username_clean'],
								);
								$sql = 'UPDATE ' . USERS_TABLE . '
										SET ' . $db->sql_build_array('UPDATE', $sql_arr) . '
										WHERE user_id = ' . $row['request_user_id'];
								$db->sql_query($sql);
								unset($sql_arr);

								// Add to master log entry INSERT array
								$insert_arr[] = array(
									'request_id' => $row['request_id'],
									'logging_user_id' => $user->data['user_id'],
									'request_user_id' => $row['request_user_id'],
									'username_old' => $row['username'],
									'username_new' => $row['requested_username'],
									'notification_sent' => ($send_notification_to_user) ? true : false,
									'reason_incl_in_notification' => ($incl_reason_in_notification) ? true : false,
									'usernote_logged' => ($log_usernote) ? true : false,
									'timestamp' => $current_time,
									'status_old' => $row['request_status'],
									'status_new' => LAXSLASH_NCREQUESTS_REQUEST_STATUS_APPROVED,
									'log_type' => LAXSLASH_NCREQUESTS_LOG_TYPE_APPROVAL,
									'action_reason' => $action_reason,
									'action_ip_address' => $user->ip,
								);

								// Add to the update_ids() array
								$update_ids[] = $row['request_id'];

								// Add to ACP log entry array
								$acp_log_entry_arr[] = $row['username'] . ' -> ' . $row['requested_username'];

								// If create user note selected, create the usernote now
								if ($log_usernote)
								{
									$phpbb_log->add('user', $user->data['user_id'], $user->ip, 'LAXSLASH_NCREQUESTS_ULOG_ACCEPTED_NCREQUEST', time(), array('reportee_id' => $row['user_id'], $row['username'], $row['requested_username']));
								}

								// Work on sending the notification to the user.
								if ($send_notification_to_user)
								{
									$notification_manager = $phpbb_container->get('notification_manager');
									$notify_data = array(
										'request_id' => $row['request_id'],
										'request_user_id' => $row['request_user_id'],
										'username_old' => $row['username'],
										'username_new' => $row['requested_username'],
										'approved_by' => $user->data['user_id'],
									);

									$notification_manager->add_notifications(array(
										'laxslash.ncrequests.notification.type.ncr_approval',
									), $notify_data);
								}

								unset($row);
							}

							// Done with this.
							unset($pending_changes);

							// Alright, here we go!
							$db->sql_multi_insert(LAXSLASH_NCREQUESTS_ADMINLOG_TABLE, $insert_arr);
							unset($insert_arr);

							$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LAXSLASH_NCREQUESTS_LOG_ADMIN_APPROVED_REQS', time(), array(implode(', ', $acp_log_entry_arr)));
							unset($acp_log_entry_arr);

							$sql_arr = array(
								'request_status' => LAXSLASH_NCREQUESTS_REQUEST_STATUS_APPROVED,
							);

							$sql = 'UPDATE ' . LAXSLASH_NCREQUESTS_REQUESTS_TABLE . '
									SET ' . $db->sql_build_array('UPDATE', $sql_arr) . '
									WHERE ' . $db->sql_in_set('request_id', $update_ids);
							$db->sql_query($sql);
							unset($update_ids);
							unset($sql_arr);

							// Send the notifications here, if applicable.

							// Sweet success!
							trigger_error($user->lang['LAXSLASH_NCREQUESTS_PENDING_REQUESTS_APPROVED_SUCCESSFULLY'] . adm_back_link($u_action));
						} else {
							// Welp, we don't need this anymore...
							unset($pending_changes);
						}
					break;
					case 'deny':
						// Verify auth.

						// Update each request in the database with the new info.

						// Creating arrays for updating the table, adding the administrator logs, and for inserting into the requests adminlog table.
						$insert_arr = array();
						$update_ids = array();
						$acp_log_entry_arr = array();

						$sql_ary = array(
							'SELECT' => 'r.*, u.username, u.user_id',
							'FROM' => array(
								LAXSLASH_NCREQUESTS_REQUESTS_TABLE => 'r',
								USERS_TABLE => 'u',
							),
							'WHERE' => $db->sql_in_set('request_id', $marked) . ' AND u.user_id = r.request_user_id',
						);
						unset($marked);
						$sql = $db->sql_build_query('SELECT', $sql_ary);
						unset($sql_ary);
						$result = $db->sql_query($sql);
						$pending = $db->sql_fetchrowset($result);
						$db->sql_freeresult($sql);

						// Get the time now to save on overhead.
						$current_time = time();

						foreach ($pending as $working_request)
						{
							// Add the ID to the update_ids() array.
							$update_ids[] = $working_request['request_id'];

							// And for the ACP log entries...
							$acp_log_entry_arr[] = $working_request['username'] . ' -> ' . $working_request['requested_username'];

							// And for the insert array as well now.
							$insert_arr[] = array(
								'request_id' => $working_request['request_id'],
								'logging_user_id' => $user->data['user_id'],
								'request_user_id' => $working_request['request_user_id'],
								'username_old' => $working_request['requested_username'],
								'notification_sent' => ($send_notification_to_user) ? true : false,
								'reason_incl_in_notification' => ($incl_reason_in_notification) ? true : false,
								'timestamp' => $current_time,
								'status_old' => $working_request['request_status'],
								'status_new' => LAXSLASH_NCREQUESTS_REQUEST_STATUS_DENIED,
								'log_type' => LAXSLASH_NCREQUESTS_LOG_TYPE_DENY,
								'action_reason' => $action_reason,
								'action_ip_address' => $user->ip,
							);

							if ($send_notification_to_user)
							{
								$notification_manager = $phpbb_container->get('notification_manager');
								$notify_data = array(
									'request_id' => $working_request['request_id'],
									'request_user_id' => $working_request['user_id'],
									'username_requested' => $working_request['requested_username'],
									'action_reason' => (incl_reason_in_notification) ? $action_reason : '',
									'denied_by' => $user->data['user_id'],
								);

								$notification_manager->add_notifications(array(
									'laxslash.ncrequests.notification.type.ncr_denial',
								), $notify_data);
							}

							unset($working_request);
						}

						unset($pending);

						$db->sql_multi_insert(LAXSLASH_NCREQUESTS_ADMINLOG_TABLE, $insert_arr);
						unset($insert_arr);

						$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LAXSLASH_NCREQUESTS_LOG_ADMIN_DENIED_REQS', time(), array(implode(' ,', $acp_log_entry_arr)));
						unset($acp_log_entry_arr);

						$sql_arr = array(
							'request_status' => LAXSLASH_NCREQUESTS_REQUEST_STATUS_DENIED,
						);

						$sql = 'UPDATE ' . LAXSLASH_NCREQUESTS_REQUESTS_TABLE . '
								SET ' . $db->sql_build_array('UPDATE', $sql_arr) . '
								WHERE ' . $db->sql_in_set('request_id', $update_ids);
						unset($sql_arr);
						unset($update_ids);
						$db->sql_query($sql);

						// Send notifications, and store the log entries.

						// Success.
						trigger_error($user->lang['LAXSLASH_NCREQUESTS_PENDING_REQUESTS_DENIED_SUCCESSFULLY'] . adm_back_link($u_action));
					break;
					case 'edit':
						// Only if there's edit perms to the user.
						if (!$auth->acl_get('a_laxslash_ncrequests_edit_name_change_requests'))
						{
							$error[] = $user->lang('LAXSLASH_NCREQUESTS_NO_PERMISSION_TO_EDIT');
						} else {
							// So the user does have permission then? Great. Let's get started.

							// Get anything that's still pending, and is marked. If the marked requests are no longer pending, throw a "Must Select One Pending Request" error here.
							$sql_ary = array(
								'SELECT' => 'u.username, r.*',
								'FROM' => array(
									USERS_TABLE => 'u',
									LAXSLASH_NCREQUESTS_REQUESTS_TABLE => 'r',
								),
								'WHERE' => $db->sql_in_set('request_id', $marked) . ' AND r.request_status = ' . LAXSLASH_NCREQUESTS_REQUEST_STATUS_PENDING . ' AND u.user_id = r.request_user_id',
							);
							$sql = $db->sql_build_query('SELECT', $sql_ary);
							$result = $db->sql_query($sql);
							$pending_changes = $db->sql_fetchrowset($result);
							$db->sql_freeresult($result);
							unset($sql_ary);
							unset($marked);

							if (!sizeof($pending_changes))
							{
								unset($pending_changes); // No changes? Unset anyways.
								$error[] = $user->lang('LAXSLASH_NCREQUESTS_ACP_ERR_MARK_ONE_REQUEST');
							} else {
								// First and foremost, error check each change.
								foreach ($pending_changes as $working_change)
								{
									$new_username = utf8_normalize_nfc(request_var('edit_request_' . $working_change['request_id'], '', true));
									$new_username_clean = utf8_clean_string($new_username);
									if ($new_username == $working_change['requested_username'])
									{
										$error[] = $user->lang('LAXSLASH_NCREQUESTS_ACP_EDIT_ERROR_PRE', $working_change['username']) . ' ' . $user->lang('LAXSLASH_NCREQUESTS_MUST_CHANGE_MARKED_REQUESTS');
									} elseif ($new_username_clean == $user->data['username_clean'])
									{
										$error[] = $user->lang('LAXSLASH_NCREQUESTS_ACP_EDIT_ERROR_PRE', $working_change['username']) . ' ' . $user->lang('LAXSLASH_NCREQUESTS_ACP_NAMES_MUST_BE_DIFFERENT');
									} else {
										$working_change['new_username'] = $new_username;
										// String validation.
										$pre_errors = array();
										$check_ary = array(
											'new_username' => array(
												array('string', false, $config['min_name_chars'], $config['max_name_chars']),
												array('username'),
											),
										);

										if (!function_exists('validate_data'))
										{
											include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
										}

										$pre_errors = validate_data($working_change, $check_ary);
										unset($check_ary);
										if (sizeof($pre_errors))
										{
											// Nope, bad change.
											// Nested foreach statements... is this a bad idea? Or okay?
											foreach ($pre_errors as $current_error)
											{
												$error[] = $user->lang('LAXSLASH_NCREQUESTS_ACP_EDIT_ERROR_PRE', $working_change['username']) . ' ' . $user->lang($current_error . '_USERNAME');
												unset($current_error);
											}
										} else {
											unset($pre_errors);
										}

										// Always unset this.
										unset($working_change);
									}

									if (empty($error))
									{
										// We're good, so... Make some arrays here.
										$insert_arr = array();
										$acp_log_entry_arr = array();

										// Get the current time now.
										$current_time = time();

										foreach ($pending_changes as $current_change)
										{
											$new_username = utf8_normalize_nfc(request_var('edit_request_' . $current_change['request_id'], '', true));
											$new_username_clean = utf8_clean_string($new_username);

											// Add to the insert_arr() for the Admin Log Entries.
											$insert_arr[] = array(
												'request_id' => $current_change['request_id'],
												'logging_user_id' => $user->data['user_id'],
												'request_user_id' => $current_change['request_user_id'],
												'username_old' => $current_change['requested_username'],
												'username_new' => $new_username,
												'notification_sent' => ($send_notification_to_user) ? true : false,
												'reason_incl_in_notification' => ($incl_reason_in_notification) ? true : false,
												'timestamp' => $current_time,
												'log_type' => LAXSLASH_NCREQUESTS_LOG_TYPE_EDIT,
												'action_reason' => $action_reason,
												'action_ip_address' => $user->ip,
											);

											// Update now. Find out if there's a way to mass update with multiple different variables.
											$sql_ary = array(
												'requested_username' => $db->sql_escape($new_username),
												'requested_username_clean' => $db->sql_escape($new_username_clean),
											);
											$sql = 'UPDATE ' . LAXSLASH_NCREQUESTS_REQUESTS_TABLE . '
													SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
													WHERE request_id = ' . $current_change['request_id'];
											$db->sql_query($sql);
											unset($sql_ary);

											// Add this edit to the Array for the ACP Log Entry for this update.
											$acp_log_entry_arr[] = $current_change['username'] . $user->lang('COLON') . ' ' . $current_change['requested_username'] . ' -> ' . $new_username;

											// Go for notifications.
											if ($send_notification_to_user)
											{
												$notification_manager = $phpbb_container->get('notification_manager');

												$notify_data = array(
													'request_id' => $current_change['request_id'],
													'request_user_id' => $current_change['request_user_id'],
													'old_request_username' => $current_change['requested_username'],
													'new_request_username' => $new_username,
													'action_reason' => ($incl_reason_in_notification) ? $action_reason : '',
													'edited_by' => $user->data['user_id'],
												);

												$notification_manager->add_notifications(array(
													'laxslash.ncrequests.notification.type.ncr_edit',
												), $notify_data);
											}
											// Unset.
											unset($current_change);
										}

										unset($pending_changes);

										// Insert here.
										$db->sql_multi_insert(LAXSLASH_NCREQUESTS_ADMINLOG_TABLE, $insert_arr);
										unset($insert_arr);

										// Admin log entry here.
										$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'LAXSLASH_NCREQUESTS_LOG_ADMIN_EDITED_REQS', time(), array(implode(', ', $acp_log_entry_arr)));
										unset($acp_log_entry_arr);

										unset($error);

										// Trigger an error.
										trigger_error($user->lang['LAXSLASH_NCREQUESTS_PENDING_REQUESTS_EDITED_SUCCESSFULLY'] . adm_back_link($u_action));
									} else {
										// Just unset everything here.
										unset($pending_changes);
									}
								}
							}
						}
					break;
					default:
					break;
				}
			}
		}

		// Create a table of all pending name change requests.
		// Do a join where the current username is taken from the users table for the requesting ID.
		$sql_ary = array(
			'SELECT' => 'r.*, u.username, u.user_colour',
			'FROM' => array(
				USERS_TABLE => 'u',
				LAXSLASH_NCREQUESTS_REQUESTS_TABLE => 'r',
			),
			'WHERE' => 'u.user_id = r.request_user_id AND (request_status = ' . LAXSLASH_NCREQUESTS_REQUEST_STATUS_PENDING . ' OR request_status = ' . LAXSLASH_NCREQUESTS_REQUEST_STATUS_APPROVED . ')',
			'ORDER_BY' => 'request_timestamp DESC',
		);

		$sql = $db->sql_build_query('SELECT', $sql_ary);
		unset($sql_ary); // Unset what's not needed any more...
		$result = $db->sql_query($sql);

		$recent_approvals = array(); // Define an array to hold the most recent approval dates.
		$approved = array();
		$pending = array();

		while ($row = $db->sql_fetchrow($result))
		{
			// Seperate into two arrays. Pending, and approved.
			if ($row['request_status'] == LAXSLASH_NCREQUESTS_REQUEST_STATUS_APPROVED && !isset($approved[$row['request_user_id']]))
			{
				$approved[$row['request_user_id']] = $row;
			} elseif ($row['request_status'] == LAXSLASH_NCREQUESTS_REQUEST_STATUS_PENDING) {
				$pending[$row['request_user_id']] = $row;
			}

			unset($row);
		}

		// Remove entries in the approved array for where there is no pending request.
		// Replace this section with array-intersect_key on a new array... only proceed if $approved has rows, otherwise unset and do nothing.
		foreach ($approved as $current_check)
		{
			if (!isset($pending[$current_check['request_user_id']]))
			{
				unset($approved[$current_check['request_user_id']]);
			}

			unset($current_check);
		}

		foreach ($approved as $row)
		{
			if (!isset($recent_approvals[$row['request_user_id']]))
			{
				// Run a query for the log entry of that request ID, and get its timestamp.
				$sql = 'SELECT timestamp
						FROM ' . LAXSLASH_NCREQUESTS_ADMINLOG_TABLE . '
						WHERE request_id = ' . $row['request_id'] . ' AND log_type = ' . LAXSLASH_NCREQUESTS_LOG_TYPE_APPROVAL;
				$approved_result = $db->sql_query($sql);
				$approved_row = $db->sql_fetchrow($approved_result);
				$db->sql_freeresult($approved_result);
				$recent_approvals[$row['request_user_id']] = $approved_row['timestamp'];
				unset($approved_row);
			}

			unset($row);
		}

		unset($approved);

		if (!empty($pending))
		{
			// Can we do this a different way/a cleaner way other than in the loop? Maybe with fetch_rowset($result) and an empty() check on that array?
			$template->assign_var('S_LAXSLASH_NCREQUESTS_REQUESTS_FOUND', true);

			foreach ($pending as $row)
			{
				$template->assign_block_vars('show_requests', array(
					'LAXSLASH_NCREQUESTS_REQUESTED_USERNAME' => $row['requested_username'],
					'LAXSLASH_NCREQUESTS_USERNAME_CURRENT_COLOR' => $row['user_colour'],
					'LAXSLASH_NCREQUESTS_REQUESTED_BY_USERNAME' => $row['username'],
					'LAXSLASH_NCREQUESTS_REQUEST_SUBMITTED_TIMESTAMP' => $user->format_date($row['request_timestamp']),
					'LAXSLASH_NCREQUESTS_REQUEST_SUBMITTED_IPADDRESS' => $row['request_from_ip_address'],
					'LAXSLASH_NCREQUESTS_REQUEST_PREV_REQ_APPROVED_DATE' => (isset($recent_approvals[$row['request_user_id']])) ? $user->format_date($recent_approvals[$row['request_user_id']]) : '-',
					'LAXSLASH_NCREQUESTS_REQUEST_ID' => $row['request_id'],
				));

			unset($row);

			}
		}

		unset($pending);

		unset($recent_approvals);

		$options_set = array(
			'approve' => 'LAXSLASH_NCREQUESTS_REQUEST_OPTION_APPROVE',
			'deny' => 'LAXSLASH_NCREQUESTS_REQUEST_OPTION_DENY',
		);
		if($auth->acl_get('a_laxslash_ncrequests_edit_name_change_requests'))
		{
			$options_set += array('edit' => 'LAXSLASH_NCREQUESTS_REQUEST_OPTION_EDIT');
		}

		// Generic template vars get setup here
		$template->assign_vars(array(
			'U_ACTION' => $this->u_action,
			'S_LAXSLASH_NCREQUESTS_MANAGE_OPTIONS' => build_select($options_set),
			'S_ERRORS' => (sizeof($error)) ? true : false,
			'ERRORS_OUTPUT' => (sizeof($error)) ? implode('<br />', $error) : '',
			'LAXSLASH_NCREQUESTS_ACTION_REASON' => (isset($action_reason)) ? $action_reason : '',
			'LAXSLASH_NCREQUESTS_LOG_USERNOTE' => ($log_usernote) ? true : false,
			'LAXSLASH_NCREQUESTS_INCL_REASON' => ($incl_reason_in_notification) ? true : false,
			'LAXSLASH_NCREQUESTS_SEND_NOTIFICATION' => ($send_notification_to_user) ? true : false,
			'S_LAXSLASH_NCREQUESTS_CAN_EDIT_REQUESTS' => ($auth->acl_get('a_laxslash_ncrequests_edit_name_change_requests')) ? true : false,

		));

		unset($row);
		unset($error);

		$db->sql_freeresult($result);

		// Create the template file here.
		$this->tpl_name = 'manage_requests_acp';
	}
}
