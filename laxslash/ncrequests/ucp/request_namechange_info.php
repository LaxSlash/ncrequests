<?php
/**
 * This file is a part of the Name Change Requests
 * phpBB 3.1 Extension by LaxSlash1993.
 *
 * @copyright (c) LaxSlash1993 <https://www.github.com/LaxSlash>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace laxslash\ncrequests\ucp;

class request_namechange_info
{
	function module()
	{
		return array(
			'filename'		=>		'\laxslash\ncrequests\ucp\request_namechange_module',
			'title'			=>		'UCP_LAXSLASH_NCREQUESTS_REQUEST_CHANGE_MODULE',
			'version'		=>		'0.4 ALPHA',
			'modes'			=>		array(
				'ncrequest'		=>		array(
					'title'		=>		'UCP_LAXSLASH_NCREQUESTS_REQUEST_CHANGE_MODULE',
					'auth'		=>		'ext_laxslash/ncrequests && acl_u_laxslash_ncrequests_submit_ncr',
					'cat'		=>		'UCP_PROFILE',
				),
			),
		);
	}
}
