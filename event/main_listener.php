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

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\language\language */
	protected $lang;

	/* @var \phpbbstudio\dtst\core\operator */
	protected $dtst_utils;

	/**
	 * Constructor
	 *
	 * @param  \phpbb\auth\auth					$auth			Auth object
	 * @param  \phpbb\request\request			$request		Request object
	 * @param  \phpbb\template\template			$template		Template object
	 * @param  \phpbb\user						$user			User object
	 * @param  \phpbb\language\language			$lang			Language object
	 * @param  \phpbbstudio\dtst\core\operator	$dtst_utils		Functions to be used by Classes
	 * @access public
	 */
	public function __construct(\phpbb\auth\auth $auth, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, \phpbb\language\language $lang, \phpbbstudio\dtst\core\operator $dtst_utils)
	{
		$this->auth			= $auth;
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;
		$this->lang			= $lang;
		$this->dtst_utils	= $dtst_utils;
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
			'core.permissions'							=> 'dtst_add_permissions',
			'core.posting_modify_template_vars'			=> 'dtst_topic_data_topic',
			'core.posting_modify_submission_errors'		=> 'dtst_topic_add_to_post_data',
			'core.posting_modify_submit_post_before'	=> 'dtst_topic_add',
			'core.posting_modify_message_text'			=> 'dtst_modify_message_text',
			'core.submit_post_modify_sql_data'			=> 'dtst_submit_post_modify_sql_data',
			'core.viewtopic_modify_page_title'			=> 'dtst_topic_add_viewtopic',
			'core.modify_posting_auth'					=> 'dtst_topic_add_posting_reply',
			'core.viewforum_modify_topicrow'			=> 'dtst_modify_topicrow',
			'core.search_modify_tpl_ary'				=> 'dtst_search_modify_tpl_ary',
			'core.mcp_view_forum_modify_topicrow'		=> 'dtst_modify_topicrow',
			'core.acp_manage_forums_request_data'		=> 'dtst_acp_manage_forums_request_data',
			'core.acp_manage_forums_initialise_data'	=> 'dtst_acp_manage_forums_initialise_data',
			'core.acp_manage_forums_display_form'		=> 'dtst_acp_manage_forums_display_form',
		);
	}

	/**
	 * Add permissions for DTST - Permission's language file is automatically loaded
	 *
	 * @param	\phpbb\event\data		$event		The event object
	 * @event	core.permissions
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
	 * @event core.posting_modify_template_vars
	 * @param \phpbb\event\data		$event		The event object
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
			$post_data['dtst_partecipants']	= (!empty($post_data['dtst_partecipants'])) ? $post_data['dtst_partecipants'] : 0;

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
				$event_type_list = $this->dtst_utils->dtst_forum_select($event_type);

				/* Add our template vars */
				$page_data['DTST_LOCATION']			= $preset_location_list;
				$page_data['DTST_LOC_CUSTOM']		= $this->request->variable('dtst_loc_custom', $post_data['dtst_loc_custom'], true);
				$page_data['DTST_HOST']				= $this->request->variable('dtst_host', $post_data['dtst_host'], true);
				$page_data['DTST_DATE']				= $this->request->variable('dtst_date', $post_data['dtst_date'], true);
				$page_data['DTST_EVENT_TYPE']		= $event_type_list;
				$page_data['DTST_AGE_MIN']			= $this->request->variable('dtst_age_min', $post_data['dtst_age_min']);
				$page_data['DTST_AGE_MAX']			= $this->request->variable('dtst_age_max', $post_data['dtst_age_max']);
				$page_data['DTST_PARTECIPANTS']		= $this->request->variable('dtst_partecipants', $post_data['dtst_partecipants']);

				/* Add our placeholders */
				$page_data['DTST_LOC_CUSTOM_HOLDER']	= $this->lang->lang('DTST_LOC_CUSTOM_HOLDER');
				$page_data['DTST_HOST_HOLDER']			= $this->user->data['username'];

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
	 * @event core.posting_modify_submission_errors
	 * @param \phpbb\event\data		$event		The event object
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
					'dtst_partecipants'	=> trim($this->request->variable('dtst_partecipants', '', 0)),
				)
			);
		}
	}

	/**
	 * Modifies our post's submission prior to happens
	 *
	 * @event core.posting_modify_submit_post_before
	 * @param \phpbb\event\data		$event		The event object
	 * @return void
	 * @access public
	 */
	public function dtst_topic_add($event)
	{
		/* Check if Date Topic Event Calendar is enabled for this forum */
		if ( $this->dtst_utils->forum_dtst_enabled('forum_id', $event['forum_id']) )
		{
			$event['data'] = array_merge($event['data'], array(
				'dtst_location'		=> $event['post_data']['dtst_location'],
				'dtst_loc_custom'	=> $event['post_data']['dtst_loc_custom'],
				'dtst_host'			=> $event['post_data']['dtst_host'],
				'dtst_date'			=> $event['post_data']['dtst_date'],
				'dtst_event_type'	=> $event['post_data']['dtst_event_type'],
				'dtst_age_min'		=> $event['post_data']['dtst_age_min'],
				'dtst_age_max'		=> $event['post_data']['dtst_age_max'],
				'dtst_partecipants'	=> $event['post_data']['dtst_partecipants'],
			));
		}
	}

	/**
	 * Modify the post's data before the post action is performed
	 * in this case the topic's description fields newly created by this extension.
	 *
	 * @event core.posting_modify_message_text
	 * @param \phpbb\event\data		$event		The event object
	 * @return void
	 * @access public
	 */
	public function dtst_modify_message_text($event)
	{
		/* Check if Date Topic Event Calendar is enabled for this forum */
		if ( $this->dtst_utils->forum_dtst_enabled('forum_id', $event['forum_id']) )
		{
			$event['post_data']	= array_merge($event['post_data'], array(
				'dtst_location'		=> $this->request->variable('dtst_location', ( (!empty($event['post_data']['dtst_location'])) ? $event['post_data']['dtst_location'] : '' ), true),
				'dtst_loc_custom'	=> $this->request->variable('dtst_loc_custom', ( (!empty($event['post_data']['dtst_loc_custom'])) ? $event['post_data']['dtst_loc_custom'] : '' ), true),
				'dtst_host'			=> $this->request->variable('dtst_host', ( (!empty($event['post_data']['dtst_host'])) ? $event['post_data']['dtst_host'] : '' ), true),
				'dtst_date'			=> $this->request->variable('dtst_date', ( (!empty($event['post_data']['dtst_date'])) ? $event['post_data']['dtst_date'] : '' ), true),
				'dtst_event_type'	=> $this->request->variable('dtst_event_type', ( (!empty($event['post_data']['dtst_event_type'])) ? $event['post_data']['dtst_event_type'] : '' ), 0),
				'dtst_age_min'		=> $this->request->variable('dtst_age_min', ( (!empty($event['post_data']['dtst_age_min'])) ? $event['post_data']['dtst_age_min'] : 0 ), 0),
				'dtst_age_max'		=> $this->request->variable('dtst_age_max', ( (!empty($event['post_data']['dtst_age_max'])) ? $event['post_data']['dtst_age_max'] : 0 ), 0),
				'dtst_partecipants'	=> $this->request->variable('dtst_partecipants', ( (!empty($event['post_data']['dtst_partecipants'])) ? $event['post_data']['dtst_partecipants'] : 0 ), 0),
			));
		}
	}

	/**
	 * Modify the sql data before on submit, only on the modes allowed.
	 *
	 * @event core.submit_post_modify_sql_data
	 * @param \phpbb\event\data		$event		The event object
	 * @return void
	 * @access public
	 */
	public function dtst_submit_post_modify_sql_data($event)
	{
		$mode = $event['post_mode'];

		$dtst_location		= $event['data']['dtst_location'];
		$dtst_loc_custom	= $event['data']['dtst_loc_custom'];
		$dtst_host 			= $event['data']['dtst_host'];
		$dtst_date 			= $event['data']['dtst_date'];
		$dtst_event_type	= $event['data']['dtst_event_type'];
		$dtst_age_min		= $event['data']['dtst_age_min'];
		$dtst_age_max		= $event['data']['dtst_age_max'];
		$dtst_partecipants	= $event['data']['dtst_partecipants'];

		$data_sql = $event['sql_data'];

		/* Only applies to authed users */
		if ((bool) $this->dtst_utils->is_authed() && $this->dtst_utils->forum_dtst_enabled('forum_id', $data_sql[TOPICS_TABLE]['sql']['forum_id']))// $data_ary['forum_id']	?¿
		{
			if ( in_array($mode, array('post', 'edit_topic', 'edit_first_post')) )
			{
				$data_sql[TOPICS_TABLE]['sql']['dtst_location']		= $dtst_location;
				$data_sql[TOPICS_TABLE]['sql']['dtst_loc_custom']	= $dtst_loc_custom;
				$data_sql[TOPICS_TABLE]['sql']['dtst_host']			= $dtst_host;
				$data_sql[TOPICS_TABLE]['sql']['dtst_date']			= $dtst_date;
				$data_sql[TOPICS_TABLE]['sql']['dtst_event_type']	= $dtst_event_type;
				$data_sql[TOPICS_TABLE]['sql']['dtst_age_min']		= $dtst_age_min;
				$data_sql[TOPICS_TABLE]['sql']['dtst_age_max']		= $dtst_age_max;
				$data_sql[TOPICS_TABLE]['sql']['dtst_partecipants']	= $dtst_partecipants;
			}
		}

		$event['sql_data'] = $data_sql;
	}

	/**
	 * This event allows you to modify the page title of the viewtopic page
	 *
	 * @event core.viewtopic_modify_page_title
	 * @param \phpbb\event\data		$event		The event object
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
			'DTST_PARTECIPANTS'	=> $topic_data['dtst_partecipants'],
		));
	}

	/**
	 * This event is being used to modify the page title of the posting page
	 *
	 * @event core.modify_posting_auth
	 * @param \phpbb\event\data		$event		The event object
	 * @return void
	 * @access public
	 */
	public function dtst_topic_add_posting_reply($event)
	{
		$post_data = $event['post_data'];

		/* Add our language file only when needed */
		$this->lang->add_lang('common', 'phpbbstudio/dtst');

		$this->template->assign_vars(array(
			'DTST_LOCATION'		=> !empty($post_data['dtst_location']) ? $post_data['dtst_location'] : '',
			'DTST_LOC_CUSTOM'	=> !empty($post_data['dtst_loc_custom']) ? censor_text($post_data['dtst_loc_custom']) : '',
			'DTST_HOST'			=> !empty($post_data['dtst_host']) ? censor_text($post_data['dtst_host']) : '',
			'DTST_DATE'			=> !empty($post_data['dtst_date']) ? $post_data['dtst_date'] : '',
			'DTST_EVENT_TYPE'	=> !empty($post_data['dtst_event_type']) ? $this->dtst_utils->dtst_forum_id_to_name($post_data['dtst_event_type']) : '',
			'DTST_AGE_MIN'		=> !empty($post_data['dtst_age_min']) ? $post_data['dtst_age_min'] : 0,
			'DTST_AGE_MAX'		=> !empty($post_data['dtst_age_max']) ? $post_data['dtst_age_max'] : 0,
			'DTST_PARTECIPANTS'	=> !empty($post_data['dtst_partecipants']) ? $post_data['dtst_partecipants'] : 0,
		));
	}

	/**
	 * Modify the topic data before it is assigned to the template
	 *
	 * @event core.search_modify_tpl_ary
	 * @param \phpbb\event\data		$event		The event object
	 * @return void
	 * @access public
	 */
	public function dtst_search_modify_tpl_ary($event)
	{
		$row = $event['row'];

		/* We just need only one of those fields to be set to display the whole template */
		if ( $event['show_results'] == 'topics' && (!empty($row['dtst_location']) || !empty($row['dtst_host']) || !empty($row['dtst_date'])) )
		{
			/* Add our language file only when needed */
			$this->lang->add_lang('common', 'phpbbstudio/dtst');

			$tpl_array = $event['tpl_ary'];

			$tpl_array['DTST_LOCATION']		= $this->lang->lang('DTST_LOCATION') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_location'])) ? $row['dtst_location'] : '' );
			$tpl_array['DTST_LOC_CUSTOM']	= $this->lang->lang('DTST_LOC_CUSTOM') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_loc_custom'])) ? censor_text($row['dtst_loc_custom']) : '' );
			$tpl_array['DTST_HOST']			= $this->lang->lang('DTST_HOST') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_host'])) ? censor_text($row['dtst_host']) : '' );
			$tpl_array['DTST_DATE']			= $this->lang->lang('DTST_DATE') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_date'])) ? $row['dtst_date'] : '' );
			$tpl_array['DTST_EVENT_TYPE']	= $this->lang->lang('DTST_EVENT_TYPE') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_event_type'])) ? $this->dtst_utils->dtst_forum_id_to_name($row['dtst_event_type']) : '' );
			$tpl_array['DTST_AGE_MIN']		= $this->lang->lang('DTST_AGE_MIN') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_age_min'])) ? $row['dtst_age_min'] : $this->lang->lang('DTST_AGE_RANGE_NO') );
			$tpl_array['DTST_AGE_MAX']		= $this->lang->lang('DTST_AGE_MAX') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_age_max'])) ? $row['dtst_age_max'] : $this->lang->lang('DTST_AGE_RANGE_NO') );
			$tpl_array['DTST_PARTECIPANTS']	= $this->lang->lang('DTST_PARTECIPANTS') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_partecipants'])) ? $row['dtst_partecipants'] : $this->lang->lang('DTST_UNLIMITED') );

			$event['tpl_ary'] = $tpl_array;
		}
	}

	/**
	 * Modify the topic data before it is assigned to the template and in MCP
	 *
	 * @event core.viewforum_modify_topicrow
	 * @event core.mcp_view_forum_modify_topicrow
	 * @param \phpbb\event\data		$event		The event object
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
			$topic_row['DTST_DATE']			= $this->lang->lang('DTST_DATE') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_date'])) ? $row['dtst_date'] : '' );
			$topic_row['DTST_EVENT_TYPE']	= $this->lang->lang('DTST_EVENT_TYPE') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_event_type'])) ? $this->dtst_utils->dtst_forum_id_to_name($row['dtst_event_type']) : '' );
			$topic_row['DTST_AGE_MIN']		= $this->lang->lang('DTST_AGE_MIN') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_age_min'])) ? $row['dtst_age_min'] : $this->lang->lang('DTST_AGE_RANGE_NO') );
			$topic_row['DTST_AGE_MAX']		= $this->lang->lang('DTST_AGE_MAX') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_age_max'])) ? $row['dtst_age_max'] : $this->lang->lang('DTST_AGE_RANGE_NO') );
			$topic_row['DTST_PARTECIPANTS']	= $this->lang->lang('DTST_PARTECIPANTS') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_partecipants'])) ? $row['dtst_partecipants'] : $this->lang->lang('DTST_UNLIMITED') );

			$event['topic_row'] = $topic_row;
		}
	}

	/**
	 * (Add/update actions) - Submit form
	 *
	 * @event core.acp_manage_forums_request_data
	 * @param \phpbb\event\data		$event		The event object
	 * @return void
	 * @access public
	 */
	public function dtst_acp_manage_forums_request_data($event)
	{
		$forum_data = $event['forum_data'];

		$forum_data['dtst_f_enable']		= $this->request->variable('dtst_f_enable', 0);
		$forum_data['dtst_f_forced_fields']	= $this->request->variable('dtst_f_forced_fields', 0);

		$event['forum_data'] = $forum_data;
	}

	/**
	 * New Forums added (default disabled)
	 *
	 * @event core.acp_manage_forums_initialise_data
	 * @param \phpbb\event\data		$event		The event object
	 * @return void
	 * @access public
	 */
	public function dtst_acp_manage_forums_initialise_data($event)
	{
		if ($event['action'] == 'add')
		{
			$forum_data = $event['forum_data'];

			$forum_data['dtst_f_enable']		= (bool) false;
			$forum_data['dtst_f_forced_fields']	= (bool) false;

			$event['forum_data'] = $forum_data;
		}
	}

	/**
	 * ACP forums (template data)
	 *
	 * @event core.acp_manage_forums_display_form
	 * @param \phpbb\event\data		$event		The event object
	 * @return void
	 * @access public
	 */
	public function dtst_acp_manage_forums_display_form($event)
	{
		$template_data = $event['template_data'];

		$template_data['S_DTST_F_ENABLE']			= $event['forum_data']['dtst_f_enable'];
		$template_data['S_DTST_F_FORCED_FIELDS']	= $event['forum_data']['dtst_f_forced_fields'];

		$event['template_data'] = $template_data;
	}
}
