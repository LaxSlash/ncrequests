<?php
/**
 * This file is a part of the Force Style Changes modification by
 * lax.slash for the phpBB 3.1 Forums Software.
 *
 * @copyright (c) lax.slash <https://www.github.com/LaxSlash>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace laxslash\ncrequests\notifications;

class ncrequest_status_change_approved extends \phpbb\notification\type\base
{
	public function get_type()
	{
		return 'laxslash.ncrequests.ncrequest_status_change_approved';
	}

	public function is_available()
	{
		return false;
	}
}
