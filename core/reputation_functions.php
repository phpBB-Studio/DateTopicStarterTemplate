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
 * @ignore
 */
use phpbbstudio\dtst\ext;

/**
 * Date Topic Event Calendar's Reputation helper service.
 */
class reputation_functions
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbb\notification\manager */
	protected $notification_manager;

	/** @var \phpbbstudio\dtst\core\operator */
	protected $dtst_utils;

	/** @var string DTST Reputation table */
	protected $dtst_reputation;

	/** @var string DTST Slots table */
	protected $dtst_slots;

	/**
	 * Constructor.
	 *
	 * @param  \phpbb\config\config					$config					Configuration object
	 * @param  \phpbb\db\driver\driver_interface	$db						Database object
	 * @param  \phpbb\language\language				$lang					Language object
	 * @param  \phpbb\notification\manager			$notification_manager	Notification manager object
	 * @param  \phpbbstudio\dtst\core\operator		$dtst_utils				DTST Operator
	 * @param  string								$dtst_reputation		DTST Reputation table
	 * @param  string								$dtst_slots				DTST Slots table
	 * @return void
	 * @access public
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\language\language $lang, \phpbb\notification\manager $notification_manager, \phpbbstudio\dtst\core\operator $dtst_utils, $dtst_reputation, $dtst_slots)
	{
		$this->config				= $config;
		$this->db					= $db;
		$this->lang 				= $lang;
		$this->notification_manager	= $notification_manager;
		$this->dtst_utils			= $dtst_utils;

		$this->dtst_reputation	= $dtst_reputation;
		$this->dtst_slots		= $dtst_slots;
	}

	/**
	 * Get the reputation class for a positive/negative reputation.
	 *
	 * @param  int		$reputation		The amount of reputation
	 * @return string					The reputation class
	 * @access public
	 */
	public function get_reputation_class($reputation)
	{
		/* Enforce a data type */
		$reputation = (int) $reputation;

		switch (true)
		{
			case $reputation == 0:
				$class = 'dtst-rep-neutral';
			break;

			case $reputation < 0:
				$class = 'dtst-rep-negative';
			break;

			case $reputation > 0:
			default:
				$class = 'dtst-rep-positive';
			break;
		}

		return $class;
	}

	/**
	 * Get the reputation language strings for reputation actions.
	 *
	 * @param  string	$mode		The mode (r|g) [Received | Given]
	 * @return array				An array containing all language strings.
	 * @access public
	 */
	public function get_reputation_lang($mode = '')
	{
		/* Enforce a data type */
		$mode = (string) $mode;

		$lang_array = array(
			'r'	=> array(
				ext::DTST_REP_MOD			=> $this->lang->lang('DTST_REP_ACTION_MOD', utf8_ucfirst($this->config['dtst_rep_name'])),
				ext::DTST_REP_HOSTED		=> $this->lang->lang('DTST_REP_ACTION_HOSTED'),
				ext::DTST_REP_CANCELED		=> $this->lang->lang('DTST_REP_ACTION_CANCELED'),
				ext::DTST_REP_ATTENDED		=> $this->lang->lang('DTST_REP_ACTION_ATTENDED'),
				ext::DTST_REP_WITHDREW		=> $this->lang->lang('DTST_REP_ACTION_WITHDREW'),
				ext::DTST_REP_CONDUCT_GOOD	=> $this->lang->lang('DTST_REP_ACTION_CONDUCT_GOOD'),
				ext::DTST_REP_CONDUCT_BAD	=> $this->lang->lang('DTST_REP_ACTION_CONDUCT_BAD'),
				ext::DTST_REP_THUMBS_UP		=> $this->lang->lang('DTST_REP_ACTION_THUMBS_UP'),
				ext::DTST_REP_THUMBS_DOWN	=> $this->lang->lang('DTST_REP_ACTION_THUMBS_DOWN'),
				ext::DTST_REP_NO_SHOW		=> $this->lang->lang('DTST_REP_ACTION_NO_SHOW'),
				ext::DTST_REP_NO_REPLY		=> $this->lang->lang('DTST_REP_ACTION_NO_REPLY'),
			),
			'g'	=> array(
				ext::DTST_REP_MOD			=> $this->lang->lang('DTST_REP_GIVEN_MOD', $this->config['dtst_rep_name']),
				ext::DTST_REP_HOSTED		=> $this->lang->lang('DTST_REP_GIVEN_HOSTED'),
				ext::DTST_REP_CANCELED		=> $this->lang->lang('DTST_REP_GIVEN_CANCELED'),
				ext::DTST_REP_ATTENDED		=> $this->lang->lang('DTST_REP_GIVEN_ATTENDED'),
				ext::DTST_REP_WITHDREW		=> $this->lang->lang('DTST_REP_GIVEN_WITHDREW'),
				ext::DTST_REP_CONDUCT_GOOD	=> $this->lang->lang('DTST_REP_GIVEN_CONDUCT_GOOD'),
				ext::DTST_REP_CONDUCT_BAD	=> $this->lang->lang('DTST_REP_GIVEN_CONDUCT_BAD'),
				ext::DTST_REP_THUMBS_UP		=> $this->lang->lang('DTST_REP_GIVEN_THUMBS_UP'),
				ext::DTST_REP_THUMBS_DOWN	=> $this->lang->lang('DTST_REP_GIVEN_THUMBS_DOWN'),
				ext::DTST_REP_NO_SHOW		=> $this->lang->lang('DTST_REP_GIVEN_NO_SHOW'),
				ext::DTST_REP_NO_REPLY		=> $this->lang->lang('DTST_REP_GIVEN_NO_REPLY'),
			),
		);

		return $mode ? $lang_array[$mode] : $lang_array;
	}

	/**
	 * Update a user's reputation.
	 *
	 * @param  int		$user_id		The user identifier
	 * @param  int		$reputation		The reputation to add
	 * @return void
	 * @access public
	 */
	public function set_reputation($user_id, $reputation)
	{
		/* Enforce a data type */
		$user_id	= (int) $user_id;
		$reputation	= (int) $reputation;

		if (!$reputation || !$user_id)
		{
			return;
		}

		$sql = 'UPDATE ' . USERS_TABLE . '
				SET dtst_reputation = dtst_reputation + ' . (int) $reputation . '
				WHERE user_id = ' . (int) $user_id;
		$this->db->sql_query($sql);
	}

	/**
	 * Check for host that have not replied.
	 *
	 * @param  int		$time		UNIX Timestamp before which an applicant should have applied
	 * @return array				Array containing host data
	 * @access public
	 */
	public function no_replies($time)
	{
		/* Enforce a data type */
		$time = (int) $time;

		/* Set up collection arrays */
		$apps = $reps = $hosts = array();

		/* Set up reputation amount */
		$reputation = (int) $this->config['dtst_rep_points_noreply'];

		/*
		 * Select all hosts that should have replied by now.
		 *
		 * SQL WHERE:
		 * 		- Applicant's status has to be "pending"
		 * 		- Application time has to be for the given timestamp
		 * 		- Event should not have ended yet
		 * 		- Reputation should NOT be present in the table
		 * 			if it is present, it means the host has already reputation deducted
		 * 			for this applicant & event combination
		 */
		$sql = 'SELECT t.topic_id, t.topic_title,
						u1.user_id as host_id, u1.username as host_name,
						u2.user_id as applicant_id, u2.username applicant_name
				FROM ' . $this->dtst_slots . ' s
				JOIN ' . TOPICS_TABLE . ' t
					ON s.topic_id = t.topic_id
				JOIN ' . USERS_TABLE . ' u1
					ON t.topic_poster = u1.user_id
				JOIN ' . USERS_TABLE . ' u2
					ON s.user_id = u2.user_id
				LEFT JOIN ' . $this->dtst_reputation . ' r
					ON s.topic_id = r.topic_id
						AND s.user_id = r.user_id
						AND r.reputation_action = ' . ext::DTST_REP_NO_REPLY . '
				WHERE s.dtst_status = ' . ext::DTST_STATUS_PENDING . '
					AND s.dtst_time < ' . (int) $time . '
					AND t.dtst_event_ended = 0
					AND r.reputation_action IS NULL';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			/* Count the reputation */
			$reps[$row['host_id']] = isset($reps[$row['host_id']]) ? $reps[$row['host_id']] + $reputation : $reputation;

			/* Add them to the hosts array */
			$hosts[] = array(
				'user'			=> (string) $row['applicant_name'],
				'host'			=> (string) $row['host_name'],
				'topic_id'		=> (int) $row['topic_id'],
				'topic_title'	=> (int) $row['topic_title'],
			);

			/* Add them to the applications array */
			$apps[] = array(
				'topic_id'          => (int) $row['topic_id'],
				'user_id'           => (int) $row['applicant_id'],
				'recipient_id'      => (int) $row['host_id'],
				'reputation_action' => (int) ext::DTST_REP_NO_REPLY,
				'reputation_points' => (int) $reputation,
				'reputation_reason' => '',
				'reputation_time'   => time(),
			);
		}
		$this->db->sql_freeresult($result);

		/* Update the hosts' reputation */
		if ($reps)
		{
			foreach ($reps as $host_id => $reputation_amount)
			{
				$this->set_reputation((int) $host_id, (int) $reputation_amount);
			}
		}

		/* Insert into the reputation table */
		if ($apps)
		{
			$this->db->sql_multi_insert($this->dtst_reputation, $apps);
		}

		return $hosts;
	}

	/**
	 * Check for events that have ended.
	 *
	 * @param  int		$time		UNIX Timestamp before which an event should be hosted
	 * @return array				Array containing event data
	 * @access public
	 */
	public function event_ended($time)
	{
		/* Enforce a data type */
		$time = (int) $time;

		/* Set up collection array */
		$events = array();

		/* Select all events that should be marked as ended */
		$sql = 'SELECT forum_id, topic_id, topic_poster, topic_title
		 		FROM ' . TOPICS_TABLE . '
				WHERE dtst_date_unix < ' . (int) $time . '
					AND dtst_date_unix <> 0
					AND dtst_event_ended = 0';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			/* Add them to the events array */
			$events[(int) $row['topic_id']] = array(
				'forum'	=> (int) $row['forum_id'],
				'title'	=> (string) $row['topic_title'],
				'host'	=> (int) $row['topic_poster'],
			);
		}
		$this->db->sql_freeresult($result);

		/* Mark the events as ended */
		if ($events)
		{
			$sql = 'UPDATE ' . TOPICS_TABLE . '
					SET dtst_event_ended = 1
					WHERE ' . $this->db->sql_in_set('topic_id', array_keys($events));
			$this->db->sql_query($sql);

			/* Give reputation to the users of these events */
			$this->give_event_reputation($events);

			/* Notify for the change in event's reputation status */
			$this->notify_reputation_period('opened', $events);
		}

		return $events;
	}

	/**
	 * Check for events' reputation period that have ended.
	 *
	 * @param  int		$time		UNIX Timestamp before which an event should be hosted
	 * @return array				Array containing event data
	 * @access private
	 */
	public function reputation_ended($time)
	{
		/* Enforce a data type */
		$time = (int) $time;

		/* Set up collection array */
		$events = array();

		/* Select all events that should be marked as having their reputation period ended */
		$sql = 'SELECT forum_id, topic_id, topic_title, topic_poster
				FROM ' . TOPICS_TABLE . '
				WHERE dtst_date_unix < ' . (int) $time . '
					AND dtst_date_unix <> 0
					AND dtst_rep_ended = 0
					AND dtst_event_ended = 1';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			/* Add them to the events array */
			$events[(int) $row['topic_id']] = array(
				'forum'	=> (int) $row['forum_id'],
				'title'	=> (string) $row['topic_title'],
				'host'	=> (int) $row['topic_poster'],
			);
		}

		/* Mark the events' reputation period as ended */
		if ($events)
		{
			$sql = 'UPDATE ' . TOPICS_TABLE . '
					SET dtst_rep_ended = 1
					WHERE ' . $this->db->sql_in_set('topic_id', array_keys($events));
			$this->db->sql_query($sql);

			/* Notify for the change in event's reputation status */
			$this->notify_reputation_period('closed', $events);
		}

		return $events;
	}

	/**
	 * Give reputation to the users of the events that have ended.
	 *
	 * @param  array	$events		Events that have ended
	 * @return void
	 * @access private
	 */
	private function give_event_reputation($events)
	{
		/* Select all users that have attended these events */
		$sql = 'SELECT topic_id, user_id
				FROM ' . $this->dtst_slots . '
				WHERE ' . $this->db->sql_in_set('topic_id', array_keys($events)) . '
					AND dtst_status = ' . ext::DTST_STATUS_ACCEPTED;
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			/* Add the user to the events array */
			$events[(int) $row['topic_id']]['attendees'][] = (int) $row['user_id'];
		}
		$this->db->sql_freeresult($result);

		/* Iterate over all the events */
		foreach ($events as $topic_id => $event)
		{
			/* Add the host to the reputation array */
			$users = array(
				array(
					'topic_id'          => (int) $topic_id,
					'user_id'           => 0,
					'recipient_id'      => (int) $event['host'],
					'reputation_action' => (int) ext::DTST_REP_HOSTED,
					'reputation_points' => (int) $this->config['dtst_rep_points_host'],
					'reputation_reason' => '',
					'reputation_time'   => time(),
				)
			);

			/* If the host didn't attend the event, he still gets the host reputation here */
			if (!isset($event['attendees']) || !in_array($event['host'], $event['attendees']))
			{
				$this->set_reputation((int) $event['host'], (int) $this->config['dtst_rep_points_host']);
			}

			/* If there are any attendees for this event */
			if (isset($event['attendees']))
			{
				/* Iterate over all the attendees for this event */
				foreach ($event['attendees'] as $user_id)
				{
					/* If this user was the host and thus did attend, add the hosting reputation aswell */
					$reputation = $this->config['dtst_rep_points_attend'];
					$reputation = ($user_id == $event['host']) ? $reputation + $this->config['dtst_rep_points_host'] : $reputation;

					/* Update this user's reputation */
					$this->set_reputation((int) $user_id, (int) $reputation);

					/* Add this user to the reputation array */
					$users[] = array(
						'topic_id'          => (int) $topic_id,
						'user_id'           => 0,
						'recipient_id'      => (int) $user_id,
						'reputation_action' => (int) ext::DTST_REP_ATTENDED,
						'reputation_points' => (int) $this->config['dtst_rep_points_attend'],
						'reputation_reason' => '',
						'reputation_time'   => time(),
					);
				}
			}

			/* Insert all the reputation given for this event */
			$this->db->sql_multi_insert($this->dtst_reputation, $users);
		}
	}

	/**
	 * Post a reply and send notifications for changes in a reputation status.
	 *
	 * @param  string		$mode			Reputation period opened|closed
	 * @param  array		$events			The events of which the reputation status changed.
	 * @return void
	 * @access private
	 */
	private function notify_reputation_period($mode, $events)
	{
		/* Iterate over all the events */
		foreach ($events as $topic_id => $event)
		{
			/* Check if we are using a bot */
			$user_id = ((bool) $this->config['dtst_use_bot']) ? (int) $this->config['dtst_bot'] : (int) $event['host'];

			/* Post a reply to the event */
			$this->dtst_utils->dtst_post_reply('reputation_' . $mode, $event['forum'], $topic_id, $user_id, '');

			/* Increment our notifications sent counter */
			$this->config->increment('dtst_rep_notification_id', 1);

			/* Send out notifications */
			$this->notification_manager->add_notifications('phpbbstudio.dtst.notification.type.reputation', array(
				'mode'				=> (string) $mode,
				'user_id'			=> (int) $user_id,
				'topic_id'			=> (int) $topic_id,
				'forum_id'			=> (int) $event['forum'],
				'topic_title'		=> (string) $event['title'],
				'reputation_name'	=> (string) $this->config['dtst_rep_name'],
				'notification_id'	=> (int) $this->config['dtst_rep_notification_id'],
			));
		}
	}
}
