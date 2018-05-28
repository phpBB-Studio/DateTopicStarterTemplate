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

class install_dtst_schema extends \phpbb\db\migration\migration
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
					'user_id'	=> array('UINT', 0),
					'topic_id'	=> array('UINT', 0),
					'active'	=> array('BOOL', 0),
					'inactive'	=> array('BOOL', 0), // for the new idea of management of this part
					'post_time' => array('TIMESTAMP', 0),
				),
				'KEYS'			=> array(
					'id'		=> array('UNIQUE', array('user_id', 'topic_id')))
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
