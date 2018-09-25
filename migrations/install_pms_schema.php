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

class install_pms_schema extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v32x\v322');
	}

	public function update_schema()
	{
		/**
		 * 0 unused	NULL, from 1 to	3 as per ext.php constants
		 * const DTST_STATUS_PM_APPLY =	1
		 * const DTST_STATUS_PM_CANCEL = 2
		 * const DTST_STATUS_PM_WITHDRAWAL = 3
		 */
		return array(
			'add_tables'	=> array(
			$this->table_prefix . 'dtst_privmsg' =>	array(
				'COLUMNS'		=> array(
					'dtst_pm_id'		=> array('ULINT', null, 'auto_increment'),
					'dtst_pm_isocode'	=> array('VCHAR_UNI', null),// language isocode as per the phpBB guidelines, MAX 255
					'dtst_pm_status'	=> array('TINT:3', 0),//APPLY = 1 - CANCEL = 2 - WITHDRAWAL = 3
					'dtst_pm_message'	=> array('TEXT_UNI', null),// MAX 255
					'dtst_pm_title'		=> array('VCHAR_UNI', null),// MAX 255
				),
					'PRIMARY_KEY' => 'dtst_pm_id',
			))
		);
	}

	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'insert_default_en_pms'))),
		);
	}

	/**
	 * Inserts the default english PMs templates
	 *
	 * @access public
	 * @return void
	 */
	public function insert_default_en_pms()
	{
		$insert_data = array(
			array(
				'dtst_pm_isocode'	=> 'en',
				'dtst_pm_status'	=> 1,
				'dtst_pm_message'	=> 'Dear {RECIP_NAME_FULL}, 
{SENDER_NAME_FULL} applied for the event: [b]{P_TITLE}[/b].

You can manage that at the following link: {P_LINK}.

Best regards.
The Management.',
				'dtst_pm_title'		=> '{SENDER_NAME}’s application for: {P_TITLE}',
			),
			array(
				'dtst_pm_isocode'	=> 'en',
				'dtst_pm_status'	=> 2,
				'dtst_pm_message'	=> 'Dear {RECIP_NAME_FULL}, 
{SENDER_NAME_FULL} canceled the application for the event: [b]{P_TITLE}[/b].

You can check that at the following link: {P_LINK}.

Best regards.
The Management.',
				'dtst_pm_title'		=> '{SENDER_NAME}’s cancellation to: {P_TITLE}',
			),
			array(
				'dtst_pm_isocode'	=> 'en',
				'dtst_pm_status'	=> 3,
				'dtst_pm_message'	=> 'Dear {RECIP_NAME_FULL}, 
{SENDER_NAME_FULL} withdrew the attending for the event: [b]{P_TITLE}[/b].

You can check that at the following link: {P_LINK}.

Best regards.
The Management.',
				'dtst_pm_title'		=> '{SENDER_NAME}’s withdrawal to: {P_TITLE}',
			),
		);
		$this->db->sql_multi_insert($this->table_prefix . 'dtst_privmsg', $insert_data);
	}

	public function revert_schema()
	{
		return array(
			'drop_tables' => array(
				$this->table_prefix . 'dtst_privmsg',
			)
		);
	}
}
