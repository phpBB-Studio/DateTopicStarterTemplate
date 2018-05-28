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
			array('config.add', array('dtst_locked_withdrawal', 1)),
			array('config.add', array('dtst_utc', 'd m Y - H:i')),

			array('config.add', array('dtst_string_1', '')), // string_1
			array('config.add', array('dtst_bool_1', 0)), // bool_1
			array('config.add', array('dtst_string_2', '')), // string_2
			array('config.add', array('dtst_bool_2', 0)), // bool_2
		);
	}
}
