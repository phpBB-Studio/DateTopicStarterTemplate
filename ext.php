<?php
/**
 *
 * Date Topic Event Calendar. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\dtst;

/**
 * Date Topic Event Calendar Extension base
 */
class ext extends \phpbb\extension\base
{
	/* Define constants to be used everywhere */
	const DTST_STATUS_PENDING = 1;
	const DTST_STATUS_ACCEPTED = 2;
	const DTST_STATUS_DENIED = 3;
	const DTST_STATUS_CANCELED = 4;
	const DTST_STATUS_WITHDRAWN = 5;

	const DTST_STATUS_PM_APPLY = 1;
	const DTST_STATUS_PM_CANCEL = 2;
	const DTST_STATUS_PM_WITHDRAWAL = 3;

	/**
	 * Check whether the extension can be enabled.
	 * Provides meaningful(s) error message(s) and the back-link on failure.
	 * CLI and 3.1/3.2 compatible (we do not use the $lang object here on purpose)
	 *
	 * @return bool
	 */
	public function is_enableable()
	{
		$is_enableable = true;

		$user = $this->container->get('user');
		$user->add_lang_ext('phpbbstudio/dtst', 'ext_require');
		$lang = $user->lang;

		if ( !(phpbb_version_compare(PHPBB_VERSION, '3.2.2', '>=') && phpbb_version_compare(PHPBB_VERSION, '3.3.0@dev', '<')) )
		{
			$lang['EXTENSION_NOT_ENABLEABLE'] .= '<br>' . $user->lang('DTST_ERROR_322_VERSION');
			$is_enableable = false;
		}

		if (!phpbb_version_compare(PHP_VERSION, '5.5', '>='))
		{
			$lang['EXTENSION_NOT_ENABLEABLE'] .= '<br>' . $user->lang('DTST_ERROR_PHP_VERSION');
			$is_enableable = false;
		}

		$user->lang = $lang;

		return $is_enableable;
	}

	/**
	 * Enable notifications for the extension
	 *
	 * @param mixed $old_state State returned by previous call of this method
	 *
	 * @return mixed Returns false after last step, otherwise temporary state
	 */
	public function enable_step($old_state)
	{
		switch ($old_state)
		{
			case '': // Empty means nothing has run yet

				$phpbb_notifications = $this->container->get('notification_manager');
				$phpbb_notifications->enable_notifications('phpbbstudio.dtst.notification.type.opting');
				return 'notification';

			break;

			default:

				return parent::enable_step($old_state);

			break;
		}
	}

	/**
	 * Disable notifications for the extension
	 *
	 * @param mixed $old_state State returned by previous call of this method
	 *
	 * @return mixed Returns false after last step, otherwise temporary state
	 */
	public function disable_step($old_state)
	{
		switch ($old_state)
		{
			case '': // Empty means nothing has run yet

				$phpbb_notifications = $this->container->get('notification_manager');
				$phpbb_notifications->disable_notifications('phpbbstudio.dtst.notification.type.opting');
				return 'notification';

			break;

			default:

				return parent::disable_step($old_state);

			break;
		}
	}

	/**
	 * Purge notifications for the extension
	 *
	 * @param mixed $old_state State returned by previous call of this method
	 *
	 * @return mixed Returns false after last step, otherwise temporary state
	 */
	public function purge_step($old_state)
	{
		switch ($old_state)
		{
			case '': // Empty means nothing has run yet

				$phpbb_notifications = $this->container->get('notification_manager');
				$phpbb_notifications->purge_notifications('phpbbstudio.dtst.notification.type.opting');
				return 'notification';

			break;

			default:

				return parent::purge_step($old_state);

			break;
		}
	}
}
