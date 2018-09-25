<?php
/**
 *
 * Date Topic Event Calendar. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\dtst\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use phpbbstudio\dtst\ext;

/**
 * Date Topic Event Calendar Event listener.
 */
class main_listener implements EventSubscriberInterface
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\language\language */
	protected $lang;

	/** @var \phpbbstudio\dtst\core\event_cron */
	protected $dtst_cron;

	/** @var \phpbbstudio\dtst\core\operator */
	protected $dtst_utils;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string php File extension */
	protected $php_ext;

	protected $dtst_slots;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/**
	 * Constructor.
	 *
	 * @param  \phpbb\auth\auth						$auth		Authentication object
	 * @param  \phpbb\config\config					$config		Configuration object
	 * @param  \phpbb\db\driver\driver_interface	$db			Database object
	 * @param  \phpbb\request\request				$request	Request	object
	 * @param  \phpbb\template\template				$template	Template object
	 * @param  \phpbb\user							$user		User object
	 * @param  \phpbb\language\language				$lang		Language object
	 * @param  \phpbbstudio\dtst\core\event_cron	$dtst_cron	DTST Event cron
	 * @param  \phpbbstudio\dtst\core\operator		$dtst_utils	Functions to be	used by	Classes
	 * @param  string								$root_path	phpBB root path
	 * @param  string								$php_ext	php	File extension
	 * @param  string								$dtst_slots	The	DTST slots table
	 * @param  \phpbb\controller\helper				$helper		Helper object
	 * @return void
	 * @access public
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, \phpbb\language\language $lang, \phpbbstudio\dtst\core\event_cron $dtst_cron, \phpbbstudio\dtst\core\operator $dtst_utils, $root_path, $php_ext, $dtst_slots, \phpbb\controller\helper $helper)
	{
		$this->auth			= $auth;
		$this->config		= $config;
		$this->db			= $db;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;
		$this->lang			= $lang;
		$this->dtst_cron	= $dtst_cron;
		$this->dtst_utils	= $dtst_utils;
		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;
		$this->dtst_slots	= $dtst_slots;
		$this->helper		= $helper;
	}

	/**
	 * Assign functions defined in this class to event listeners in the core.
	 *
	 * @static
	 * @return array
	 * @access public
	 */
	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'							=> 'dtst_notification_language',
			'core.page_header_after'					=> 'dtst_template_switch',
			'core.page_header'							=> 'dtst_cron',
			'core.permissions'							=> 'dtst_add_permissions',
			'core.posting_modify_template_vars'			=> 'dtst_topic_data_topic',
			'core.posting_modify_submission_errors'		=> 'dtst_topic_add_to_post_data',
			'core.posting_modify_submit_post_before'	=> 'dtst_topic_add',
			'core.posting_modify_message_text'			=> 'dtst_modify_message_text',
			'core.submit_post_modify_sql_data'			=> array(array('dtst_submit_post_modify_sql_data'), array('dtst_modify_reply_data')),
			'core.submit_post_end'						=> 'dtst_modify_reply_poster',
			'core.viewtopic_modify_post_data'			=> 'dtst_viewtopic_modify_post_data',
			'core.viewtopic_modify_page_title'			=> 'dtst_topic_add_viewtopic',
			'core.viewforum_modify_topicrow'			=> 'dtst_modify_topicrow',
			'core.search_modify_tpl_ary'				=> 'dtst_search_modify_tpl_ary',
			'core.mcp_view_forum_modify_topicrow'		=> 'dtst_modify_topicrow',
			'core.viewforum_modify_page_title'			=> 'dtst_viewforum_filters',
			'core.viewforum_get_topic_ids_data'			=> 'dtst_viewforum_apply_filters',
		);
	}

	/**
	 * Load common language files during user setup.
	 *
	 * @param  \phpbb\event\data		$event		Event object
	 * @event  core.user_setup
	 * @return void
	 * @access public
	 */
	public function dtst_notification_language($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'phpbbstudio/dtst',
			'lang_set' => 'notifications_dtst',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	/**
	 * Template switches over all.
	 *
	 * @event  core.page_header_after
	 * @return void
	 * @access public
	 */
	public function dtst_template_switch()
	{
		/**
		 * Check perms first
		 */
		if ($this->dtst_utils->is_authed())
		{
			$this->dtst_utils->dtst_template_switches_over_all();
		}
	}

	/**
	 * Run the DTST cron.
	 *
	 * @event  core.page_header
	 * @return void
	 * @access public
	 */
	public function dtst_cron()
	{
		$this->dtst_cron->run();
	}

	/**
	 * Add permissions for DTST - Permission's language file is automatically loaded.
	 *
	 * @event  core.permissions
	 * @param  \phpbb\event\data		$event		The event object
	 * @return void
	 * @access public
	 */
	public function dtst_add_permissions($event)
	{
		/* Assigning them to local variables first */
		$permissions = $event['permissions'];
		$categories = $event['categories'];

		/* Setting up a new permissions's CAT for us */
		if ( !isset($categories['phpbb_studio']))
		{
			$categories['phpbb_studio']= 'ACL_CAT_PHPBB_STUDIO';
		}

		$permissions += [
			'u_allow_dtst' => [
				'lang'	=> 'ACL_U_ALLOW_DTST',
				'cat'	=> 'phpbb_studio',
			],
			'u_dtst_attendees' => [
				'lang'	=> 'ACL_U_ALLOW_ATTENDEES',
				'cat'	=> 'phpbb_studio',
			],
			'a_dtst_admin' => [
				'lang'	=> 'ACL_A_DTST_ADMIN',
				'cat'	=> 'phpbb_studio',
			],
			'm_dtst_mod' => [
				'lang'	=> 'ACL_M_DTST_MOD',
				'cat'	=> 'phpbb_studio',
			],
		];

		/* Merging our CAT to the native array of perms */
		$event['categories'] = array_merge($event['categories'], $categories);

		/* Copying back to event variable */
		$event['permissions'] = $permissions;
	}

	/**
	 * Modify the page's data before it is assigned to the template.
	 *
	 * @event  core.posting_modify_template_vars
	 * @param  \phpbb\event\data					$event		The event object
	 * @return void
	 * @access public
	 */
	public function dtst_topic_data_topic($event)
	{
		/* Check if Date Topic Event Calendar is enabled for this forum */
		if ( $this->dtst_utils->forum_dtst_enabled('forum_id', $event['forum_id']) )
		{
			$mode = $event['mode'];
			$post_data = $event['post_data'];
			$page_data = $event['page_data'];

			$post_data['dtst_location']		= (!empty($post_data['dtst_location'])) ? $post_data['dtst_location'] : '';
			$post_data['dtst_loc_custom']	= (!empty($post_data['dtst_loc_custom'])) ? $post_data['dtst_loc_custom'] : '';
			$post_data['dtst_host']			= (!empty($post_data['dtst_host'])) ? $post_data['dtst_host'] : $this->user->data['username'];
			$post_data['dtst_date']			= (!empty($post_data['dtst_date'])) ? $post_data['dtst_date'] : '';
			$post_data['dtst_event_type']	= (!empty($post_data['dtst_event_type'])) ? $post_data['dtst_event_type'] : '';
			$post_data['dtst_age_min']		= (!empty($post_data['dtst_age_min'])) ? $post_data['dtst_age_min'] : 0;
			$post_data['dtst_age_max']		= (!empty($post_data['dtst_age_max'])) ? $post_data['dtst_age_max'] : 0;
			$post_data['dtst_participants']	= (!empty($post_data['dtst_participants'])) ? $post_data['dtst_participants'] : 0;
			$post_data['dtst_event_ended']	= (!empty($post_data['dtst_event_ended'])) ? $post_data['dtst_event_ended'] : false;

			/* Check if we are posting or editing the very first post of the topic */
			if ( $mode == 'post' || ($mode == 'edit' && $post_data['topic_first_post_id'] == $post_data['post_id']) )
			{
				/* Add our language file only when needed */
				$this->lang->add_lang('common', 'phpbbstudio/dtst');

				/* Check what preset location and event type was selected. */
				$preset_location = $this->request->variable('dtst_location', $post_data['dtst_location'], true);
				$event_type = $this->request->variable('dtst_event_type', (int) $event['forum_id']);

				/* Get a preset location list for the select box */
				$preset_location_list = $this->dtst_utils->dtst_location_preset_select($preset_location);
				$event_type_list = $this->dtst_utils->dtst_event_type_select($event_type);

				/* Add our template vars */
				$page_data['DTST_LOCATION']			= $preset_location_list;
				$page_data['DTST_LOC_CUSTOM']		= $this->request->variable('dtst_loc_custom', $post_data['dtst_loc_custom'], true);
				$page_data['DTST_HOST']				= $this->request->variable('dtst_host', $post_data['dtst_host'], true);
				$page_data['DTST_DATE']				= $this->request->variable('dtst_date', $post_data['dtst_date'], true);
				$page_data['DTST_EVENT_TYPE']		= $event_type_list;
				$page_data['DTST_AGE_MIN']			= $this->request->variable('dtst_age_min', $post_data['dtst_age_min']);
				$page_data['DTST_AGE_MAX']			= $this->request->variable('dtst_age_max', $post_data['dtst_age_max']);
				$page_data['DTST_PARTICIPANTS']		= $this->request->variable('dtst_participants', $post_data['dtst_participants']);

				/* Add our placeholders */
				$page_data['DTST_LOC_CUSTOM_HOLDER']	= $this->lang->lang('DTST_LOC_CUSTOM_HOLDER');
				$page_data['DTST_HOST_HOLDER']			= $this->lang->lang('DTST_HOST_EXPLAIN');

				/* Template switches */
				$page_data['S_DTST_TOPIC'] = true;
				$page_data['S_DTST_TOPIC_PERMS'] = (bool) $this->dtst_utils->is_authed();
				$page_data['S_DTST_EVENT_ENDED'] = (bool) ($post_data['dtst_event_ended'] && ($mode !== 'post'));

				if ($mode == 'edit')
				{
					$page_data['S_DTST_TOPIC_EDIT'] = true;
				}
			}

			$event['page_data'] = $page_data;
		}
	}

	/**
	 * This event allows you to define errors before the post action is performed.
	 *
	 * @event  core.posting_modify_submission_errors
	 * @param  \phpbb\event\data						$event		The event object
	 * @return void
	 * @access public
	 */
	public function dtst_topic_add_to_post_data($event)
	{
		/* Check if Date Topic Event Calendar is enabled for this forum */
		if ( $this->dtst_utils->forum_dtst_enabled('forum_id', $event['forum_id']) )
		{
			$error = $event['error'];

			/* Add our errors language file only if needed */
			$this->lang->add_lang('common_errors', 'phpbbstudio/dtst');

			/* Request the new participants limit */
			$dtst_participants = (int) $this->request->variable('dtst_participants', 0);
			$dtst_participants_unlimited = $dtst_participants === 0;

			/* Request the date */
			$dtst_date = $this->request->variable('dtst_date', '', true);

			/* If we are editing a topic, we check the possible new participants limit */
			if ((bool) $this->dtst_utils->is_authed() && ($event['mode'] === 'edit') && ($event['post_data']['topic_first_post_id'] == $event['post_id']))
			{
				/* Grab the current amount of people accepted for this event */
				$sql = 'SELECT COUNT(user_id) as attendees
						FROM ' . $this->dtst_slots . '
						WHERE topic_id = ' . (int) $event['topic_id'] . '
							AND dtst_status = ' . (int) ext::DTST_STATUS_ACCEPTED;
				$result = $this->db->sql_query($sql);
				$current_attendees = $this->db->sql_fetchfield('attendees');
				$this->db->sql_freeresult($result);

				/* If the participants limit is not unlimited and the current attendees are more than the the (new) limit, we throw an error */
				if (!$dtst_participants_unlimited && ($current_attendees > $dtst_participants))
				{
					$error[] = $this->lang->lang('DTST_PARTICIPANTS_TOO_LOW', $dtst_participants, $current_attendees);
				}

				/* Also check if we are not adjusted the event date, after the event has already ended. */
				if ($event['post_data']['dtst_event_ended'])
				{
					/* Grab the old date for comparison */
					$sql = 'SELECT dtst_date FROM ' . TOPICS_TABLE . ' WHERE topic_id = ' . (int) $event['topic_id'];
					$result = $this->db->sql_query_limit($sql, 1);
					$old_date = $this->db->sql_fetchfield('dtst_date');
					$this->db->sql_freeresult($result);

					if ($dtst_date !== $old_date)
					{
						$error[] = $this->lang->lang('DTST_EVENT_ENDED_DATE', $old_date);
					}
				}
			}

			/* Check if the fields are all mandatory for this forum */
			if ( $this->dtst_utils->forum_dtst_forced_fields('dtst_f_forced_fields', $event['forum_id']) )
			{
				/* All fields are mandatory, we check that on submit */

				/* Only applies to authenticated users */
				if ( (bool) $this->dtst_utils->is_authed() && ($event['post_data']['topic_first_post_id'] == $event['post_id']))
				{
					if (!$event['post_data']['dtst_location'] && !$event['post_data']['dtst_loc_custom'])
					{
						$error[] = $this->lang->lang('DTST_LOCATION_MISSING');
					}

					if (utf8_strlen($event['post_data']['dtst_loc_custom']) >= 100)
					{
						$error[] = $this->lang->lang('DTST_LOC_CUSTOM_LONG');
					}

					if (!$event['post_data']['dtst_host'])
					{
						$error[] = $this->lang->lang('DTST_HOST_MISSING');
					}

					if (!$event['post_data']['dtst_date'])
					{
						$error[] = $this->lang->lang('DTST_DATE_MISSING');
					}

					if (!$event['post_data']['dtst_event_type'])
					{
						$error[] = $this->lang->lang('DTST_EVENT_TYPE_MISSING');
					}
				}
			}

			$event['error'] = $error;

			/**
			 * No errors? Let's party :-D
			 *
			 * Emojis will be stripped away.
			 */
			$event['post_data']	= array_merge($event['post_data'], array(
					'dtst_location'			=> $this->dtst_utils->dtst_strip_emojis($this->request->variable('dtst_location', '', true)),
					'dtst_loc_custom'		=> $this->dtst_utils->dtst_strip_emojis($this->request->variable('dtst_loc_custom', '', true)),
					'dtst_host'				=> $this->dtst_utils->dtst_strip_emojis($this->request->variable('dtst_host', '', true)),
					'dtst_date'				=> $dtst_date,
					/* Here we are storing the timestamp exactly the as per the time of submission */
					'dtst_date_unix'		=> (!empty($dtst_date)) ? $this->user->get_timestamp_from_format('d-m-Y', $dtst_date, new \DateTimeZone($this->config['board_timezone'])) : false,
					'dtst_event_type'		=> $this->request->variable('dtst_event_type', 0),
					'dtst_age_min'			=> $this->request->variable('dtst_age_min', 0),
					'dtst_age_max'			=> $this->request->variable('dtst_age_max', 0),
					'dtst_participants'		=> $dtst_participants,
					'dtst_participants_unl'	=> $dtst_participants_unlimited,
				)
			);
		}
	}

	/**
	 * Modifies our post's submission prior to happens.
	 *
	 * @event  core.posting_modify_submit_post_before
	 * @param  \phpbb\event\data						$event		The event object
	 * @return void
	 * @access public
	 */
	public function dtst_topic_add($event)
	{
		/* Check if Date Topic Event Calendar is enabled for this forum */
		if ( $this->dtst_utils->forum_dtst_enabled('forum_id', $event['forum_id']) )
		{
			$event['data'] = array_merge($event['data'], array(
				'dtst_location'			=> $event['post_data']['dtst_location'],
				'dtst_loc_custom'		=> $event['post_data']['dtst_loc_custom'],
				'dtst_host'				=> $event['post_data']['dtst_host'],
				'dtst_date'				=> $event['post_data']['dtst_date'],
				/* Here we are storing the timestamp exactly the as per the time of submission */
				'dtst_date_unix'		=> $this->user->get_timestamp_from_format('d-m-Y', $event['post_data']['dtst_date'], new \DateTimeZone($this->config['board_timezone'])),
				'dtst_event_type'		=> $event['post_data']['dtst_event_type'],
				'dtst_age_min'			=> $event['post_data']['dtst_age_min'],
				'dtst_age_max'			=> $event['post_data']['dtst_age_max'],
				'dtst_participants'		=> $event['post_data']['dtst_participants'],
				'dtst_participants_unl'	=> $event['post_data']['dtst_participants_unl'],
			));
		}
	}

	/**
	 * Modify the post's data before the post action is performed,
	 * in this case the topic's description fields newly created by this extension.
	 *
	 * @event  core.posting_modify_message_text
	 * @param  \phpbb\event\data					$event		The event object
	 * @return void
	 * @access public
	 */
	public function dtst_modify_message_text($event)
	{
		/* Check if Date Topic Event Calendar is enabled for this forum */
		if ( $this->dtst_utils->forum_dtst_enabled('forum_id', $event['forum_id']) )
		{
			$event['post_data']	= array_merge($event['post_data'], array(
				'dtst_location'			=> $this->request->variable('dtst_location', ( (!empty($event['post_data']['dtst_location'])) ? $event['post_data']['dtst_location'] : '' ), true),
				'dtst_loc_custom'		=> $this->request->variable('dtst_loc_custom', ( (!empty($event['post_data']['dtst_loc_custom'])) ? $event['post_data']['dtst_loc_custom'] : '' ), true),
				'dtst_host'				=> $this->request->variable('dtst_host', ( (!empty($event['post_data']['dtst_host'])) ? $event['post_data']['dtst_host'] : '' ), true),
				'dtst_date'				=> $this->request->variable('dtst_date', ( (!empty($event['post_data']['dtst_date'])) ? $event['post_data']['dtst_date'] : '' ), true),
				/* dtst_date_unix No user input needed here */
				'dtst_date_unix'		=> (isset($event['post_data']['dtst_date_unix'])) ? $event['post_data']['dtst_date_unix'] : 0,
				'dtst_event_type'		=> $this->request->variable('dtst_event_type', ( (!empty($event['post_data']['dtst_event_type'])) ? $event['post_data']['dtst_event_type'] : '' ), 0),
				'dtst_age_min'			=> $this->request->variable('dtst_age_min', ( (!empty($event['post_data']['dtst_age_min'])) ? $event['post_data']['dtst_age_min'] : 0 ), 0),
				'dtst_age_max'			=> $this->request->variable('dtst_age_max', ( (!empty($event['post_data']['dtst_age_max'])) ? $event['post_data']['dtst_age_max'] : 0 ), 0),
				'dtst_participants'		=> $this->request->variable('dtst_participants', ( (!empty($event['post_data']['dtst_participants'])) ? $event['post_data']['dtst_participants'] : 0 ), 0),
				/* If participants unlimited is set to TRUE then is TRUE, else it is FALSE o_O - No user input needed here */
				'dtst_participants_unl'	=> (isset($event['post_data']['dtst_participants_unl'])) ? $event['post_data']['dtst_participants_unl'] : 0,
			));
		}
	}

	/**
	 * Modify the sql data before on submit, only on the modes allowed.
	 *
	 * @event  core.submit_post_modify_sql_data
	 * @param  \phpbb\event\data					$event		The event object
	 * @return void
	 * @access public
	 */
	public function dtst_submit_post_modify_sql_data($event)
	{
		$mode = $event['post_mode'];

		if (!in_array($mode, array('edit_topic', 'edit_first_post', 'post')))
		{
			return;
		}

		$dtst_location			= $event['data']['dtst_location'];
		$dtst_loc_custom		= $event['data']['dtst_loc_custom'];
		$dtst_host 				= $event['data']['dtst_host'];
		$dtst_date 				= $event['data']['dtst_date'];
		$dtst_date_unix 		= $event['data']['dtst_date_unix'];
		$dtst_event_type		= $event['data']['dtst_event_type'];
		$dtst_age_min			= $event['data']['dtst_age_min'];
		$dtst_age_max			= $event['data']['dtst_age_max'];
		$dtst_participants		= $event['data']['dtst_participants'];
		$dtst_participants_unl	= $event['data']['dtst_participants_unl'];

		$data_sql = $event['sql_data'];

		/* Only applies to authed users */
		if ((bool) $this->dtst_utils->is_authed() && $this->dtst_utils->forum_dtst_enabled('forum_id', $data_sql[TOPICS_TABLE]['sql']['forum_id']))
		{
			$data_sql[TOPICS_TABLE]['sql']['dtst_location']			= $dtst_location;
			$data_sql[TOPICS_TABLE]['sql']['dtst_loc_custom']		= $dtst_loc_custom;
			$data_sql[TOPICS_TABLE]['sql']['dtst_host']				= $dtst_host;
			$data_sql[TOPICS_TABLE]['sql']['dtst_date']				= $dtst_date;
			$data_sql[TOPICS_TABLE]['sql']['dtst_date_unix']		= $dtst_date_unix;
			$data_sql[TOPICS_TABLE]['sql']['dtst_event_type']		= $dtst_event_type;
			$data_sql[TOPICS_TABLE]['sql']['dtst_age_min']			= $dtst_age_min;
			$data_sql[TOPICS_TABLE]['sql']['dtst_age_max']			= $dtst_age_max;
			$data_sql[TOPICS_TABLE]['sql']['dtst_participants']		= $dtst_participants;
			$data_sql[TOPICS_TABLE]['sql']['dtst_participants_unl']	= $dtst_participants_unl;
		}

		$event['sql_data'] = $data_sql;
	}

	/**
	 * Modify the sql data before on submit, only when it is our own automated reply.
	 *
	 * @event  core.submit_post_modify_sql_data
	 * @param  \phpbb\event\data					$event		The event object
	 * @return void
	 * @access public
	 */
	public function dtst_modify_reply_data($event)
	{
		$data = $event['data'];
		$sql_data = $event['sql_data'];

		if (isset($data['s_dtst_reply']) && $data['s_dtst_reply'])
		{
			/* Are we using the PMs Bot? */
			$dtst_user_id		= (int) $data['dtst_user_id'];
			$dtst_username		= $data['dtst_username'];
			$dtst_user_colour	= $data['dtst_user_colour'];

			$sql_data[POSTS_TABLE]['sql'] = array_merge($sql_data[POSTS_TABLE]['sql'], array(
					'poster_id'			=> $dtst_user_id,
					'poster_ip'			=> '0',
					'post_username'		=> $dtst_username,
			));

			/* Do not increment post count of the host */
			unset($sql_data[USERS_TABLE]);

			/* Increment post count for the accepted user */
			$sql = 'UPDATE ' . USERS_TABLE . '
					SET user_lastpost_time = ' . time() . ', user_posts = user_posts + 1
					WHERE user_id = ' . (int) $dtst_user_id;
			$this->db->sql_query($sql);

			$event['sql_data'] = $sql_data;
		}
	}

	/**
	 * Modify the poster id and username after a DTST reply.
	 *
	 * @event  core.submit_post_end
	 * @param  \phpbb\event\data					$event		The event object
	 * @return void
	 * @access public
	 */
	public function dtst_modify_reply_poster($event)
	{
		$data = $event['data'];

		if (isset($data['s_dtst_reply']) && $data['s_dtst_reply'] && $this->config['dtst_use_bot'])
		{
			$sql = 'UPDATE ' . TOPICS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', array(
						'topic_last_poster_id'		=> (int) $data['dtst_user_id'],
						'topic_last_poster_name'	=> (string) $data['dtst_username'],
						'topic_last_poster_colour'	=> (string) $data['dtst_user_colour'],
					)) . ' WHERE topic_id = ' . (int) $data['topic_id'];
			$this->db->sql_query($sql);

			$sql = 'UPDATE ' . FORUMS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', array(
						'forum_last_poster_id'		=> (int) $data['dtst_user_id'],
						'forum_last_poster_name'	=> (string) $data['dtst_username'],
						'forum_last_poster_colour'	=> (string) $data['dtst_user_colour'],
					)) . ' WHERE forum_id = ' . (int) $data['forum_id'];
			$this->db->sql_query($sql);
		}
	}

	/**
	 * Does the opt-in/out for the Date Topic Event Calendar.
	 *
	 * @event  core.viewtopic_modify_post_data
	 * @param  \phpbb\event\data					$event		The event object
	 * @return void
	 * @access public
	 */
	public function dtst_viewtopic_modify_post_data($event)
	{
		/* Check if Date Topic Event Calendar is enabled for this forum */
		if ( $this->dtst_utils->forum_dtst_enabled('forum_id', $event['forum_id']) )
		{
			$topic_data = $event['topic_data'];

			/* Add our language file only when needed */
			$this->lang->add_lang('common', 'phpbbstudio/dtst');

			/* Lets set up some data */
			$data = array(
				ext::DTST_STATUS_ACCEPTED	=> array(
					'user_count'		=> 0,
					'user_status'		=> $this->lang->lang('DTST_USER_STATUS_ACCEPTED'),
					'user_none'			=> $this->lang->lang('DTST_NO_ACCEPTED'),
					'button_class'		=> 'dtst-button-red',
					'button_icon'		=> 'fa-user-times',
					'button_text'		=> $this->lang->lang('DTST_BUTTON_TEXT_WITHDRAW'),
					'template_block'	=> 'dtst_attendees',
					'template_icon'		=> 'fa-check light-green',
				),
				ext::DTST_STATUS_PENDING 	=> array(
					'user_count'		=> 0,
					'user_status'		=> $this->lang->lang('DTST_USER_STATUS_PENDING'),
					'user_none'			=> $this->lang->lang('DTST_NO_APPLICATIONS'),
					'button_class'		=> 'dtst-button-red',
					'button_icon'		=> 'fa-user-times',
					'button_text'		=> $this->lang->lang('CANCEL'),
					'template_block'	=> 'dtst_pending',
					'template_icon'		=> 'fa-question orange'
				),
				ext::DTST_STATUS_DENIED		=> array(
					'user_count'		=> 0,
					'user_status'		=> $this->lang->lang('DTST_USER_STATUS_DENIED'),
					'user_none'			=> $this->lang->lang('DTST_NO_DENIALS'),
					'button_class'		=> 'dtst-button-green',
					'button_icon'		=> 'fa-user-plus',
					'button_text'		=> $this->lang->lang('DTST_BUTTON_TEXT_REAPPLY'),
					'template_block'	=> 'dtst_denials',
					'template_icon'		=> 'fa-times red',
				),
				ext::DTST_STATUS_WITHDRAWN	=> array(
					'user_count'		=> 0,
					'user_status'		=> $this->lang->lang('DTST_USER_STATUS_WITHDRAWN'),
					'user_none'			=> $this->lang->lang('DTST_NO_WITHDRAWALS'),
					'button_class'		=> 'dtst-button-green',
					'button_icon'		=> 'fa-user-plus',
					'button_text'		=> $this->lang->lang('DTST_BUTTON_TEXT_REAPPLY'),
					'template_block'	=> 'dtst_withdrawals',
					'template_icon'		=> 'fa-hand-o-left red',
				),
				ext::DTST_STATUS_CANCELED	=> array(
					'user_count'		=> 0,
					'user_status'		=> $this->lang->lang('DTST_USER_STATUS_CANCELED'),
					'user_none'			=> $this->lang->lang('DTST_NO_CANCELLATIONS'),
					'button_class'		=> 'dtst-button-green',
					'button_icon'		=> 'fa-user-plus',
					'button_text'		=> $this->lang->lang('DTST_BUTTON_TEXT_REAPPLY'),
					'template_block'	=> 'dtst_canceled',
					'template_icon'		=> 'fa-hand-o-left red',
				),
			);

			/* Set up default button variables and user status */
			$button_class = 'dtst-button-green';
			$button_icon = 'fa-user-plus';
			$button_text = $this->lang->lang('DTST_BUTTON_TEXT_ATTEND');
			$user_status = $this->lang->lang('DTST_USER_STATUS_NOT');
			$user_attended = false;

			/* Query the slots table */
			$sql = $this->dtst_utils->dtst_slots_query($topic_data['topic_id']);
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				/* Check if current user is attending */
				if ($this->user->data['user_id'] == $row['user_id'])
				{
					/* Then we overwrite the default variables */
					$button_class = $data[$row['dtst_status']]['button_class'];
					$button_icon = $data[$row['dtst_status']]['button_icon'];
					$button_text = $data[$row['dtst_status']]['button_text'];

					$user_status = $data[$row['dtst_status']]['user_status'];

					if ($row['dtst_status'] == ext::DTST_STATUS_ACCEPTED)
					{
						$user_attended = true;
					}
				}

				/* Increment the user count */
				$data[$row['dtst_status']]['user_count']++;

				/* Assign the variables to the correct block */
				$this->template->assign_block_vars($data[$row['dtst_status']]['template_block'], array(
					/* Auth check is already done within get_username_string() but we check also for more */
					'USERNAME'		=> $this->auth->acl_get('u_viewprofile') ? get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']) : get_username_string('no_profile', $row['user_id'], $row['username'], $row['user_colour']),
					/* Time format as per the Board's default time setting */
					'USER_TIME'		=> $this->user->format_date((int) $row['dtst_time'], $this->config['default_dateformat']),
				));
			}
			$this->db->sql_freeresult($result);

			/**
			 * If the topic is locked (true) and the admin disallowed opting while the topic is locked (false)
			*/
			$s_participate_closed = (($topic_data['topic_status'] == ITEM_LOCKED) && !$this->config['dtst_locked_withdrawal']);

			/**
			* Or if the participant list is full and the active user count is equal or bigger than dtst_participants
			* and if is a topic with unlimited participants
			*/
			$s_participate_full =	($topic_data['dtst_participants_unl'] && !empty($topic_data['dtst_participants']) && $data[ext::DTST_STATUS_ACCEPTED]['user_count'] >= $topic_data['dtst_participants']);

			$dtst_p = (int) $this->request->variable('dtst_p', 0);
			$dtst_p_local = isset($event['rowset'][$dtst_p]);
			$dtst_p_url = $dtst_p_local ? '#p' . $dtst_p : append_sid("viewtopic.{$this->php_ext}", 'f=' . (int) $event['forum_id'] . '&t=' . (int) $event['topic_id'] . '&p=' . $dtst_p . '#p' . $dtst_p);

			$this->template->assign_vars(array(
				'DTST_REP_NAME'				=> $this->config['dtst_rep_name'],

				'DTST_BUTTON_CLASS'			=> $button_class,
				'DTST_BUTTON_ICON'			=> $button_icon,
				'DTST_BUTTON_TEXT'			=> $button_text,
				'DTST_USER_STATUS'			=> $user_status,
				'DTST_DATA'					=> $data,
				'DTST_USER_COUNT_ACCEPTED'	=> $data[ext::DTST_STATUS_ACCEPTED]['user_count'],

				'S_DTST_PARTICIPATE_CLOSED'	=> (bool) $s_participate_closed,
				'S_DTST_PARTICIPATE_FULL'	=> (bool) $s_participate_full,
				'S_DTST_PARTICIPATE'		=> (bool) $this->auth->acl_get('f_reply', $topic_data['forum_id']),
				'S_DTST_ATTENDEES'			=> (bool) $this->auth->acl_get('u_dtst_attendees'),
				'S_DTST_IS_HOST'			=> (bool) ((int) $this->user->data['user_id'] === (int) $topic_data['topic_poster']),
				'S_DTST_EVENT_CANCELED'		=> (bool) $topic_data['dtst_event_canceled'] == ITEM_LOCKED,
				'S_DTST_EVENT_ENDED'		=> (bool) $topic_data['dtst_event_ended'],
				'S_DTST_GIVE_REP'			=> (bool) ($topic_data['dtst_event_ended'] && !$topic_data['dtst_rep_ended'] && ($user_attended || ((int) $this->user->data['user_id'] === (int) $topic_data['topic_poster']))),
				'S_DTST_REP_ENDED'			=> (bool) $topic_data['dtst_rep_ended'],

				'U_DTST_REASON_REPLIES'		=> $dtst_p ? $dtst_p_url : false,
				'U_DTST_MANAGE'				=> $this->helper->route('dtst_manager', array('f' => $topic_data['forum_id'], 't' => $topic_data['topic_id'])),
				'U_DTST_OPT'				=> $this->helper->route('dtst_controller', array('t' => $topic_data['topic_id'])),
				'U_DTST_REP'				=> $this->helper->route('dtst_reputation', array('t' => $topic_data['topic_id'])),
			));
		}
	}

	/**
	 * This event allows you to modify the page title of the viewtopic page.
	 *
	 * @event  core.viewtopic_modify_page_title
	 * @param  \phpbb\event\data					$event		The event object
	 * @return void
	 * @access public
	 */
	public function dtst_topic_add_viewtopic($event)
	{
		$topic_data = $event['topic_data'];

		/* Add our language file only when needed */
		$this->lang->add_lang('common', 'phpbbstudio/dtst');

		$this->template->assign_vars(array(
			'DTST_LOCATION'		=> $topic_data['dtst_location'],
			'DTST_LOC_CUSTOM'	=> censor_text($topic_data['dtst_loc_custom']),
			'DTST_HOST'			=> censor_text($topic_data['dtst_host']),
			'DTST_DATE'			=> $topic_data['dtst_date'],
			'DTST_EVENT_TYPE'	=> $this->dtst_utils->dtst_forum_id_to_name($topic_data['dtst_event_type']),
			'DTST_AGE_MIN'		=> $topic_data['dtst_age_min'],
			'DTST_AGE_MAX'		=> $topic_data['dtst_age_max'],
			'DTST_PARTICIPANTS'	=> $topic_data['dtst_participants'],

			'U_DTST_CANCEL'		=> $this->helper->route('dtst_cancel', array('f' => $topic_data['forum_id'], 't' => $topic_data['topic_id'])),
		));
	}

	/**
	 * Modify the topic data before it is assigned to the template.
	 *
	 * @event  core.search_modify_tpl_ary
	 * @param  \phpbb\event\data				$event		The event object
	 * @return void
	 * @access public
	 */
	public function dtst_search_modify_tpl_ary($event)
	{
		$row = $event['row'];

		/* We just need only one of those fields to be set to display the whole template */
		if ( $event['show_results'] == 'topics' && (!empty($row['dtst_location'] || $row['dtst_host'] || $row['dtst_date'])) )
		{
			/* Add our language file only when needed */
			$this->lang->add_lang('common', 'phpbbstudio/dtst');

			$tpl_array = $event['tpl_ary'];

			$tpl_array['DTST_LOCATION']		= $this->lang->lang('DTST_LOCATION') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_location'])) ? $row['dtst_location'] :  $this->lang->lang('DTST_AGE_RANGE_NO') );
			$tpl_array['DTST_LOC_CUSTOM']	= $this->lang->lang('DTST_LOC_CUSTOM') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_loc_custom'])) ? censor_text($row['dtst_loc_custom']) :  $this->lang->lang('DTST_AGE_RANGE_NO') );
			$tpl_array['DTST_HOST']			= $this->lang->lang('DTST_HOST') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_host'])) ? censor_text($row['dtst_host']) :  $this->lang->lang('DTST_AGE_RANGE_NO') );
			$tpl_array['DTST_DATE']			= $this->lang->lang('DTST_DATE') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_date'])) ? $row['dtst_date'] : $this->lang->lang('DTST_DATE_NONE') ) . '&nbsp;&bull;&nbsp;' . $this->lang->lang('DTST_EVENT_TYPE') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_event_type'])) ? $this->dtst_utils->dtst_forum_id_to_name($row['dtst_event_type']) :  $this->lang->lang('DTST_AGE_RANGE_NO') );
			$tpl_array['DTST_AGE_MIN']		= $this->lang->lang('DTST_AGE_MIN') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_age_min'])) ? $row['dtst_age_min'] : $this->lang->lang('DTST_AGE_RANGE_NO') ) . '&nbsp;&bull;&nbsp;' . $this->lang->lang('DTST_AGE_MAX') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_age_max'])) ? $row['dtst_age_max'] : $this->lang->lang('DTST_AGE_RANGE_NO') ) . '&nbsp;&bull;&nbsp;' . $this->lang->lang('DTST_PARTICIPANTS') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_participants'])) ? $row['dtst_participants'] : $this->lang->lang('DTST_UNLIMITED') );

			$event['tpl_ary'] = $tpl_array;
		}
	}

	/**
	 * Modify the topic data before it is assigned to the template and in MCP.
	 *
	 * @event  core.viewforum_modify_topicrow
	 * @event  core.mcp_view_forum_modify_topicrow
	 * @param  \phpbb\event\data						$event		The event object
	 * @return void
	 * @access public
	 */
	public function dtst_modify_topicrow($event)
	{
		$row = $event['row'];

		/* We just need only one of those fields to be set to display the whole template */
		if ( !empty($row['dtst_location'] || $row['dtst_host'] || $row['dtst_date']) )
		{
			/* Add our language file only when needed */
			$this->lang->add_lang('common', 'phpbbstudio/dtst');

			$topic_row = $event['topic_row'];

			$topic_row['DTST_LOCATION']		= $this->lang->lang('DTST_LOCATION') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_location'])) ? $row['dtst_location'] :  $this->lang->lang('DTST_AGE_RANGE_NO') );
			$topic_row['DTST_LOC_CUSTOM']	= $this->lang->lang('DTST_LOC_CUSTOM') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_loc_custom'])) ? censor_text($row['dtst_loc_custom']) :  $this->lang->lang('DTST_AGE_RANGE_NO') );
			$topic_row['DTST_HOST']			= $this->lang->lang('DTST_HOST') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_host'])) ? censor_text($row['dtst_host']) :  $this->lang->lang('DTST_AGE_RANGE_NO') );
			$topic_row['DTST_DATE']			= $this->lang->lang('DTST_DATE') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_date'])) ? $row['dtst_date'] : $this->lang->lang('DTST_DATE_NONE') ) . '&nbsp;&bull;&nbsp;' . $this->lang->lang('DTST_EVENT_TYPE') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_event_type'])) ? $this->dtst_utils->dtst_forum_id_to_name($row['dtst_event_type']) :  $this->lang->lang('DTST_AGE_RANGE_NO') );
			$topic_row['DTST_AGE_MIN']		= $this->lang->lang('DTST_AGE_MIN') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_age_min'])) ? $row['dtst_age_min'] : $this->lang->lang('DTST_AGE_RANGE_NO') ) . '&nbsp;&bull;&nbsp;' . $this->lang->lang('DTST_AGE_MAX') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_age_max'])) ? $row['dtst_age_max'] : $this->lang->lang('DTST_AGE_RANGE_NO') ) . '&nbsp;&bull;&nbsp;' . $this->lang->lang('DTST_PARTICIPANTS') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_participants'])) ? $row['dtst_participants'] : $this->lang->lang('DTST_UNLIMITED') );

			$event['topic_row'] = $topic_row;
		}
	}

	/**
	 * Display the Date Topic Starter Template filters in the viewforum page.
	 *
	 * @event  core.viewforum_modify_page_title
	 * @param  \phpbb\event\data		$event		The event object
	 * @return void
	 * @access public
	 */
	public function dtst_viewforum_filters($event)
	{
		/* Check permissions */
		if (!$this->dtst_utils->is_authed())
		{
			return;
		}

		/* Check if DTST is enabled */
		if (!$this->dtst_utils->forum_dtst_enabled('1', $event['forum_id']))
		{
			return;
		}

		/* Check if we are inside a forum, not a category or link */
		if ((int) $event['forum_data']['forum_type'] !== FORUM_POST)
		{
			return;
		}

		/* Request the dtst filters */
		$age_range			= $this->request->variable('dtst_age', '0,99', true);
		$participants_range	= $this->request->variable('dtst_participants', '0,999', true);
		$date_after			= $this->request->variable('dtst_after', '', true);
		$date_before		= $this->request->variable('dtst_before', '', true);
		$selected_types		= $this->request->variable('dtst_type', array(0));
		$selected_locations = $this->request->variable('dtst_location', array(0));

		/* Decode our ranges, as they are comma separated and that is being encoded by the html form 'get' action */
		$age_range			= htmlspecialchars_decode($age_range, ENT_COMPAT);
		$participants_range	= htmlspecialchars_decode($participants_range, ENT_COMPAT);

		$event_type_list = $this->dtst_utils->dtst_list_enabled_forum_names();
		/**
		 * Sort Array (Ascending Order) According its values using our
		 * supplied comparison function and maintaining index association.
		 */
		uasort($event_type_list, 'strnatcasecmp');

		/* Traverse items in our array */
		foreach ($event_type_list as $key => $event_type)
		{
			/* If not empty */
			if (!empty($event_type))
			{
				/* Assign block-vars to our template */
				$this->template->assign_block_vars('dtst_event_types', array(
					'INDEX'				=> $key,
					'NAME'				=> $event_type,
					'S_SELECTED_TYPES'	=> in_array($key, $selected_types),
				));
			}
		}

		/* Pull the array from the DB or cast it if null */
		$locations = (array) $this->dtst_utils->dtst_json_decode_locations();
		/**
		 * Sort Array (Ascending Order) According its values using our
		 * supplied comparison function and maintaining index association.
		 */
		uasort($locations, 'strnatcasecmp');

		/* Traverse items in our array */
		foreach ($locations as $key => $location)
		{
			/* If not empty */
			if (!empty($location))
			{
				/* Assign block-vars to our template */
				$this->template->assign_block_vars('dtst_locations', array(
					'INDEX'			=> $key,
					'LOCATION'		=> htmlspecialchars_decode($location, ENT_COMPAT),
					'S_SELECTED'	=> in_array($key, $selected_locations),
				));
			}
		}

		/* Says it all */
		$this->template->assign_vars(array(
			'DTST_AGE_RANGE'			=> $age_range,
			'DTST_PARTICIPANTS_RANGE'	=> $participants_range,
			'DTST_DATE_AFTER'			=> $date_after,
			'DTST_DATE_BEFORE'			=> $date_before,

			'S_DTST_DISPLAY'			=> true,
			'S_DTST_SIDEBAR'			=> (bool) $this->config['dtst_sidebar'],

			'U_DTST_FILTER_ACTION'		=> append_sid("{$this->root_path}viewforum.{$this->php_ext}", 'f=' . $event['forum_id']),
		));
	}

	/**
	 * Apply the Date Topic Starter Template filters in the viewforum page.
	 *
	 * @event  core.viewforum_get_topic_ids_data
	 * @param  \phpbb\event\data					$event		The event object
	 * @return void
	 * @access public
	 */
	public function dtst_viewforum_apply_filters($event)
	{
		/* Check permissions */
		if (!$this->dtst_utils->is_authed())
		{
			return;
		}

		/* Check if DTST is enabled */
		if (!$this->dtst_utils->forum_dtst_enabled('1', $event['forum_data']['forum_id']))
		{
			return;
		}

		/* Check if we are inside a forum, not a category or link */
		if ((int) $event['forum_data']['forum_type'] !== FORUM_POST)
		{
			return;
		}

		/* Add our language file only when needed */
		$this->lang->add_lang('common', 'phpbbstudio/dtst');

		/* Request the dtst filters */
		$age_range			= $this->request->variable('dtst_age', '0,99', true);
		$participants_range	= $this->request->variable('dtst_participants', '0,999', true);
		$date_after			= $this->request->variable('dtst_after', '', true);
		$date_before		= $this->request->variable('dtst_before', '', true);
		$selected_types		= $this->request->variable('dtst_type', array(0));
		$selected_locations = $this->request->variable('dtst_location', array(0));

		/* Decode our ranges, as they are comma separated and that is being encoded by the html form 'get' action */
		$age_range	= htmlspecialchars_decode($age_range, ENT_COMPAT);
		$age_range	= explode(',', $age_range);
		$age_min	= isset($age_range[0]) ? (int) $age_range[0] : false;
		$age_max	= isset($age_range[1]) ? (int) $age_range[1] : false;

		$participants_range	= htmlspecialchars_decode($participants_range, ENT_COMPAT);
		$participants_range	= explode(',', $participants_range);
		$participants_min	= isset($participants_range[0]) ? (int) $participants_range[0] : false;
		$participants_max	= isset($participants_range[1]) ? (int) $participants_range[1] : false;
		$participants_unl	= ($participants_min === 0 && $participants_max === 0) ? true : false;

		/* Instantiate an empty array */
		$locations_array = array();

		/* Pull the array from the DB or cast it if null */
		$locations = (array) $this->dtst_utils->dtst_json_decode_locations();

		foreach ($locations as $key => $location)
		{
			if (in_array($key, $selected_locations))
			{
				$locations_array[] = $this->db->sql_escape(htmlspecialchars_decode($location, ENT_COMPAT));
			}
		}

		/* Do we have a request here? */
		if ($date_after)
		{
			/* Explode request */
			list($after_day, $after_month, $after_year) = explode('-', $date_after);

			/**
			 * Set the timestamp for the last second of the given date (ex.: 01 09 2010 23:59:59)
			 * Which means exactly "after" of the given date, as per the request.
			 */
			$date_after_real = mktime(23, 59, 59, $after_month, $after_day, $after_year);
		}
		$date_after = !empty($date_after) ? (int) $date_after_real : false;

		/* Do we have a request here? */
		if ($date_before)
		{
			/* Explode request */
			list($before_day, $before_month, $before_year) = explode('-', $date_before);

			/**
			 * Set the timestamp for the one second before of the given date (ex.: 31 08 2010 23:59:59)
			 * Which means exactly "before" of the given date, as per the request.
			 */
			$date_before_real = mktime(0, 0, -1, $before_month, $before_day, $before_year);
		}
		$date_before = !empty($date_before) ? (int) $date_before_real : false;

		$sql_ary = $event['sql_ary'];

		$sql_ary['WHERE'] .= !empty($age_min) ? ' AND t.dtst_age_min >= ' . (int) $age_min . ' AND t.dtst_age_max <= ' . (int) $age_max : '';
		$sql_ary['WHERE'] .= !empty($age_max) && empty($age_min) && $age_max !== 99 ? ' AND t.dtst_age_max <= ' . (int) $age_max : '';

		$sql_ary['WHERE'] .= !empty($participants_min) ? ' AND t.dtst_participants >= ' . (int) $participants_min  . ' AND t.dtst_participants <= ' . (int) $participants_max : '';
		$sql_ary['WHERE'] .= !empty($participants_max) && empty($participants_min) && $participants_max !== 999 ? ' AND t.dtst_participants <= ' . (int) $participants_max : '';
		$sql_ary['WHERE'] .= $participants_unl ? ' AND t.dtst_participants = 0' : '';

		$sql_ary['WHERE'] .= !empty($date_after) ? ' AND t.dtst_date_unix > ' . (int) $date_after : '';
		$sql_ary['WHERE'] .= !empty($date_before) ? ' AND t.dtst_date_unix < ' . (int) $date_before : '';

		$sql_ary['WHERE'] .= !empty($selected_types) ? ' AND ' . $this->db->sql_in_set('t.dtst_event_type', $selected_types) : '';
		$sql_ary['WHERE'] .= !empty($locations_array) ? ' AND ' . $this->db->sql_in_set('t.dtst_location', $locations_array) : '';

		/* Merge the SQL WHERE back in to the event parameters */
		$event['sql_ary'] = $sql_ary;
	}
}
