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
 * Date Topic Event Calendar Event Controller.
 */
class event_controller implements event_interface
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

	/** @var \phpbb\notification\manager */
	protected $notification_manager;

	/** @var \phpbb\path_helper */
	protected $path_helper;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbbstudio\dtst\core\operator */
	protected $dtst_utils;

	/** @var string DTST Reputation table */
	protected $dtst_reputation;

	/** @var string DTST Slots table */
	protected $dtst_slots;

	/** @var string DTST PMs table */
	protected $dtst_privmsg;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string php File extension */
	protected $php_ext;


	/**
	 * Constructor.
	 *
	 * @param  \phpbb\auth\auth						$auth					Authentication object
	 * @param  \phpbb\config\config					$config					Configuration object
	 * @param  \phpbb\db\driver\driver_interface	$db						Database driver object
	 * @param  \phpbb\controller\helper				$helper					Controller helper object
	 * @param  \phpbb\language\language				$lang					Language object
	 * @param  \phpbb\log\log						$log					Log object
	 * @param  \phpbb\notification\manager			$notification_manager	Notification manager
	 * @param  \phpbb\path_helper					$path_helper			Path helper object
	 * @param  \phpbb\request\request				$request				Request object
	 * @param  \phpbb\template\template				$template				Template object
	 * @param  \phpbb\user							$user					User object
	 * @param  \phpbbstudio\dtst\core\operator		$dtst_utils				DTST Utilities
	 * @param  string								$dtst_reputation		DTST Reputation table
	 * @param  string								$dtst_slots				DTST Slots table
	 * @param  string								$dtst_privmsg			DTST PMs table
	 * @param  string								$root_path				phpBB root path
	 * @param  string								$php_ext				php File extension
	 * @access public
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\controller\helper $helper, \phpbb\language\language $lang, \phpbb\log\log $log, \phpbb\notification\manager $notification_manager, \phpbb\path_helper $path_helper, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, \phpbbstudio\dtst\core\operator $dtst_utils, $dtst_reputation, $dtst_slots, $dtst_privmsg, $root_path, $php_ext)
	{
		$this->auth					= $auth;
		$this->config				= $config;
		$this->db 					= $db;
		$this->helper				= $helper;
		$this->lang					= $lang;
		$this->log					= $log;
		$this->notification_manager	= $notification_manager;
		$this->path_helper			= $path_helper;
		$this->request				= $request;
		$this->template				= $template;
		$this->user					= $user;
		$this->dtst_utils			= $dtst_utils;

		$this->dtst_reputation		= $dtst_reputation;
		$this->dtst_slots			= $dtst_slots;
		$this->dtst_privmsg			= $dtst_privmsg;

		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;
	}

	/**
	 * Handles actions (apply|reapply|withdraw|cancel) performed by a user for an event.
	 *
	 * @return void
	 * @access public
	 */
	public function handle()
	{
		$topic_id = (int) $this->request->variable('t', 0);

		/* Add our language file only when needed */
		$this->lang->add_lang('common', 'phpbbstudio/dtst');

		/* First check if the user is even authenticated */
		if (!$this->dtst_utils->is_authed())
		{
			throw new \phpbb\exception\http_exception(403, 'DTST_NOT_AUTHORISED');
		}

		/* Check if a topic was found */
		if (!$topic_id)
		{
			throw new \phpbb\exception\http_exception(404, 'DTST_TOPIC_NOT_FOUND');
		}

		$data = $this->get_dtst_data();

		/* Set up a JSON response */
		$json_response = new \phpbb\json_response();

		/* Set up default variables */
		$user_status = 0;
		$users = array();

		/* Grab all users that have something to do with this event */
		$sql = $this->dtst_utils->dtst_slots_query($topic_id);
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			/* Increment the user count */
			$data[$row['dtst_status']]['user_count']++;

			/* Check if current user was already in the list */
			if ($this->user->data['user_id'] == $row['user_id'])
			{
				$user_status = (int) $row['dtst_status'];
			}

			/* Add this row to the users array, indexed by the cleaned username */
			$users[$row['username_clean']] = $row;
		}

		/* Free the database results */
		$this->db->sql_freeresult($result);

		/* Check if this user is the host of the event and grab the topic title */
		$sql = 'SELECT topic_poster as host, topic_title FROM ' . TOPICS_TABLE . ' WHERE topic_id = ' . (int) $topic_id;
		$result = $this->db->sql_query($sql);
		$topic = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$user_host = (int) $topic['host'];
		$s_user_host = (int) $this->user->data['user_id'] === (int) $user_host;
		$topic_title = $topic['topic_title'];

		if (confirm_box(true))
		{
			/* Request the reason for performing this action */
			$dtst_reason = $this->request->variable('dtst_reason', '', true);

			/* Without Emojis please! */
			$dtst_reason = $this->dtst_utils->dtst_strip_emojis($dtst_reason);

			if (($dtst_reason_length = utf8_strlen($dtst_reason)) > 255)
			{
				$json_response->send(array(
					'MESSAGE_TITLE'		=> $this->lang->lang('ERROR'),
					'MESSAGE_TEXT'		=> $this->lang->lang('DTST_REASON_TOO_LONG', $dtst_reason_length),
				));
			}

			/* If applicant is not the Host and no reason is given then throw an error message */
			if (empty($dtst_reason) && !$s_user_host)
			{
				$json_response->send(array(
					'MESSAGE_TITLE'		=> $this->lang->lang('ERROR'),
					'MESSAGE_TEXT'		=> $this->lang->lang('DTST_REASON_MISSING', $dtst_reason),
				));
			}

			/* If the user was somehow already connected to this event */
			if (!empty($user_status))
			{
				/* Get the new status for this user */
				$new_status = (int) $data[$user_status]['new_status'];

				/**
				 * If the new status is 'pending' and the user is the host,
				 * we skip the 'application' process and accept him straight away
				 */
				$new_status = $s_user_host && ($new_status === ext::DTST_STATUS_PENDING) ? ext::DTST_STATUS_ACCEPTED : $new_status;

				$user_to_update = array(
					'dtst_time'			=> (int) time(),
					'dtst_status'		=> (int) $new_status,
					'dtst_reason'		=> $dtst_reason,
					'dtst_host_reason'	=> '',
				);

				/* If applicant is the Host */
				if ($s_user_host)
				{
					$user_to_update['dtst_host_time'] = (int) time();
				}

				/* Update the slots table with the new status, time and reason */
				$sql = 'UPDATE ' . $this->dtst_slots . '
						SET ' . $this->db->sql_build_array('UPDATE', $user_to_update) . '
						WHERE user_id = ' . (int) $this->user->data['user_id'] . '
							AND topic_id = ' . (int) $topic_id;
				$this->db->sql_query($sql);

				/* If we are withdrawing from the event, update the reputation points */
				if ($new_status == ext::DTST_STATUS_WITHDRAWN)
				{
					/* Deduct reputation points from the user */
					$sql = 'UPDATE ' . USERS_TABLE . ' SET dtst_reputation = dtst_reputation + ' . (int) $this->config['dtst_rep_points_withdraw'] . ' WHERE user_id = ' . (int) $this->user->data['user_id'];
					$this->db->sql_query($sql);

					/* Insert the reputation into the reputation table */
					$sql = 'INSERT INTO ' . $this->dtst_reputation . ' ' . $this->db->sql_build_array('INSERT', array(
							'topic_id' 				=> (int) $topic_id,
							'user_id' 				=> $this->config['dtst_use_bot'] ? (int) $this->config['dtst_bot'] : 0,
							'recipient_id' 			=> (int) $this->user->data['user_id'],
							'reputation_action' 	=> ext::DTST_REP_WITHDREW,
							'reputation_points' 	=> $this->config['dtst_rep_points_withdraw'],
							'reputation_reason' 	=> '',
							'reputation_time' 		=> time(),
						));
					$this->db->sql_query($sql);
				}
			}
			else
			{
				/* Get the new status for this user */
				$new_status = $s_user_host ? ext::DTST_STATUS_ACCEPTED : ext::DTST_STATUS_PENDING;

				/* Set up SQL data for this user */
				$sql_data = array(
					'user_id'			=> (int) $this->user->data['user_id'],
					'topic_id'			=> $topic_id,
					'dtst_status'		=> $new_status,
					'dtst_time'			=> time(),
					'dtst_reason'		=> $dtst_reason,
					'dtst_host_time'	=> $s_user_host ? time() : 0,
				);

				/* Insert into the slots table */
				$sql = 'INSERT INTO ' . $this->dtst_slots . ' ' . $this->db->sql_build_array('INSERT', $sql_data);
				$this->db->sql_query($sql);
			}

			/* If applicant is not the Host */
			if (!$s_user_host)
			{
				/* Send out a private message */
				$this->dtst_utils->send_pm($data[$new_status]['pm_id'], $user_host, $topic_id, $topic_title);

				/* Add it to the log - Without Emojis */
				$this->log->add('user', $this->user->data['user_id'], $this->user->ip, $data[$new_status]['log_action'], time(), array('reportee_id' => $this->user->data['user_id'], $this->dtst_utils->dtst_strip_emojis($topic_title)));
				$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, $data[$new_status]['log_pm'], time(), array('topic_id' => $topic_id, $this->dtst_utils->dtst_strip_emojis($topic_title)));
			}

			/* Send JSON response */
			$json_response->send(array(
				'MESSAGE_TITLE'		=> $this->lang->lang('INFORMATION'),
				'MESSAGE_TEXT'		=> $this->lang->lang($data[$user_status]['message']),

				'DTST_DATA'			=> $this->build_dtst_data($data, $users, $user_status, $new_status),

				'DTST_BUTTON_TEXT'	=> $data[$new_status]['button_text'],
				'DTST_USER_STATUS'	=> $data[$new_status]['user_status'],

				'S_DTST_MANAGE'		=> false,
			));
		}
		else
		{
			// No reason required when this user is the host
			$this->template->assign_var('S_DTST_NO_REASON', $s_user_host);

			/* Set up a confirm box */
			confirm_box(false, $this->lang->lang($data[$user_status]['message'] . '_CONFIRM'), build_hidden_fields(array(
				'topic_id'		=> $topic_id,
			)), '@phpbbstudio_dtst/dtst_confirm_body.html', $this->helper->get_current_url());
		}
	}

	/**
	 * Handles the cancellation of an event by the host.
	 *
	 * @return mixed
	 * @access public
	 */
	public function cancel()
	{
		/* Request the topic and forum identifiers */
		$forum_id	= (int) $this->request->variable('f', 0);
		$topic_id	= (int) $this->request->variable('t', 0);

		/* Add our language file only when needed */
		$this->lang->add_lang('common', 'phpbbstudio/dtst');

		/* Check if this user is the host of the event and grab the topic title */
		$sql = 'SELECT topic_poster as host, topic_title FROM ' . TOPICS_TABLE . ' WHERE topic_id = ' . (int) $topic_id;
		$result = $this->db->sql_query($sql);
		$topic = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		$user_host = (int) $topic['host'];
		$s_user_host = (int) $this->user->data['user_id'] === (int) $user_host;
		$topic_title = $topic['topic_title'];

		/* If the user is not the host */
		if (!$s_user_host)
		{
			throw new \phpbb\exception\http_exception(403, 'DTST_NOT_AUTHORISED');
		}

		/* First check if the user is even authenticated */
		if (!$this->dtst_utils->is_authed())
		{
			throw new \phpbb\exception\http_exception(403, 'DTST_NOT_AUTHORISED');
		}

		/* Check if a topic was found */
		if (!$topic_id)
		{
			throw new \phpbb\exception\http_exception(404, 'DTST_TOPIC_NOT_FOUND');
		}

		/* Set up the topic url and return message for this case */
		$topic_url = append_sid("{$this->root_path}viewtopic.{$this->php_ext}", 'f=' . $forum_id . '&amp;t=' . $topic_id);
		$topic_return = '&laquo; ' . $this->lang->lang('RETURN_TOPIC', '<a href="' . $topic_url . '">', '</a>');

		/* Add a topic prefix like "canceled" as per request */
		$event_canceled_title = $this->lang->lang('DTST_TOPIC_PREFIX_CANCELED') . $topic_title;

		if (confirm_box(true))
		{
			/* Query the DTST Slots table */
			$sql = $this->dtst_utils->dtst_slots_query($topic_id);
			$result = $this->db->sql_query($sql);

			/* Iterate over the users involved with this event */
			while ($row = $this->db->sql_fetchrow($result))
			{
				/* Check for all those attending but the Host */
				if ( ($row['dtst_status'] == ext::DTST_STATUS_ACCEPTED) && ((int) $row['user_id'] !== (int) $this->user->data['user_id']) )
				{
					/* Increment our notifications sent counter */
					$this->config->increment('dtst_notification_id', 1);

					/* Send out notifications */
					$this->notification_manager->add_notifications('phpbbstudio.dtst.notification.type.opting', array(
						'action'				=> 'canceled',
						'topic_id'				=> (int) $topic_id,
						'forum_id'				=> (int) $forum_id,
						'author_id'				=> (int) $row['user_id'],
						'actionee_id'			=> (int) $this->user->data['user_id'],
						'notification_id'		=> (int) $this->config['dtst_notification_id'],
					));
				}
			}
			$this->db->sql_freeresult($result);

			/* For the sake of the posterity */
			$this->db->sql_transaction('begin');

			$sql = 'UPDATE ' . TOPICS_TABLE . '
					SET ' . $this->db->sql_build_array('UPDATE', array(
						'topic_title'			=> $event_canceled_title,
						'topic_status'			=> ITEM_LOCKED,
						'dtst_event_canceled'	=> ITEM_LOCKED,
					)) . '
					WHERE topic_id = ' . (int) $topic_id;
			$this->db->sql_query($sql);

			$sql = 'UPDATE ' . FORUMS_TABLE . '
					SET ' . $this->db->sql_build_array('UPDATE', array(
						'forum_last_post_subject'		=> $event_canceled_title,
					)) . '
					WHERE forum_id = ' . (int) $forum_id;
			$this->db->sql_query($sql);

			$sql = 'UPDATE ' . $this->dtst_slots . '
					SET ' . $this->db->sql_build_array('UPDATE', array(
						'dtst_status'			=> ext::DTST_STATUS_DENIED,
					)) . '
					WHERE dtst_status != ' . ext::DTST_STATUS_DENIED . '
						AND topic_id = ' . (int) $topic_id;
			$this->db->sql_query($sql);

			$this->db->sql_transaction('commit');

			/* Add it to the log - Without Emojis */
			$this->log->add('user', $this->user->data['user_id'], $this->user->ip, $this->lang->lang('LOG_DTST_EVENT_CANCELED'), time(), array('reportee_id' => $this->user->data['user_id'], $this->dtst_utils->dtst_strip_emojis($topic_title)));
			$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, $this->lang->lang('LOG_DTST_EVENT_CANCELED'), time(), array('topic_id' => $topic_id, $this->dtst_utils->dtst_strip_emojis($topic_title)));

			/* Deduct reputation points from the host */
			$sql = 'UPDATE ' . USERS_TABLE . ' SET dtst_reputation = dtst_reputation + ' . (int) $this->config['dtst_rep_points_cancel_event'] . ' WHERE user_id = ' . (int) $this->user->data['user_id'];
			$this->db->sql_query($sql);

			/* Insert the reputation into the reputation table */
			$sql = 'INSERT INTO ' . $this->dtst_reputation . ' ' . $this->db->sql_build_array('INSERT', array(
				'topic_id' 				=> (int) $topic_id,
				'user_id' 				=> $this->config['dtst_use_bot'] ? (int) $this->config['dtst_bot'] : 0,
				'recipient_id' 			=> (int) $this->user->data['user_id'],
				'reputation_action' 	=> ext::DTST_REP_CANCELED,
				'reputation_points' 	=> $this->config['dtst_rep_points_cancel_event'],
				'reputation_reason' 	=> '',
				'reputation_time' 		=> time(),
				));
			$this->db->sql_query($sql);

			/* If the request is AJAX */
			if ($this->request->is_ajax())
			{
				$json_response = new \phpbb\json_response;
				$json_response->send(array(
					'MESSAGE_TITLE' => $this->lang->lang('INFORMATION'),
					'MESSAGE_TEXT'  => $this->lang->lang('DTST_EVENT_CANCEL_SUCCESS'),
					'REFRESH_DATA'  => array(
						'url'  => '',
						'time' => 3,
					),
				));
			}

			/**
			 * If the request is not AJAX
			 * Show success message and refresh the page
			 */
			meta_refresh(3, $topic_url);
			return $this->helper->message($this->lang->lang('DTST_EVENT_CANCEL_SUCCESS') . '<br><br>' . $topic_return);
		}
		else
		{
			confirm_box(false, 'DTST_EVENT_CANCEL', build_hidden_fields(array(
				'topic_id'	=> $topic_id,
				'forum_id'	=> $forum_id,
			)), 'confirm_body.html', $this->helper->get_current_url());

			return redirect($topic_url);
		}
	}

	/**
	 * Handles actions (accept|deny|remove) performed by a host for an event.
	 *
	 * @return mixed
	 * @access public
	 */
	public function manage()
	{
		/* Request the topic and forum identifiers */
		$forum_id = (int) $this->request->variable('f', 0);
		$topic_id = (int) $this->request->variable('t', 0);
		/* Request the form actions */
		$submit = $this->request->is_set_post('confirm');
		$cancel = $this->request->is_Set_post('cancel');

		/* Get information about this event */
		$sql = 'SELECT topic_poster as host, dtst_participants as participants_limit, dtst_participants_unl as participants_unlimited, dtst_event_ended
				FROM ' . TOPICS_TABLE . '
				WHERE topic_id = ' . (int) $topic_id;
		$result = $this->db->sql_query_limit($sql, 1);
		$topic_data = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		/* Set information about this event */
		$s_user_host				= (int) $this->user->data['user_id'] === (int) $topic_data['host'];
		$participants_limit			= (int) $topic_data['participants_limit'];
		$participants_unlimited 	= (bool) $topic_data['participants_unlimited'];
		$participants_limit_reached = false;

		/* Add our language file only when needed */
		$this->lang->add_lang('common', 'phpbbstudio/dtst');

		/* If the user is not the host */
		if (!$s_user_host)
		{
			throw new \phpbb\exception\http_exception(403, 'DTST_NOT_AUTHORISED');
		}

		/* First check if the user is even authenticated */
		if (!$this->dtst_utils->is_authed())
		{
			throw new \phpbb\exception\http_exception(403, 'DTST_NOT_AUTHORISED');
		}

		/* Check if a topic was found */
		if (!$topic_id)
		{
			throw new \phpbb\exception\http_exception(404, 'DTST_TOPIC_NOT_FOUND');
		}

		if ($topic_data['dtst_event_ended'])
		{
			if ($this->request->is_ajax())
			{
				$json_response = new \phpbb\json_response;
				$json_response->send(array(
					'MESSAGE_TITLE'		=> $this->lang->lang('ERROR'),
					'MESSAGE_TEXT'		=> $this->lang->lang( 'DTST_EVENT_ENDED'),
				));
			}

			throw new \phpbb\exception\http_exception(401, 'DTST_REP_EVENT_NOT_ENDED');
		}

		/* Set up the topic url and return message for Ajax */
		$topic_url = append_sid("viewtopic.{$this->php_ext}", 'f=' . $forum_id . '&t=' . $topic_id);
		$topic_return = '&laquo; ' . $this->lang->lang('RETURN_TOPIC', '<a href="' . $topic_url . '">', '</a>');

		/* Set up the topic url and return message for no-Ajax */
		$topic_url_full_page = append_sid("{$this->root_path}viewtopic.{$this->php_ext}", 'f=' . $forum_id . '&amp;t=' . $topic_id);
		$topic_return_full_page = '&laquo; ' . $this->lang->lang('RETURN_TOPIC', '<a href="' . $topic_url_full_page . '">', '</a>');

		/* Assign the template file we are working with */
		$this->template->set_filenames(array(
			'body' => '@phpbbstudio_dtst/dtst_manage.html',
		));

		/* Assign a form token to the form */
		add_form_key('dtst_manage');

		/* Set up default variables */
		$users = $errors = $dtst_user_data = array();
		$s_user_accepted = false;

		/* Grab the DTST data */
		$data = $this->get_dtst_data();
		/* Unset the 'not attending' part of the data, as that is not applicable when managing users */
		unset($data[0]);

		/* Query the DTST Slots table */
		$sql = $this->dtst_utils->dtst_slots_query($topic_id);
		$result = $this->db->sql_query($sql);

		/* Iterate over the users involved with this event */
		while ($row = $this->db->sql_fetchrow($result))
		{
			/* Increment the user count */
			$data[$row['dtst_status']]['user_count']++;

			/* Assign the variables to the correct block */
			$this->template->assign_block_vars($data[$row['dtst_status']]['template_block'], array(
				'USER_ID'			=> $row['user_id'],
				'USERNAME'			=> $this->auth->acl_get('u_viewprofile') ? get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']) : get_username_string('no_profile', $row['user_id'], $row['username'], $row['user_colour']),
				'DTST_TIME'			=> $this->user->format_date((int) $row['dtst_time'], $this->config['default_dateformat']),
				'DTST_REASON'		=> $row['dtst_reason'],
				'DTST_HOST_TIME'	=> $this->user->format_date((int) $row['dtst_host_time'], $this->config['default_dateformat']),
				'DTST_HOST_REASON'	=> $row['dtst_host_reason'],

				'S_DTST_HOST'		=> (bool) ($s_user_host && ($this->user->data['user_id'] == $row['user_id']))
			));

			/* Add this row to the users array */
			$users[$row['user_id']] = $row;
		}
		$this->db->sql_freeresult($result);

		/* Check if the participants limit has not been exceeded to begin with */
		if (!$participants_unlimited && ($data[ext::DTST_STATUS_ACCEPTED]['user_count'] > $participants_limit))
		{
			$participants_limit_reached = true;
		}

		/* If the form was canceled, redirect to the topic. Only applied to the not-AJAX page */
		if ($cancel)
		{
			redirect($topic_url);
		}

		/* If the form was submitted */
		if ($submit)
		{
			/* Check the form key */
			$check_form_key = check_form_key('dtst_manage');

			/* Set up an empty array with users we need to update */
			$users_to_update = array();

			/* Grab the form data of all users involved with this event */
			$dtst_user_data = $this->request->variable('dtst_user_data', array(0 => array('' => '')));

			/* Grab the topic title */
			$sql = 'SELECT topic_title FROM ' . TOPICS_TABLE . ' WHERE topic_id = ' . (int) $topic_id;
			$result = $this->db->sql_query($sql);
			$topic_title = $this->db->sql_fetchfield('topic_title');
			$this->db->sql_freeresult($result);

			/* Iterate over all the users involved with this event */
			foreach ($dtst_user_data as $user_id => $user_data)
			{
				/**
				 * There are two 'if'-statements in here:
				 * 1 - If the user is pending, check if the user status has changed
				 * 2 - If the user is accepted, check if the 'remove' user has been checked
				 */
				if (
					(isset($user_data['dtst_action']) && ((int) $user_data['dtst_action'] !== $users[$user_id]['dtst_status'])) ||
					(isset($user_data['dtst_remove']) && $user_data['dtst_remove'])
				)
				{
					/* Check the reason for its length */
					if (($reason_length = utf8_strlen($user_data['dtst_action_reason'])) > 255)
					{
						/* Add it ot the error array indexed by the user id */
						$errors[$user_id] = true;
					}

					/* Grab the old and new status for this user */
					$new_status = isset($user_data['dtst_remove']) ? ext::DTST_STATUS_DENIED : (int) $user_data['dtst_action'];
					$old_status = $users[$user_id]['dtst_status'];

					/* Update the user counts */
					$data[$new_status]['user_count']++;
					$data[$old_status]['user_count']--;

					/* Set up the user array with feels that need to be updated in the database */
					$user_array = array(
						'dtst_status'		=> $new_status,
						'dtst_reason'		=> $user_data['dtst_reason'],
						'dtst_host_time'	=> (int) time(),
						'dtst_host_reason'	=> $user_data['dtst_action_reason'],
					);

					/* Add the user to the to-be-updated list */
					$users_to_update[$user_id] = $user_array;

					/* Update the users array */
					$users[$user_id] = array_merge($users[$user_id], $user_array);
				}
			}

			/* Check if the participants limit has not been exceeded after changes */
			if (!$participants_unlimited && ($data[ext::DTST_STATUS_ACCEPTED]['user_count'] > $participants_limit))
			{
				$participants_limit_reached = true;
			}

			/* If there are no errors, the form is valid and the limit has not been reached. */
			if (empty($errors) && $check_form_key && !$participants_limit_reached)
			{
				/* Update the DTST Slots table */
				foreach ($users_to_update as $user_id => $user_to_update)
				{
					/* Update the slots table with the new status, time and reason */
					$sql = 'UPDATE ' . $this->dtst_slots . '
							SET ' . $this->db->sql_build_array('UPDATE', $user_to_update) . '
							WHERE user_id = ' . (int) $user_id . '
								AND topic_id = ' . (int) $topic_id;
					$this->db->sql_query($sql);

					/* If the user was accepted */
					if ($user_to_update['dtst_status'] === ext::DTST_STATUS_ACCEPTED)
					{
						$action = 'accepted';

						/**
						 * Send him/her a notification
						 */
						/* Increment our notifications sent counter */
						$this->config->increment('dtst_notification_id', 1);
						/* Send out notification */
						$this->notification_manager->add_notifications('phpbbstudio.dtst.notification.type.opting', array(
							'action'				=> $action,
							'topic_id'				=> (int) $topic_id,
							'forum_id'				=> (int) $forum_id,
							'author_id'				=> (int) $user_id,
							'actionee_id'			=> (int) $this->user->data['user_id'],
							'notification_id'		=> (int) $this->config['dtst_notification_id'],
						));

						/* Add it to the log - Without Emojis */
						$this->log->add('user', $this->user->data['user_id'], $this->user->ip, $this->lang->lang('LOG_DTST_OPT_ACCEPTED'), time(), array('reportee_id' => $this->user->data['user_id'], $this->dtst_utils->dtst_strip_emojis($topic_title)));
						$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, $this->lang->lang('LOG_DTST_OPT_ACCEPTED'), time(), array('topic_id' => $topic_id, $this->dtst_utils->dtst_strip_emojis($topic_title)));

						/* Post a reply */
						$last_post_url = $this->dtst_utils->dtst_post_reply('reason', (int) $forum_id, (int) $topic_id, (int) $user_id, $user_to_update['dtst_reason']);

						/* Post a reply succeed - Let's help the submit_post() thingy do the missing work */
						if ($last_post_url)
						{
							/* Are we using the PMs Bot? */
							$dtst_user_id = ((bool) $this->config['dtst_use_bot']) ? (int) $this->config['dtst_bot'] : (int) $user_id; // (int) $s_user_host ?

							/* Call the main SQL query */
							$row_user = $this->dtst_utils->dtst_sql_users($dtst_user_id);
							$topic_last_poster_name = $row_user['username'];
							$topic_last_post_colour = $row_user['user_colour'];
							$topic_last_post_id = $row_user['user_id'];

							/* For the sake of the last post function */
							$this->db->sql_transaction('begin');

							$sql = 'UPDATE ' . TOPICS_TABLE . '
									SET ' . $this->db->sql_build_array('UPDATE', array(
										'topic_last_poster_name'		=> $topic_last_poster_name,
										'topic_last_poster_colour'		=> $topic_last_post_colour,
									)) . '
									WHERE topic_id = ' . (int) $topic_id;
							$this->db->sql_query($sql);

							$sql = 'UPDATE ' . FORUMS_TABLE . '
									SET ' . $this->db->sql_build_array('UPDATE', array(
										'forum_last_poster_id'			=> $topic_last_post_id,
										'forum_last_poster_name'		=> $topic_last_poster_name,
										'forum_last_poster_colour'		=> $topic_last_post_colour,
									)) . '
									WHERE forum_id = ' . (int) $forum_id;
							$this->db->sql_query($sql);

							$this->db->sql_transaction('commit');

							/* Grab the id of the first reply */
							if (!$s_user_accepted)
							{
								$last_post_params = $this->path_helper->get_url_parts($last_post_url);
								$topic_url = $topic_url . '&dtst_p=' . (int) $last_post_params['params']['p'];
							}

							/* Define that we have accepted at least one user */
							$s_user_accepted = true;
						}
					}

					/* If the user was denied */
					else if ($user_to_update['dtst_status'] === ext::DTST_STATUS_DENIED)
					{
						$action = 'denied';

						/**
						 * Send him/her a notification
						 */
						/* Increment our notifications sent counter */
						$this->config->increment('dtst_notification_id', 1);

						/* Send out notification */
						$this->notification_manager->add_notifications('phpbbstudio.dtst.notification.type.opting', array(
							'action'				=> $action,
							'topic_id'				=> (int) $topic_id,
							'forum_id'				=> (int) $forum_id,
							'author_id'				=> (int) $user_id,
							'actionee_id'			=> (int) $this->user->data['user_id'],
							'notification_id'		=> (int) $this->config['dtst_notification_id'],
						));

						/* Add it to the log - Without Emojis */
						$this->log->add('user', $this->user->data['user_id'], $this->user->ip, $this->lang->lang('LOG_DTST_OPT_DENIED'), time(), array('reportee_id' => $this->user->data['user_id'], $this->dtst_utils->dtst_strip_emojis($topic_title)));
						$this->log->add('mod', $this->user->data['user_id'], $this->user->ip, $this->lang->lang('LOG_DTST_OPT_DENIED'), time(), array('topic_id' => $topic_id, $this->dtst_utils->dtst_strip_emojis($topic_title)));
					}
				}

				/* If the request is AJAX */
				if ($this->request->is_ajax())
				{
					/* Set up the JSON response */
					$json_array = array(
						'MESSAGE_TITLE' => $this->lang->lang('INFORMATION'),
						'MESSAGE_TEXT'  => $this->lang->lang('DTST_ATTENDEES_MANAGE_SUCCESS'),

						'DTST_DATA' 	=> $this->build_dtst_list($data, $users),

						'S_DTST_MANAGE' => true,
					);

					/* Only refresh when a user was accepted, to show the reply. Otherwise everything is done through jQuery */
					if ($s_user_accepted)
					{
						$json_array['REFRESH_DATA'] = array(
							'url'	=> $topic_url,
							'time'	=> 3,
						);
					}

					/* Send the JSON response */
					$json_response = new \phpbb\json_response;
					$json_response->send($json_array);
				}

				/**
				 * If the request is not AJAX
				 * Show success message and refresh the page to the last post
				 */
				meta_refresh(3, $topic_url);
				return $this->helper->message($this->lang->lang('DTST_ATTENDEES_MANAGE_SUCCESS') . '<br><br>' . $topic_return_full_page);
			}
		}

		/* Assign template variables */
		$this->template->assign_vars(array(
			'DTST_DATA'		=> $data,
			'DTST_ERRORS'	=> $errors,
			'DTST_USERS'	=> $dtst_user_data,

			'DTST_FORUM_ID'	=> $forum_id,
			'DTST_TOPIC_ID'	=> $topic_id,

			'DTST_STATUS_ACCEPTED'		=> ext::DTST_STATUS_ACCEPTED,
			'DTST_STATUS_CANCELED'		=> ext::DTST_STATUS_CANCELED,
			'DTST_STATUS_DENIED'		=> ext::DTST_STATUS_DENIED,
			'DTST_STATUS_PENDING'		=> ext::DTST_STATUS_PENDING,
			'DTST_STATUS_WITHDRAWN'		=> ext::DTST_STATUS_WITHDRAWN,

			'S_DTST_AJAX'				=> $this->request->is_ajax(),
			'S_DTST_FORM_INVALID'		=> isset($check_form_key) ? !$check_form_key : false,
			'S_DTST_LIMIT_REACHED'		=> $participants_limit_reached,

			'S_CONFIRM_ACTION' 			=> $this->helper->get_current_url(),

			'U_DTST_MANAGE'				=> $this->helper->route('dtst_manager', array('f' => $forum_id, 't' => $topic_id)),
			'U_DTST_RETURN'				=> $topic_return,
			'U_DTST_RETURN_FULL_PAGE'	=> $topic_return_full_page,
		));

		/* If the request is AJAX */
		if ($this->request->is_ajax())
		{
			/* Send a JSON response */
			$json_response = new \phpbb\json_response;
			$json_response->send(array(
				// Assign the template file we are using
				'MESSAGE_BODY'		=> $this->template->assign_display('body'),

				// This HAS to be set!
				'S_CONFIRM_ACTION' 	=> $this->helper->get_current_url(),
			));
		}

		/* If the request is not AJAX */
		return $this->helper->render('@phpbbstudio_dtst/dtst_manage.html', $this->lang->lang('DTST_ATTENDEES_MANAGE_FULL'));
	}

	/**
	 * Basic information about event statuses.
	 * Possible statuses:
	 * 		- 0: Not attending
	 * 		- 2: Accepted			(This is out of numeric order, for the sake of iteration. Accepted comes before the rest)
	 * 		- 1: Pending
	 * 		- 3: Denied
	 * 		- 4: Withdrawn
	 * 		- 5: Canceled
	 *
	 * @return array
	 * @access private
	 */
	private function get_dtst_data()
	{
		return array(
			0							=> array(
				'message'		=> 'DTST_USER_STATUS_MSG_APPLIED',
			),
			ext::DTST_STATUS_ACCEPTED	=> array(
				'log_action'		=> '',
				'log_pm'			=> '',
				'message'			=> 'DTST_USER_STATUS_MSG_WITHDRAWN',
				'button_text'		=> $this->lang->lang('DTST_BUTTON_TEXT_WITHDRAW'),
				'user_status'		=> $this->lang->lang('DTST_USER_STATUS_ACCEPTED'),
				'user_none'			=> $this->lang->lang('DTST_NO_ACCEPTED'),
				'user_list'			=> '',
				'user_count'		=> 0,
				'new_status'		=> ext::DTST_STATUS_WITHDRAWN,
				'template_block'	=> 'dtst_attendees',
				'template_icon'		=> 'fa-check light-green',
				'pm_id'				=> 0,
			),
			ext::DTST_STATUS_PENDING 	=> array(
				'log_action'		=> 'ACP_DTST_LOG_APPLIED',
				'log_pm'			=> 'ACP_DTST_LOG_PM_APPLIED',
				'message'			=> 'DTST_USER_STATUS_MSG_CANCELED',
				'button_text'		=> $this->lang->lang('CANCEL'),
				'user_status'		=> $this->lang->lang('DTST_USER_STATUS_PENDING'),
				'user_none'			=> $this->lang->lang('DTST_NO_APPLICATIONS'),
				'user_list'			=> '',
				'user_count'		=> 0,
				'new_status'		=> ext::DTST_STATUS_CANCELED,
				'template_block'	=> 'dtst_pending',
				'template_icon'		=> 'fa-question orange',
				'pm_id'				=> ext::DTST_STATUS_PM_APPLY,
			),
			ext::DTST_STATUS_DENIED		=> array(
				'log_action'		=> '',
				'log_pm'			=> '',
				'message'			=> 'DTST_USER_STATUS_MSG_REAPPLIED',
				'button_text'		=> $this->lang->lang('DTST_BUTTON_TEXT_REAPPLY'),
				'user_status'		=> $this->lang->lang('DTST_USER_STATUS_DENIED'),
				'user_none'			=> $this->lang->lang('DTST_NO_DENIALS'),
				'user_list'			=> '',
				'user_count'		=> 0,
				'new_status'		=> ext::DTST_STATUS_PENDING,
				'template_block'	=> 'dtst_denials',
				'template_icon'		=> 'fa-times red',
				'pm_id'				=> 0,
			),
			ext::DTST_STATUS_WITHDRAWN	=> array(
				'log_action'		=> 'ACP_DTST_LOG_WITHDRAWN',
				'log_pm'			=> 'ACP_DTST_LOG_PM_WITHDRAWN',
				'message'			=> 'DTST_USER_STATUS_MSG_REAPPLIED',
				'button_text'		=> $this->lang->lang('DTST_BUTTON_TEXT_REAPPLY'),
				'user_status'		=> $this->lang->lang('DTST_USER_STATUS_WITHDRAWN'),
				'user_none'			=> $this->lang->lang('DTST_NO_WITHDRAWALS'),
				'user_list'			=> '',
				'user_count'		=> 0,
				'new_status'		=> ext::DTST_STATUS_PENDING,
				'template_block'	=> 'dtst_withdrawals',
				'template_icon'		=> 'fa-hand-o-left red',
				'pm_id'				=> ext::DTST_STATUS_PM_WITHDRAWAL,
			),
			ext::DTST_STATUS_CANCELED	=> array(
				'log_action'		=> 'ACP_DTST_LOG_CANCELED',
				'log_pm'			=> 'ACP_DTST_LOG_PM_CANCELED',
				'message'			=> 'DTST_USER_STATUS_MSG_REAPPLIED',
				'button_text'		=> $this->lang->lang('DTST_BUTTON_TEXT_REAPPLY'),
				'user_status'		=> $this->lang->lang('DTST_USER_STATUS_CANCELED'),
				'user_none'			=> $this->lang->lang('DTST_NO_CANCELLATIONS'),
				'user_list'			=> '',
				'user_count'		=> 0,
				'new_status'		=> ext::DTST_STATUS_PENDING,
				'template_block'	=> 'dtst_canceled',
				'template_icon'		=> 'fa-hand-o-left red',
				'pm_id'				=> ext::DTST_STATUS_PM_CANCEL,
			),
		);
	}

	/**
	 * Build new dtst data after an status action has been performed.
	 *
	 * @param  array	$data 			The current dtst data
	 * @param  array	$users			The users that are attending the event
	 * @param  int		$old_status		The old status for this user
	 * @param  int		$new_status		The new status for this user
	 * @return array	$data			The new dtst data
	 * @access private
	 */
	private function build_dtst_data($data, $users, $old_status, $new_status)
	{
		/* Unset the 0 key from the data, as there is no 'Not attending'-list */
		unset($data[0]);

		/* First fix the user counts */
		$data[$new_status]['user_count']++;

		if (!empty($old_status))
		{
			/* Only fix the old count, if the old user status is not 0 */
			$data[$old_status]['user_count']--;

			/* Update the user status and time */
			$users[$this->user->data['username_clean']]['dtst_status'] = $new_status;
			$users[$this->user->data['username_clean']]['dtst_time'] = time();
		}
		else
		{
			/* The user was 'Not attending', lets add him (or her) to the list. */
			$users[$this->user->data['username_clean']] = array(
				'user_id'		=> $this->user->data['user_id'],
				'username'		=> $this->user->data['username'],
				'user_colour'	=> $this->user->data['user_colour'],
				'dtst_status'	=> $new_status,
				'dtst_time'		=> time(),
			);

			/* Resort the users list per keys (cleaned username) */
			ksort($users, SORT_STRING);
		}

		/* Build user lists */
		$data = $this->build_dtst_list($data, $users);

		return $data;
	}

	/**
	 * Builds a list of users based on their status.
	 * The user status is added as a <span title=""> to the usernames.
	 *
	 * @param  array	$data		The current DTST data with the outdated user lists
	 * @param  array	$users		The users
	 * @return array	$data		The new DTST data with updated user lists
	 * @access private
	 */
	private function build_dtst_list($data, $users)
	{
		/* Iterate over the users and add them to the correct list */
		foreach ($users as $row)
		{
			/* Set the username for this user */
			$username = $this->auth->acl_get('u_viewprofile') ? get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']) : get_username_string('no_profile', $row['user_id'], $row['username'], $row['user_colour']);

			/* Set the user status for this user */
			$user_status = $data[$row['dtst_status']]['user_status'] . $this->lang->lang('COLON') . ' ' . $this->user->format_date((int) $row['dtst_time'], $this->config['default_dateformat']);

			$data[$row['dtst_status']]['user_list'] .= '<span title="' . $user_status . '">';
			$data[$row['dtst_status']]['user_list'] .= $username;
			$data[$row['dtst_status']]['user_list'] .= '</span>';
			$data[$row['dtst_status']]['user_list'] .= $this->lang->lang('COMMA_SEPARATOR');
		}

		/* Iterate over the lists and remove the last comma separator or provide empty values */
		foreach ($data as $status => $value)
		{
			$data[$status]['user_list'] = !empty($data[$status]['user_list']) ? rtrim($data[$status]['user_list'], $this->lang->lang('COMMA_SEPARATOR')) : $data[$status]['user_none'];
		}

		return $data;
	}
}
