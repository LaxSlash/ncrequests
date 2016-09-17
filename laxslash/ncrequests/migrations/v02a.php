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

class v02a extends migration
{
	static public function depends_on()
	{
		return array('\laxslash\ncrequests\migrations\v01pl1a');
	}

	public function update_data()
	{
		return array(
			array('config.update', array('laxslash_ncrequests_version', '0.2 ALPHA')),
			array('module.remove', array(
				'acp',
				'ACP_CAT_USERS',
				array(
					'module_basename' => '\laxslash\ncrequests\acp\manage_requests_module',
				),
			)),
			array('module.remove', array(
				'ucp',
				'UCP_PROFILE',
				array(
					'module_basename' => '\laxslash\ncrequests\ucp\request_namechange_module',
				),
			)),
			array('module.add', array(
				'acp',
				'ACP_CAT_USERS',
				array(
					'module_basename' => '\laxslash\ncrequests\acp\manage_requests_module',
				),
			)),
			array('module.add', array(
				'ucp',
				'UCP_PROFILE',
				array(
					'module_basename' => '\laxslash\ncrequests\ucp\request_namechange_module',
				),
			)),
		);
	}
}
