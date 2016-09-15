<?php
/**
 * This file is a part of the Force Style Changes modification by
 * lax.slash for the phpBB 3.1 Forums Software.
 *
 * @copyright (c) lax.slash <https://www.github.com/LaxSlash>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace laxslash\ncrequests\event;

use phpbb\db\driver\driver_interface;
use phpbb\template\template;
use phpbb\auth\auth;
use phpbb\user;
use laxslash\ncrequests\controller\laxslash_ncrequests_constants;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event listener
 */

class main_listener implements EventSubscriberInterface
{

	/* @var $db */
	protected $db;

	/* @var $template */
	protected $template;

	/* @var $auth */
	protected $auth;

	/* @var $user */
	protected $user;

	/* @var $phpbb_admin_path */
	protected $phpbb_admin_path;

	/* @var $phpEx */
	protected $php_ext;

	/* @var $laxslash_ncrequests_constants */
	protected $laxslash_ncrequests_constants;

	/**
	 * Constructor
	 *
	 * @param driver_interface $db
	 * @param template $template
	 * @param auth $auth
	 * @param user $user
	 * @static $phpbb_admin_path
	 * @static $php_ext
	 * @param laxslash_ncrequests_constants $laxslash_ncrequests_constants
	 */
	public function __construct(driver_interface $db, template $template, auth $auth, user $user, $php_ext, laxslash_ncrequests_constants $laxslash_ncrequests_constants)
	{
		$this->db = $db;
		$this->template = $template;
		$this->auth = $auth;
		$this->user = $user;
		$this->phpEx = $php_ext;
		$this->laxslash_ncrequests_constants = $laxslash_ncrequests_constants;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup' => 'pre_user_setup',
			'core.page_header' => 'check_for_open_requests',
			//'core.acp_users_overview_modify_data' => 'check_acp_username_on_submit', // See note above this function.
			'core.permissions' => 'add_permissions',
		);
	}

	public function pre_user_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'laxslash/ncrequests',
			'lang_set' => 'acp_ncrequests_lang',
		);
		$event['lang_set_ext'] = $lang_set_ext;

		unset($lang_set_ext);

		// Get the constants here...
		$this->laxslash_ncrequests_constants->dec_ncrequests_consts_for_db();
	}

	public function check_for_open_requests($event)
	{
		global $phpbb_admin_path; // This needs to be requested to be put into the %core.*% definition in container_builder.php.

		if ($this->auth->acl_get('a_laxslash_ncrequests_manage_name_change_requests'))
		{
			$sql = 'SELECT COUNT(request_id) AS open_requests
					FROM ' . LAXSLASH_NCREQUESTS_REQUESTS_TABLE . '
					WHERE request_status = ' . LAXSLASH_NCREQUESTS_REQUEST_STATUS_PENDING;
			$result = $this->db->sql_query($sql);
			$open_requests = (int) $this->db->sql_fetchfield('open_requests');
			$this->db->sql_freeresult($result);

			if ($open_requests > 0)
			{
				$this->template->assign_vars(array(
					'LAXSLASH_NCREQUESTS_OPEN_REQUESTS_FOUND' => true,
					'L_LAXSLASH_NCREQUESTS_OPEN_REQUESTS_DISPLAY' => $this->user->lang('LAXSLASH_NCREQUESTS_OPEN_REQUESTS_ALERT', $open_requests),
					'U_LAXSLASH_NCREQUESTS_ACP_MANAGE_LINK' => append_sid("{$phpbb_admin_path}index.$this->phpEx", "i=-laxslash-ncrequests-acp-manage_requests_module&amp;mode=manage_requests", true, $this->user->session_id),
				));
			}
		}
	}

	public function add_permissions($event)
	{
		$permissions = $event['permissions'];
		$permissions['a_laxslash_ncrequests_manage_name_change_requests'] = array(
			'lang' => 'ACL_A_LAXSLASH_NCREQUESTS_MANAGE_NAME_CHANGE_REQUESTS',
			'cat' => 'user_group',
		);
		$permissions['a_laxslash_ncrequests_edit_name_change_requests'] = array(
			'lang' => 'ACL_A_LAXSLASH_NCREQUESTS_EDIT_NAME_CHANGE_REQUESTS',
			'cat' => 'user_group',
		);
		$permissions['u_laxslash_ncrequests_submit_ncr'] = array(
			'lang' => 'ACL_U_LAXSLASH_NCREQUESTS_SUBMIT_NCR',
			'cat' => 'profile',
		);
		$event['permissions'] = $permissions;
		unset($permissions);
	}

/*
 * We need to submit an event request to be able to do this.
 */

/**	public function get_acp_username_on_submit($event)
	{
		$data = $event['data']
		if ($data['username'] != $this->user->data['username']
		{

		}
		// Check the requests table
		$sql = 'SELECT COUNT(request_id) AS matching_requests
				FROM ' . LAXSLASH_NCREQUESTS_REQUESTS_TABLE . "
				WHERE requested_username = '" . $this->db->sql_escape($username);
		exit(print_r($event));

		// If the option is selected, and the user ID target matches the user ID that has the open request, we can approve the request through here, provided the admin has
		// request management permission.

		// Alternatively, if the user has a request open and the option is enabled, no username changes through ACP are allowed for this user until their request is approved/denied.

		// If the option is enabled, and the request is open for a user that is NOT being edited, edit this user and then deny their request.

		// Are we requiring users without the (an) override perm to go through username requests, and denying ACP changes without?

		// Alert that the user has a request open?

	} **/
}