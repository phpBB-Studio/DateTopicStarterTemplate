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
	 * Constructor
	 *
	 * @param  \phpbb\auth\auth						$auth			Auth object
	 * @param  \phpbb\config\config					$config			Configuration object
	 * @param  \phpbb\db\driver\driver_interface	$db				Database object
	 * @param  \phpbb\request\request				$request		Request object
	 * @param  \phpbb\template\template				$template		Template object
	 * @param  \phpbb\user							$user			User object
	 * @param  \phpbb\language\language				$lang			Language object
	 * @param  \phpbbstudio\dtst\core\operator		$dtst_utils		Functions to be used by Classes
	 * @param  string								$root_path		phpBB root path
	 * @param  string								$php_ext		php File extension
	 * @access public
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, \phpbb\language\language $lang, \phpbbstudio\dtst\core\operator $dtst_utils, $root_path, $php_ext, $dtst_slots, \phpbb\controller\helper $helper)
	{
		$this->auth			= $auth;
		$this->config		= $config;
		$this->db			= $db;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;
		$this->lang			= $lang;
		$this->dtst_utils	= $dtst_utils;
		$this->root_path	= $root_path;
		$this->php_ext		= $php_ext;
		$this->dtst_slots	= $dtst_slots;
		$this->helper		= $helper;
	}

	/**
	 * Assign functions defined in this class to event listeners in the core
	 *
	 * @static
	 * @return array
	 * @access public
	 */
	static public function getSubscribedEvents()
	{
		return array(
			'core.page_header_after'					=> 'dtst_template_switch',
			'core.permissions'							=> 'dtst_add_permissions',
			'core.posting_modify_template_vars'			=> 'dtst_topic_data_topic',
			'core.posting_modify_submission_errors'		=> 'dtst_topic_add_to_post_data',
			'core.posting_modify_submit_post_before'	=> 'dtst_topic_add',
			'core.posting_modify_message_text'			=> 'dtst_modify_message_text',
			'core.submit_post_modify_sql_data'			=> 'dtst_submit_post_modify_sql_data',
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
	 * Template switches over all
	 *
	 * @event core.page_header_after
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
	 * Add permissions for DTST - Permission's language file is automatically loaded
	 *
	 * @event	core.permissions
	 * @param	\phpbb\event\data		$event		The event object
	 * @return	void
	 * @access	public
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
		];

		/* Merging our CAT to the native array of perms */
		$event['categories'] = array_merge($event['categories'], $categories);

		/* Copying back to event variable */
		$event['permissions'] = $permissions;
	}

	/**
	 * Modify the page's data before it is assigned to the template
	 *
	 * @event	core.posting_modify_template_vars
	 * @param	\phpbb\event\data					$event		The event object
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

				if ($mode == 'edit')
				{
					$page_data['S_DTST_TOPIC_EDIT'] = true;
				}
			}

			$event['page_data'] = $page_data;
		}
	}

	/**
	 * This event allows you to define errors before the post action is performed
	 *
	 * @event	core.posting_modify_submission_errors
	 * @param	\phpbb\event\data						$event		The event object
	 * @return void
	 * @access public
	 */
	public function dtst_topic_add_to_post_data($event)
	{
		/* Check if Date Topic Event Calendar is enabled for this forum */
		if ( $this->dtst_utils->forum_dtst_enabled('forum_id', $event['forum_id']) )
		{
			/* Check if the fields are all mandatory for this forum */
			if ( $this->dtst_utils->forum_dtst_forced_fields('dtst_f_forced_fields', $event['forum_id']) )
			{
				/* All fields are mandatory, we check that on submit */
				$error = $event['error'];

				/* Only applies to authed users */
				if ( (bool) $this->dtst_utils->is_authed() )
				{
					/* Add our errors language file only if needed */
					$this->lang->add_lang('common_errors', 'phpbbstudio/dtst');

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
				}

				$event['error'] = $error;
			}

			/**
			 * No errors? Let's party :-D
			 *
			 * Emojis will be stripped away.
			 */
			$event['post_data']	= array_merge($event['post_data'], array(
					'dtst_location'		=> trim(preg_replace('/[\x{10000}-\x{10FFFF}]/u', "", $this->request->variable('dtst_location', '', true))),
					'dtst_loc_custom'	=> trim(preg_replace('/[\x{10000}-\x{10FFFF}]/u', "", $this->request->variable('dtst_loc_custom', '', true))),
					'dtst_host'			=> trim(preg_replace('/[\x{10000}-\x{10FFFF}]/u', "", $this->request->variable('dtst_host', '', true))),
					'dtst_date'			=> trim($this->request->variable('dtst_date', '', true)),
					'dtst_event_type'	=> trim($this->request->variable('dtst_event_type', '', 0)),
					'dtst_age_min'		=> trim($this->request->variable('dtst_age_min', '', 0)),
					'dtst_age_max'		=> trim($this->request->variable('dtst_age_max', '', 0)),
					'dtst_participants'	=> trim($this->request->variable('dtst_participants', '', 0)),
					/* If participants is set to zero, participants are unlimited therefore flagged as TRUE in the related column */
					'dtst_participants_unl'	=> (trim($this->request->variable('dtst_participants', '', 0)) === 0) ? false : true,
				)
			);
		}
	}

	/**
	 * Modifies our post's submission prior to happens
	 *
	 * @event	core.posting_modify_submit_post_before
	 * @param	\phpbb\event\data						$event		The event object
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
				'dtst_event_type'		=> $event['post_data']['dtst_event_type'],
				'dtst_age_min'			=> $event['post_data']['dtst_age_min'],
				'dtst_age_max'			=> $event['post_data']['dtst_age_max'],
				'dtst_participants'		=> $event['post_data']['dtst_participants'],
				'dtst_participants_unl'	=> $event['post_data']['dtst_participants_unl'],
			));
		}
	}

	/**
	 * Modify the post's data before the post action is performed
	 * in this case the topic's description fields newly created by this extension.
	 *
	 * @event	core.posting_modify_message_text
	 * @param	\phpbb\event\data					$event		The event object
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
				'dtst_event_type'		=> $this->request->variable('dtst_event_type', ( (!empty($event['post_data']['dtst_event_type'])) ? $event['post_data']['dtst_event_type'] : '' ), 0),
				'dtst_age_min'			=> $this->request->variable('dtst_age_min', ( (!empty($event['post_data']['dtst_age_min'])) ? $event['post_data']['dtst_age_min'] : 0 ), 0),
				'dtst_age_max'			=> $this->request->variable('dtst_age_max', ( (!empty($event['post_data']['dtst_age_max'])) ? $event['post_data']['dtst_age_max'] : 0 ), 0),
				'dtst_participants'		=> $this->request->variable('dtst_participants', ( (!empty($event['post_data']['dtst_participants'])) ? $event['post_data']['dtst_participants'] : 0 ), 0),
				/* If participants unlimited is set to TRUE hen is TRUE, else is FALSE o_O - No user input needed here */
				'dtst_participants_unl'	=> ($event['post_data']['dtst_participants_unl']) ? $event['post_data']['dtst_participants_unl'] : 0,
			));
		}
	}

	/**
	 * Modify the sql data before on submit, only on the modes allowed.
	 *
	 * @event	core.submit_post_modify_sql_data
	 * @param	\phpbb\event\data					$event		The event object
	 * @return void
	 * @access public
	 */
	public function dtst_submit_post_modify_sql_data($event)
	{
		$mode = $event['post_mode'];

		$dtst_location			= $event['data']['dtst_location'];
		$dtst_loc_custom		= $event['data']['dtst_loc_custom'];
		$dtst_host 				= $event['data']['dtst_host'];
		$dtst_date 				= $event['data']['dtst_date'];
		$dtst_event_type		= $event['data']['dtst_event_type'];
		$dtst_age_min			= $event['data']['dtst_age_min'];
		$dtst_age_max			= $event['data']['dtst_age_max'];
		$dtst_participants		= $event['data']['dtst_participants'];
		$dtst_participants_unl	= $event['data']['dtst_participants_unl'];

		$data_sql = $event['sql_data'];

		/* Only applies to authed users */
		if ((bool) $this->dtst_utils->is_authed() && $this->dtst_utils->forum_dtst_enabled('forum_id', $data_sql[TOPICS_TABLE]['sql']['forum_id']))
		{
			if ( in_array($mode, array('post', 'edit_topic', 'edit_first_post')) )
			{
				$data_sql[TOPICS_TABLE]['sql']['dtst_location']			= $dtst_location;
				$data_sql[TOPICS_TABLE]['sql']['dtst_loc_custom']		= $dtst_loc_custom;
				$data_sql[TOPICS_TABLE]['sql']['dtst_host']				= $dtst_host;
				$data_sql[TOPICS_TABLE]['sql']['dtst_date']				= $dtst_date;
				$data_sql[TOPICS_TABLE]['sql']['dtst_event_type']		= $dtst_event_type;
				$data_sql[TOPICS_TABLE]['sql']['dtst_age_min']			= $dtst_age_min;
				$data_sql[TOPICS_TABLE]['sql']['dtst_age_max']			= $dtst_age_max;
				$data_sql[TOPICS_TABLE]['sql']['dtst_participants']		= $dtst_participants;
				$data_sql[TOPICS_TABLE]['sql']['dtst_participants_unl']	= $dtst_participants_unl;
			}
		}

		$event['sql_data'] = $data_sql;
	}

	/**
	 * Does the opt-in/outfor the Date Topic Event Calendar
	 *
	 * @event	core.viewtopic_modify_post_data
	 * @param	\phpbb\event\data					$event		The event object
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

			/* Set up variables */
			$user_attending = $user_active = false;
			$user_active_count = 0;
			$user_inactive_count = 0;

			/* Query the slots table */
			$sql = $this->dtst_utils->dtst_slots_query($topic_data['topic_id']);
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				/* Check if current user is attending*/
				if ($this->user->data['user_id'] == $row['user_id'])
				{
					$user_attending = true;
					$user_active = $row['active'];
				}

				/* Increment the user count */
				if ($row['active'])
				{
					$user_active_count++;
					$block = 'dtst_attendees';
				}
				else
				{
					$user_inactive_count++;
					$block = 'dtst_withdrawals';
				}

				$this->template->assign_block_vars($block, array(
					/* Auth check is already done within get_username_string() but we check also for more */
					'USERNAME'		=> $this->auth->acl_get('u_viewprofile') ? get_username_string('full', $row['user_id'], $row['username'], $row['user_colour']) : get_username_string('no_profile', $row['user_id'], $row['username'], $row['user_colour']),
					/* Time format as per the Board's defaul time setting */
					'USER_TIME'		=> $this->user->format_date((int) $row['post_time'], $this->config['default_dateformat']),
				));
			}
			$this->db->sql_freeresult($result);

			/**
			 * If the topic is locked (true) and the admin disallowed opting while the topic is locked (false)
			*/
			$s_participate_closed = (($topic_data['topic_status'] == ITEM_LOCKED) && !$this->config['dtst_locked_withdrawal']);
