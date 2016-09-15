<?php
/**
 * This file is a part of the Force Style Changes modification by
 * lax.slash for the phpBB 3.1 Forums Software.
 *
 * @copyright (c) lax.slash <https://www.github.com/LaxSlash>
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

		add_form_key('laxslash/ncrequests');

		$this->tpl_name = 'request_namechange_template';

		$this->page_title = $user->lang('UCP_LAXSLASH_NCREQUESTS_BROWSER_TITLE');

		$template->assign_var('S_UCP_ACTION', $this->u_action);

		// Set an errors array here.
		$errors = array();

		// Start the passwords manager.
		$passwords_manager = $phpbb_container->get('passwords.manager');

		// Load the language file here.
		$user->add_lang('acp/users');

		// Are namechanges allowed on the forums? If nt, then block the requests system.
		if (!$config['allow_namechange'])
		{
			$errors[] = $user->lang('LAXSLASH_NCREQUESTS_NAME_CHANGES_DISABLED');
		}

		// See if the user has an open request. If they do, show them the cancel request page.
		$sql = 'SELECT requested_username, request_id, request_status
				FROM ' . LAXSLASH_NCREQUESTS_REQUESTS_TABLE . '
				WHERE (request_user_id = ' . $user->data['user_id'] . ' AND request_status = ' . LAXSLASH_NCREQUESTS_REQUEST_STATUS_PENDING . ')';
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if (!empty($row))
		{
			$template->assign_vars(array(
				'S_REQUEST_EXISTS' => true,
				'LAXSLASH_NCREQUESTS_CURRENT_REQUESTED_USERNAME' => $row['requested_username'],
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
							WHERE request_id = ' . $row['request_id'];
					$db->sql_query($sql);

					$current_time = time();

					//Successful, or no?
					if ($db->sql_affectedrows() == 0)
					{
						unset($row);
						unset($sql_arr);
						trigger_error('LAXSLASH_NCREQUESTS_ERROR_NO_RECORDS_DELETED', E_USER_WARNING); // Is it even possible to go here?
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

						$phpbb_log->add('user', $user->data['user_id'], $user->ip, 'LAXSLASH_NCREQUESTS_LOG_USER_CANCELLED_CHANGE', time(), array($user->data['username'], $row['requested_username']));

						unset($row);
						trigger_error('LAXSLASH_NCREQUESTS_UCP_REQUEST_CANCELLED_SUCCESS');
					}
				}
			}
		}
		unset($row);

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
				$phpbb_log->add('user', $user->data['user_id'], $user->ip, 'LAXSLASH_NCREQUESTS_LOG_USER_REQUESTED_CHANGE', time(), array($user->data['username'], $data['username_requested']));

				// Goodbye stranger, it's been nice...
				unset($sql_ary);
				unset($data);

				// We can stop the user from running into brick walls now.
				trigger_error('LAXSLASH_NCREQUESTS_UCP_REQUEST_SUBMITTED_SUCCESS');
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
	}
}
