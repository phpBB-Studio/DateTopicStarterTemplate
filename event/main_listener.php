<?php
/**
 *
 * Date Topic Starter Template. An extension for the phpBB Forum Software package.
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
 * Date Topic Starter Template Event listener.
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
	 * Modify the page's data before it is assigned to the template
	 *
	 * @event core.posting_modify_template_vars
	 * @param \phpbb\event\data		$event		The event object
	 * @return void
	 * @access public
	 */
	public function dtst_topic_data_topic($event)
	{
		/* Check if Date Topic Starter Template is enabled for this forum */
		if ($this->dtst_utils->forum_dtst_enabled('forum_id', $event['forum_id']))
		{
			$mode = $event['mode'];
			$post_data = $event['post_data'];
			$page_data = $event['page_data'];

			/* Return the Forum Location if Date Topic Starter Template is enabled for this forum */
			$forum_dtst_location = $this->dtst_utils->forum_dtst_location('dtst_f_location', $event['forum_id']);

			$post_data['dtst_location'] = (!empty($post_data['dtst_location'])) ? $post_data['dtst_location'] : $forum_dtst_location;
			$post_data['dtst_host']		= (!empty($post_data['dtst_host'])) ? $post_data['dtst_host'] : $this->user->data['username'];
			$post_data['dtst_date'] 	= (!empty($post_data['dtst_date'])) ? $post_data['dtst_date'] : '';

			/* Check if we are posting or editing the very first post of the topic */
			if ( $mode == 'post' || ($mode == 'edit' && $post_data['topic_first_post_id'] == $post_data['post_id']) )
			{
				/* Add our language file only when needed */
				$this->lang->add_lang('common', 'phpbbstudio/dtst');

				/* Add our template vars */
				$page_data['DTST_LOCATION']			= $this->request->variable('dtst_location', $post_data['dtst_location'], true);
				$page_data['DTST_HOST']				= $this->request->variable('dtst_host', $post_data['dtst_host'], true);
				$page_data['DTST_DATE']				= $this->request->variable('dtst_date', $post_data['dtst_date'], true);
				/* Add our placeholders */
				$page_data['DTST_HOST_HOLDER']		= $this->user->data['username'];
				$page_data['DTST_LOCATION_HOLDER']	= $forum_dtst_location;

				/* Template switch */
				$page_data['S_DTST_TOPIC'] = true;
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
		/* Check if Date Topic Starter Template is enabled for this forum */
		if ( $this->dtst_utils->forum_dtst_enabled('forum_id', $event['forum_id']) )
		{
			/* Check if the fields are all mandatory for this forum */
			if ( $this->dtst_utils->forum_dtst_forced_fields('dtst_f_forced_fields', $event['forum_id']) )
			{
				/* All fields are mandatory, we check that on submit */
				$error = $event['error'];

				/* Add our errors language file only if needed */
				$this->lang->add_lang('common_errors', 'phpbbstudio/dtst');

				if (!$event['post_data']['dtst_location'])
				{
					$error[] = $this->lang->lang('DTST_LOCATION_MISSING');
				}

				if (!$event['post_data']['dtst_host'])
				{
					$error[] = $this->lang->lang('DTST_HOST_MISSING');
				}

				if (!$event['post_data']['dtst_date'])
				{
					$error[] = $this->lang->lang('DTST_DATE_MISSING');
				}

				$event['error'] = $error;
			}

			/**
			 * No errors? Let's party :-D
			 *
			 * Emojis will be stripped away.
			 */
			$event['post_data']	= array_merge($event['post_data'], array(
					'dtst_location'	=> trim(preg_replace('/[\x{10000}-\x{10FFFF}]/u', "", $this->request->variable('dtst_location', '', true))),
					'dtst_host'		=> trim(preg_replace('/[\x{10000}-\x{10FFFF}]/u', "", $this->request->variable('dtst_host', '', true))),
					'dtst_date'		=> trim(preg_replace('/[\x{10000}-\x{10FFFF}]/u', "", $this->request->variable('dtst_date', '', true))),
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
		/* Check if Date Topic Starter Template is enabled for this forum */
		if ( $this->dtst_utils->forum_dtst_enabled('forum_id', $event['forum_id']) )
		{
			$event['data'] = array_merge($event['data'], array(
				'dtst_location'	=> $event['post_data']['dtst_location'],
				'dtst_host'		=> $event['post_data']['dtst_host'],
				'dtst_date'		=> $event['post_data']['dtst_date'],
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
		/* Check if Date Topic Starter Template is enabled for this forum */
		if ( $this->dtst_utils->forum_dtst_enabled('forum_id', $event['forum_id']) )
		{
			$event['post_data']	= array_merge($event['post_data'], array(
				'dtst_location'	=> $this->request->variable('dtst_location', ( (!empty($event['post_data']['dtst_location'])) ? $event['post_data']['dtst_location'] : '' ), true),
				'dtst_host'		=> $this->request->variable('dtst_host', ( (!empty($event['post_data']['dtst_host'])) ? $event['post_data']['dtst_host'] : '' ), true),
				'dtst_date'		=> $this->request->variable('dtst_date', ( (!empty($event['post_data']['dtst_date'])) ? $event['post_data']['dtst_date'] : '' ), true),
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

		$dtst_location	= $event['data']['dtst_location'];
		$dtst_host 		= $event['data']['dtst_host'];
		$dtst_date 		= $event['data']['dtst_date'];

		$data_sql = $event['sql_data'];

		if ( in_array($mode, array('post', 'edit_topic', 'edit_first_post')) )
		{
			$data_sql[TOPICS_TABLE]['sql']['dtst_location']	= $dtst_location;
			$data_sql[TOPICS_TABLE]['sql']['dtst_host']		= $dtst_host;
			$data_sql[TOPICS_TABLE]['sql']['dtst_date']		= $dtst_date;
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
			'DTST_DATE'		=> $topic_data['dtst_date'],
			'DTST_HOST'		=> censor_text($topic_data['dtst_host']),
			'DTST_LOCATION'	=> censor_text($topic_data['dtst_location']),
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
			'DTST_DATE'		=> !empty($post_data['dtst_date']) ? $post_data['dtst_date'] : '',
			'DTST_HOST'		=> !empty($post_data['dtst_host']) ? censor_text($post_data['dtst_host']) : '',
			'DTST_LOCATION'	=> !empty($post_data['dtst_location']) ? censor_text($post_data['dtst_location']) : '',
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

			$tpl_array['DTST_LOCATION']	= $this->lang->lang('DTST_LOCATION') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_location'])) ? censor_text($row['dtst_location']) : '' );
			$tpl_array['DTST_HOST']		= $this->lang->lang('DTST_HOST') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_host'])) ? censor_text($row['dtst_host']) : '' );
			$tpl_array['DTST_DATE']		= $this->lang->lang('DTST_DATE') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_date'])) ? $row['dtst_date'] : '' );

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
		if ( !empty($row['dtst_location']) || !empty($row['dtst_host']) || !empty($row['dtst_date']) )
		{
			/* Add our language file only when needed */
			$this->lang->add_lang('common', 'phpbbstudio/dtst');

			$topic_row = $event['topic_row'];

			$topic_row['DTST_LOCATION']	= $this->lang->lang('DTST_LOCATION') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_location'])) ? censor_text($row['dtst_location']) : '' );
			$topic_row['DTST_HOST']		= $this->lang->lang('DTST_HOST') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_host'])) ? censor_text($row['dtst_host']) : '' );
			$topic_row['DTST_DATE']		= $this->lang->lang('DTST_DATE') . $this->lang->lang('COLON') . '&nbsp;' . ( (!empty($row['dtst_date'])) ? $row['dtst_date'] : '' );

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
		$forum_data['dtst_f_location']		= $this->request->variable('dtst_f_location', '', true);
		$forum_data['dtst_f_forced_fields']	= $this->request->variable('dtst_f_forced_fields', 0);

		$event['forum_data'] = $forum_data;
	}

	/**
	 * New Forums added (default enabled)
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

			$forum_data['dtst_f_enable']		= (int) 1;
			$forum_data['dtst_f_location']		= '';
			$forum_data['dtst_f_forced_fields']	= (int) 1;

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
		$template_data['DTST_F_LOCATION']			= $event['forum_data']['dtst_f_location'];
		$template_data['S_DTST_F_FORCED_FIELDS']	= $event['forum_data']['dtst_f_forced_fields'];

		$event['template_data'] = $template_data;
	}
}
