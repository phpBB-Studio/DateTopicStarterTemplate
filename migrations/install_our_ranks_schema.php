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

use phpbbstudio\dtst\ext;

class install_our_ranks_schema extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v32x\v322');
	}

	public function update_schema()
	{
		/**
		 * From 0 to 100 steps are: (0), (1) then every (10) as per ext.php constants
		 */
		return array(
			'add_tables'	=> array(
			$this->table_prefix . 'dtst_ranks ' =>	array(
				'COLUMNS'		=> array(
					'dtst_rank_id'		=> array('ULINT', null, 'auto_increment'),
					'dtst_rank_isocode'	=> array('VCHAR_UNI', null),					// language isocode as per the phpBB guidelines, MAX 255
					'dtst_rank_value'	=> array('TINT:12', null),						// value
					'dtst_rank_title'	=> array('VCHAR_UNI:30', null),					// MAX 30
					'dtst_rank_desc'	=> array('VCHAR_UNI:30', null),					// title attr. + rank text MAX 30
					'dtst_rank_bckg'	=> array('VCHAR_UNI:7', null),					// HexDec color MAX 7
					'dtst_rank_text'	=> array('VCHAR_UNI:7', null),					// HexDec color MAX 7
				),
					'PRIMARY_KEY' => 'dtst_rank_id',
			))
		);
	}

	public function update_data()
	{
		return array(
			array('custom', array(array($this, 'insert_default_ranks'))),
		);
	}

	/**
	 * Inserts the default Ranks
	 *
	 * @access public
	 * @return void
	 */
	public function insert_default_ranks()
	{
		$insert_data = array(
			array(
				'dtst_rank_isocode'		=> 'en',
				'dtst_rank_value'		=> ext::DTST_RANK_ZERO,
				'dtst_rank_title'		=> 'Village Idiot',
				'dtst_rank_bckg'		=> '#ff0000',
				'dtst_rank_text'		=> '#000000',
				'dtst_rank_desc'		=> 'Unwelcome',
			),
			array(
				'dtst_rank_isocode'		=> 'en',
				'dtst_rank_value'		=> ext::DTST_RANK_MIN,
				'dtst_rank_title'		=> 'Beginner',
				'dtst_rank_bckg'		=> '#b72b34',
				'dtst_rank_text'		=> '#0000a0',
				'dtst_rank_desc'		=> 'New Member',
			),
			array(
				'dtst_rank_isocode'		=> 'en',
				'dtst_rank_value'		=> ext::DTST_RANK_ONE,
				'dtst_rank_title'		=> 'Tourist',
				'dtst_rank_bckg'		=> '#ff80c0',
				'dtst_rank_text'		=> '#6600cc',
				'dtst_rank_desc'		=> 'Very low Member',
			),
			array(
				'dtst_rank_isocode'		=> 'en',
				'dtst_rank_value'		=> ext::DTST_RANK_TWO,
				'dtst_rank_title'		=> 'Commuter',
				'dtst_rank_bckg'		=> '#ffb6c1',
				'dtst_rank_text'		=> '#ffffff',
				'dtst_rank_desc'		=> 'Low Member',
			),
			array(
				'dtst_rank_isocode'		=> 'en',
				'dtst_rank_value'		=> ext::DTST_RANK_THREE,
				'dtst_rank_title'		=> 'Sightseer',
				'dtst_rank_bckg'		=> '#ff6a6a',
				'dtst_rank_text'		=> '#cc00cc',
				'dtst_rank_desc'		=> 'Light Member',
			),
			array(
				'dtst_rank_isocode'		=> 'en',
				'dtst_rank_value'		=> ext::DTST_RANK_FOUR,
				'dtst_rank_title'		=> 'Wanderer',
				'dtst_rank_bckg'		=> '#ffb6c1',
				'dtst_rank_text'		=> '#ffffff',
				'dtst_rank_desc'		=> 'Above Light Member',
			),
			array(
				'dtst_rank_isocode'		=> 'en',
				'dtst_rank_value'		=> ext::DTST_RANK_FIVE,
				'dtst_rank_title'		=> 'Backpacker',
				'dtst_rank_bckg'		=> '#34dc52',
				'dtst_rank_text'		=> '#0080c0',
				'dtst_rank_desc'		=> 'Medium Member',
			),
			array(
				'dtst_rank_isocode'		=> 'en',
				'dtst_rank_value'		=> ext::DTST_RANK_SIX,
				'dtst_rank_title'		=> 'Rolling Stone',
				'dtst_rank_bckg'		=> '#158277',
				'dtst_rank_text'		=> '#003853',
				'dtst_rank_desc'		=> 'Above Medium Member',
			),
			array(
				'dtst_rank_isocode'		=> 'en',
				'dtst_rank_value'		=> ext::DTST_RANK_SEVEN,
				'dtst_rank_title'		=> 'Adventurer',
				'dtst_rank_bckg'		=> '#477a76',
				'dtst_rank_text'		=> '#71ff71',
				'dtst_rank_desc'		=> 'Below Average Member',
			),
			array(
				'dtst_rank_isocode'		=> 'en',
				'dtst_rank_value'		=> ext::DTST_RANK_EIGHT,
				'dtst_rank_title'		=> 'Traveler',
				'dtst_rank_bckg'		=> '#c100c1',
				'dtst_rank_text'		=> '#ffffff',
				'dtst_rank_desc'		=> 'Average Member',
			),
			array(
				'dtst_rank_isocode'		=> 'en',
				'dtst_rank_value'		=> ext::DTST_RANK_NINE,
				'dtst_rank_title'		=> 'Journeyman',
				'dtst_rank_bckg'		=> '#ff8000',
				'dtst_rank_text'		=> '#400080',
				'dtst_rank_desc'		=> 'Above Average Member',
			),
			array(
				'dtst_rank_isocode'		=> 'en',
				'dtst_rank_value'		=> ext::DTST_RANK_TEN,
				'dtst_rank_title'		=> 'Voyager',
				'dtst_rank_bckg'		=> '#ffff00',
				'dtst_rank_text'		=> '#0000ff',
				'dtst_rank_desc'		=> 'Trusted Member',
			),
		);
		$this->db->sql_multi_insert($this->table_prefix . 'dtst_ranks ', $insert_data);
	}

	public function revert_schema()
	{
		return array(
			'drop_tables' => array(
				$this->table_prefix . 'dtst_ranks ',
			)
		);
	}
}
