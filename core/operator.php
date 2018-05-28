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

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string PHP extension */
	protected $php_ext;

	protected $dtst_slots;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth						$auth			Authentication object
	 * @param \phpbb\config\db_text					$config_text
	 * @param \phpbb\db\driver\driver_interface		$db				Database object
	 * @param  \phpbb\template\template				$template		Template object
	 * @param  \phpbb\user							$user			User object
	 * @param										$root_path		phpBB root path
	 * @param										$php_ext		PHP extension
	 * @access public
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\db_text $config_text, \phpbb\db\driver\driver_interface $db, \phpbb\template\template $template, \phpbb\user $user, $root_path, $php_ext, $dtst_slots)
	{
		$this->auth			= $auth;
		$this->config_text	= $config_text;
		$this->db			= $db;
		$this->template		= $template;
		$this->user			= $user;

		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;

		$this->dtst_slots	= $dtst_slots;
	}

	/**
	 * Template switches over all
	 *
	 * @return void
	 */
	public function dtst_template_switches_over_all()
	{
		$this->template->assign_vars(array(
			'S_DTST_FILTERS_PERMS'	=> (bool) $this->user->data['dtst_auto_reload'],
		));
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
	 * Returns the array of preset locations from the DB and cast it if null
	 *
	 * @return array
	 * @access public
	 */
	public function dtst_json_decode_locations()
	{
		return json_decode($this->config_text->get('dtst_locations'), true);
	}

	/**
	 * Returns the list of event type from the enabled forums's name
	 *
	 * @param string	$select		The selected event_type
	 * @return string
	 * @access public
	 */
	public function dtst_event_type_select($select = '')
	{
		$forum_dtst_event_type = $this->dtst_list_enabled_forum_names();

		$dtst_event_type = '';

		foreach ($forum_dtst_event_type as $key => $value)
		{
			$selected = ($key === $select) ? ' selected="selected"' : '';

			$dtst_event_type .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
		}

		return $dtst_event_type;
	}

	/**
	 * Returns the HTML formatted list of locations from a Json array in the DB
	 *
	 * @param string	$select		The selected location
	 * @return string
	 * @access public
	 */
	public function dtst_location_preset_select($select = '')
	{
		/* Pull the array from the DB or cast it if null */
		$forum_dtst_preset_location = (array) $this->dtst_json_decode_locations();

		/**
		 * Sort Array (Ascending Order) According its values using our
		 * supplied comparison function and maintaining index association.
		 */
		uasort($forum_dtst_preset_location, 'strnatcasecmp');

		$location_preset = '';

		foreach ($forum_dtst_preset_location as $key => $value)
		{
			$selected = ($value === $select) ? ' selected="selected"' : '';

			$location_preset .= '<option' . $selected . '>' . htmlspecialchars_decode($value, ENT_COMPAT) . '</option>';
		}

		return $location_preset;
	}

	/**
	 * Returns the forum's name based on the forum ID
	 *
	 * @param int		$forum_id		the forum ID to use
	 * @return string	$forum_name		the forum name
	 * @access public
	 */
	public function dtst_forum_id_to_name($forum_id)
	{
		$sql = 'SELECT forum_name
				FROM ' . FORUMS_TABLE . '
				WHERE forum_id = ' . (int) $forum_id . '
					AND forum_type = ' . FORUM_POST;
		/* Cache results for a bit */
		$result = $this->db->sql_query($sql, 600);
		$forum_name = $this->db->sql_fetchfield('forum_name');
		$this->db->sql_freeresult($result);

		return (string) $forum_name;
	}

	/**
	 * Returns the key and value of forums those they are DTST enabled
	 *
	 * @return array	$rowset
	 * @access public
	 */
	public function dtst_list_enabled_forum_names()
	{
		$sql = 'SELECT forum_id, forum_name
				FROM ' . FORUMS_TABLE . '
				WHERE dtst_f_enable = ' . true . '
					AND forum_type = ' . FORUM_POST . '
				ORDER BY forum_name ASC';
		/* Cache results for a bit */
		$result = $this->db->sql_query($sql, 600);

		$rowset = array();

		while ($row = $this->db->sql_fetchrow($result))
		{
			$rowset[(int) $row['forum_id']] = $row['forum_name'];
		}

		$this->db->sql_freeresult($result);

		return (array) $rowset;
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
					AND forum_type = ' . FORUM_POST . '
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
		/* Cache results for a bit */
		$result = $this->db->sql_query($sql, 600);
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
		/* Cache results for a bit */
		$result = $this->db->sql_query($sql, 600);
		$dtst_f_forced_fields = $this->db->sql_fetchfield($dtst_mode);
		$this->db->sql_freeresult($result);

		return (bool) $dtst_f_forced_fields;
	}

	/**
	 * Builds a query based on user input
	 *
	 * @param int    $topic_id Current topic ID
	 * @return string Query ready for execution
	 * @access public
	 */
	public function dtst_slots_query($topic_id)
	{
		$query = $this->db->sql_build_query('SELECT', [
			'SELECT'	=> 'u.username, u.user_colour, u.user_id, u.username_clean, d.active, d.post_time',
			'FROM'		=> [
				USERS_TABLE => 'u',
			],
			'LEFT_JOIN' => [
				[
					'FROM'	=> [$this->dtst_slots => 'd'],
					'ON'	=> 'd.user_id = u.user_id'
				]
			],
			'WHERE'		=> 'd.topic_id = ' . (int) $topic_id . '
				AND u.user_id <> ' . ANONYMOUS . '
				AND ' . $this->db->sql_in_set('u.user_type', [USER_NORMAL, USER_FOUNDER]),
			'ORDER_BY'	=> 'u.username_clean ASC'
		]);
//			'ORDER_BY'	=> 'd.active DESC, d.post_time ASC' // to make this optional?
		return $query;
	}

	/**
	 * Query the topics table based on user input
	 *
	 * @param int    $topic_id Current topic ID
	 * @return string Query ready for execution
	 * @access public
	 */
	public function dtst_topics_query($topic_id)
	{
		$query = 'SELECT dtst_participants
				FROM ' . TOPICS_TABLE . '
				WHERE topic_id = ' . (int) $topic_id;

		return $query;
	}
}
