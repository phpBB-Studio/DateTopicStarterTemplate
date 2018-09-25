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

use phpbbstudio\dtst\ext;

class install_user_schema extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		/* If doesn't exist go ahead */
		return $this->db_tools->sql_column_exists($this->table_prefix . 'topics', 'dtst_location');
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
					'dtst_date_unix'		=>	array('TIMESTAMP', 0), /* The Date stored as UNIX TIMESTAMP */
					'dtst_event_type'		=>	array('UINT', 0), /* The type of event for the topic, user input */
					'dtst_age_min'			=>	array('TINT:2', 0), /* The min age for the topic, user input - min 0 = open */
					'dtst_age_max'			=>	array('TINT:2', 0), /* The max age for the topic, user input - max 99 - min 0 = open */
					'dtst_participants'		=>	array('UINT:3', 0), /* The # of participants for the topic max 999, user input - min 0 = unlimited */
					'dtst_participants_unl'	=>	array('BOOL', 0), /* If the participants are unlimited, no as per default */
					'dtst_event_canceled'	=>	array('BOOL', 0), /* If the topic event has been canceled by the host */
					'dtst_event_ended'		=>	array('BOOL', 0), /* If the topic event has ended */
					'dtst_rep_ended'		=>	array('BOOL', 0), /* If the topic event reputation giving has ended */
				),
				$this->table_prefix . 'forums'		=> array(
					'dtst_f_enable'			=>	array('BOOL', 0), /* Does not affect the existing forums */
					'dtst_f_forced_fields'	=>	array('BOOL', 0), /* Does not affect the existing forums */
				),
				$this->table_prefix . 'users'	=>	array(
					'dtst_auto_reload'		=>	array('BOOL', 1), /* Yes as per default */
					'dtst_reputation'		=>	array('BINT', 50), /* Reputation points */
					'dtst_rank_value'		=>	array('TINT:1', ext::DTST_RANK_MIN),	// default 1
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
					'dtst_date_unix',
					'dtst_event_type',
					'dtst_age_min',
					'dtst_age_max',
					'dtst_participants',
					'dtst_participants_unl',
					'dtst_event_canceled',
					'dtst_event_ended',
					'dtst_rep_ended',
				),
				$this->table_prefix . 'forums'	=> array(
					'dtst_f_enable',
					'dtst_f_forced_fields',
				),
				$this->table_prefix . 'users'	=> array(
					'dtst_auto_reload',
					'dtst_reputation',
					'dtst_rank_value',
				),
			),
		);
	}
}
