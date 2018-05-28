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
					'dtst_location'			=>	array('TEXT_UNI', null), /* The preset location for the topic, user input */
					'dtst_loc_custom'		=>	array('TEXT_UNI', null), /* The custom location for the topic, user input */
					'dtst_host'				=>	array('TEXT_UNI', null), /* The Host for the topic, user input */
					'dtst_date'				=>	array('VCHAR_UNI', null), /* The Date stored as string, user input */
					'dtst_date_max'			=>	array('VCHAR_UNI', null), /* The Date of end event stored as string, user input */
					'dtst_date_unix'		=>	array('TIMESTAMP', 0), /* The Date stored as UNIX TIMESTAMP, for DBAL WIP */
					'dtst_date_max_unix'	=>	array('TIMESTAMP', 0), /* The Date of end event stored as UNIX TIMESTAMP, for DBAL WIP */
					'dtst_event_type'		=>	array('UINT', 0), /* The type of event for the topic, user input */
					'dtst_age_min'			=>	array('TINT:2', 0), /* The min age for the topic, user input - min 0 = open */
					'dtst_age_max'			=>	array('TINT:2', 0), /* The max age for the topic, user input - max 99 - min 0 = open */
					'dtst_participants'		=>	array('UINT:3', 0), /* The # of partecipants for the topic max 999, user input - min 0 = unlimited */
					'dtst_participants_unl'	=>	array('BOOL', 0), /* If the participants are unlimited, no as per default */
				),
				$this->table_prefix . 'forums'		=> array(
					'dtst_f_enable'			=>	array('BOOL', 0), /* Does not affect the existing forums */
					'dtst_f_forced_fields'	=>	array('BOOL', 0), /* Does not affect the existing forums */
				),
				$this->table_prefix . 'users'	=>	array(
					'dtst_auto_reload'		=>	array('BOOL', 1), /* Yes as per default */
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
					'dtst_loc_custom',
					'dtst_host',
					'dtst_date',
					'dtst_date_max',
					'dtst_date_unix',
					'dtst_date_max_unix',
					'dtst_event_type',
					'dtst_age_min',
					'dtst_age_max',
					'dtst_participants',
					'dtst_participants_unl',
				),
				$this->table_prefix . 'forums'	=> array(
					'dtst_f_enable',
					'dtst_f_forced_fields',
				),
				$this->table_prefix . 'users'	=> array(
					'dtst_auto_reload',
				),
			),
		);
	}
}
