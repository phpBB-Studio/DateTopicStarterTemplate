<?php
/**
 *
 * Date Topic Event Calendar. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\dtst\migrations;

class install_configs extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v32x\v322');
	}

	public function update_data()
	{
		return array(
			/* (BOOL) Topic locked withdrawal 1 = allow opting if locked - 0 = disallow */
			array('config.add', array('dtst_locked_withdrawal', 1)),

			/* (INT) notifications sent counter */
			array('config.add', array('dtst_notification_id', 0)),

			/* (BOOL) Sidebar position 1 = left - 0 = right */
			array('config.add', array('dtst_sidebar', 1)),

			/* (INT) Bot user ID default - 2 founder */
			array('config.add', array('dtst_bot', 2)),

			/* (BOOL) Use of the Bot - 1 = yes - 0 = No */
			array('config.add', array('dtst_use_bot', 0)),

		);
	}
}
