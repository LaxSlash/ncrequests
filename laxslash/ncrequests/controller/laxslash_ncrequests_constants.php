<?php
/**
 * This file is a part of the Force Style Changes modification by
 * lax.slash for the phpBB 3.1 Forums Software.
 *
 * @copyright (c) lax.slash <https://www.github.com/LaxSlash>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace laxslash\ncrequests\controller;

class laxslash_ncrequests_constants
{
	/* @var $requests_table */
	protected $requests_table;

	/* @var $admin_log_table */
	protected $admin_log_table;

	/**
	 * Constructor
	 *
	 * @static $requests_table
	 */
	public function __construct($requests_table, $admin_log_table)
	{
		$this->requests_table = $requests_table;
		$this->admin_log_table = $admin_log_table;
	}

	/** Declare the database constants here.
	 *
	 * @return null Nothing to return here. Just constants to set.
	 */
	public function dec_ncrequests_consts_for_db()
	{
		define('LAXSLASH_NCREQUESTS_REQUESTS_TABLE', $this->requests_table);
		define('LAXSLASH_NCREQUESTS_ADMINLOG_TABLE', $this->admin_log_table);
		define('LAXSLASH_NCREQUESTS_REQUEST_STATUS_PENDING', 0);
		define('LAXSLASH_NCREQUESTS_LOG_TYPE_CREATE', 0);
		define('LAXSLASH_NCREQUESTS_REQUEST_STATUS_APPROVED', 1);
		define('LAXSLASH_NCREQUESTS_LOG_TYPE_APPROVAL', 1);
		define('LAXSLASH_NCREQUESTS_REQUEST_STATUS_DENIED', 2);
		define('LAXSLASH_NCREQUESTS_LOG_TYPE_DENY', 2);
		define('LAXSLASH_NCREQUESTS_REQUEST_STATUS_USER_CANCELLED', 3);
		define('LAXSLASH_NCREQUESTS_LOG_TYPE_USER_CANCEL', 3);
		define('LAXSLASH_NCREQUESTS_LOG_TYPE_EDIT', 4);
		return;
	}
}