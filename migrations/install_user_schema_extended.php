<?php
/**
 *
 * Date Topic Starter Template. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\dtst\migrations;

class install_user_schema_extended extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		/* If doesn't exist go ahead */
		return $this->db_tools->sql_column_exists($this->table_prefix . 'forums', 'dtst_f_forced_fields');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v32x\v322');
	}

	public function update_schema()
	{
		return array(
			'add_columns'	=> array(
				$this->table_prefix . 'forums'	=> array(
					'dtst_f_forced_fields'	=>	array('BOOL', 1), /* All the existing/newly created forums will be enabled with all fields as mandatory */
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_columns' => array(
				$this->table_prefix . 'forums'	=> array(
					'dtst_f_forced_fields',
				),
			),
		);
	}
}
