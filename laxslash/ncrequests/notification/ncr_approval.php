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

class ncr_approval extends \phpbb\notification\type\base
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
		return 'laxslash.ncrequests.notification.type.ncr_approval';
	}

	protected $language_key = 'LAXSLASH_NCREQUESTS_NOTIFICATION_TYPE_NCR_APPROVE';

	public static $notification_option = array(
		'group' => 'NOTIFICATION_GROUP_MISCELLANEOUS',
		'lang' => 'LAXSLASH_NCREQUESTS_NOTIFICATION_TYPE_NCR_APPROVE_OPTION',
	);

	public function is_available()
	{
		return false;
	}

	public static function get_item_id($data)
	{
		return (int) $data['request_id'];
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
		$new_username = $this->user_loader->get_username($this->get_data('request_user_id'), 'no_profile');
		$old_username = $this->get_data('username_old');

		return $this->user->lang($this->language_key, $old_username, $new_username);
	}

	public function users_to_query()
	{
		$request_user = $this->get_data('request_user_id');
		$approved_by = $this->get_data('approved_by');

		$users = array(
			$request_user,
			$approved_by,
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
		return '@laxslash_ncrequests/ncr_approve_email';
	}

	public function get_reference()
	{
		return $this->user->lang('LAXSLASH_NCREQUESTS_NOTIFICATION_TYPE_NCR_APPROVE_TEXT', $this->get_data('username_old'), $this->get_data('username_new'), $this->user_loader->get_username($this->get_data('approved_by'), 'full'));
	}

	public function get_email_template_variables()
	{
		$user_data = $this->user_loader->get_user($this->get_data('request_user_id'));

		return array(
			'LAXSLASH_NCREQUESTS_OLD_USERNAME' => htmlspecialchars_decode($this->get_data($username_old)),
			'LAXSLASH_NCREQUESTS_NEW_USERNAME' => htmlspecialchars_decode($this->get_data($username_new)),
			'U_LAXSLASH_NCREQUESTS_BOARD_LINK' => generate_board_url(),
		);
	}

	public function create_insert_array($data, $pre_create_data = array())
	{
		$this->set_data('request_user_id', $data['request_user_id']);
		$this->set_data('username_old', $data['username_old']);
		$this->set_data('username_new', $data['username_new']);
		$this->set_data('approved_by', $data['approved_by']);

		return parent::create_insert_array($data, $pre_create_data);
	}
}
