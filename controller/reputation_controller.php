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
 * Date Topic Event Calendar Reputation Controller.
 */
class reputation_controller implements reputation_interface
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

	/** @var \phpbb\pagination */
	protected $pagination;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbbstudio\dtst\core\reputation_functions */
	protected $rep_func;

	/** @var \phpbbstudio\dtst\core\operator */
	protected $utils;

	/** @var string DTST Reputation table */
	protected $dtst_reputation;

	/** @var string DTST Slots table */
	protected $dtst_slots;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string php File extension */
	protected $php_ext;

	/**
	 * Constructor.
	 *
	 * @param  \phpbb\auth\auth						$auth				Authentication object
	 * @param  \phpbb\config\config					$config				Configuration object
	 * @param  \phpbb\db\driver\driver_interface	$db					Database connection object
	 * @param  \phpbb\controller\helper				$helper				Controller helper object
	 * @param  \phpbb\language\language				$lang				Language object
	 * @param  \phpbb\log\log						$log				Log object
	 * @param  \phpbb\pagination					$pagination			Pagination object
	 * @param  \phpbb\request\request				$request			Request object
	 * @param  \phpbb\template\template				$template			Template object
	 * @param  \phpbb\user							$user				User object
	 * @param  \phpbbstudio\dtst\core\reputation_functions	$rep_func	DTST Reputation functions
	 * @param  \phpbbstudio\dtst\core\operator		$utils				DTST Utilities
	 * @param  string								$dtst_reputation	DTST Reputation table
	 * @param  string								$dtst_slots			DTST Slots table
	 * @param  string								$root_path			phpBB root path
	 * @param  string								$php_ext			php File extension
	 * @return void
	 * @access public
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\controller\helper $helper, \phpbb\language\language $lang, \phpbb\log\log $log, \phpbb\pagination $pagination, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, \phpbbstudio\dtst\core\reputation_functions $rep_func, \phpbbstudio\dtst\core\operator $utils, $dtst_reputation, $dtst_slots, $root_path, $php_ext)
	{
		$this->auth			= $auth;
		$this->config		= $config;
		$this->db			= $db;
		$this->helper		= $helper;
		$this->lang			= $lang;
		$this->log			= $log;
		$this->pagination	= $pagination;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;
		$this->rep_func		= $rep_func;
		$this->utils		= $utils;

		$this->dtst_reputation	= $dtst_reputation;
		$this->dtst_slots		= $dtst_slots;

		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;
	}

	/**
	 * Display reputation page per event.
	 *
	 * @return mixed
	 * @access public
	 */
	public function handle()
	{
		/* Request some variables */
		$topic_id	= (int) $this->request->variable('t', 0);	// The topic id for the event
		$filter		= $this->request->variable('filter', 0);	// The filter we have to apply
		$username	= $this->request->variable('u', '', true);	// The username we are searching for
		$page		= $this->request->variable('page', 1);		// The page we are on

		/* Set start variable for pagination */
		$users_per_page = ((bool) $this->config['dtst_rep_users_page']) ? (int) $this->config['dtst_rep_users_page'] : (int) $this->config['topics_per_page'];
		$start = (($page - 1) * $users_per_page);

		/* Add language file */
		$this->lang->add_lang('common', 'phpbbstudio/dtst');

		/* Grab event data, together with all host information */
		$sql = 'SELECT t.*, s.dtst_status, u.dtst_reputation, r.reputation_action,
					u.user_id, u.username, u.username_clean, u.user_colour,
					u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height, u.user_lang
				FROM ' . TOPICS_TABLE . ' t
				JOIN ' . USERS_TABLE . ' u
					ON t.topic_poster = u.user_id 
				LEFT JOIN ' . $this->dtst_slots . ' s
					ON t.topic_id = s.topic_id
						AND s.user_id = ' . (int) $this->user->data['user_id'] . '
				LEFT JOIN ' . $this->dtst_reputation . ' r 
					ON t.topic_poster = r.recipient_id
						AND r.topic_id = ' . (int) $topic_id . '
						AND r.user_id = ' . (int) $this->user->data['user_id'] . '
				WHERE t.topic_id = ' . (int) $topic_id;
		$result = $this->db->sql_query_limit($sql, 1);
		$event = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		/* Check if this user is the host of the event */
		$s_is_host = (bool) ($this->user->data['user_id'] == $event['topic_poster']);

		/* Mark this user as not a "no show" */
		$s_is_no_show = false;

		/* If this user is not the host and not accepted to the event, we throw an error */
		if (!$s_is_host && (!isset($event['dtst_status']) || $event['dtst_status'] != ext::DTST_STATUS_ACCEPTED))
		{
			throw new \phpbb\exception\http_exception(403, 'DTST_REP_NOT_ATTENDING');
		}

		/* Set up some filter constants, for the host we use "conduct", for everyone else "thumbs" */
		$filters_const = array(
			'up'		=> $s_is_host ? ext::DTST_REP_CONDUCT_GOOD : ext::DTST_REP_THUMBS_UP,
			'down'		=> $s_is_host ? ext::DTST_REP_CONDUCT_BAD : ext::DTST_REP_THUMBS_DOWN,
			'noshow'	=> ext::DTST_REP_NO_SHOW,
		);
		/* And the filter params for the route */
		$filters_params = array(
			'up'		=> $filter == $filters_const['up'] ? array('t' => (int) $topic_id) : array('t' => (int) $topic_id, 'filter' => $filters_const['up']),
			'down'		=> $filter == $filters_const['down'] ? array('t' => (int) $topic_id) : array('t' => (int) $topic_id, 'filter' => $filters_const['down']),
			'noshow'	=> $filter == $filters_const['noshow'] ? array('t' => (int) $topic_id) : array('t' => (int) $topic_id, 'filter' => $filters_const['noshow']),
		);

		/* No date was set, so there will never be a 'reputation page' */
		if (!$event['dtst_date'] || !$event['dtst_date_unix'])
		{
			throw new \phpbb\exception\http_exception(403, $this->lang->lang('DTST_REP_EVENT_NO_DATE', utf8_strtolower($this->config['dtst_rep_name'])));
		}

		/* Event has not ended yet */
		if (!$event['dtst_event_ended'])
		{
			throw new \phpbb\exception\http_exception(403, 'DTST_REP_EVENT_NOT_ENDED');
		}

		/* Reputation period is over */
		if ($event['dtst_rep_ended'])
		{
			throw new \phpbb\exception\http_exception(403, $this->lang->lang('DTST_REP_ENDED', utf8_strtolower($this->config['dtst_rep_name'])));
		}

		/* Let's calculate some dates and days */
		$today = new \DateTime(); // This object represents current date/time
		$event_ended = \DateTime::createFromFormat('d-m-Y', $event['dtst_date']); // This object represents event date
		$diff = $today->diff($event_ended); // Calculate the difference
		$days_diff = (int) $diff->format("%R%a"); // Extract days count in interval
		$days_max = $this->config['dtst_rep_time'] / ext::ONE_DAY; // Calculate how many days the reputation period has
		$days_left = $days_max + $days_diff; // +, cause the difference already includes the minus "-".
		$event_ended->modify('+' . $days_max . ' days'); // Modify the event ended date to the 'reputation ended' date

		$counts = array(
			ext::DTST_REP_THUMBS_UP => 0,
			ext::DTST_REP_THUMBS_DOWN => 0,
			ext::DTST_REP_CONDUCT_GOOD => 0,
			ext::DTST_REP_CONDUCT_BAD => 0,
		);

		/* Grab event participants */
		$sql_array = array(
			'SELECT'	=> 'u.user_id, u.username, u.username_clean, u.user_colour,
							u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height,
							u.dtst_reputation, u.user_lang,
							r.reputation_action, rr.reputation_action as no_show',

			'FROM'		=> array(
				$this->dtst_slots  => 's',
			),

			'LEFT_JOIN' => array(
				array(
					'FROM'	=> array(USERS_TABLE => 'u'),
					'ON'	=> 'u.user_id = s.user_id',
				),
				array(
					'FROM'	=> array($this->dtst_reputation => 'r'),
					'ON'	=> 'u.user_id = r.recipient_id
									AND r.topic_id = ' . (int) $topic_id . '
									AND r.user_id = ' . (int) $this->user->data['user_id'],
				),
				array(
					'FROM'	=> array($this->dtst_reputation => 'rr'),
					'ON'	=> 'u.user_id = rr.recipient_id 
									AND rr.topic_id = ' . (int) $topic_id . '
									AND rr.reputation_action = ' . ext::DTST_REP_NO_SHOW,
				),
			),

			'WHERE'		=> 's.topic_id = ' . (int) $topic_id . '
								AND s.user_id != ' . (int) $event['topic_poster'] . '
								AND s.dtst_status = ' . ext::DTST_STATUS_ACCEPTED,

			'ORDER_BY'	=> 'u.username_clean',
		);

		/* Apply a filter, if required */
		switch ($filter)
		{
			case $filters_const['up']:
				$sql_array['WHERE'] .= ' AND r.reputation_action = ' . (int) $filters_const['up'];
			break;
			case $filters_const['down']:
				$sql_array['WHERE'] .= ' AND r.reputation_action = ' . (int) $filters_const['down'];
			break;
			case $filters_const['noshow']:
				$sql_array['WHERE'] .= ' AND rr.reputation_action = ' . (int) $filters_const['noshow'];
			break;
		}

		/* Search for a username, if required */
		if (!empty($username))
		{
			$sql_like_username = $this->db->sql_like_expression($username . $this->db->get_any_char());
			$sql_array['WHERE'] .= ' AND (u.username ' . $sql_like_username . ' OR u.username_clean ' . $sql_like_username . ')';
		}

		/* Run the SQL Query */
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query_limit($sql, $users_per_page, $start); // paginated query
		while ($row = $this->db->sql_fetchrow($result))
		{
			/* If the amount of this 'action' (thumbs up/down, etc..) should be counted, increment the count */
			if (isset($row['reputation_action']) && array_key_exists($row['reputation_action'], $counts))
			{
				$counts[$row['reputation_action']]++;
			}

			/* If the reputation action is "no show" and the user row is this user, mark this user as a no show */
			if (($this->user->data['user_id'] == $row['user_id']) && isset($row['no_show']))
			{
				$s_is_no_show = true;
			}

			/* Grab the sync'ed values for this */
			$percent_rank = $this->utils->percentage($row['dtst_reputation']);

			/* Get Ranks in their localised form or default to EN */
			list($dtst_rank_title, $dtst_rank_desc, $dtst_rank_bckg, $dtst_rank_text) = $this->utils->dtst_ranks_vars($percent_rank);

			/* Obtain a float value for rateYo */
			$percent = $this->utils->dtst_percent($row['dtst_reputation']);

			/* Assign vars to the template */
			$this->template->assign_block_vars('dtst_attendees', array(
				'AVATAR'			=> phpbb_get_user_avatar($row),
				'USER_ID'			=> $row['user_id'],
				'USERNAME'			=> get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']),
				'PERCENT_RATEYO'	=> $this->utils->dtst_percent_rateyo($percent),
				'REPUTATION'		=> $row['dtst_reputation'],
				'RANK_TITLE'		=> $dtst_rank_title,
				'RANK_DESC'			=> $dtst_rank_desc,
				'RANK_BCKG'			=> $dtst_rank_bckg,
				'RANK_TEXT'			=> $dtst_rank_text,

				'S_REPPED_UP'		=> isset($row['reputation_action']) && in_array($row['reputation_action'], array(ext::DTST_REP_THUMBS_UP, ext::DTST_REP_CONDUCT_GOOD)),
				'S_REPPED_DOWN'		=> isset($row['reputation_action']) && in_array($row['reputation_action'], array(ext::DTST_REP_THUMBS_DOWN, ext::DTST_REP_CONDUCT_BAD)),
				'S_NO_SHOW'			=> isset($row['no_show']) && ($row['no_show'] == ext::DTST_REP_NO_SHOW),
				'S_THIS_USER'		=> $row['user_id'] == $this->user->data['user_id'],

				'U_REP_GOOD'	=> $this->helper->route('dtst_reputation_give', array('t' => (int) $topic_id, 'u' => (int) $row['user_id'], 'a' => ($s_is_host ? ext::DTST_REP_CONDUCT_GOOD : ext::DTST_REP_THUMBS_UP))),
				'U_REP_BAD'		=> $this->helper->route('dtst_reputation_give', array('t' => (int) $topic_id, 'u' => (int) $row['user_id'], 'a' => ($s_is_host ? ext::DTST_REP_CONDUCT_BAD : ext::DTST_REP_THUMBS_DOWN))),
				'U_NO_SHOW'		=> $this->helper->route('dtst_reputation_give', array('t' => (int) $topic_id, 'u' => (int) $row['user_id'], 'a' => ext::DTST_REP_NO_SHOW)),
			));
		}
		$this->db->sql_freeresult($result);

		/* Run the same query again but now count the users */
		$sql_array['SELECT'] = 'COUNT(u.user_id) as count';
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);
		$user_count = $this->db->sql_fetchfield('count');
		$this->db->sql_freeresult($result);

		/* Start pagination */
		$this->pagination->generate_template_pagination(
			array(
				'routes' => 'dtst_reputation',
				'params' => !empty($filter) ? array('t' => (int) $topic_id, 'filter' => (int) $filter) : array('t' => (int) $topic_id),
			), 'pagination', 'page', $user_count, (int) $users_per_page, $start);

		/* Set up host data */

		/* Grab the sync'ed values for this */
		$percent_rank = $this->utils->percentage($event['dtst_reputation']);

		/* Get Ranks in their localised form or default to EN */
		list($dtst_rank_title, $dtst_rank_desc, $dtst_rank_bckg, $dtst_rank_text) = $this->utils->dtst_ranks_vars($percent_rank);

		/* Obtain a float value for rateYo */
		$percent = $this->utils->dtst_percent($event['dtst_reputation']);

		$host_data = array(
			0 => array(
				'AVATAR'			=> phpbb_get_user_avatar($event),
				'USER_ID'			=> $event['user_id'],
				'USERNAME'			=> get_username_string('full', $event['user_id'], $event['username'], $event['user_colour']),
				'PERCENT_RATEYO'	=> $this->utils->dtst_percent_rateyo($percent),
				'REPUTATION'		=> $event['dtst_reputation'],
				'RANK_TITLE'		=> $dtst_rank_title,
				'RANK_DESC'			=> $dtst_rank_desc,
				'RANK_BCKG'			=> $dtst_rank_bckg,
				'RANK_TEXT'			=> $dtst_rank_text,

				'S_REPPED_UP'	=> isset($event['reputation_action']) && in_array($event['reputation_action'], array(ext::DTST_REP_THUMBS_UP, ext::DTST_REP_CONDUCT_GOOD)),
				'S_REPPED_DOWN'	=> isset($event['reputation_action']) && in_array($event['reputation_action'], array(ext::DTST_REP_THUMBS_DOWN, ext::DTST_REP_CONDUCT_BAD)),
				'S_THIS_USER'	=> $event['user_id'] == $this->user->data['user_id'],

				'U_REP_GOOD'	=> $this->helper->route('dtst_reputation_give', array('t' => (int) $topic_id, 'u' => (int) $event['user_id'], 'a' => ($s_is_host ? ext::DTST_REP_CONDUCT_GOOD : ext::DTST_REP_THUMBS_UP))),
				'U_REP_BAD'		=> $this->helper->route('dtst_reputation_give', array('t' => (int) $topic_id, 'u' => (int) $event['user_id'], 'a' => ($s_is_host ? ext::DTST_REP_CONDUCT_BAD : ext::DTST_REP_THUMBS_DOWN))),
				'U_NO_SHOW'		=> $this->helper->route('dtst_reputation_give', array('t' => (int) $topic_id, 'u' => (int) $event['user_id'], 'a' => ext::DTST_REP_NO_SHOW)),
			)
		);

		// If we are applying a filter, we have to overwrite the up/down counts
		if (!empty($filter) || !empty($username))
		{
			$sql = 'SELECT COUNT(user_id) as count, reputation_action
					FROM ' . $this->dtst_reputation . '
					WHERE topic_id = ' . (int) $topic_id . '
						AND user_id = ' . (int) $this->user->data['user_id'] . '
						AND ' . $this->db->sql_in_set('reputation_action', array_keys($counts)) . '
					GROUP BY user_id, reputation_action';
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result))
			{
				$counts[$row['reputation_action']] = (int) $row['count'];
			}
			$this->db->sql_freeresult($result);
		}

		$good_count		= $s_is_host ? (int) $this->config['dtst_rep_count_good'] : (int) $this->config['dtst_rep_count_up'];
		$good_remain	= $good_count - ($s_is_host ? $counts[ext::DTST_REP_CONDUCT_GOOD] : $counts[ext::DTST_REP_THUMBS_UP]);
		$bad_count		= $s_is_host ? (int) $this->config['dtst_rep_count_bad'] : (int) $this->config['dtst_rep_count_bad'];
		$bad_remain		= $good_count - ($s_is_host ? $counts[ext::DTST_REP_CONDUCT_BAD] : $counts[ext::DTST_REP_THUMBS_DOWN]);

		$this->template->assign_vars(array(
			'PAGE_NUMBER'		=> $this->pagination->on_page($user_count, $users_per_page, $start),
			'TOTAL_USERS'		=> $this->lang->lang('TOTAL_USERS', $user_count),

			'DTST_HOST_DATA'		=> $host_data,

			'DTST_EVENT_DATE'		=> $event['dtst_date'],
			'DTST_EVENT_HOST'		=> censor_text($event['dtst_host']),
			'DTST_EVENT_HOST_ID'	=> $event['topic_poster'],
			'DTST_EVENT_LOCATION'	=> $event['dtst_location'],
			'DTST_EVENT_LOC_CUSTOM'	=> censor_text($event['dtst_loc_custom']),
			'DTST_EVENT_TYPE'		=> $this->utils->dtst_forum_id_to_name($event['dtst_event_type']),
			'DTST_EVENT_TITLE'		=> $event['topic_title'],

			'DTST_REP_NAME'			=> $this->config['dtst_rep_name'],

			'DTST_REP_GOOD_COUNT'	=> $good_count,
			'DTST_REP_GOOD_REMAIN'	=> $good_remain,
			'DTST_REP_BAD_COUNT'	=> $bad_count,
			'DTST_REP_BAD_REMAIN'	=> $bad_remain,
			'DTST_REP_END_DATE'		=> $event_ended->format('d-m-Y'),
			'DTST_REP_END_DAYS'		=> $days_left,

			'DTST_TOPIC_ID'			=> $topic_id,

			'S_DTST_IS_HOST'		=> $s_is_host,
			'S_DTST_IS_NO_SHOW'		=> $s_is_no_show,

			'S_DTST_FILTER_UP'		=> $filter == $filters_const['up'],
			'S_DTST_FILTER_DOWN'	=> $filter == $filters_const['down'],
			'S_DTST_FILTER_NO_SHOW'	=> $filter == $filters_const['noshow'],

			'U_DTST_FILTER_UP'		=> $this->helper->route('dtst_reputation', $filters_params['up']),
			'U_DTST_FILTER_DOWN'	=> $this->helper->route('dtst_reputation', $filters_params['down']),
			'U_DTST_FILTER_NO_SHOW'	=> $this->helper->route('dtst_reputation', $filters_params['noshow']),

			'U_DTST_EVENT'			=> append_sid("{$this->root_path}viewtopic.{$this->php_ext}", 'f=' . (int) $event['forum_id'] . '&t=' . (int) $event['topic_id']),
			'U_DTST_REP_SEARCH'		=> $this->helper->route('dtst_reputation', array('t' => (int) $topic_id)),
		));

		$name = $this->lang->lang('DTST_REP_EVENT', utf8_strtolower($this->config['dtst_rep_name']));

		make_jumpbox(append_sid("{$this->root_path}viewforum.{$this->php_ext}"));

		return $this->helper->render('@phpbbstudio_dtst/dtst_reputation.html', $name);
	}

	/**
	 * Give reputation to a user.
	 *
	 * @return mixed
	 * @access public
	 */
	public function give()
	{
		/* Request some variables */
		$topic_id = (int) $this->request->variable('t', 0);
		$user_id = (int) $this->request->variable('u', 0);
		$action = $this->request->variable('a', 0);

		if (!$topic_id)
		{
			if ($this->request->is_ajax())
			{
				$json_response = new \phpbb\json_response;
				$json_response->send(array(
					'MESSAGE_TITLE'		=> $this->lang->lang('ERROR'),
					'MESSAGE_TEXT'		=>  $this->lang->lang('NO_TOPIC'),
				));
			}

			throw new \phpbb\exception\http_exception(404, 'NO_TOPIC');
		}

		/* Add our language file */
		$this->lang->add_lang('common', 'phpbbstudio/dtst');

		if ($user_id === (int) $this->user->data['user_id'])
		{
			// User can not thumbs up/down themselves!
			if ($this->request->is_ajax())
			{
				$json_response = new \phpbb\json_response;
				$json_response->send(array(
					'MESSAGE_TITLE'		=> $this->lang->lang('ERROR'),
					'MESSAGE_TEXT'		=>  $this->lang->lang('DTST_REP_NOT_SELF'),
				));
			}

			throw new \phpbb\exception\http_exception(401, 'DTST_REP_NOT_SELF');
		}

		// Check if user is even attending this event
		$sql = 'SELECT s.user_id, r.reputation_action, t.topic_poster, t.topic_title
				FROM ' . $this->dtst_slots . ' s
				JOIN ' . TOPICS_TABLE . ' t
					ON s.topic_id = t.topic_id
				LEFT JOIN ' . $this->dtst_reputation . ' r
					ON s.user_id = r.recipient_id
						AND r.reputation_action = ' . ext::DTST_REP_NO_SHOW . '
				WHERE s.topic_id = ' . (int) $topic_id . '
					AND s.user_id = ' . (int) $this->user->data['user_id'] . '
					AND s.dtst_status = ' . ext::DTST_STATUS_ACCEPTED;
		$result = $this->db->sql_query_limit($sql, 1);
		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$topic_title = $row['topic_title']; // Grab the topic title for the log
		$s_is_host = $this->user->data['user_id'] == $row['topic_poster']; // Check if this user is the host of the event

		// If the user was not attending or is marked as a "no show", he can not give out reputation
		if (!$row || (isset($row['reputation_action']) && $row['reputation_action'] == ext::DTST_REP_NO_SHOW))
		{
			if ($this->request->is_ajax())
			{
				$json_response = new \phpbb\json_response;
				$json_response->send(array(
					'MESSAGE_TITLE' => $this->lang->lang('ERROR'),
					'MESSAGE_TEXT'  => $this->lang->lang('DTST_REP_NOT_ATTENDING'),
				));
			}

			throw new \phpbb\exception\http_exception(401, 'DTST_REP_NOT_ATTENDING');
		}

		$reps = array();
		$counts = array(
			ext::DTST_REP_THUMBS_UP => 0,
			ext::DTST_REP_THUMBS_DOWN => 0,
			ext::DTST_REP_CONDUCT_GOOD => 0,
			ext::DTST_REP_CONDUCT_BAD => 0,
			ext::DTST_REP_NO_SHOW => 0,
		);

		/* Select all reputation given out by this user */
		$sql = 'SELECT *
				FROM ' . $this->dtst_reputation . '
				WHERE user_id = ' . (int) $this->user->data['user_id'] . '
					AND topic_id = ' . (int) $topic_id;
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			$reps[$row['recipient_id']] = $row;

			if (array_key_exists($action, $counts))
			{
				$counts[$row['reputation_action']]++;
			}
		}
		$this->db->sql_freeresult($result);

		// If the user that is receiving the thumbs up/down is already in the list and the action is the same, it means we are removing it.
		$remove_reputation = $change_reputation = false;

		if (array_key_exists($action, $counts) && array_key_exists($user_id, $reps))
		{
			if ($reps[$user_id]['reputation_action'] == $action)
			{
				$remove_reputation = true;
			}
			else
			{
				$change_reputation = true;
			}
		}

		/* Set up the initial counts */
		$count_up	= $s_is_host ? (int) $this->config['dtst_rep_count_good'] - $counts[ext::DTST_REP_CONDUCT_GOOD] : (int) $this->config['dtst_rep_count_up'] - $counts[ext::DTST_REP_THUMBS_UP];
		$count_down	= $s_is_host ? (int) $this->config['dtst_rep_count_bad'] - $counts[ext::DTST_REP_CONDUCT_BAD] : (int) $this->config['dtst_rep_count_down'] - $counts[ext::DTST_REP_THUMBS_DOWN];

		/* Calculate the new counts */
		if (($remove_reputation && in_array($action, array(ext::DTST_REP_CONDUCT_GOOD, ext::DTST_REP_THUMBS_UP))) || ($change_reputation && in_array($action, array(ext::DTST_REP_CONDUCT_BAD, ext::DTST_REP_THUMBS_DOWN))))
		{
			$count_up++;
		}

		if (($remove_reputation && in_array($action, array(ext::DTST_REP_CONDUCT_BAD, ext::DTST_REP_THUMBS_DOWN))) || ($change_reputation && in_array($action, array(ext::DTST_REP_CONDUCT_GOOD, ext::DTST_REP_THUMBS_UP))))
		{
			$count_down++;
		}

		if (!$remove_reputation && in_array($action, array(ext::DTST_REP_CONDUCT_GOOD, ext::DTST_REP_THUMBS_UP)))
		{
			$count_up--;
		}

		if (!$remove_reputation && in_array($action, array(ext::DTST_REP_CONDUCT_BAD, ext::DTST_REP_THUMBS_DOWN)))
		{
			$count_down--;
		}

		$reputation_url = $this->helper->route('dtst_reputation', array('t' => (int) $topic_id));
		$reputation_points = 0;
		$reputation_lang = '';
		$reputation_class = '';

		switch ($action)
		{
			case ext::DTST_REP_THUMBS_UP:
				$reputation_max		= $this->config['dtst_rep_count_up'];
				$reputation_points	= $this->config['dtst_rep_points_up'];
				$reputation_lang	= $remove_reputation ? 'THUMBS_UP_DEL' : 'THUMBS_UP';
				$reputation_class	= 'dtst-repped-up';
			break;
			case ext::DTST_REP_THUMBS_DOWN:
				$reputation_max		= $this->config['dtst_rep_count_down'];
				$reputation_points	= $this->config['dtst_rep_points_down'];
				$reputation_lang	= $remove_reputation ? 'THUMBS_DOWN_DEL' : 'THUMBS_DOWN';
				$reputation_class	= 'dtst-repped-down';
			break;

			case ext::DTST_REP_CONDUCT_GOOD:
				$reputation_max		= $this->config['dtst_rep_count_good'];
				$reputation_points	= $this->config['dtst_rep_points_good'];
				$reputation_lang	= $remove_reputation ? 'CONDUCT_GOOD_DEL' : 'CONDUCT_GOOD';
				$reputation_class	= 'dtst-repped-up';
			break;
			case ext::DTST_REP_CONDUCT_BAD:
				$reputation_max		= $this->config['dtst_rep_count_bad'];
				$reputation_points	= $this->config['dtst_rep_points_bad'];
				$reputation_lang	= $remove_reputation ? 'CONDUCT_BAD_DEL' : 'CONDUCT_BAD';
				$reputation_class	= 'dtst-repped-down';
			break;

			case ext::DTST_REP_NO_SHOW:
				$reputation_max		= INF;
				$reputation_points	= $this->config['dtst_rep_points_noshow'];
				$reputation_lang	= 'NO_SHOW';
				$reputation_class	= 'dtst-repped-down';
			break;
		}

		/* Make sure the user not exceeding max amount of thumbs up / down, unless we are removing */
		if (!$remove_reputation && array_key_exists($action, $counts) && ($counts[$action] == $reputation_max))
		{
			if ($this->request->is_ajax())
			{
				$json_response = new \phpbb\json_response;
				$json_response->send(array(
					'MESSAGE_TITLE' => $this->lang->lang('ERROR'),
					'MESSAGE_TEXT'  => $this->lang->lang('DTST_REP_' . $reputation_lang . '_MAXED'),
				));
			}

			throw new \phpbb\exception\http_exception(401, 'DTST_REP_' . $reputation_lang . '_MAXED');
		}

		/* Set the confirm body, depending on if we need a reason supplied or not */
		$confirm_body = $remove_reputation ? 'confirm_body.html' : '@phpbbstudio_dtst/dtst_confirm_body.html';

		if (confirm_box(true) || $this->request->is_set_post('confirm'))
		{
			/* If we are removing a given reputation */
			if ($remove_reputation)
			{
				$sql = 'DELETE FROM ' . $this->dtst_reputation . '
						WHERE user_id = ' . (int) $this->user->data['user_id'] . '
							AND recipient_id = ' . (int) $user_id . '
							AND topic_id = ' . (int) $topic_id;
				$this->db->sql_query($sql);

				$points_sql = -$reputation_points;
			}
			else
			{
				/* Request the reason for giving this reputation */
				$reputation_reason = $this->request->variable('dtst_reason', '', true);
				$reputation_reason_length = utf8_strlen($reputation_reason);

				/* Reason has to be Emojis safe */
				if (preg_match_all('/[\x{10000}-\x{10FFFF}]/u', $reputation_reason, $matches))
				{
					$list = implode('<br>', $matches[0]);
					$reputation_reason_emoji = $this->lang->lang('DTST_REASON_EMOJIS_SUPPORT', $list);

					if ($this->request->is_ajax())
					{
						$json_response = new \phpbb\json_response;
						$json_response->send(array(
							'MESSAGE_TITLE' => $this->lang->lang('ERROR'),
							'MESSAGE_TEXT'  => $reputation_reason_emoji,
						));
					}

					throw new \phpbb\exception\http_exception(411, $reputation_reason_emoji);
				}

				if ($reputation_reason_length > 255 || $reputation_reason_length == 0)
				{
					$reputation_reason_lang = $reputation_reason_length == 0 ? $this->lang->lang('DTST_REASON_MISSING') : $this->lang->lang('DTST_REASON_TOO_LONG', $reputation_reason_length);

					if ($this->request->is_ajax())
					{
						$json_response = new \phpbb\json_response;
						$json_response->send(array(
							'MESSAGE_TITLE' => $this->lang->lang('ERROR'),
							'MESSAGE_TEXT'  => $reputation_reason_lang,
						));
					}

					throw new \phpbb\exception\http_exception(411, $reputation_reason_lang);
				}

				/* If we are changing the reputation from a user (from good to bad, etc..) */
				if ($change_reputation)
				{
					$sql = 'UPDATE ' . $this->dtst_reputation . ' SET ' . $this->db->sql_build_array('UPDATE', array(
							'reputation_action'	=> (int) $action,
							'reputation_points'	=> (int) $reputation_points,
							'reputation_reason'	=> (string) $reputation_reason,
						)) . '
							WHERE user_id = ' . (int) $this->user->data['user_id'] . '
								AND recipient_id = ' . (int) $user_id . '
								AND topic_id = ' . (int) $topic_id;
					$this->db->sql_query($sql);

					$points_sql = ($reputation_points - $reps[$user_id]['reputation_points']);
				}
				else
				{
					/* If we are marking the user as a 'no show', we delete all reputation given and gained by this user. */
					if ($action == ext::DTST_REP_NO_SHOW)
					{
						$sql = 'SELECT SUM(reputation_points) as points 
								FROM ' . $this->dtst_reputation . ' 
								WHERE topic_id = ' . (int) $topic_id . '
									AND recipient_id = ' . (int) $user_id;
						$result = $this->db->sql_query($sql);
						$remove_points = $this->db->sql_fetchfield('points');
						$this->db->sql_freeresult($result);

						$sql = 'DELETE FROM ' . $this->dtst_reputation . '
								WHERE topic_id = ' . (int) $topic_id . '
									AND (user_id = ' . (int) $user_id . '
										OR recipient_id = ' . (int) $user_id . ')';
						$this->db->sql_query($sql);
					}

					/* Insert the reputation action */
					$rep_array = array(
						'topic_id'          => (int) $topic_id,
						'user_id'           => (int) $this->user->data['user_id'],
						'recipient_id'      => (int) $user_id,
						'reputation_action' => (int) $action,
						'reputation_points' => (int) $reputation_points,
						'reputation_reason' => (string) $reputation_reason,
						'reputation_time'   => time(),
					);

					$sql = 'INSERT INTO ' . $this->dtst_reputation . ' ' . $this->db->sql_build_array('INSERT', $rep_array);
					$this->db->sql_query($sql);

					$points_to_add = isset($remove_points) ? $reputation_points - $remove_points : $reputation_points;
					$points_sql = $points_to_add;
				}
			}

			/* Update the user points */
			$this->rep_func->set_reputation((int) $user_id, (int) $points_sql);

			/* Log the action */
			$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'ACP_DTST_LOG_REP_' . $reputation_lang, time(), array('reportee_id' => (int) $user_id, $topic_title));

			/* If the request is AJAX */
			if ($this->request->is_ajax())
			{
				$json_response = new \phpbb\json_response;
				$json_response->send(array(
					'MESSAGE_TITLE'		=> $this->lang->lang('INFORMATION'),
					'MESSAGE_TEXT'		=> $this->lang->lang('DTST_REP_' . $reputation_lang . '_SUCCESS'),

					'DTST_SUCCESS'		=> true,
					'DTST_CLASS'		=> $reputation_class,
					'DTST_NO_SHOW'		=> $action == ext::DTST_REP_NO_SHOW,
					'DTST_COUNT_UP'		=> $count_up,
					'DTST_COUNT_DOWN'	=> $count_down,
				));
			}

			/* If the request is NOT ajax */
			meta_refresh(3, $reputation_url);
			return $this->helper->message($this->lang->lang('DTST_REP_' . $reputation_lang . '_SUCCESS') . '<br><br>' . $this->lang->lang('RETURN_PAGE', '<a href="' . $reputation_url . '">', '</a>'));
		}
		else
		{
			/* Supply a form action for the non-AJAX page */
			if (!$this->request->is_ajax())
			{
				$this->template->assign_vars(array(
					'S_CONFIRM_ACTION'	=> $this->helper->route('dtst_reputation_give', array('t' => (int) $topic_id, 'u' => (int) $user_id, 'a' => (int) $action)),
					'S_DTST_NO_AJAX'	=> true,
				));
			}

			confirm_box(false, $this->lang->lang('DTST_REP_' . $reputation_lang . '_CONFIRM', utf8_strtolower($this->config['dtst_rep_name'])), build_hidden_fields(array(
				't'		=> $topic_id,
				'u'		=> $user_id,
				'a'		=> $action,
			)), $confirm_body, $this->helper->get_current_url());

			/* If the confirm box was canceled */
			return redirect($reputation_url);
		}
	}

	/**
	 * View reputation list for a specific user.
	 *
	 * @param  int		$user_id		The user identifier
	 * @param  int		$page			The page number
	 * @return \Symfony\Component\HttpFoundation\Response
	 * @access public
	 */
	public function view($user_id, $page)
	{
		if (!$user_id)
		{
			throw new \phpbb\exception\http_exception(404, 'NO_USER');
		}

		/**
		 * Request sorting variables
		 * $sort		a - Action
		 * 				e - Event name
		 * 				f - From (or to)
		 * 				p - Points
		 * 				t - Time (default)
		 * $sort_dir	d - Descending (DESC) (default)
		 * 				a - Ascending (ASC)
		 * $sort_rep	r - Received reputation
		 * 				g - Given reputation
		 * 				e - Event reputation
		 */
		$sort = $this->request->variable('s', 't');
		$sort_dir = $this->request->variable('d', 'd');
		$sort_rep = $this->request->variable('r', 'r');

		/* Set start variable for pagination */
		$reputation_per_page = ((bool) $this->config['dtst_rep_users_page']) ? (int) $this->config['dtst_rep_users_page'] : (int) $this->config['topics_per_page'];
		$start = (($page - 1) * $reputation_per_page);

		/* Add our language file only when necessary */
		$this->lang->add_lang('common', 'phpbbstudio/dtst');

		/* Set up some language strings for the reputation actions */
		$reputation_lang = $this->rep_func->get_reputation_lang();

		if (!function_exists('phpbb_get_user_rank'))
		{
			include($this->root_path . 'includes/functions_display.' . $this->php_ext);
		}

		/* Check if we are using a DTST Bot */
		if ($this->config['dtst_use_bot'])
		{
			/* If so, let's request some information about this bot :) */
			$sql = 'SELECT user_id, username, user_colour FROM ' . USERS_TABLE . ' WHERE user_id = ' . (int) $this->config['dtst_bot'];
			$result = $this->db->sql_query_limit($sql, 1);
			$bot = $this->db->sql_fetchrow($result);
			$this->db->sql_freeresult($result);

			$bot_name = get_username_string('full', $bot['user_id'], $bot['username'], $bot['user_colour']);
		}

		/* Grab some basic information about the user we are viewing */
		$sql = 'SELECT u.user_id, u.username, u.user_colour, u.user_lang,  u.dtst_reputation, u.user_rank, u.user_posts,
						u.user_avatar, u.user_avatar_type, u.user_avatar_width, u.user_avatar_height, u.dtst_rank_value
				FROM ' . USERS_TABLE . ' u
				WHERE u.user_id = ' . (int) $user_id;
		$result = $this->db->sql_query_limit($sql, 1);
		$user = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$user_rank_data = phpbb_get_user_rank($user, $user['user_posts']);

		/* Set up SQL Query sorting */
		$sql_direction = $sort_dir == 'd' ? 'DESC' : 'ASC';

		if (in_array($sort_rep, array('r', 'g')))
		{
			switch ($sort)
			{
				case 'a':
					$sql_order = 'r.reputation_action';
				break;
				case 'p':
					$sql_order = 'r.reputation_points';
				break;
				case 'f':
					$sql_order = 'u.username_clean';
				break;
				case 'e':
					$sql_order = 't.topic_title';
				break;
				case 't':
				default:
					$sql_order = 'r.reputation_time';
				break;
			}
		}
		else
		{
			switch ($sort)
			{
				case 'e':
					$sql_order = 't.topic_title';
				break;
				case 'p':
					$sql_order = 'points';
				break;
				case 't':
				default:
					$sql_order = 't.dtst_date_unix';
				break;
			}
		}

		/* If we are viewing the reputation that is "giving" or "received" */
		if (in_array($sort_rep, array('r', 'g')))
		{
			$sql_where_column = $sort_rep == 'r' ? 'r.recipient_id' : 'r.user_id';

			$sql_where_actions = array(ext::DTST_REP_WITHDREW, ext::DTST_REP_NO_REPLY, null);

			if ((!$sort_rep != 'g') || (!$this->config['dtst_show_mod_anon']))
			{
				$sql_where_actions[] = ext::DTST_REP_MOD;
			}

			$sql_array = array(
				'SELECT' => 'r.*, u.user_id, u.username, u.user_colour,
								t.forum_id, t.topic_id, t.topic_title',

				'FROM' => array(
					$this->dtst_reputation	=> 'r',
				),

				'LEFT_JOIN' => array(
					array(
						'FROM'	=> array(TOPICS_TABLE => 't'),
						'ON'	=> 'r.topic_id = t.topic_id'
					),
					array(
						'FROM'	=> array(USERS_TABLE => 'u'),
						'ON'	=> ($sort_rep == 'r') ? 'u.user_id = r.user_id' : 'u.user_id = r.recipient_id',
					),
				),

				'WHERE' => $sql_where_column . ' = ' . (int) $user_id . '
								AND (
									t.dtst_event_ended = 1 
									OR t.dtst_event_canceled = 1 
									OR ' . $this->db->sql_in_set('r.reputation_action', $sql_where_actions) . '
								)',

				'ORDER_BY' => $sql_order . ' ' . $sql_direction,
			);
			$sql = $this->db->sql_build_query('SELECT', $sql_array);
			$result = $this->db->sql_query_limit($sql, $reputation_per_page, $start);

			while ($row = $this->db->sql_fetchrow($result))
			{
				/* Make sure we can read this forum */
				list ($dtst_rep_event, $u_dtst_rep_event) = $this->check_private_forum($row['forum_id'], $row['topic_title'], $row['topic_id']);

				$this->template->assign_block_vars('reputation', array(
					'ACTION'	=> $reputation_lang[$sort_rep][(int) $row['reputation_action']],
					'CLASS'		=> $this->rep_func->get_reputation_class($row['reputation_points']),
					'POINTS'	=> $row['reputation_points'],
					'FROM'		=> $row['user_id'] <> 0 ? get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']) : (isset($bot_name) ? $bot_name : htmlspecialchars_decode($this->config['sitename'])),
					'EVENT'		=> $dtst_rep_event,
					'TIME'		=> $this->user->format_date($row['reputation_time']),

					'S_MOD'		=> $row['reputation_action'] == ext::DTST_REP_MOD,

					'U_DEL'		=> $this->helper->route('dtst_reputation_delete', array('u' => (int) $user_id, 'r' => $row['reputation_id'])),
					'U_EVENT'	=> $u_dtst_rep_event,
				));
			}
			$this->db->sql_freeresult($result);

			/* Run the same query again but now count the users */
			unset($sql_array['ORDER_BY']);
			$sql_array['SELECT'] = 'COUNT(r.reputation_id) as count';
			$sql_array['GROUP_BY'] = $sql_where_column;
			$sql = $this->db->sql_build_query('SELECT', $sql_array);
			$result = $this->db->sql_query($sql);
			$reputation_count = $this->db->sql_fetchfield('count');
			$this->db->sql_freeresult($result);
		}
		else
		{
			$events_list = array();

			/* First we have to grab the event identifiers */
			$sql_array = array(
				'SELECT' => 'r.topic_id, SUM(r.reputation_points) as points',

				'FROM' => array(
					$this->dtst_reputation	=> 'r',
				),

				'LEFT_JOIN' => array(
					array(
						'FROM'	=> array(TOPICS_TABLE => 't'),
						'ON'	=> 'r.topic_id = t.topic_id',
					),
				),

				'WHERE' => 'r.topic_id <> 0 AND r.recipient_id = ' . (int) $user_id,

				'GROUP_BY' => 'r.topic_id, t.topic_title, t.dtst_date_unix',

				'ORDER_BY' => $sql_order . ' ' . $sql_direction,
			);
			$sql = $this->db->sql_build_query('SELECT', $sql_array);
			$result = $this->db->sql_query_limit($sql, $reputation_per_page, $start);
			while ($row = $this->db->sql_fetchrow($result))
			{
				/* Add them to the array */
				$events_list[$row['topic_id']] = array(
					'points'	=> $row['points'],
				);
			}
			$this->db->sql_freeresult($result);

			if ($events_list)
			{
				/* Secondly we have to grab the event information */
				$sql = 'SELECT forum_id, topic_id, topic_title, dtst_date_unix
						FROM ' . TOPICS_TABLE . '
						WHERE ' . $this->db->sql_in_set('topic_id', array_keys($events_list));
				$result = $this->db->sql_query($sql);
				while ($row = $this->db->sql_fetchrow($result))
				{
					/* Merge them in to the existing array, to keep the ORDER BY order */
					$events_list[$row['topic_id']] = array_merge($events_list[$row['topic_id']], $row);
				}
				$this->db->sql_freeresult($result);

				/* Now iterate over all the events */
				foreach ($events_list as $row)
				{
					/* Make sure we can read this forum */
					list ($dtst_rep_event, $u_dtst_rep_event) = $this->check_private_forum($row['forum_id'], $row['topic_title'], $row['topic_id']);

					$this->template->assign_block_vars('events', array(
						'CLASS'		=> $this->rep_func->get_reputation_class($row['points']),
						'POINTS'	=> $row['points'],
						'TITLE'		=> $dtst_rep_event,
						'TIME'		=> $this->user->format_date($row['dtst_date_unix']),

						'U_EVENT'	=> $u_dtst_rep_event,
					));
				}

				/* Run the same query again but now count the events */
				$sql_array = array(
					'SELECT' => 'COUNT(DISTINCT(r.topic_id)) as count',

					'FROM' => array(
						$this->dtst_reputation	=> 'r',
					),

					'WHERE' => 'r.topic_id <> 0 AND r.recipient_id = ' . (int) $user_id,

					'GROUP_BY' => 'r.recipient_id',
				);
				$sql = $this->db->sql_build_query('SELECT', $sql_array);
				$result = $this->db->sql_query($sql);
				$reputation_count = $this->db->sql_fetchfield('count');
				$this->db->sql_freeresult($result);
			}
			else
			{
				$reputation_count = 0;
			}
		}

		/* Start pagination */
		$this->pagination->generate_template_pagination(
			array(
				'routes' => array(
					'dtst_reputation_view',
					'dtst_reputation_view_page',
				),
				'params' => array('user_id' => (int) $user_id, 'r' => $sort_rep, 's' => $sort, 'd' => $sort_dir),
			), 'pagination', 'page', $reputation_count, (int) $reputation_per_page, $start);

		/* Set up starting array for this user's statistics */
		$counts = array(
			'given'		=> array(
				ext::DTST_REP_THUMBS_UP		=> 0,
				ext::DTST_REP_THUMBS_DOWN	=> 0,
				ext::DTST_REP_CONDUCT_GOOD	=> 0,
				ext::DTST_REP_CONDUCT_BAD	=> 0,
				ext::DTST_REP_NO_SHOW		=> 0,
			),
			'received'	=> array(
				ext::DTST_REP_THUMBS_UP		=> 0,
				ext::DTST_REP_THUMBS_DOWN	=> 0,
				ext::DTST_REP_CONDUCT_GOOD	=> 0,
				ext::DTST_REP_CONDUCT_BAD	=> 0,
				ext::DTST_REP_NO_SHOW		=> 0,
				ext::DTST_REP_ATTENDED		=> 0,
				ext::DTST_REP_HOSTED		=> 0,
			),
		);

		/* Grab the counts for this user */
		$counts = $this->get_reputation_counts((int) $user_id, $counts, 'given');
		$counts = $this->get_reputation_counts((int) $user_id, $counts, 'received');

		$sql_array = array(
			'SELECT' => 't.topic_id, t.topic_title, SUM(r.reputation_points) as total',

			'FROM' => array(
				$this->dtst_reputation	=> 'r',
				TOPICS_TABLE			=> 't',
			),

			'WHERE' => 'r.recipient_id = ' . (int) $user_id . '
							AND r.topic_id <> 0
							AND r.topic_id = t.topic_id',

			'GROUP_BY' => 't.topic_id, t.topic_title',

			'ORDER_BY' => 'total ASC',
		);
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query_limit($sql, 1);
		$worst = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$sql_array['ORDER_BY'] = 'total DESC';
		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query_limit($sql, 1);
		$best = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$sql = 'SELECT t.topic_id, t.topic_title 
				FROM ' . $this->dtst_reputation . ' r
				JOIN ' . TOPICS_TABLE . ' t
					ON r.topic_id = t.topic_id 
				WHERE ' . $this->db->sql_in_set('r.reputation_action', array(ext::DTST_REP_HOSTED, ext::DTST_REP_ATTENDED)) . '
				ORDER BY r.reputation_time DESC';
		$result = $this->db->sql_query_limit($sql, 1);
		$recent = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$events = array(
			'best'		=> array(
				'total'		=> $best['total'],
				'title'		=> $best['topic_title'],
				'url'		=> append_sid("{$this->root_path}viewtopic.{$this->php_ext}", 't=' . (int) $best['topic_id']),
			),
			'worst'		=> array(
				'total'		=> $worst['total'],
				'title'		=> $worst['topic_title'],
				'url'		=> append_sid("{$this->root_path}viewtopic.{$this->php_ext}", 't=' . (int) $worst['topic_id']),
			),
			'recent'	=> array(
				'title'		=> $recent['topic_title'],
				'url'		=> append_sid("{$this->root_path}viewtopic.{$this->php_ext}", 't=' . (int) $recent['topic_id']),
			),
		);

		/**
		 * Percentages for styling etc..
		 */
		$percent = $this->utils->dtst_percent($user['dtst_reputation']);
		$degrees = (360 * $percent) / 100;
		$rank_start = 90;

		/* Grab the sync'ed values for this */
		$percent_rank = $this->utils->percentage($user['dtst_reputation']);

		/* Get Ranks in their localised form or default to EN */
		list($dtst_rank_title, $dtst_rank_desc, $dtst_rank_bckg, $dtst_rank_text) = $this->utils->dtst_ranks_vars($percent_rank);

		$this->template->assign_vars(array(
			'PAGE_NUMBER'		=> $this->pagination->on_page($reputation_count, $reputation_per_page, $start),
			'TOTAL_REPUTATION'	=> $sort_rep == 'e' ? $this->lang->lang('DTST_EVENT_TOTAL', $reputation_count) : $this->lang->lang('DTST_REP_TOTAL', $reputation_count, utf8_strtolower($this->config['dtst_rep_name'])),

			'USER_ID'		=> $user['user_id'],
			'USERNAME'		=> get_username_string('full', $user['user_id'], $user['username'], $user['user_colour']),

			'AVATAR'		=> phpbb_get_user_avatar($user),

			'RANK_TITLE'	=> $user_rank_data['title'],
			'RANK_IMG'		=> $user_rank_data['img'],
			'RANK_IMG_SRC'	=> $user_rank_data['img_src'],

			'REPUTATION'		=> (int) $user['dtst_reputation'],
			'MAX_REPUTATION'	=> ext::DTST_MAX_REP,
			'RANK_VALUE'		=> ((int) $user['dtst_rank_value'] >= ext::DTST_RANK_ZERO) ? (int) $user['dtst_rank_value'] : 0,

			'PERCENT_RATEYO'	=> $this->utils->dtst_percent_rateyo($percent),
			'PERCENT'			=> number_format((float) $percent, 2, '.', ','),
			'DEGREE'			=> $percent > 50 ? $degrees - $rank_start : $degrees + $rank_start,
			'S_REP_AVAILABLE'	=> ((int) $user['dtst_reputation'] == 0) ? false : true,

			'DTST_RANK_TITLE'	=> $dtst_rank_title,
			'DTST_RANK_DESC'	=> $dtst_rank_desc,
			'DTST_RANK_BCKG'	=> $dtst_rank_bckg, // color
			'DTST_RANK_TEXT'	=> $dtst_rank_text, // color

			'STATS_COUNT_HOSTED'		=> $counts['received'][ext::DTST_REP_HOSTED],
			'STATS_COUNT_ATTENDED'		=> $counts['received'][ext::DTST_REP_ATTENDED],
			'STATS_COUNT_NO_SHOW'		=> $counts['received'][ext::DTST_REP_NO_SHOW],
			'STATS_COUNT_GIVEN_UP'		=> $counts['given'][ext::DTST_REP_THUMBS_UP] + $counts['given'][ext::DTST_REP_CONDUCT_GOOD],
			'STATS_COUNT_GIVEN_DOWN'	=> $counts['given'][ext::DTST_REP_THUMBS_DOWN] + $counts['given'][ext::DTST_REP_CONDUCT_BAD] + $counts['given'][ext::DTST_REP_NO_SHOW],
			'STATS_COUNT_REC_UP'		=> $counts['received'][ext::DTST_REP_THUMBS_UP] + $counts['received'][ext::DTST_REP_CONDUCT_GOOD],
			'STATS_COUNT_REC_DOWN'		=> $counts['received'][ext::DTST_REP_THUMBS_DOWN] + $counts['received'][ext::DTST_REP_CONDUCT_BAD],
			'STATS_EVENT_BEST'			=> array_change_key_case($events['best'], CASE_UPPER),
			'STATS_EVENT_WORST'			=> array_change_key_case($events['worst'], CASE_UPPER),
			'STATS_EVENT_RECENT'		=> array_change_key_case($events['recent'], CASE_UPPER),

			'S_DTST_DELETE_REP'			=> $this->auth->acl_get('m_dtst_mod'),

			'S_DTST_ANONIMITY_MOD'		=> $this->config['dtst_show_mod_anon'],

			'S_DTST_SORT_REP_EVENTS'	=> $sort_rep == 'e',
			'S_DTST_SORT_REP_GIVEN'		=> $sort_rep == 'g',
			'S_DTST_SORT_REP_RECEIVED'	=> $sort_rep == 'r',

			'U_DTST_SORT_ACTION'		=> $this->helper->route('dtst_reputation_view', array('user_id' => (int) $user_id, 'r' => $sort_rep, 's' => 'a', 'd' => (($sort == 'a' && $sort_dir == 'a') ? 'd' : 'a'))),
			'U_DTST_SORT_POINTS'		=> $this->helper->route('dtst_reputation_view', array('user_id' => (int) $user_id, 'r' => $sort_rep, 's' => 'p', 'd' => (($sort == 'p' && $sort_dir == 'a') ? 'd' : 'a'))),
			'U_DTST_SORT_FROM'			=> $this->helper->route('dtst_reputation_view', array('user_id' => (int) $user_id, 'r' => $sort_rep, 's' => 'f', 'd' => (($sort == 'f' && $sort_dir == 'a') ? 'd' : 'a'))),
			'U_DTST_SORT_EVENT'			=> $this->helper->route('dtst_reputation_view', array('user_id' => (int) $user_id, 'r' => $sort_rep, 's' => 'e', 'd' => (($sort == 'e' && $sort_dir == 'a') ? 'd' : 'a'))),
			'U_DTST_SORT_TIME'			=> $this->helper->route('dtst_reputation_view', array('user_id' => (int) $user_id, 'r' => $sort_rep, 's' => 't', 'd' => (($sort == 't' && $sort_dir == 'd') ? 'a' : 'd'))),

			'U_DTST_SORT_REP_EVENTS'	=> $this->helper->route('dtst_reputation_view', array('user_id' => (int) $user_id, 'r' => 'e')),
			'U_DTST_SORT_REP_GIVEN'		=> $this->helper->route('dtst_reputation_view', array('user_id' => (int) $user_id, 'r' => 'g')),
			'U_DTST_SORT_REP_RECEIVED'	=> $this->helper->route('dtst_reputation_view', array('user_id' => (int) $user_id, 'r' => 'r')),
		));

		$name = $this->lang->lang('DTST_REP_U_LIST', utf8_strtolower($this->config['dtst_rep_name']));

		make_jumpbox(append_sid("{$this->root_path}viewforum.{$this->php_ext}"));

		return $this->helper->render('@phpbbstudio_dtst/dtst_reputation_view.html', $name);
	}

	/**
	 * Count the amount of reputation given/received for a user.
	 *
	 * @param  int		$user_id		The user identifier
	 * @param  array	$counts			The current counts array
	 * @param  string	$direction		The direction (given|received)
	 * @return array
	 * @access private
	 */
	private function get_reputation_counts($user_id, $counts, $direction)
	{
		/* Set up the SQL WHERE column */
		$column = $direction == 'given' ? 'user_id' : 'recipient_id';

		$sql = 'SELECT COUNT(reputation_action) as count, reputation_action
				FROM ' . $this->dtst_reputation . '
				WHERE ' . $column . ' = ' . (int) $user_id . '
					AND ' . $this->db->sql_in_set('reputation_action', array_keys($counts[$direction])) . '
				GROUP BY reputation_action';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			/* Add the SQL COUNT to the counts array, indexed by reputation action constant. */
			$counts[$direction][(int) $row['reputation_action']] = (int) $row['count'];
		}
		$this->db->sql_freeresult($result);

		/* Return the counts array */
		return $counts;
	}

	/**
	 * Delete a reputation for a user.
	 *
	 * @return mixed
	 * @access public
	 */
	public function delete()
	{
		/* Request the reputation id to be deleted */
		$reputation_id = (int) $this->request->variable('r', 0);
		/* And request the user id from who the reputation will be deleted */
		$user_id = (int) $this->request->variable('u', 0);

		if (!$user_id)
		{
			throw new \phpbb\exception\http_exception(404, 'NO_USER');
		}

		/* Add our language file only when necessary */
		$this->lang->add_lang('common', 'phpbbstudio/dtst');

		/* Set up the back link */
		$reputation_url = $this->request->variable('mcp', false) ? append_sid("{$this->root_path}mcp.{$this->php_ext}", 'i=-phpbbstudio-dtst-mcp-main_module&mode=recent') : $this->helper->route('dtst_reputation_view', array('user_id' => (int) $user_id));

		if (!$this->auth->acl_get('m_dtst_mod'))
		{
			throw new \phpbb\exception\http_exception(403, $this->lang->lang('DTST_REP_DELETE_FORBIDDEN', utf8_strtolower($this->config['dtst_rep_name'])));
		}

		/* Grab some data about this reputation action */
		$sql = 'SELECT r.reputation_points, r.reputation_action, t.topic_title, t.topic_id, u.username
					FROM ' . $this->dtst_reputation . ' r
					JOIN ' . USERS_TABLE . ' u
						ON r.recipient_id = u.user_id
					LEFT JOIN ' . TOPICS_TABLE . ' t
						ON r.topic_id = t.topic_id
					WHERE r.reputation_id = ' . (int) $reputation_id;
		$result = $this->db->sql_query_limit($sql, 1);
		$event = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		if (!$event)
		{
			throw new \phpbb\exception\http_exception(404, $this->lang->lang('DTST_NO_REP', $this->config['dtst_rep_name']));
		}

		if (confirm_box(true) || $this->request->is_set_post('confirm'))
		{
			/* Request the reason for giving this reputation */
			$reputation_reason = $this->request->variable('dtst_reason', '', true);
			$reputation_reason_length = utf8_strlen($reputation_reason);

			/* Reason has to be between 0 and 255 characters and can not be empty */
			if ($reputation_reason_length > 255 || $reputation_reason_length == 0)
			{
				$reputation_reason_lang = $reputation_reason_length == 0 ? $this->lang->lang('DTST_REASON_MISSING') : $this->lang->lang('DTST_REASON_TOO_LONG', $reputation_reason_length);

				throw new \phpbb\exception\http_exception(411, $reputation_reason_lang);
			}

			/* Set up some language strings for the reputation actions */
			$reputation_lang = $this->rep_func->get_reputation_lang('r');

			/* Delete the reputation from the DTST Reputation table */
			$sql = 'DELETE FROM ' . $this->dtst_reputation . ' WHERE reputation_id = ' . (int) $reputation_id;
			$this->db->sql_query($sql);

			/* Update the user's reputation points */
			$sql = 'UPDATE ' . USERS_TABLE . ' SET dtst_reputation = dtst_reputation - ' . (int) $event['reputation_points'] . ' WHERE user_id = ' . (int) $user_id;
			$this->db->sql_query($sql);

			/* Log the action */
			$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, 'ACP_DTST_LOG_REPUTATION_DELETED', false, array('topic_id' => (int) $event['topic_id'], $event['username'], $event['topic_title'], $reputation_lang[$event['reputation_action']], $reputation_reason));

			/* Assign a refresh and return the success message */
			meta_refresh(3, $reputation_url);
			return $this->helper->message($this->lang->lang('DTST_REP_DELETE_SUCCESS', utf8_strtolower($this->config['dtst_rep_name'])) . '<br><br>' . $this->lang->lang('RETURN_PAGE', '<a href="' . $reputation_url . '">', '</a>'));
		}
		else
		{
			/* Assign some variables for our confirm body */
			$this->template->assign_vars(array(
				'S_CONFIRM_ACTION'	=> $this->helper->route('dtst_reputation_delete', array('u' => (int) $user_id, 'r' => (int) $reputation_id)),
				'S_DTST_NO_AJAX'	=> true,
			));

			/* Set up a confirm box */
			confirm_box(false, $this->lang->lang('DTST_REP_DELETE_CONFIRM', utf8_strtolower($this->config['dtst_rep_name'])), build_hidden_fields(array(
				'r'		=> (int) $reputation_id,
				'u'		=> (int) $user_id,
			)), '@phpbbstudio_dtst/dtst_confirm_body.html', $this->helper->get_current_url());

			/* If the confirm box was canceled, we end up here, so we redirect to the previous page */
			return redirect($reputation_url);
		}
	}

	/**
	 * Protects private forums content's visibility
	 *
	 * @param  int			$f_id		Forum ID
	 * @param  string		$t_title	Topic title
	 * @param  int			$t_id		Topic id
	 * @return array
	 * @access protected
	 */
	protected function check_private_forum($f_id, $t_title, $t_id)
	{
		/* Make sure we can read this forum */
		if ($this->auth->acl_get('f_read', (int) $f_id))
		{
			$dtst_rep_event = !empty($t_title) ? $t_title : '-';
			$u_dtst_rep_event = append_sid("{$this->root_path}viewtopic.{$this->php_ext}", 'f=' . (int) $f_id . '&t=' . (int) $t_id);
		}
		else
		{
			$dtst_rep_event = $this->lang->lang('DTST_REP_CLASSIFIED');
			$u_dtst_rep_event = false;
		}

		return array($dtst_rep_event, $u_dtst_rep_event);
	}
}
