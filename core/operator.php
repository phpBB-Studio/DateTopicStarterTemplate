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

	/** @var \phpbb\config\config */

	/** @var \phpbb\config\db_text */
	protected $config_text;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string PHP extension */
	protected $php_ext;

	protected $dtst_slots;

	protected $dtst_privmsg;

	/**
	 * Constructor
	 *
	 * @param \phpbb\auth\auth						$auth			Authentication object
	 * @param \phpbb\config\config					$config			Configuration	object
	 * @param \phpbb\config\db_text					$config_text	Text configuration object
	 * @param \phpbb\db\driver\driver_interface		$db				Database object
	 * @param \phpbb\language\language				$lang			Language object
	 * @param \phpbb\template\template				$template		Template object
	 * @param \phpbb\user							$user			User object
	 * @param string								$root_path		phpBB	root path
	 * @param string								$php_ext		PHP extension
	 * @param string								$dtst_slots		DTST Slots table
	 * @param string								$dtst_privmsg	DTST PMs table
	 * @access public
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\config\db_text $config_text, \phpbb\db\driver\driver_interface $db, \phpbb\language\language $lang, \phpbb\template\template $template, \phpbb\user $user, $root_path, $php_ext, $dtst_slots, $dtst_privmsg)
	{
		$this->auth			= $auth;
		$this->config		= $config;
		$this->config_text	= $config_text;
		$this->db			= $db;
		$this->lang			= $lang;
		$this->template		= $template;
		$this->user			= $user;

		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;

		$this->dtst_slots	= $dtst_slots;
		$this->dtst_privmsg	= $dtst_privmsg;
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

		/* We need the first selection to be false for the sake of posting regular topics */
		$fake_selection = array("0" => "",);

		/* Join arrays maintaining the correct keys */
		$forum_dtst_event_type = $fake_selection + $forum_dtst_event_type;

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
	 * @param int	$topic_id	Current topic ID
	 * @return string			Query ready for execution
	 * @access public
	 */
	public function dtst_slots_query($topic_id)
	{
		$query = $this->db->sql_build_query('SELECT', array(
			'SELECT'	=> 'u.username, u.user_colour, u.user_id, u.username_clean, d.dtst_status, d.dtst_time, d.dtst_reason, d.dtst_host_time, d.dtst_host_reason',

			'FROM'		=> array(USERS_TABLE => 'u'),

			'LEFT_JOIN' => array(
				array(
					'FROM'	=> array($this->dtst_slots => 'd'),
					'ON'	=> 'd.user_id = u.user_id'
				)
			),

			'WHERE'		=> 'd.topic_id = ' . (int) $topic_id . '
				AND u.user_id <> ' . ANONYMOUS . '
				AND ' . $this->db->sql_in_set('u.user_type', array(USER_NORMAL, USER_FOUNDER)),

			'ORDER_BY'	=> 'u.username_clean ASC'
		));
		return $query;
	}

	/**
	 * Query the topics table based on user input
	 *
	 * @param int	$topic_id	Current topic ID
	 * @return string			Query ready for execution
	 * @access public
	 */
	public function dtst_topics_query($topic_id)
	{
		$query = 'SELECT dtst_participants
				FROM ' . TOPICS_TABLE . '
				WHERE topic_id = ' . (int) $topic_id;

		return $query;
	}

	/**
	 * Tokens to be replaced with configured values
	 *
	 * @return	array
	 * @access public
	 */
	public function dtst_tokens()
	{
		/* Map arguments for tokens */
		return $dtst_tokens = array(
			'{SENDER_NAME_FULL}',
			'{SENDER_NAME}',
			'{RECIP_NAME_FULL}',
			'{RECIP_NAME}',
			'{P_LINK}',
			'{P_TITLE}',
		);
	}

	/**
	 * Replacement values for tokens
	 *
	 * @param $sen_uname_full
	 * @param $sen_uname
	 * @param $rec_uname_full
	 * @param $rec_uname
	 * @param $post_url
	 * @param $topic_title
	 * @return array
	 * @access public
	 */
	public function dtst_tokens_replacements($sen_uname_full, $sen_uname, $rec_uname_full, $rec_uname, $post_url, $topic_title)
	{
		/* Map arguments for replacement */
		return $dtst_tokens_replacements = array(
			$sen_uname_full,
			$sen_uname,
			$rec_uname_full,
			$rec_uname,
			$post_url,
			$topic_title,
		);
	}

	/**
	 * Executes the main SQL query, called on request
	 *
	 * @param $user_id
	 * @return	array	$row	user data
	 * @access public
	 */
	public function dtst_sql_users($user_id)
	{
		$sql = 'SELECT user_id, user_ip, username, user_colour, user_lang, user_sig, user_sig_bbcode_uid, user_sig_bbcode_bitfield
			FROM ' . USERS_TABLE . '
			WHERE user_id = ' . (int) $user_id;
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row;
	}

	/**
	 * Mimic get_username_string() due to the new parser
	 *
	 * Param int	$user_id	The user ID
	 *
	 * @param $user_id
	 * @return array|string		Array of strings formatted with BBcode markup
	 * @access public
	 */
	public function dtst_get_username_string($user_id)
	{
		/* Call the main SQL query */
		$row_user = $this->dtst_sql_users($user_id);

		$username_flat = $row_user['username'];

		/**
		* That's for Rhea - Mimic get_username_string due to the new parser
		*/
		if ($this->auth->acl_get('u_viewprofile'))
		{
			$url_profile = generate_board_url() . '/memberlist.' . $this->php_ext . '?mode=viewprofile&u=' . $row_user['user_id'];
			$color = $row_user['user_colour'] ? '[color=#' . $row_user['user_colour'] . ']' . $row_user['username'] . '[/color]' : $row_user['username'];
			$username_full = '[b]' . '[url=' . $url_profile . ']' . $color . '[/url]' . '[/b]';
		}
		else
		{
			$username_full = $row_user['username'];
		}

		return array($username_full, $username_flat);
	}

	/**
	 * Send PM
	 *
	 * @param int		$pm_status		The PM status id
	 * @param int		$user_id		The user ID of the recipient
	 * @param int		$topic_id		The topic ID
	 * @param string	$topic_title	The topic title
	 * @return void
	 * @access public
	 */
	public function send_pm($pm_status, $user_id, $topic_id, $topic_title)
	{
		/* Return if there is no pm status */
		if (!$pm_status)
		{
			return;
		}

		if (!function_exists('generate_text_for_storage'))
		{
			include($this->root_path . 'includes/functions_content.' . $this->php_ext);
		}

		if (!function_exists('submit_pm'))
		{
			include($this->root_path . 'includes/functions_privmsgs.' . $this->php_ext);
		}

		/* Grab the correct language */
		$recipient_lang = $this->dtst_sql_users($user_id);

		/* Does the related preset exist? */
		$sql1 = 'SELECT *
					FROM ' . $this->dtst_privmsg . '
					WHERE dtst_pm_status = ' . (int) $pm_status . '
						AND dtst_pm_isocode = "' . $this->db->sql_escape($recipient_lang['user_lang']) . '"';
		$result1 = $this->db->sql_query($sql1);
		$row1 = $this->db->sql_fetchrow($result1);
		$this->db->sql_freeresult($result1);
		unset($sql1);

		/* If not, use the default board language - If necessary we will fallback to 'en' */
		$dtst_pm_isocode = ($row1) ? $recipient_lang['user_lang'] : $this->config['default_lang'];

		/* Query our PMs table */
		$sql = 'SELECT *
					FROM ' . $this->dtst_privmsg . '
					WHERE dtst_pm_status = ' . (int) $pm_status . '
						AND dtst_pm_isocode = "' . $this->db->sql_escape($dtst_pm_isocode) . '"';
		$result = $this->db->sql_query($sql);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$post_url = append_sid(generate_board_url() . '/viewtopic.' . $this->php_ext, "t={$topic_id}", false);

		/* Call the mimic */
		list($sen_uname_full, $sen_uname) = $this->dtst_get_username_string($this->user->data['user_id']);
		list($rec_uname_full, $rec_uname) = $this->dtst_get_username_string($user_id);

		/* Prepare the PM and tokens */
		$uid = $bitfield = '';
		$m_flags = 0;

		/* No censor-text applies to both fields*/
		$pm_title = htmlspecialchars_decode($row['dtst_pm_title'], ENT_COMPAT);
		$pm_title = str_replace($this->dtst_tokens(), $this->dtst_tokens_replacements($sen_uname, $sen_uname, $rec_uname, $rec_uname, false, $topic_title), $pm_title);

		generate_text_for_display($pm_title, $uid, $bitfield, $m_flags, $censor_text = false);

		$pm_message = htmlspecialchars_decode($row['dtst_pm_message'], ENT_COMPAT);
		$pm_message = str_replace($this->dtst_tokens(), $this->dtst_tokens_replacements($sen_uname_full, $sen_uname, $rec_uname_full, $rec_uname, $post_url, $topic_title), $pm_message);

		$allow_bbcode = $allow_urls = $allow_smilies = true;
		generate_text_for_storage($pm_message, $uid, $bitfield, $m_flags, $allow_bbcode, $allow_urls, $allow_smilies);

		/* Call the main SQL query */
		$row_data = $this->dtst_sql_users($user_id);

		/* Are we using the PMs Bot? */
		$dtst_from_user_id = ((bool) $this->config['dtst_use_bot']) ? (int) $this->config['dtst_bot'] : (int) $this->user->data['user_id'];

		$pm_data = array(
			'address_list'		=> array('u' => array((int) $user_id => 'to')),
			'from_user_id'		=> (int) $dtst_from_user_id,
			'from_user_ip'		=> $row_data['user_ip'],
			'enable_sig'		=> true,
			'enable_bbcode'		=> $allow_bbcode,
			'enable_smilies'	=> $allow_smilies,
			'enable_urls'		=> $allow_urls,
			'icon_id'			=> false,
			'bbcode_bitfield'	=> $bitfield,
			'bbcode_uid'		=> $uid,
			'message'			=> $pm_message,
		);

		/**
		 * We do not want the sent PMs to be stored into the User's PM's outbox or sentbox
		 */
		submit_pm('post', $pm_title, $pm_data, false);
	}

	/**
	 * Return the HTML list of users from where to chose the Bot
	 *
	 * @param bool|int	$dtst_u_id		The user ID
	 * @return string	$allowed_users	HTML
	 * @access public
	 */
	public function pms_bot_selector($dtst_u_id = false)
	{
		$allowed_users = '';

		/* No BOTs or Inactive users = array(USER_IGNORE, USER_INACTIVE) */
		$ignore_list = array(USER_INACTIVE);

		$sql = 'SELECT user_id, username, username_clean
			FROM ' . USERS_TABLE . '
			WHERE ' . $this->db->sql_in_set('user_type', $ignore_list, true, true) . '
				AND user_id <> ' . ANONYMOUS . '
			ORDER BY username_clean';
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$selected = ((int) $row['user_id'] == (int) $dtst_u_id) ? ' selected="selected"' : '';
			$allowed_users .= '<option value="' . $row['user_id'] . '"' . $selected . '>' . $row['username'] . '</option>';
		}
		$this->db->sql_freeresult($result);

		return $allowed_users;
	}

	/**
	 * The drop down for the Bot's selection
	 *
	 * @param  int		$dtst_bot_id		The Bot ID
	 * @return string	selector			HTML
	 * @access public
	 */
	public function dtst_bot_select($dtst_bot_id)
	{
		return '<select id="dtst_bot" name="dtst_bot">' . $this->pms_bot_selector($dtst_bot_id) . '</select>';
	}

	/**
	 * Strip emojis from a string
	 * @param string $string
	 * @return string
	 */
	public function dtst_strip_emojis($string)
	{
		return preg_replace('/[\x{10000}-\x{10FFFF}]/u', "", $string);
	}

	/**
	 * Post a reply in a existing topic
	 *
	 * @param int		$forum_id	the forum id where to post
	 * @param int		$topic_id	the topic id where to post, 0 to post a new Topic instead
	 * @param int		$user_id	the user id of the applicant
	 * @param string	$reason		the reason of the applicant to be posted (only if has been accepted)
	 * @return string
	 */
	public function dtst_post_reply($forum_id, $topic_id, $user_id, $reason)
	{
		if (!function_exists('generate_text_for_storage'))
		{
			include($this->root_path . 'includes/functions_content.' . $this->php_ext);
		}

		if (!function_exists('submit_post'))
		{
			include($this->root_path . 'includes/functions_posting.' . $this->php_ext);
		}

		/* Add our language file only when needed */
		$this->lang->add_lang('common', 'phpbbstudio/dtst');

		/* Call the main SQL query */
		$row_user = $this->dtst_sql_users($user_id);
		$username_title = $dtst_post_username = $row_user['username'];

		$reason_quote = $this->lang->lang('DTST_REASON_QUOTE') . $this->lang->lang('COMMA_SEPARATOR');
		$post_text = '[quote=' . $reason_quote . ']' . $reason . '[/quote]';

		/* Has to be set, otherwise breaks 'last post' function */
		$topic_title = $username_title . $this->lang->lang('COLON') . ' ' . $this->lang->lang('DTST_USER_STATUS_ACCEPTED');

		/* Gets overwritten anyway */
		$username = '';

		$poll = $uid = $bitfield = $options = '';
		$allow_bbcode = $allow_urls = $allow_smilies = true;
		generate_text_for_storage($post_text, $uid, $bitfield, $options, $allow_bbcode, $allow_urls, $allow_smilies);

		$data = array(
			'forum_id'				=> (int) $forum_id,
			'topic_id'				=> (int) $topic_id,
			'icon_id'				=> false,
			'enable_bbcode'			=> true,
			'enable_smilies'		=> true,
			'enable_urls'			=> true,
			'enable_sig'			=> true,
			'message'				=> $post_text,
			'message_md5'			=> md5($post_text),
			'bbcode_bitfield'		=> $bitfield,
			'bbcode_uid'			=> $uid,
			'post_edit_locked'		=> 0,
			'topic_title'			=> $topic_title,
			'notify_set'			=> true,
			'notify'				=> true,
			'post_time'				=> 0,
			'forum_name'			=> $this->dtst_forum_id_to_name($forum_id),
			'enable_indexing'		=> true,

			's_dtst_reply'			=> true,
			'dtst_poster_id'		=> (int) $user_id,
			'dtst_post_username'	=> $dtst_post_username,
		);

		return submit_post('reply', $topic_title, $username, POST_NORMAL, $poll, $data);
	}

	/**
	 * Returns an array of forum IDS to be used with "sql_in_set"
	 *
	 * @param int		$forum_id		the forum ID to use
	 * @return array					array of forum IDS, empty array otherwise
	 * @access public
	 */
	public function dtst_forum_id_to_parents($forum_id)
	{
		$forum_parents = array();

		$sql = 'SELECT *
			FROM ' . FORUMS_TABLE . '
			WHERE forum_id = ' . (int) $forum_id;
		$result = $this->db->sql_query($sql);
		$forum_data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if ($forum_data)
		{
			$sql = 'SELECT forum_id
				FROM ' . FORUMS_TABLE . '
				WHERE left_id < ' . $forum_data['left_id'] . '
					AND right_id > ' . $forum_data['right_id'] . '
				ORDER BY left_id ASC';
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$forum_parents = (int) $row['forum_id'];
			}
			$this->db->sql_freeresult($result);
		}

		return (array) $forum_parents;
	}
}
