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
		$db = $this->container->get('dbal.conn');

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

		if ( !in_array($db->get_sql_layer(), array('mysqli', 'mysql4')) )
		{
			$lang['EXTENSION_NOT_ENABLEABLE'] .= '<br>' . $user->lang('DTST_ERROR_DBMS_TYPE');
			$is_enableable = false;
		}

		$user->lang = $lang;

		return $is_enableable;
	}
}
