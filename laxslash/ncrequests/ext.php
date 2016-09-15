<?php
/**
 * This file is a part of the Force Style Changes modification by
 * lax.slash for the phpBB 3.1 Forums Software.
 *
 * @copyright (c) lax.slash <https://www.github.com/LaxSlash>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace laxslash\ncrequests;

class ext extends \phpbb\extension\base
{
	public function enable_step($old_state)
	{
		switch ($old_state)
		{
			case '':
				$phpbb_notifications = $this->container->get('notification_manager');
				$phpbb_notifications->enable_notifications('laxslash.ncrequests.notification.type.ncr_approval');
				$phpbb_notifications->enable_notifications('laxslash.ncrequests.notification.type.ncr_denial');
				$phpbb_notifications->enable_notifications('laxslash.ncrequests.notification.type.ncr_edit');
				return 'notifications';
			break;

			default:
				return parent::enable_step($old_state);
			break;
		}
	}

	public function disable_step($old_state)
	{
		switch ($old_state)
		{
			case '':
				$phpbb_notifications = $this->container->get('notification_manager');
				$phpbb_notifications->disable_notifications('laxslash.ncrequests.notification.type.ncr_approval');
				$phpbb_notifications->disable_notifications('laxslash.ncrequests.notification.type.ncr_denial');
				$phpbb_notifications->disable_notifications('laxslash.ncrequests.notification.type.ncr_edit');
				return 'notifications';
			break;

			default:
				return parent::disable_step($old_state);
			break;
		}
	}

	public function purge_step($old_state)
	{
		switch ($old_state)
		{
			case '':
				$phpbb_notifications = $this->container->get('notification_manager');
				$phpbb_notifications->purge_notifications('laxslash.ncrequests.notification.type.ncr_approval');
				$phpbb_notifications->purge_notifications('laxslash.ncrequests.notification.type.ncr_denial');
				$phpbb_notifications->purge_notifications('laxslash.ncrequests.notification.type.ncr_edit');
				return 'notifications';
			break;

			default:
				return parent::purge_step($old_state);
			break;
		}
	}
}

