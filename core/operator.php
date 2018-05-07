<?php
/**
 *
 * Date Topic Event Calendar. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\dtst\core;

/**
 * Date Topic Event Calendar's helper service.
 */
class operator
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\db_text */
	protected $config_text;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string PHP extension */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth						$auth			Authentication object
	 * @param \phpbb\config\db_text					$config_text
	 * @param \phpbb\db\driver\driver_interface		$db				Database object
	 * @param										$root_path		phpBB root path
	 * @param										$php_ext		PHP extension
	 * @access public
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\db_text $config_text, \phpbb\db\driver\driver_interface $db, $root_path, $php_ext)
	{
		$this->auth			= $auth;
		$this->config_text	= $config_text;
		$this->db			= $db;

		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;
	}

	/**
	 * Returns whether the user is authed
	 *
	 * @return bool
	 * @access public
	 */
	public function is_authed()
	{
		return (bool) ($this->auth->acl_get('u_allow_dtst') || $this->auth->acl_get('a_dtst_admin'));
	}

	/**
	 * Returns the list of preset locations from the DB
	 *
	 * @return array
	 * @access public
	 */
	public function dtst_json_decode_locations()
	{
		return json_decode($this->config_text->get('dtst_locations'), true);
	}

	/**
	 * todo
	 *
	 * @param string	$select		The selected location
	 * @return string
	 * @access public
	 */
	public function dtst_location_preset_select($select = '')
	{
		$forum_dtst_preset_location = $this->dtst_json_decode_locations();

		/* Sort Array (Ascending Order), According to Value */
		asort($forum_dtst_preset_location);

		$location_preset = '';

		foreach ($forum_dtst_preset_location as $key => $value)
		{
			$selected = ($value === $select) ? ' selected="selected"' : '';

			$location_preset .= '<option' . $selected . '>' . htmlspecialchars_decode($value, ENT_COMPAT) . '</option>';
		}

		return $location_preset;
	}

	/**
	 * Create the selection for the topic's location of choice
	 *
	 * @param			$value
	 * @param string	$key
	 * @return string
	 * @access public
	 */
	public function dtst_forum_select($value, $key = '')
	{
		if (!function_exists('make_forum_select'))
		{
			include($this->root_path . 'includes/functions_admin.' . $this->php_ext);
		}

		return '<select id="' . $key . '" name="dtst_event_type">' . make_forum_select($value, false, false, false, true, true) . '</select>';
	}

	/**
	 * Returns the form's name based on the forum ID
	 *
	 * @param int		$forum_id		the forum ID to use
	 * @return string	$forum_name		the forum name
	 * @access public
	 */
	public function dtst_forum_id_to_name($forum_id)
	{
		$sql = 'SELECT forum_name
				FROM ' . FORUMS_TABLE . '
				WHERE forum_id = ' . (int) $forum_id;
		$result = $this->db->sql_query($sql);
		$forum_name = $this->db->sql_fetchfield('forum_name');
		$this->db->sql_freeresult($result);

		return (string) $forum_name;
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
	 * Check if Date Topic Event Calendar is enabled for this forum
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
