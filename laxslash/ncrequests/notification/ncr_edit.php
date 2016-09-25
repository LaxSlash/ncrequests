<?php
/**
 * This file is a part of the Name Change Requests
 * phpBB 3.1 Extension by LaxSlash1993.
 *
 * @copyright (c) LaxSlash1993 <https://www.github.com/LaxSlash>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace laxslash\ncrequests\notification;

use \phpbb\user_loader;
use \phpbb\db\driver\driver_interface;
use \phpbb\user;
use \phpbb\auth\auth;
use \phpbb\config\config;

class ncr_edit extends \phpbb\notification\type\base
{

	protected $user_loader;
	protected $db;
	protected $cache;
	protected $user;
	protected $auth;
	protected $config;
	protected $notification_types_table;
	protected $notifications_table;
	protected $user_notifications_table;
	protected $phpbb_root_path;
	protected $php_ext;

	public function __construct(user_loader $user_loader, driver_interface $db, \phpbb\cache\driver\driver_interface $cache, user $user, auth $auth, config $config, $notification_types_table, $notifications_table, $user_notifications_table, $phpbb_root_path, $php_ext)
	{
		$this->user_loader = $user_loader;
		$this->db = $db;
		$this->cache = $cache;
		$this->user = $user;
		$this->auth = $auth;
		$this->config = $config;
		$this->notification_types_table = $notification_types_table;
		$this->notifications_table = $notifications_table;
		$this->user_notifications_table = $user_notifications_table;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	public function get_type()
	{
		return 'laxslash.ncrequests.notification.type.ncr_edit';
	}

	protected $language_key = 'LAXSLASH_NCREQUESTS_NOTIFICATION_TYPE_NCR_EDIT';

	public static $notification_option = array(
		'group' => 'NOTIFICATION_GROUP_MISCELLANEOUS',
		'lang' => 'LAXSLASH_NCREQUESTS_NOTIFICATION_TYPE_NCR_EDIT_OPTION',
	);

	public function is_available()
	{
		return false;
	}

	public static function get_item_id($data)
	{
		return (int) $data['laxslash_ncrequests_notification_id'];
	}

	public static function get_item_parent_id($data)
	{
		return 0;
	}

	public function find_users_for_notification($data, $options = array())
	{
		$options = array_merge(array(
			'ignore_users'		=>		array(),
		), $options);

		$users = array((int) $data['request_user_id']);

		return $this->check_user_notification_options($users, $options);
	}

	public function get_avatar()
	{
		return $this->user_loader->get_avatar($this->get_data('request_user_id'));
	}

	public function get_title()
	{
		$requested_username = $this->get_data('username_requested');

		return $this->user->lang($this->language_key, $requested_username);
	}

	public function users_to_query()
	{
		$request_user = $this->get_data('request_user_id');
		$edited_by = $this->get_data('edited_by');

		$users = array(
			$request_user,
			$edited_by,
		);

		return $users;
	}

	public function get_url()
	{
		return append_sid($this->phpbb_root_path . 'memberlist.' . $this->php_ext, "&amp;mode=viewprofile&amp;u={$this->get_data('request_user_id')}");
	}

	public function get_redirect_url()
	{
		return $this->get_url();
	}

	public function get_email_template()
	{
		return '@laxslash_ncrequests/ncr_edit_email';
	}

	public function get_reference()
	{
		if ($this->get_data('action_reason') == '')
		{
			return $this->user->lang('LAXSLASH_NCREQUESTS_NOTIFICATION_TYPE_NCR_EDIT_TEXT', $this->get_data('old_request_username'), $this->get_data('new_request_username'), $this->user_loader->get_username($this->get_data('edited_by'), 'full'));
		} else {
			// Why is the admin username adding an extra line break?
			return $this->user->lang('LAXSLASH_NCREQUESTS_NOTIFICATION_TYPE_NCR_EDIT_TEXT_W_REASON', $this->get_data('old_request_username'), $this->get_data('new_request_username'), $this->user_loader->get_username($this->get_data('edited_by'), 'full'), $this->get_data('action_reason'));
		}
	}

	public function get_email_template_variables()
	{
		$user_data = $this->user_loader->get_user($this->get_data('request_user_id'));

		return array(
			'LAXSLASH_NCREQUESTS_REQUESTED_USERNAME_OLD' => htmlspecialcharacters_decode($this->get_data($old_request_username)),
			'LAXSLASH_NCREQUESTS_REQUESTED_USERNAME_NEW' => htmlspecialcharacters_decode($this->get_data($new_request_username)),
			'U_LAXSLASH_NCREQUESTS_BOARD_LINK' => generate_board_url(),
		);
	}

	public function create_insert_array($data, $pre_create_data = array())
	{
		$this->set_data('request_id', $data['request_id']);
		$this->set_data('old_request_username', $data['old_request_username']);
		$this->set_data('new_request_username', $data['new_request_username']);
		$this->set_data('edited_by', $data['edited_by']);
		$this->set_data('request_user_id', $data['request_user_id']);
		$this->set_data('action_reason', (isset($data['action_reason'])) ? $data['action_reason'] : '');
		$this->set_data('laxslash_ncrequests_notification_id', $data['laxslash_ncrequests_notification_id']);

		return parent::create_insert_array($data, $pre_create_data);
	}
}
