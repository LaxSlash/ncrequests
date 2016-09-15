<?php
/**
 * This file is a part of the Name Change Requests
 * phpBB 3.1 Extension by LaxSlash1993.
 *
 * @copyright (c) LaxSlash1993 <https://www.github.com/LaxSlash>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace laxslash\ncrequests\acp;

class manage_requests_info
{
	public function module()
	{
		return array(
			'filename'		=>		'\laxslash\ncrequests\acp\manage_requests_module',
			'title'			=>		'ACP_LAXSLASH_NCREQUESTS_MANAGE_MODULE_TITLE',
			'version'		=>		'0.1-PL1 ALPHA',
			'modes'			=>		array(
				'manage_requests'		=>		array(
					'title'				=>		'ACP_LAXSLASH_NCREQUESTS_MANAGE_MODULE_TITLE',
					'auth'				=>		'ext_laxslash/ncrequests && acl_a_laxslash_ncrequests_manage_name_change_requests',
					'cat'				=>		array('ACP_CAT_USERS'),
				),
			),
		);
	}
}
