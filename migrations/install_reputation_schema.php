<?php
/**
 *
 * Date Topic Event Calendar. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version	2 (GPL-2.0)
 *
 */

namespace phpbbstudio\dtst\migrations;

class install_reputation_schema extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v32x\v322');
	}

	public function update_schema()
	{
		return array(
			'add_tables'	=> array(
				$this->table_prefix . 'dtst_reputation'	=> array(
					'COLUMNS'	=> array(
						'reputation_id'		=> array('ULINT', null, 'auto_increment'),
						'topic_id'			=> array('ULINT', 0),
						'user_id'			=> array('ULINT', 0),
						'recipient_id'		=> array('ULINT', 0),
						'reputation_action'	=> array('ULINT', 0),
						'reputation_points'	=> array('BINT', 0),
						'reputation_reason'	=> array('VCHAR_CI', ''),
						'reputation_time'	=> array('TIMESTAMP', 0),
					),
					'PRIMARY_KEY'		=> 'reputation_id',
			))
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_tables' => array(
				$this->table_prefix . 'dtst_reputation',
			)
		);
	}
}
