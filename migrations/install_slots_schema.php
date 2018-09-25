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

class install_slots_schema extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v32x\v322');
	}

	public function update_schema()
	{
		return array(
			'add_tables'	=> array(
			$this->table_prefix . 'dtst_slots' => array(
				'COLUMNS'		=> array(
					'user_id'			=> array('ULINT', 0),
					'topic_id'			=> array('ULINT', 0),
					'dtst_time' 		=> array('TIMESTAMP', 0),
					'dtst_status'		=> array('TINT:5', 1),
					'dtst_reason'		=> array('VCHAR_UNI', null),
					'dtst_host_time'	=> array('TIMESTAMP', 0),
					'dtst_host_reason'	=> array('VCHAR_UNI', null),
				),
				'KEYS'			=> array(
					'id'				=> array('UNIQUE', array('user_id', 'topic_id')))
				)
			)
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_tables' => array(
				$this->table_prefix . 'dtst_slots',
			)
		);
	}
}