// debug
//var_dump($s_participate_closed);

			/**
			* Or if the participant list is full and the active user count is equal or bigger than dtst_participants
			* and if is a topic with unlimited participants
			*/
			$s_participate_full =	($topic_data['dtst_participants_unl'] && !empty($topic_data['dtst_participants']) && $user_active_count >= $topic_data['dtst_participants']);

			$this->template->assign_vars(array(
				'DTST_BUTTON_CLASS'			=> ($user_attending && $user_active) ? 'dtst-button-red' : 'dtst-button-green',
				'DTST_BUTTON_ICON'			=> ($user_attending && $user_active) ? 'fa-user-times' : 'fa-user-plus',
				'DTST_BUTTON_TEXT'			=> $user_attending ? ($user_active ? $this->lang->lang('DTST_OPT_WITHDRAW') : $this->lang->lang('DTST_OPT_REATTEND')) : $this->lang->lang('DTST_OPT_ATTEND'),
				'DTST_USER_STATUS'			=> $user_attending ? ($user_active ? $this->lang->lang('DTST_STATUS_ATTENDING') : $this->lang->lang('DTST_STATUS_WITHDRAWN')) : $this->lang->lang('DTST_STATUS_ATTENDING_NOT'),

				'DTST_USER_ACTIVE_COUNT'	=> $user_active_count,
				'DTST_USER_INACTIVE_COUNT'	=> $user_inactive_count,

				'S_DTST_PARTICIPATE_CLOSED'	=> (bool) $s_participate_closed,
				'S_DTST_PARTICIPATE_FULL'	=> (bool) $s_participate_full,
				'S_DTST_PARTICIPATE'		=> (bool) $this->auth->acl_get('f_reply', $topic_data['forum_id']),
				'S_DTST_ATTENDEES'			=> (bool) $this->auth->acl_get('u_dtst_attendees'),

				'U_DTST_OPT'				=> $this->helper->route('dtst_controller', array('topic_id' => $topic_data['topic_id'])),
			));
		}
	}

	/**
	 * This event allows you to modify the page title of the viewtopic page
	 *
	 * @event	core.viewtopic_modify_page_title
	 * @param	\phpbb\event\data					$event		The event object
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
		));
	}

	/**
	 * Modify the topic data before it is assigned to the template
	 *
	 * @event	core.search_modify_tpl_ary
	 * @param	\phpbb\event\data				$event		The event object
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

			$tpl_array['DTST_LOCATION']		= $this->lang->lang('DTST_LOCATION') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_location'])) ? $row['dtst_location'] : '' );

			$tpl_array['DTST_LOC_CUSTOM']	= $this->lang->lang('DTST_LOC_CUSTOM') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_loc_custom'])) ? censor_text($row['dtst_loc_custom']) : '' );

			$tpl_array['DTST_HOST']			= $this->lang->lang('DTST_HOST') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_host'])) ? censor_text($row['dtst_host']) : '' );

			$tpl_array['DTST_DATE']			= $this->lang->lang('DTST_DATE') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_date'])) ? $row['dtst_date'] : $this->lang->lang('DTST_DATE_NONE') ) . '&nbsp;&bull;&nbsp;' . $this->lang->lang('DTST_EVENT_TYPE') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_event_type'])) ? $this->dtst_utils->dtst_forum_id_to_name($row['dtst_event_type']) : '' );

			$tpl_array['DTST_AGE_MIN']		= $this->lang->lang('DTST_AGE_MIN') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_age_min'])) ? $row['dtst_age_min'] : $this->lang->lang('DTST_AGE_RANGE_NO') ) . '&nbsp;&bull;&nbsp;' . $this->lang->lang('DTST_AGE_MAX') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_age_max'])) ? $row['dtst_age_max'] : $this->lang->lang('DTST_AGE_RANGE_NO') ) . '&nbsp;&bull;&nbsp;' . $this->lang->lang('DTST_PARTICIPANTS') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_participants'])) ? $row['dtst_participants'] : $this->lang->lang('DTST_UNLIMITED') );

			$event['tpl_ary'] = $tpl_array;
		}
	}

	/**
	 * Modify the topic data before it is assigned to the template and in MCP
	 *
	 * @event	core.viewforum_modify_topicrow
	 * @event	core.mcp_view_forum_modify_topicrow
	 * @param	\phpbb\event\data						$event		The event object
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

			$topic_row['DTST_LOCATION']		= $this->lang->lang('DTST_LOCATION') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_location'])) ? $row['dtst_location'] : '' );

			$topic_row['DTST_LOC_CUSTOM']	= $this->lang->lang('DTST_LOC_CUSTOM') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_loc_custom'])) ? censor_text($row['dtst_loc_custom']) : '' );

			$topic_row['DTST_HOST']			= $this->lang->lang('DTST_HOST') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_host'])) ? censor_text($row['dtst_host']) : '' );

			$topic_row['DTST_DATE']			= $this->lang->lang('DTST_DATE') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_date'])) ? $row['dtst_date'] : $this->lang->lang('DTST_DATE_NONE') ) . '&nbsp;&bull;&nbsp;' . $this->lang->lang('DTST_EVENT_TYPE') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_event_type'])) ? $this->dtst_utils->dtst_forum_id_to_name($row['dtst_event_type']) : '' );

			$topic_row['DTST_AGE_MIN']		= $this->lang->lang('DTST_AGE_MIN') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_age_min'])) ? $row['dtst_age_min'] : $this->lang->lang('DTST_AGE_RANGE_NO') ) . '&nbsp;&bull;&nbsp;' . $this->lang->lang('DTST_AGE_MAX') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_age_max'])) ? $row['dtst_age_max'] : $this->lang->lang('DTST_AGE_RANGE_NO') ) . '&nbsp;&bull;&nbsp;' . $this->lang->lang('DTST_PARTICIPANTS') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_participants'])) ? $row['dtst_participants'] : $this->lang->lang('DTST_UNLIMITED') );

			$event['topic_row'] = $topic_row;
		}
	}

	/**
	 * Display the Date Topic Starter Template filters in the viewforum page
	 *
	 * @event	core.viewforum_modify_page_title
	 * @param	\phpbb\event\data		$event		The event object
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

			'U_DTST_FILTER_ACTION'		=> append_sid("{$this->root_path}viewforum.{$this->php_ext}", 'f=' . $event['forum_id']),
		));
	}

	/**
	 * Apply the Date Topic Starter Template filters in the viewforum page
	 *
	 * @event	core.viewforum_get_topic_ids_data
	 * @param	\phpbb\event\data					$event		The event object
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

		// Convert the date to the correct format (DD-MM-YYYY to YYYY-MM-DD)
		$date_after = !empty($date_after) ? explode('-', $date_after) : false;
		$date_after = !empty($date_after) ? $date_after[2] . '-' . $date_after[1] . '-' . $date_after[0] : false;

		$date_before = !empty($date_before) ? explode('-', $date_before) : false;
		$date_before = !empty($date_before) ? $date_before[2] . '-' . $date_before[1] . '-' . $date_before[0] : false;

		$sql_ary = $event['sql_ary'];

		$sql_ary['WHERE'] .= !empty($age_min) ? ' AND t.dtst_age_min >= ' . $age_min . ' AND t.dtst_age_max <= ' . $age_max : '';
		$sql_ary['WHERE'] .= !empty($age_max) && empty($age_min) && $age_max !== 99 ? ' AND t.dtst_age_max <= ' . $age_max : '';

		$sql_ary['WHERE'] .= !empty($participants_min) ? ' AND t.dtst_participants >= ' . $participants_min  . ' AND t.dtst_participants <= ' . $participants_max: '';
		$sql_ary['WHERE'] .= !empty($participants_max) && empty($participants_min) && $participants_max !== 999 ? ' AND t.dtst_participants <= ' . $participants_max : '';
		$sql_ary['WHERE'] .= $participants_unl ? ' AND t.dtst_participants = 0' : '';

		$sql_ary['WHERE'] .= !empty($date_after) ? ' AND str_to_date(t.dtst_date, "%d-%m-%Y") > "' . $this->db->sql_escape($date_after) . '"' : '';
		$sql_ary['WHERE'] .= !empty($date_before) ? ' AND str_to_date(t.dtst_date, "%d-%m-%Y") < "' . $this->db->sql_escape($date_before) . '"' : '';

		$sql_ary['WHERE'] .= !empty($selected_types) ? ' AND ' . $this->db->sql_in_set('t.dtst_event_type', $selected_types) : '';
		$sql_ary['WHERE'] .= !empty($locations_array) ? ' AND ' . $this->db->sql_in_set('t.dtst_location', $locations_array) : '';

// debug
//var_dump($sql_ary);

		/* Merge the SQL WHERE back in to the event parameters */
		$event['sql_ary'] = $sql_ary;
	}
}
