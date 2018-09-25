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
 * Date Topic Event Calendar ACP listener.
 */
class acp_listener implements EventSubscriberInterface
{
	/** @var \phpbb\request\request */
	protected $request;

	/**
	 * Constructor.
	 *
	 * @param  \phpbb\request\request				$request		Request object
	 * @access public
	 */
	public function __construct(\phpbb\request\request $request)
	{
		$this->request		= $request;
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
			'core.acp_manage_forums_request_data'		=> 'dtst_acp_manage_forums_request_data',
			'core.acp_manage_forums_initialise_data'	=> 'dtst_acp_manage_forums_initialise_data',
			'core.acp_manage_forums_display_form'		=> 'dtst_acp_manage_forums_display_form',
		);
	}

	/**
	 * (Add/update actions) - Submit form.
	 *
	 * @event	core.acp_manage_forums_request_data
	 * @param	\phpbb\event\data						$event		The event object
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
	 * New Forums added (default disabled).
	 *
	 * @event  core.acp_manage_forums_initialise_data
	 * @param  \phpbb\event\data		$event		The event object
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
	 * ACP forums (template data).
	 *
	 * @event  core.acp_manage_forums_display_form
	 * @param  \phpbb\event\data		$event		The event object
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
