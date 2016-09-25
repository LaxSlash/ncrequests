<?php
/**
 * This file is a part of the Name Change Requests
 * phpBB 3.1 Extension by LaxSlash1993.
 *
 * @copyright (c) LaxSlash1993 <https://www.github.com/LaxSlash>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace laxslash\ncrequests\ucp;

class request_namechange_module
{
	public $u_action;
	public $tpl_name;
	public $page_title;

	public function main($id, $mode)
	{
		global $user, $template, $request, $config, $phpbb_container, $phpbb_root_path, $phpEx, $db, $phpbb_log;

		$action = $request->variable('action', '');

		$template->assign_var('S_UCP_ACTION', $this->u_action);

		switch ($action)
		{
			case 'view_log_for_request':
				// Get the desired ID here.
				$request_id = $request->variable('request_id', 0);

				$this->tpl_name = 'request_viewlog_ucp_template';

				$this->page_title = $user->lang('UCP_LAXSLASH_NCREQUESTS_BROWSER_TITLE');

				// Get all of the needeed data here.
				$sql_ary = array(
					'SELECT' => 'r.*, l.*',
					'FROM' => array(
						LAXSLASH_NCREQUESTS_REQUESTS_TABLE => 'r',
						LAXSLASH_NCREQUESTS_ADMINLOG_TABLE => 'l',
					),
					'WHERE' => 'r.request_id = ' . $request_id . ' AND r.request_user_id = ' . $user->data['user_id'] . ' AND l.request_id = r.request_id',
					'ORDER_BY' => 'timestamp ASC',
				);
				$sql = $db->sql_build_query('SELECT', $sql_ary);
				$result = $db->sql_query($sql);
				$rowset = $db->sql_fetchrowset($result);
				$db->sql_freeresult($result);
				unset($sql_ary);

				if(empty($rowset))
				{
					trigger_error($user->lang('LAXSLASH_NCREQUESTS_UCP_NO_LOG_ENTRIES_FOUND') . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>'), E_USER_WARNING);
				}

				// Rowset's not empty, load all of the User IDs needed for this entry.
				$user_ids_pre_load = array();

				// Since all request User IDs for each entry should be this user, load this user only for request_ids.
				$user_ids_pre_load[] = $user->data['user_id'];

				foreach ($rowset as $row)
				{
					if ($row['request_user_id'] != $row['logging_user_id'])
					{
						$user_ids_pre_load[] = (int) $row['logging_user_id'];
					}
					unset($row);
				}

				// Run array_unique() here
				$user_ids_to_load = array();
				$user_ids_to_load = array_unique($user_ids_pre_load);

				unset($user_ids_pre_load);

				$sql = 'SELECT *
						FROM ' . USERS_TABLE . '
						WHERE ' . $db->sql_in_set('user_id', $user_ids_to_load);
				$result = $db->sql_query($sql);
				$users_rowset = $db->sql_fetchrowset($result);
				$db->sql_freeresult($result);

				if (empty($users_rowset))
				{
					// Fail-safe against a failure to load users.
					unset($users_rowset);
					trigger_error($user->lang['LAXSLASH_NCREQUESTS_USERS_FAILED_TO_LOAD'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>'));
				}

				foreach ($users_rowset as $current_user)
				{
					$users_ary[$current_user['user_id']] = $current_user;
					unset($current_user);
				}
				unset($users_rowset);

				// Now put each action into a template block_var.
				$row_count = (int) 0; // Initiate a row count here.
				foreach($rowset as $row)
				{
					// The template loaded for the first one should always be for the creation log entry.
					$row_log_entry_id = $row['log_entry_id'];
					$log_entry_type = $row['log_type'];

					if (!isset($first_log_entry_loaded))
					{
						$first_log_entry_loaded = true;

						// Get the log_entry_id here since it's the first row found.
						$log_entry_id = $row['log_entry_id'];

						// Load the first log entry into the template here. It *should* always be the creation entry, but we'll make sure here.
						// Remember that this is being shown to the user, so run extra checks on the displayed data, and don't show any IPs. (Unless a feature/permission is added, or moderator abilities are checked?)
						switch ($log_entry_type)
						{
							case LAXSLASH_NCREQUESTS_LOG_TYPE_CREATE:
								$template->assign_vars(array(
									'LAXSLASH_NCREQUESTS_LOG_VIEW_MODE' => 'create',
									'LAXSLASH_NCREQUESTS_USERNAME_PRE_REQUEST' => $row['username_old'],
									'LAXSLASH_NCREQUESTS_USERNAME_REQUESTED' => $row['username_new'],
									'LAXSLASH_NCREQUESTS_REQUEST_SUBMITTED_DATE' => $user->format_date($row['request_timestamp']),
								));
							break;
							case LAXSLASH_NCREQUESTS_LOG_TYPE_APPROVAL:
								$template->assign_vars(array(
									'LAXSLASH_NCREQUESTS_LOG_VIEW_MODE' => 'approve',
									'LAXSLASH_NCREQUESTS_USERNAME_REQUESTED' => $row['username_new'],
									'LAXSLASH_NCREQUESTS_REQUEST_SUBMITTED_DATE' => $user->format_date($row['request_timestamp']),
									'LAXSLASH_NCREQUESTS_OLD_USERNAME' => $row['username_old'],
									'LAXSLASH_NCREQUESTS_NOTIFICATION_SENT' => ($row['notification_sent']) ? true : false,
									'LAXSLASH_NCREQUESTS_REQUEST_APPROVAL_NOTE' => ($row['reason_incl_in_notification']) ? $row['action_reason'] : false,
									'LAXSLASH_NCREQUESTS_REQUEST_APPROVAL_DATE' => $user->date_format($row['timestamp']),
									'LAXSLASH_NCREQUESTS_APPROVING_USER' => $users_ary[$row['logging_usedr_id']]['username'],
									'LAXSLASH_NCREQUESTS_APPROVING_USER_COLOR' => $users_ary[$row['logging_user_id']]['user_colour'],
								));
							break;
							case LAXSLASH_NCREQUESTS_LOG_TYPE_EDIT:
								$template->assign_vars(array(
									'LAXSLASH_NCREQUESTS_LOG_VIEW_MODE' => 'edit',
									'LAXSLASH_NCREQUESTS_OLD_USERNAME' => $row['username_old'],
									'LAXSLASH_NCREQUESTS_NEW_USERNAME' => $row['username_new'],
									'LAXSLASH_NCREQUESTS_REQUEST_SUBMITTED_DATE' => $user->format_date($row['request_timestamp']),
									'LAXSLASH_NCREQUESTS_DATE_OF_CHANGE' => $user->format_date($row['timestamp']),
									'LAXSLASH_NCREQUESTS_NOTIFICATION_SENT' => ($row['notification_sent']) ? true : false,
									'LAXSLASH_NCREQUESTS_EDIT_REASON' => ($row['reason_incl_in_notification']) ? $row['action_reason'] : false,
									'LAXSLASH_NCREQUESTS_EDITING_USER' => $users_ary[$row['logging_user_id']]['username'],
									'LAXSLASH_NCREQUESTS_EDITING_USER_COLOR' => $users_ary[$row['logging_user_id']]['user_colour'],
								));
							break;
							case LAXSLASH_NCREQUESTS_LOG_TYPE_DENY:
								$template->assign_vars(array(
									'LAXSLASH_NCREQUESTS_LOG_VIEW_MODE' => 'deny',
									'LAXSLASH_NCREQUESTS_REQUESTED_USERNAME' => $row['username_old'],
									'LAXSLASH_NCREQUESTS_REQUEST_SUBMITTED_DATE' => $user->format_date($row['request_timestamp']),
									'LAXSLASH_NCREQUESTS_REQUEST_DENIAL_DATE' => $user->format_date($row['timestamp']),
									'LAXSLASH_NCREQUESTS_NOTIFICATION_SENT' => ($row['notification_sent']) ? true : false,
									'LAXSLASH_NCREQUESTS_DENY_REASON' => ($row['reason_incl_in_notification']) ? $row['action_reason'] : false,
									'LAXSLASH_NCREQUESTS_DENYING_USER' => $users_ary[$row['logging_user_id']]['username'],
									'LAXSLASH_NCREQUESTS_DENYING_USER_COLOR' => $users_ary[$row['logging_user_id']]['user_colour'],
								));
							break;
							case LAXSLASH_NCREQUESTS_LOG_TYPE_USER_CANCEL:
								$template->assign_vars(array(
									'LAXSLASH_NCREQUESTS_LOG_VIEW_MODE' => 'cancel',
									'LAXSLASH_NCREQUESTS_REQUESTED_USERNAME' => $row['username_old'],
									'LAXSLASH_NCREQUESTS_REQUEST_SUBMITTED_DATE' => $user->format_date($row['request_timestamp']),
									'LAXSLASH_NCREQUESTS_REQUEST_CANCELLATION_DATE' => $user->format_date($row['timestamp']),
								));
							break;
							default:
							break;
						}
					}

					// Get the language variable for this entry here.
					switch ($log_entry_type)
					{
						case LAXSLASH_NCREQUESTS_LOG_TYPE_CREATE:
							$action_type_lang = $user->lang('LAXSLASH_NCREQUESTS_REQUEST_LOG_TYPE_CREATE');
						break;
						case LAXSLASH_NCREQUESTS_LOG_TYPE_APPROVAL:
							$action_type_lang = $user->lang('LAXSLASH_NCREQUESTS_REQUEST_LOG_TYPE_APPROVAL');
						break;
						case LAXSLASH_NCREQUESTS_LOG_TYPE_EDIT:
							$action_type_lang = $user->lang('LAXSLASH_NCREQUESTS_REQUEST_LOG_TYPE_EDIT');
						break;
						case LAXSLASH_NCREQUESTS_LOG_TYPE_DENY:
							$action_type_lang = $user->lang('LAXSLASH_NCREQUESTS_REQUEST_LOG_TYPE_DENY');
						break;
						case LAXSLASH_NCREQUESTS_LOG_TYPE_USER_CANCEL:
							$action_type_lang = $user->lang('LAXSLASH_NCREQUESTS_REQUEST_LOG_TYPE_USER_CANCEL');
						break;
						default:
							$action_type_lang = '';
						break;
					}

					$row_count++;

					$template->assign_block_vars('log_entries', array(
						'LAXSLASH_NCREQUESTS_ENTRY_DATE' => $user->format_date($row['timestamp']),
						'LAXSLASH_NCREQUESTS_ACTION_TYPE' => $action_type_lang,
						'LAXSLASH_NCREQUESTS_LOGGING_USER' => $users_ary[$row['logging_user_id']]['username'],
						'LAXSLASH_NCREQUESTS_LOGGING_USER_COLOR' => $users_ary[$row['logging_user_id']]['user_colour'],
						'U_LAXSLASH_NCREQUESTS_VIEW_ACTION_DETAILS' => $this->u_action . '&amp;action=view_action_details&amp;log_entry_id=' . $row_log_entry_id,
						'S_LAXSLASH_NCREQUESTS_ACTIVE_LOG_ENTRY' => ($row_log_entry_id == $log_entry_id) ? true : false,
						'S_LAXSLASH_NCREQUESTS_LOG_ENTRIES_ROW_COUNT' => $row_count,
					));

					unset($row);
				}
			break;
			case 'view_action_details':
				// Get the desired ID here.
				$log_entry_id = $request->variable('log_entry_id', 0);

				$this->tpl_name = 'request_viewlog_ucp_template';

				$this->page_title = $user->lang('UCP_LAXSLASH_NCREQUESTS_BROWSER_TITLE');

				// Get the relevant request_id here...
				// ...and figure out a more efficient way to do this.
				$sql = 'SELECT request_id
						FROM ' . LAXSLASH_NCREQUESTS_ADMINLOG_TABLE . '
						WHERE log_entry_id = ' . $log_entry_id;
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				if (empty($row))
				{
					trigger_error($user->lang('LAXSLASH_NCREQUESTS_UCP_LOG_ENTRY_DOES_NOT_EXIST') . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>'), E_USER_WARNING);
				}

				$request_id = $row['request_id'];
				unset($row);

				// Get all of the needeed data here.
				$sql_ary = array(
					'SELECT' => 'r.*, l.*',
					'FROM' => array(
						LAXSLASH_NCREQUESTS_REQUESTS_TABLE => 'r',
						LAXSLASH_NCREQUESTS_ADMINLOG_TABLE => 'l',
					),
					'WHERE' => 'l.request_id = ' . $request_id . ' AND r.request_user_id = ' . $user->data['user_id'] . ' AND l.request_id = r.request_id',
					'ORDER_BY' => 'timestamp ASC',
				);
				$sql = $db->sql_build_query('SELECT', $sql_ary);
				$result = $db->sql_query($sql);
				$rowset = $db->sql_fetchrowset($result);
				$db->sql_freeresult($result);
				unset($sql_ary);

				if(empty($rowset))
				{
					trigger_error($user->lang('LAXSLASH_NCREQUESTS_UCP_NO_LOG_ENTRIES_FOUND') . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>'), E_USER_WARNING);
				}

				// Rowset's not empty, load all of the User IDs needed for this entry.
				$user_ids_pre_load = array();

				// Since all request User IDs for each entry should be this user, load this user only for request_ids.
				$user_ids_pre_load[] = $user->data['user_id'];

				foreach ($rowset as $row)
				{
					if ($row['request_user_id'] != $row['logging_user_id'])
					{
						$user_ids_pre_load[] = (int) $row['logging_user_id'];
					}
					unset($row);
				}

				// Run array_unique() here
				$user_ids_to_load = array();
				$user_ids_to_load = array_unique($user_ids_pre_load);

				unset($user_ids_pre_load);

				$sql = 'SELECT *
						FROM ' . USERS_TABLE . '
						WHERE ' . $db->sql_in_set('user_id', $user_ids_to_load);
				$result = $db->sql_query($sql);
				$users_rowset = $db->sql_fetchrowset($result);
				$db->sql_freeresult($result);

				if (empty($users_rowset))
				{
					// Fail-safe against a failure to load users.
					unset($users_rowset);
					trigger_error($user->lang['LAXSLASH_NCREQUESTS_USERS_FAILED_TO_LOAD'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>'));
				}

				foreach ($users_rowset as $current_user)
				{
					$users_ary[$current_user['user_id']] = $current_user;
					unset($current_user);
				}
				unset($users_rowset);

				// Now put each action into a template block_var.
				$row_count = (int) 0; // Initiate a row count here.
				foreach($rowset as $row)
				{
					// The template loaded for the first one should always be for the creation log entry.
					$row_log_entry_id = $row['log_entry_id'];
					$log_entry_type = $row['log_type'];

					if ($row_log_entry_id == $log_entry_id)
					{
						$first_log_entry_loaded = true;

						// Remember that this is being shown to the user, so run extra checks on the displayed data, and don't show any IPs. (Unless a feature/permission is added, or moderator abilities are checked?)
						switch ($log_entry_type)
						{
							case LAXSLASH_NCREQUESTS_LOG_TYPE_CREATE:
								$template->assign_vars(array(
									'LAXSLASH_NCREQUESTS_LOG_VIEW_MODE' => 'create',
									'LAXSLASH_NCREQUESTS_USERNAME_PRE_REQUEST' => $row['username_old'],
									'LAXSLASH_NCREQUESTS_USERNAME_REQUESTED' => $row['username_new'],
									'LAXSLASH_NCREQUESTS_REQUEST_SUBMITTED_DATE' => $user->format_date($row['request_timestamp']),
								));
							break;
							case LAXSLASH_NCREQUESTS_LOG_TYPE_APPROVAL:
								$template->assign_vars(array(
									'LAXSLASH_NCREQUESTS_LOG_VIEW_MODE' => 'approve',
									'LAXSLASH_NCREQUESTS_USERNAME_REQUESTED' => $row['username_new'],
									'LAXSLASH_NCREQUESTS_REQUEST_SUBMITTED_DATE' => $user->format_date($row['request_timestamp']),
									'LAXSLASH_NCREQUESTS_OLD_USERNAME' => $row['username_old'],
									'LAXSLASH_NCREQUESTS_NOTIFICATION_SENT' => ($row['notification_sent']) ? true : false,
									'LAXSLASH_NCREQUESTS_REQUEST_APPROVAL_NOTE' => ($row['reason_incl_in_notification']) ? $row['action_reason'] : false,
									'LAXSLASH_NCREQUESTS_REQUEST_APPROVAL_DATE' => $user->format_date($row['timestamp']),
									'LAXSLASH_NCREQUESTS_APPROVING_USER' => $users_ary[$row['logging_user_id']]['username'],
									'LAXSLASH_NCREQUESTS_APPROVING_USER_COLOR' => $users_ary[$row['logging_user_id']]['user_colour'],
								));
							break;
							case LAXSLASH_NCREQUESTS_LOG_TYPE_EDIT:
								$template->assign_vars(array(
									'LAXSLASH_NCREQUESTS_LOG_VIEW_MODE' => 'edit',
									'LAXSLASH_NCREQUESTS_OLD_USERNAME' => $row['username_old'],
									'LAXSLASH_NCREQUESTS_NEW_USERNAME' => $row['username_new'],
									'LAXSLASH_NCREQUESTS_REQUEST_SUBMITTED_DATE' => $user->format_date($row['request_timestamp']),
									'LAXSLASH_NCREQUESTS_DATE_OF_CHANGE' => $user->format_date($row['timestamp']),
									'LAXSLASH_NCREQUESTS_NOTIFICATION_SENT' => ($row['notification_sent']) ? true : false,
									'LAXSLASH_NCREQUESTS_EDIT_REASON' => ($row['reason_incl_in_notification']) ? $row['action_reason'] : false,
									'LAXSLASH_NCREQUESTS_EDITING_USER' => $users_ary[$row['logging_user_id']]['username'],
									'LAXSLASH_NCREQUESTS_EDITING_USER_COLOR' => $users_ary[$row['logging_user_id']]['user_colour'],
								));
							break;
							case LAXSLASH_NCREQUESTS_LOG_TYPE_DENY:
								$template->assign_vars(array(
									'LAXSLASH_NCREQUESTS_LOG_VIEW_MODE' => 'deny',
									'LAXSLASH_NCREQUESTS_REQUESTED_USERNAME' => $row['username_old'],
									'LAXSLASH_NCREQUESTS_REQUEST_SUBMITTED_DATE' => $user->format_date($row['request_timestamp']),
									'LAXSLASH_NCREQUESTS_REQUEST_DENIAL_DATE' => $user->format_date($row['timestamp']),
									'LAXSLASH_NCREQUESTS_NOTIFICATION_SENT' => ($row['notification_sent']) ? true : false,
									'LAXSLASH_NCREQUESTS_DENY_REASON' => ($row['reason_incl_in_notification']) ? $row['action_reason'] : false,
									'LAXSLASH_NCREQUESTS_DENYING_USER' => $users_ary[$row['logging_user_id']]['username'],
									'LAXSLASH_NCREQUESTS_DENYING_USER_COLOR' => $users_ary[$row['logging_user_id']]['user_colour'],
								));
							break;
							case LAXSLASH_NCREQUESTS_LOG_TYPE_USER_CANCEL:
								$template->assign_vars(array(
									'LAXSLASH_NCREQUESTS_LOG_VIEW_MODE' => 'cancel',
									'LAXSLASH_NCREQUESTS_REQUESTED_USERNAME' => $row['username_old'],
									'LAXSLASH_NCREQUESTS_REQUEST_SUBMITTED_DATE' => $user->format_date($row['request_timestamp']),
									'LAXSLASH_NCREQUESTS_REQUEST_CANCELLATION_DATE' => $user->format_date($row['timestamp']),
								));
							break;
							default:
							break;
						}
					}

					// Get the language variable for this entry here.
					switch ($log_entry_type)
					{
						case LAXSLASH_NCREQUESTS_LOG_TYPE_CREATE:
							$action_type_lang = $user->lang('LAXSLASH_NCREQUESTS_REQUEST_LOG_TYPE_CREATE');
						break;
						case LAXSLASH_NCREQUESTS_LOG_TYPE_APPROVAL:
							$action_type_lang = $user->lang('LAXSLASH_NCREQUESTS_REQUEST_LOG_TYPE_APPROVAL');
						break;
						case LAXSLASH_NCREQUESTS_LOG_TYPE_EDIT:
							$action_type_lang = $user->lang('LAXSLASH_NCREQUESTS_REQUEST_LOG_TYPE_EDIT');
						break;
						case LAXSLASH_NCREQUESTS_LOG_TYPE_DENY:
							$action_type_lang = $user->lang('LAXSLASH_NCREQUESTS_REQUEST_LOG_TYPE_DENY');
						break;
						case LAXSLASH_NCREQUESTS_LOG_TYPE_USER_CANCEL:
							$action_type_lang = $user->lang('LAXSLASH_NCREQUESTS_REQUEST_LOG_TYPE_USER_CANCEL');
						break;
						default:
							$action_type_lang = '';
						break;
					}

					$row_count++;

					$template->assign_block_vars('log_entries', array(
						'LAXSLASH_NCREQUESTS_ENTRY_DATE' => $user->format_date($row['timestamp']),
						'LAXSLASH_NCREQUESTS_ACTION_TYPE' => $action_type_lang,
						'LAXSLASH_NCREQUESTS_LOGGING_USER' => $users_ary[$row['logging_user_id']]['username'],
						'LAXSLASH_NCREQUESTS_LOGGING_USER_COLOR' => $users_ary[$row['logging_user_id']]['user_colour'],
						'U_LAXSLASH_NCREQUESTS_VIEW_ACTION_DETAILS' => $this->u_action . '&amp;action=view_action_details&amp;log_entry_id=' . $row_log_entry_id,
						'S_LAXSLASH_NCREQUESTS_ACTIVE_LOG_ENTRY' => ($row_log_entry_id == $log_entry_id) ? true : false,
						'S_LAXSLASH_NCREQUESTS_LOG_ENTRIES_ROW_COUNT' => $row_count,
					));

					unset($row);
				}
			break;
			default:
				add_form_key('laxslash/ncrequests');

				$this->tpl_name = 'request_namechange_template';

				$this->page_title = $user->lang('UCP_LAXSLASH_NCREQUESTS_BROWSER_TITLE');

				// Set an errors array here.
				$errors = array();

				// Start the passwords manager.
				$passwords_manager = $phpbb_container->get('passwords.manager');

				// Load the language file here.
				$user->add_lang('acp/users');

				// Are namechanges allowed on the forums? If not, then block the requests system.
				if (!$config['allow_namechange'])
				{
					$errors[] = $user->lang('LAXSLASH_NCREQUESTS_NAME_CHANGES_DISABLED');
				}

				$sql = 'SELECT requested_username, request_id, request_user_id, request_status, request_timestamp
						FROM ' . LAXSLASH_NCREQUESTS_REQUESTS_TABLE . '
						WHERE request_user_id = ' . $user->data['user_id'] . '
						ORDER BY request_timestamp DESC'; // UCP should ahve the most recent entry at the top, as opposed to the ACP which has the oldest entry at the top.
				$result = $db->sql_query($sql);
				$row_collection = $db->sql_fetchrowset($result);
				$db->sql_freeresult($result);

				// See if the user has an open request. If they do, show them the cancel request page.
				foreach ($row_collection as $row)
				{
					if ($row['request_status'] == LAXSLASH_NCREQUESTS_REQUEST_STATUS_PENDING)
					{
						$pending_request = $row;
					}

					unset($row);
				}

				if (isset($pending_request))
				{
					$template->assign_vars(array(
						'S_REQUEST_EXISTS' => true,
						'LAXSLASH_NCREQUESTS_CURRENT_REQUESTED_USERNAME' => $pending_request['requested_username'],
					));

					if ($request->is_set_post('submit_request'))
					{
						// Oh no you don't... violists fingers don't strike twice, so why should you?
						$errors[] = $user->lang('LAXSLASH_NCREQUESTS_NAME_REQUEST_ALREADY_EXISTS');
					}

					if ($request->is_set_post('cancel_namechange'))
					{
						if (!check_form_key('laxslash/ncrequests'))
						{
							// Oh no you don't!
							$errors[] = $user->lang('FORM_INVALID');
						}

						// Password verification
						$password_to_verify = $request->variable('ver_user_pw', '');
						if (!$passwords_manager->check($password_to_verify, $user->data['user_password']))
						{
							$errors[] = (!$password_to_verify) ? $user->lang('CUR_PASSWORD_EMPTY') : $user->lang('CUR_PASSWORD_ERROR');
						}

						// Only continue if there are no errors.
						if (empty($errors))
						{
							// Unset the errors array, as we don't need this anymore.
							unset($errors);

							// Delete the name change, but make sure that there's both a match on the request ID and the username for security reasons.
							//$sql = 'DELETE FROM ' . LAXSLASH_NCREQUESTS_REQUESTS_TABLE . '
							//		WHERE request_id = ' . $row['request_id'] . ' AND request_user_id = ' . $user->data['user_id'];
							//$db->sql_query($sql);

							// Or just update the rows and give them a status of "User Cancelled" as well as an entry in the admin log
							$sql_arr = array(
								'request_status' => LAXSLASH_NCREQUESTS_REQUEST_STATUS_USER_CANCELLED,
							);
							$sql = 'UPDATE ' . LAXSLASH_NCREQUESTS_REQUESTS_TABLE . '
									SET ' . $db->sql_build_array('UPDATE', $sql_arr) . '
									WHERE request_id = ' . $pending_request['request_id'];
							$db->sql_query($sql);

							$current_time = time();

							//Successful, or no?
							if ($db->sql_affectedrows() == 0)
							{
								unset($pending_request);
								unset($sql_arr);
								unset($row_collection);

								// meta_refresh(3, $this->u_action); // No meta_refresh for errors.
								trigger_error($user->lang['LAXSLASH_NCREQUESTS_ERROR_NO_RECORDS_DELETED'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>'), E_USER_WARNING); // Is it even possible to go here?
							} else {
								$sql_arr = array(
									'request_id' => $row['request_id'],
									'logging_user_id' => $user->data['user_id'],
									'request_user_id' => $row['request_user_id'],
									'username_old' => $row['requested_username'],
									'timestamp' => $current_time,
									'status_old' => $row['request_status'],
									'status_new' => LAXSLASH_NCREQUESTS_REQUEST_STATUS_USER_CANCELLED,
									'log_type' => LAXSLASH_NCREQUESTS_LOG_TYPE_USER_CANCEL,
									'action_ip_address' => $user->ip,
								);
								$sql = 'INSERT INTO ' . LAXSLASH_NCREQUESTS_ADMINLOG_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_arr);
								$db->sql_query($sql);

								unset($sql_arr);
								unset($row_collection);

								$phpbb_log->add('user', $user->data['user_id'], $user->ip, 'LAXSLASH_NCREQUESTS_LOG_USER_CANCELLED_CHANGE', time(), array('reportee_id' => $user->data['user_id'], $user->data['username'], $row['requested_username']));

								unset($pending_request);
								meta_refresh(3, $this->u_action);
								trigger_error($user->lang['LAXSLASH_NCREQUESTS_UCP_REQUEST_CANCELLED_SUCCESS'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>'));
							}
						}
					}
				}
				unset($pending_request);

				// If not, allow them to submit a new request into the system.

				// Was there a request sent already to cancel the username that got rejected/blocked/denied for whatever reason?
				// (Also, make sure that there's just no errors from a good attempt...)
				if ($request->is_set_post('cancel_namechange') && empty($errors))
				{
					$errors[] = $user->lang('LAXSLASH_NCREQUESTS_ERROR_NO_RECORDS_DELETED');
				}

				// Variables requests
				$data = array(
					'username_requested' => utf8_normalize_nfc(request_var('new_username_requested', '', true)),
					'username_requested_clean' => utf8_clean_string(utf8_normalize_nfc(request_var('new_username_requested', '', true))),
				);

				// Check for an empty error array here.
				if ($request->is_set_post('submit_request') && empty($errors))
				{
					// Not a valid form. How did we know that? :o Amazing.
					if (!check_form_key('laxslash/ncrequests'))
					{
						$errors[] = $user->lang('FORM_INVALID');
					}

					// Password verification
					$password_to_verify = $request->variable('ver_user_pw', '');
					if (!$passwords_manager->check($password_to_verify, $user->data['user_password']))
					{
						$errors[] = (!$password_to_verify) ? $user->lang('CUR_PASSWORD_EMPTY') : $user->lang('CUR_PASSWORD_ERROR');
					}

					// Run the checks to make sure that the username is not taken here, and convert it into a clean username. Also make sure that it follows
					// the current forum/board rules.

					if ($data['username_requested'] == '')
					{
						// No name, no go.
						$errors[] = $user->lang('LAXSLASH_NCREQUESTS_UCP_ERR_ENTER_NEW_NAME_REQ');
					} elseif ($data['username_requested_clean'] == $user->data['username_clean']) { // Don't check clean, because we can allow case changes to be requested.
					// } elseif ($data['username_requested'] == $user->data['username']) { // Or do we not check normal? Check for clarification?
						// You can't request your own name.
						$errors[] = $user->lang('LAXSLASH_NCREQUESTS_UCP_ERR_CANT_REQUEST_OWN_NAME');
					} else {
						// Username found, check the data.
						$check_ary = array(
							'username_requested' => array(
								array('string', false, $config['min_name_chars'], $config['max_name_chars']),
								array('username'),
							),
						);

						$pre_errors = validate_data($data, $check_ary);
						unset($check_ary);

						// Parse each error to be the needed language string.
						foreach ($pre_errors as $current_error)
						{
							$errors[] = $user->lang($current_error . '_USERNAME');
						}

						unset ($pre_errors);

						// Make sure that no requests already exist for this username.
						$sql = 'SELECT COUNT(request_id) AS request_count
								FROM ' . LAXSLASH_NCREQUESTS_REQUESTS_TABLE . "
								WHERE requested_username_clean = '" . $db->sql_escape($data['username_requested_clean']) . "' AND request_status = " . LAXSLASH_NCREQUESTS_REQUEST_STATUS_PENDING;
						$result = $db->sql_query($sql);

						if ($db->sql_fetchfield('request_count') != 0)
						{
							// A request already exists for this username, please select another.
							$errors[] = $user->lang('LAXSLASH_NCREQUESTS_UCP_ERR_REQUEST_FOR_NAME_ALREADY_EXISTS');
						}

						$db->sql_freeresult($result);
					}

					// Clean of errors? Good to go then.
					// No errors?
					if (empty($errors))
					{
						// Unset this and make it emptier than a Drive-In movie theater on January 1st in Antarctica.
						unset($errors);

						// Request time now, when everything's ready to go for sure.
						$current_time = time();

						$sql_ary = array(
							'request_user_id' => $user->data['user_id'],
							'requested_username' => $data['username_requested'],
							'requested_username_clean' => $data['username_requested_clean'],
							'request_timestamp' => $current_time,
							'request_status' => LAXSLASH_NCREQUESTS_REQUEST_STATUS_PENDING,
							'request_from_ip_address' => $user->ip,
						);

						$sql = 'INSERT INTO ' . LAXSLASH_NCREQUESTS_REQUESTS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
						$db->sql_query($sql);

						// And in the admin log as well.
						$sql_ary = array(
							'request_id' => $db->sql_nextid(),
							'logging_user_id' => $user->data['user_id'],
							'request_user_id' => $user->data['user_id'],
							'username_old' => $user->data['username'],
							'username_new' => $data['username_requested'],
							'timestamp' => $current_time,
							'status_new' => LAXSLASH_NCREQUESTS_REQUEST_STATUS_PENDING,
							'log_type' => LAXSLASH_NCREQUESTS_LOG_TYPE_CREATE,
							'action_ip_address' => $user->ip,
						);
						$sql = 'INSERT INTO ' . LAXSLASH_NCREQUESTS_ADMINLOG_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);
						$db->sql_query($sql);

						// THIS MUST BE RUN *AFTER* THE ADMINLOG ENTRY, OTHERWISE IT'LL SCREW UP THE $db->sql_nextid() CALL.
						// Add a log entry upon success here in the User Log
						$phpbb_log->add('user', $user->data['user_id'], $user->ip, 'LAXSLASH_NCREQUESTS_LOG_USER_REQUESTED_CHANGE', time(), array('reportee_id' => $user->data['user_id'], $user->data['username'], $data['username_requested']));

						// Goodbye stranger, it's been nice...
						unset($sql_ary);
						unset($data);

						// We can stop the user from running into brick walls now.
						meta_refresh(3, $this->u_action);
						trigger_error($user->lang['LAXSLASH_NCREQUESTS_UCP_REQUEST_SUBMITTED_SUCCESS'] . '<br /><br />' . sprintf($user->lang['RETURN_UCP'], '<a href="' . $this->u_action . '">', '</a>'));
					}
				}

				// Make the page here.
				$template->assign_vars(array(
					'S_ERRORS' => (sizeof($errors)) ? true : false,
					'ERRORS_OUTPUT' => (sizeof($errors)) ? implode('<br />', $errors) : '',
					'L_USERNAME_EXPLAIN' => $user->lang($config['allow_name_chars'] . '_EXPLAIN', $user->lang('CHARACTERS', (int) $config['min_name_chars']), $user->lang('CHARACTERS', (int) $config['max_name_chars'])),
					'REQUESTED_USERNAME' => $data['username_requested'],
				));
				// Unset the errors array here.
				unset($errors);

				unset($data);

				// Create the previous requests table, if previous requests exist.
				if (!empty($row_collection))
				{
					$row_count = (int) 0; // We need a row count for the row coloring here.
					foreach ($row_collection as $row)
					{
						switch ($row['request_status'])
						{
							case LAXSLASH_NCREQUESTS_REQUEST_STATUS_PENDING:
								$request_status_lang = $user->lang('LAXSLASH_NCREQUESTS_REQUEST_OPEN_STATE');
							break;
							case LAXSLASH_NCREQUESTS_REQUEST_STATUS_APPROVED:
								$request_status_lang = $user->lang('LAXSLASH_NCREQUESTS_REQUEST_APPROVED_STATE');
							break;
							case LAXSLASH_NCREQUESTS_REQUEST_STATUS_DENIED:
								$request_status_lang = $user->lang('LAXSLASH_NCREQUESTS_REQUEST_DENIED_STATE');
							break;
							case LAXSLASH_NCREQUESTS_REQUEST_STATUS_USER_CANCELLED:
								$request_status_lang = $user->lang('LAXSLASH_NCREQUESTS_REQUEST_USER_CANCELLED_STATE');
							break;
							default:
								$request_status_lang = '';
							break;
						}
						$row_count++;
						$template->assign_block_vars('show_user_requests_log', array(
							'LAXSLASH_NCREQUESTS_REQUEST_SUBMIT_DATE' => $user->format_date($row['request_timestamp']),
							'LAXSLASH_NCREQUESTS_REQUESTED_USERNAME' => $row['requested_username'],
							'LAXSLASH_NCREQUESTS_REQUEST_STATUS_LANG' => $request_status_lang,
							'U_LAXSLASH_NCREQUESTS_REQUEST_VIEW_LOG' => $this->u_action . '&amp;action=view_log_for_request&amp;request_id=' . $row['request_id'],
							'S_LAXSLASH_NCREQUESTS_SHOW_USER_REQUESTS_LOG_ROW_COUNT' => $row_count,
						));
					}
				}
				unset($row_collection);
			break;
		}
	}
}
