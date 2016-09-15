<?php
/**
 * This file is a part of the Name Change Requests
 * phpBB 3.1 Extension by LaxSlash1993.
 *
 * @copyright (c) LaxSlash1993 <https://www.github.com/LaxSlash>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace laxslash\ncrequests\migrations;

use phpbb\db\migration\migration;

class v01a extends migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v31x\v319');
	}

	public function update_data()
	{
		return array(
			array('config.add', array('laxslash_ncrequests_version', '0.1 ALPHA')),
			array('module.add', array(
				'acp',
				'ACP_CAT_USERS',
				array(
					'module_basename' => '\laxslash\ncrequests\acp\manage_requests_module',
				),
			)),
			array('permission.add', array('u_laxslash_ncrequests_submit_ncr', true)),
			array('permission.add', array('a_laxslash_ncrequests_manage_name_change_requests', true)),
			array('permission.add', array('a_laxslash_ncrequests_edit_name_change_requests', true)),
			array('module.add', array(
				'ucp',
				'UCP_PROFILE',
				array(
					'module_basename' => '\laxslash\ncrequests\ucp\request_namechange_module',
				),
			)),
			array('permission.permission_set', array('ROLE_ADMIN_FULL', 'a_laxslash_ncrequests_manage_name_change_requests')),
			array('permission.permission_set', array('ROLE_ADMIN_FULL', 'a_laxslash_ncrequests_edit_name_change_requests')),
			array('permission.permission_set', array('ROLE_ADMIN_STANDARD', 'a_laxslash_ncrequests_manage_name_change_requests')),
			array('permission.permission_set', array('ROLE_ADMIN_STANDARD', 'a_laxslash_ncrequests_edit_name_change_requests')),
			array('permission.permission_set', array('ROLE_ADMIN_USERGROUP', 'a_laxslash_ncrequests_manage_name_change_requests')),
			array('permission.permission_set', array('ROLE_ADMIN_USERGROUP', 'a_laxslash_ncrequests_edit_name_change_requests')),
			array('permission.permission_set', array('ROLE_USER_FULL', 'u_laxslash_ncrequests_submit_ncr')),
		);
	}

	public function update_schema()
	{
		return array(
			'add_tables'		=>		array(
				$this->table_prefix . 'ncrequests_requests'	=>	array(
					'COLUMNS'		=>		array(
						'request_id'	=>	array('UINT', null, 'auto_increment'),
						'request_user_id'	=>	array('UINT', 0),
						'requested_username'	=>	array('VCHAR:255', ''),
						'requested_username_clean'	=>	array('VCHAR:255', ''),
						'request_timestamp'		=>	array('UINT:11', 0),
						'request_status'		=>	array('TINT:1', 0),
						'request_from_ip_address'	=>	array('VCHAR:40', ''),
					),
					'PRIMARY_KEY'	=>	'request_id',
				),
				$this->table_prefix . 'ncrequests_adminlog' => array(
					'COLUMNS'		=>		array(
						'log_entry_id'	=>	array('UINT', null, 'auto_increment'),
						'request_id'	=>	array('UINT', 0),
						'logging_user_id' => array('UINT', 0),
						'request_user_id' => array('UINT', 0),
						'username_old'	=>	array('VCHAR:255', ''),
						'username_new'	=>	array('VCHAR:255', ''),
						'notification_sent'	=> array('TINT:1', 0),
						'reason_incl_in_notification' => array('TINT:1', 0),
						'usernote_logged'	=>	array('TINT:1', 0),
						'timestamp'		=>	array('UINT:11', 0),
						'status_old'	=>	array('TINT:1', 0),
						'status_new'	=>	array('TINT:1', 0),
						'log_type'		=>	array('TINT:1', 0),
						'action_reason'	=>	array('VCHAR:255', ''),
						'action_ip_address'		=>	array('VCHAR:40', ''),
					),
					'PRIMARY_KEY'	=>	'log_entry_id',
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_tables'		=>		array(
				$this->table_prefix . 'ncrequests_requests',
				$this->table_prefix . 'ncrequests_adminlog',
			),
		);
	}
}

