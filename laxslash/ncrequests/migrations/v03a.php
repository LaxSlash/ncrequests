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

class v03a extends migration
{
	static public function depends_on()
	{
		return array('\laxslash\ncrequests\migrations\v02a');
	}

	public function update_data()
	{
		return array(
			array('config.update', array('laxslash_ncrequests_version', '0.3 ALPHA')),
			array('config.add', array('laxslash_ncrequests_notification_id', 0)),
		);
	}
}
