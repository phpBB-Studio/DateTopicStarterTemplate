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

class install_user_schema extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		/* If doesn't exist go ahead */
		return $this->db_tools->sql_column_exists($this->table_prefix . 'topics', 'dtst_date');
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v32x\v322');
	}

	public function update_schema()
	{
		return array(
			'add_columns'	=> array(
				$this->table_prefix . 'topics'		=> array(
					'dtst_location'		=>	array('TEXT_UNI', null), /* The location for the topic, user input */
					'dtst_host'			=>	array('TEXT_UNI', null), /* The Host for the topic, user input */
					'dtst_date'			=>	array('VCHAR_UNI', null), /* The Date stored as string, user input */
				),
				$this->table_prefix . 'forums'		=> array(
					'dtst_f_enable'		=>	array('BOOL', 1), /* All the existing/newly created forums will be enabled as default */
					'dtst_f_location'	=>	array('TEXT_UNI', null), /* The Location for the forum, admin input */
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_columns' => array(
				$this->table_prefix . 'topics'	=> array(
					'dtst_location',
					'dtst_host',
					'dtst_date',
		),
				$this->table_prefix . 'forums'	=> array(
					'dtst_f_enable',
					'dtst_f_location',
				),
			),
		);
	}
}
