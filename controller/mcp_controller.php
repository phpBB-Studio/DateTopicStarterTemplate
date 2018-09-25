<?php
/**
 *
 * Date Topic Event Calendar. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\dtst\controller;

/**
 * @ignore
 */
use phpbbstudio\dtst\ext;

/**
 * Date Topic Event Calendar MCP Controller.
 */
class mcp_controller implements mcp_interface
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbbstudio\dtst\core\reputation_functions */
	protected $rep_func;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbbstudio\dtst\core\operator */
	protected $utils;

	/** @var string		DTST Reputation table */
	protected $dtst_reputation;

	/** @var string		phpBB root path */
	protected $root_path;

	/** @var string		php File extension */
	protected $php_ext;

	/** @var string		Custom form action */
	protected $u_action;

	/**
	 * Constructor.
	 *
	 * @param  \phpbb\auth\auth								$auth				Authentication object
	 * @param  \phpbb\config\config							$config				Configuration object
	 * @param  \phpbb\db\driver\driver_interface			$db					Database object
	 * @param  \phpbb\controller\helper						$helper				Controller helper object
	 * @param  \phpbb\language\language						$lang				Language object
	 * @param  \phpbb\log\log								$log				Log object
	 * @param  \phpbbstudio\dtst\core\reputation_functions	$rep_func			DTST Reputation functions
	 * @param  \phpbb\request\request						$request			Request object
	 * @param  \phpbb\template\template						$template			Template object
	 * @param  \phpbb\user									$user				User object
	 * @param  \phpbbstudio\dtst\core\operator				$utils				DTST Utilities
	 * @param  string										$dtst_reputation	DTST Reputation table
	 * @param  string										$root_path			phpBB root path
	 * @param  string										$php_ext			php File extension
	 * @return void
	 * @access public
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\controller\helper $helper, \phpbb\language\language $lang, \phpbb\log\log $log, \phpbbstudio\dtst\core\reputation_functions $rep_func, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, \phpbbstudio\dtst\core\operator $utils, $dtst_reputation, $root_path, $php_ext)
	{
		$this->auth			= $auth;
		$this->config		= $config;
		$this->db			= $db;
		$this->helper		= $helper;
		$this->lang			= $lang;
		$this->log			= $log;
		$this->rep_func		= $rep_func;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;
		$this->utils		= $utils;

		$this->dtst_reputation	= $dtst_reputation;

		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;
	}

	/**
	 * Overview of worst performing users based on reputation.
	 *
	 * @return void
	 * @access public
	 */
	public function front()
	{
		/* Add our language file */
		$this->lang->add_lang('common', 'phpbbstudio/dtst');

		$users_per_page = ((bool) $this->config['dtst_rep_users_page']) ? (int) $this->config['dtst_rep_users_page'] : (int) $this->config['topics_per_page'];

		$latest_5 = array(
			ext::DTST_REP_CONDUCT_BAD	=> 'CONDUCT_BAD',
			ext::DTST_REP_THUMBS_DOWN	=> 'THUMBS_DOWN',
			ext::DTST_REP_NO_SHOW		=> 'NO_SHOW',
			ext::DTST_REP_MOD			=> 'MOD'
		);

		foreach ($latest_5 as $id => $block)
		{
			$this->template->assign_block_vars('latest', array(
				'TITLE'	=> $this->lang->lang('MCP_DTST_FRONT_' . $block),
				'NONE'	=> $this->lang->lang('MCP_DTST_FRONT_' . $block . '_NONE'),
				'S_MOD'	=> $id == ext::DTST_REP_MOD,
			));

			$sql = 'SELECT r.*, t.forum_id, t.topic_title,
							u1.user_id as from_id, u1.username as from_name, u1.user_colour as from_colour,
							u2.user_id as to_id, u2.username as to_name, u2.user_colour as to_colour
					FROM ' . $this->dtst_reputation . ' r
					LEFT JOIN ' . USERS_TABLE . ' u1
						ON r.user_id = u1.user_id
					LEFT JOIN ' . USERS_TABLE . ' u2
						ON r.recipient_id = u2.user_id
					LEFT JOIN ' . TOPICS_TABLE . ' t
						ON r.topic_id = t.topic_id
					WHERE r.reputation_action = ' . (int) $id .
					(($id == ext::DTST_REP_MOD) ? ' AND r.reputation_points < 0' : '') . '
					ORDER BY r.reputation_time DESC';
			$result = $this->db->sql_query_limit($sql, 5);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$this->template->assign_block_vars('latest.rows', array(
					'USERNAME'	=> get_username_string('full', $row['to_id'], $row['to_name'], $row['to_colour']),
					'EVENT'		=> $row['topic_title'],
					'FROM'		=> get_username_string('full', $row['from_id'], $row['from_name'], $row['from_colour']),
					'TIME'		=> $this->user->format_date($row['reputation_time']),
					'CLASS'		=> $this->rep_func->get_reputation_class($row['reputation_points']),
					'POINTS'	=> $row['reputation_points'],

					'S_MOD'		=> $row['reputation_action'] == ext::DTST_REP_MOD,

					'U_DELETE'	=> $this->helper->route('dtst_reputation_delete', array('r' => (int) $row['reputation_id'], 'u' => (int) $row['recipient_id'], 'mcp' => true)),
					'U_EVENT'	=> append_sid("{$this->root_path}viewtopic.{$this->php_ext}", 'f=' . (int) $row['forum_id'] . '&t=' . (int) $row['topic_id']),
				));
			}
			$this->db->sql_freeresult($result);
		}

		/* Set up user types */
		$user_types = array(USER_NORMAL, USER_FOUNDER);
		if ($this->auth->acl_get('a_user'))
		{
			$user_types[] = USER_INACTIVE;
		}

		$sql = 'SELECT u.user_id, u.username, u.user_colour, u.dtst_reputation, 
						COUNT(r1.reputation_action) as count_no_show, COUNT(r2.reputation_action) as count_withdrew, COUNT(r3.reputation_action) as count_conduct_bad,
						COUNT(r4.reputation_action) as count_thumbs_down, COUNT(r5.reputation_action) as count_canceled, COUNT(r6.reputation_action) as count_no_reply,
						COUNT(r7.reputation_action) as count_mod
				FROM ' . USERS_TABLE . ' u
				LEFT JOIN ' . $this->dtst_reputation . ' r1
					ON u.user_id = r1.recipient_id
						AND r1.reputation_action = ' . ext::DTST_REP_NO_SHOW . '
				LEFT JOIN ' . $this->dtst_reputation . ' r2
					ON u.user_id = r2.recipient_id
						AND r2.reputation_action = ' . ext::DTST_REP_WITHDREW . '
				LEFT JOIN ' . $this->dtst_reputation . ' r3
					ON u.user_id = r3.recipient_id
						AND r3.reputation_action = ' . ext::DTST_REP_CONDUCT_BAD . '
				LEFT JOIN ' . $this->dtst_reputation . ' r4
					ON u.user_id = r4.recipient_id
						AND r4.reputation_action = ' . ext::DTST_REP_THUMBS_DOWN . '
				LEFT JOIN ' . $this->dtst_reputation . ' r5
					ON u.user_id = r5.recipient_id
						AND r5.reputation_action = ' . ext::DTST_REP_CANCELED . '
				LEFT JOIN ' . $this->dtst_reputation . ' r6
					ON u.user_id = r6.recipient_id
						AND r6.reputation_action = ' . ext::DTST_REP_NO_REPLY . '
				LEFT JOIN ' . $this->dtst_reputation . ' r7
					ON u.user_id = r7.recipient_id
						AND r7.reputation_points < 0
						AND r7.reputation_action = ' . ext::DTST_REP_MOD . '
				WHERE ' . $this->db->sql_in_set('u.user_type', $user_types) . '
				GROUP BY u.user_id, u.username, u.user_colour, u.dtst_reputation
				ORDER BY u.dtst_reputation ASC, u.username ASC';
		$result = $this->db->sql_query_limit($sql, $users_per_page);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('users', array(
				'NAME'		=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
				'POINTS'	=> $row['dtst_reputation'],
				'CLASS'		=> $this->rep_func->get_reputation_class($row['dtst_reputation']),

				'COUNT_NO_SHOW'		=> $row['count_no_show'],
				'COUNT_WITHDREW'	=> $row['count_withdrew'],
				'COUNT_CONDUCT_BAD'	=> $row['count_conduct_bad'],
				'COUNT_THUMBS_DOWN'	=> $row['count_thumbs_down'],
				'COUNT_CANCELED'	=> $row['count_canceled'],
				'COUNT_NO_REPLY'	=> $row['count_no_reply'],
				'COUNT_MOD'			=> $row['count_mod'],
			));
		}
		$this->db->sql_freeresult($result);

		$this->template->assign_vars(array(
			'DTST_REP_NAME'		=> $this->config['dtst_rep_name'],

			'S_DTST_MODE'		=> 'front',

			'U_DTST_ACTION'		=> $this->u_action,
		));
	}

	/**
	 * Display recent reputation actions.
	 *
	 * @return void
	 * @access public
	 */
	public function recent()
	{
		/* Add our language file */
		$this->lang->add_lang('common', 'phpbbstudio/dtst');

		/* Amount of 'recent' reputation we show */
		$reputation_per_page = ((bool) $this->config['dtst_rep_users_page']) ? (int) $this->config['dtst_rep_users_page'] : (int) $this->config['topics_per_page'];

		/* Request the filters */
		$filter_types	= $this->request->variable('dtst_type', array(0));
		$filter_actions	= $this->request->variable('dtst_action', array(0));
		$filter_from	= array_filter(explode("\n", $this->request->variable('user_from', '', true)));
		$filter_to		= array_filter(explode("\n", $this->request->variable('user_to', '', true)));

		/* Request the sorting */
		$sort_key = $this->request->variable('s', 't');
		$sort_dir = $this->request->variable('d', 'd');

		/* Grab some filters */
		$actions = $this->rep_func->get_reputation_lang('r');
		$event_types = $this->utils->dtst_list_enabled_forum_names();

		foreach ($actions as $id => $name)
		{
			$this->template->assign_block_vars('actions', array(
				'ID'			=> $id,
				'LANG'			=> $name,
				'S_SELECTED'	=> (bool) in_array($id, $filter_actions),
			));
		}

		foreach ($event_types as $id => $name)
		{
			$this->template->assign_block_vars('event_types', array(
				'ID'			=> $id,
				'NAME'			=> $name,
				'S_SELECTED'	=> (bool) in_array($id, $filter_types)
			));
		}

		/* Set up user types */
		$user_types = array(USER_NORMAL, USER_FOUNDER);
		if ($this->auth->acl_get('a_user'))
		{
			$user_types[] = USER_INACTIVE;
		}

		/* Apply the filters */
		$sql_where = '';
		$sql_where .= $filter_types ? ($sql_where ? ' AND ' : '') . $this->db->sql_in_set('t.forum_id', $filter_types) : '';
		$sql_where .= $filter_actions ? ($sql_where ? ' AND ' : '') . $this->db->sql_in_set('r.reputation_action', $filter_actions) : '';
		$sql_where .= $filter_from ? ($sql_where ? ' AND ' : '') . '(' .  $this->db->sql_in_set('u1.user_type', $user_types) . ' AND ' . $this->db->sql_in_set('u1.username', $filter_from) . ')' : '';
		$sql_where .= $filter_to ? ($sql_where ? ' AND ' : '') . '(' . $this->db->sql_in_set('u2.user_type', $user_types) . ' AND ' . $this->db->sql_in_set('u2.username', $filter_to) . ')' : '';

		/* Apply the sorting */
		switch ($sort_key)
		{
			case 'e':
				$order_by = 't.topic_title';		// Event title
			break;
			case 'a':
				$order_by = 'r.reputation_action';	// Reputation action
			break;
			case 'f':
				$order_by = 'u1.username';			// From username
			break;
			case 'r':
				$order_by = 'u2.username';			// To username
			break;
			case 'p':
				$order_by = 'r.reputation_points';	// Reputation points
			break;
			case 't':
			default:
				$order_by = 'r.reputation_time';	// Reputation time
			break;
		}

		/* Let's build the SQL Query */
		$sql_array = array(
			'SELECT'    => 'r.*,
							t.topic_title, t.forum_id,
							u1.user_id as from_id, u1.username as from_name, u1.user_colour as from_colour,
							u2.user_id as to_id, u2.username as to_name, u2.user_colour as to_colour',

			'FROM'      => array(
				$this->dtst_reputation  => 'r',
			),

			'LEFT_JOIN' => array(
				array(
					'FROM'  => array(USERS_TABLE => 'u1'),
					'ON'    => 'u1.user_id = r.user_id',
				),
				array(
					'FROM'  => array(USERS_TABLE => 'u2'),
					'ON'    => 'u2.user_id = r.recipient_id',
				),
				array(
					'FROM'	=> array(TOPICS_TABLE => 't'),
					'ON'	=> 't.topic_id = r.topic_id',
				),
			),

			'WHERE'     => $sql_where,

			'ORDER_BY'  => $order_by . ' ' . ($sort_dir === 'd' ? 'DESC' : 'ASC'),
		);

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query_limit($sql, $reputation_per_page);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$this->template->assign_block_vars('reputation', array(
				'ID'			=> $row['reputation_id'],
				'ACTION'		=> $actions[$row['reputation_action']],
				'EVENT'			=> $row['topic_title'] ? $row['topic_title'] : '-',
				'CLASS'			=> $this->rep_func->get_reputation_class($row['reputation_points']),
				'POINTS'		=> $row['reputation_points'],
				'TIME'			=> $this->user->format_date($row['reputation_time']),
				'REASON'		=> $row['reputation_reason'],
				'GIVER'			=> get_username_string('full', $row['from_id'], $row['from_name'], $row['from_colour']),
				'RECEIVER'		=> get_username_string('full', $row['to_id'], $row['to_name'], $row['to_colour']),

				'U_DELETE'		=> $this->helper->route('dtst_reputation_delete', array('r' => (int) $row['reputation_id'], 'u' => (int) $row['recipient_id'], 'mcp' => true)),
				'U_EVENT'		=> append_sid("{$this->root_path}viewtopic.{$this->php_ext}", 'f=' . (int) $row['forum_id'] . '&t=' . (int) $row['topic_id']),
			));
		}
		$this->db->sql_build_array($result);

		/* Assign template variables */
		$this->template->assign_vars(array(
			'DTST_REP_NAME'		=> $this->config['dtst_rep_name'],

			'DTST_FILTER_FROM'	=> implode("\n", $filter_from),
			'DTST_FILTER_TO'	=> implode("\n", $filter_to),

			'S_DTST_AJAX'		=> $this->request->is_ajax(),
			'S_DTST_MODE'		=> 'recent',

			'U_DTST_SORT_EVENT'		=> $this->u_action . '&s=e&d=' . (($sort_key == 'e' && $sort_dir == 'a') ? 'd' : 'a'),
			'U_DTST_SORT_ACTION'	=> $this->u_action . '&s=a&d=' . (($sort_key == 'a' && $sort_dir == 'a') ? 'd' : 'a'),
			'U_DTST_SORT_TIME'		=> $this->u_action . '&s=t&d=' . (($sort_key == 't' && $sort_dir == 'd') ? 'a' : 'd'),
			'U_DTST_SORT_FROM'		=> $this->u_action . '&s=f&d=' . (($sort_key == 'f' && $sort_dir == 'a') ? 'd' : 'a'),
			'U_DTST_SORT_TO'		=> $this->u_action . '&s=r&d=' . (($sort_key == 'r' && $sort_dir == 'a') ? 'd' : 'a'),
			'U_DTST_SORT_POINTS'	=> $this->u_action . '&s=p&d=' . (($sort_key == 'p' && $sort_dir == 'd') ? 'a' : 'd'),

			'U_DTST_ACTION'		=> $this->u_action,
			'U_FIND_MEMBER'		=> $this->root_path . 'memberlist.' . $this->php_ext . '?mode=searchuser',
		));
	}

	/**
	 * Adjust a user's reputation
	 *
	 * @return void
	 * @access public
	 */
	public function adjust()
	{
		/* Set up our error collection array */
		$errors = array();

		/* Add a form key for security */
		add_form_key('mcp_dtst_adjust');

		/* Add our language file */
		$this->lang->add_lang('common', 'phpbbstudio/dtst');

		/* Request the form variables */
		$reputation = $this->request->variable('reputation', 0);
		$username = $this->request->variable('username', '');
		$action = $this->request->variable('action', '+');
		$reason = $this->request->variable('reason', '', true);
		$reason_len = utf8_strlen($reason);

		/* If the form was submitted */
		if ($submit = $this->request->is_set_post('submit'))
		{
			/* Check the form key for security */
			if (!check_form_key('mcp_dtst_adjust'))
			{
				$errors[] = $this->lang->lang('FORM_INVALID');
			}

			/* No Emojis */
			if (preg_match_all('/[\x{10000}-\x{10FFFF}]/u', $reason, $matches))
			{
				$list = implode('<br>', $matches[0]);
				$errors[] = $this->lang->lang('MCP_DTST_ERR_REASON_EMOJIS_SUPPORT', $list);
			}

			/* If no username was specified */
			if (!$username)
			{
				$errors[] = $this->lang->lang('NO_USER_SPECIFIED');
			}
			else
			{
				/* Let's try and find the user */
				$sql = 'SELECT user_id FROM ' . USERS_TABLE . ' WHERE username = "' . $this->db->sql_escape($username) . '"';
				$result = $this->db->sql_query($sql);
				$user_id = (int) $this->db->sql_fetchfield('user_id');
				$this->db->sql_freeresult($result);

				/* If no user was found */
				if (!$user_id)
				{
					$errors[] = $this->lang->lang('NO_USER');
				}
			}

			/* Make sure the reason is between 0 and 255 characters */
			if (($reason_len == 0) || ($reason_len) > 255)
			{
				$errors[] = $reason_len == 0 ? $this->lang->lang('DTST_REASON_MISSING') : $this->lang->lang('DTST_REASON_TOO_LONG');
			}

			/* If no reputation amount was specified */
			if (!$reputation)
			{
				$errors[] = $this->lang->lang('MCP_DTST_ADJUST_NO_REP', utf8_strtolower($this->config['dtst_rep_name']));
			}

			/* If there are no errors, we carry on */
			if (empty($errors))
			{
				/* Adjust the user's reputation points */
				$points = $action . $reputation;
				$this->rep_func->set_reputation((int) $user_id, (int) $points);

				/* Extra layer for Emojis */
				$reason = $this->utils->dtst_strip_emojis($reason);

				/* Add it to the DTST Reputation table */
				$sql = 'INSERT INTO ' . $this->dtst_reputation . ' ' . $this->db->sql_build_array('INSERT', array(
					'user_id'           => (int) $this->user->data['user_id'],
					'recipient_id'      => (int) $user_id,
					'reputation_action' => ext::DTST_REP_MOD,
					'reputation_points' => $points,
					'reputation_reason' => $reason,
					'reputation_time'   => time(),
				));
				$this->db->sql_query($sql);

				/* Log the moderator action */
				$log_action = $points > 0 ? 'ACP_DTST_LOG_REP_MOD_GIVE' : 'ACP_DTST_LOG_REP_MOD_TAKE';
				$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, $log_action, false, array($username, $points, $reason, utf8_strtolower($this->config['dtst_rep_name'])));

				/* Show the success message */
				trigger_error($this->lang->lang('MCP_DTST_ADJUST_SUCCESS', utf8_strtolower($this->config['dtst_rep_name'])) . '<br><br>' . $this->lang->lang('RETURN_PAGE', '<a href="' . $this->u_action . '">', '</a>'));
			}
		}

		/* Assign template variables */
		$this->template->assign_vars(array(
			'S_ERROR'				=> !empty($errors),
			'ERROR_MSG'				=> !empty($errors) ? implode('<br>', $errors) : '',

			'DTST_REP_NAME'			=> $this->config['dtst_rep_name'],

			'DTST_USERNAME'			=> $username,
			'DTST_ACTION'			=> $action,
			'DTST_REPUTATION'		=> $reputation,
			'DTST_REASON'			=> $reason,

			'S_DTST_MODE'			=> 'adjust',

			'U_DTST_ACTION'			=> $this->u_action,
			'U_FIND_MEMBER'			=> $this->root_path . 'memberlist.' . $this->php_ext . '?mode=searchuser&select_single=true',
		));
	}

	/**
	 * Set page url
	 *
	 * @param string $u_action Custom form action
	 * @return void
	 * @access public
	 */
	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}
}
