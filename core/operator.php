<?php
/**
 *
 * Date Topic Starter Template. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\dtst\core;

/**
 * Date Topic Starter Template's helper service.
 */
class operator
{
	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/**
	 * Constructor
	 *
	 * @param  \phpbb\db\driver\driver_interface		$db		Database object
	 * @access public
	 */
	public function __construct(\phpbb\db\driver\driver_interface $db)
	{
		$this->db	= $db;
	}

	/**
	 * Returns the SQL main SELECT statement used in various places.
	 *
	 * @param string	$dtst_mode	the SELECT data to be used on purpose
	 * @param int		$forum_id	the forum ID to use on purpose
	 * @return string	$sql		the DBAL SELECT statement
	 * @access protected
	 */
	protected function dtst_sql($dtst_mode, $forum_id)
	{
		$sql = 'SELECT ' . $dtst_mode . '
				FROM ' . FORUMS_TABLE . '
				WHERE dtst_f_enable = ' . true . '
					AND forum_id = ' . (int) $forum_id;

		return $sql;
	}

	/**
	 * Check if Date Topic Starter Template is enabled for this forum
	 *
	 * @param string	$dtst_mode	the SELECTed data to be used on purpose
	 * @param int		$forum_id	the forum ID to use
	 * @return bool
	 * @access public
	 */
	public function forum_dtst_enabled($dtst_mode, $forum_id)
	{
		$sql = $this->dtst_sql($dtst_mode, $forum_id);

		$result = $this->db->sql_query($sql);
		$forum_dtst_enabled = $this->db->sql_fetchfield($dtst_mode);
		$this->db->sql_freeresult($result);

		return (bool) $forum_dtst_enabled;
	}

	/**
	 * Return the Forum Location if Date Topic Starter Template is enabled for this forum
	 *
	 * @param string	$dtst_mode	the SELECTed data to be used on purpose
	 * @param int		$forum_id	the forum ID to use
	 * @return string
	 * @access public
	 */
	public function forum_dtst_location($dtst_mode, $forum_id)
	{
		$sql = $this->dtst_sql($dtst_mode, $forum_id);

		$result = $this->db->sql_query($sql);
		$dtst_f_location = $this->db->sql_fetchfield($dtst_mode);
		$this->db->sql_freeresult($result);

		return (string) $dtst_f_location;
	}

	/**
	 * Check if the fields are all mandatory for this forum
	 *
	 * @param string	$dtst_mode	the SELECTed data to be used on purpose
	 * @param int		$forum_id	the forum ID to use
	 * @return bool
	 * @access public
	 */
	public function forum_dtst_forced_fields($dtst_mode, $forum_id)
	{
		$sql = $this->dtst_sql($dtst_mode, $forum_id);

		$result = $this->db->sql_query($sql);
		$dtst_f_forced_fields = $this->db->sql_fetchfield($dtst_mode);
		$this->db->sql_freeresult($result);

		return (bool) $dtst_f_forced_fields;
	}
}
