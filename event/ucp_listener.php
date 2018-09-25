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
 * Date Topic Event Calendar UCP listener.
 */
class ucp_listener implements EventSubscriberInterface
{
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

	/**
	 * Constructor.
	 *
	 * @param  \phpbb\request\request				$request		Request object
	 * @param  \phpbb\template\template				$template		Template object
	 * @param  \phpbb\user							$user			User object
	 * @param  \phpbb\language\language				$lang			Language object
	 * @param  \phpbbstudio\dtst\core\operator		$dtst_utils		Functions to be used by Classes
	 * @access public
	 */
	public function __construct(\phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, \phpbb\language\language $lang, \phpbbstudio\dtst\core\operator $dtst_utils)
	{
		$this->request		= $request;
		$this->template		= $template;
		$this->user			= $user;
		$this->lang			= $lang;
		$this->dtst_utils	= $dtst_utils;
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
			'core.ucp_prefs_personal_data'			=>	'dtst_ucp_prefs_data',
			'core.ucp_prefs_personal_update_data'	=>	'dtst_ucp_prefs_update_data',
		);
	}

	/**
	 * Add configuration to Board preferences in UCP.
	 *
	 * @event  core.ucp.prefs_personal_data
	 * @param  \phpbb\event\data		$event		The event object
	 * @return void
	 * @access public
	 */
	public function dtst_ucp_prefs_data($event)
	{
		/**
		 * Check permissions prior to run the code
		 */
		if ( (bool) $this->dtst_utils->is_authed() )
		{
			/* Includes specified language only in UCP */
			$this->lang->add_lang('ucp_common', 'phpbbstudio/dtst');

			/* Collects the user decision */
			$dtst_auto_reload = $this->request->variable('dtst_auto_reload', (bool) $this->user->data['dtst_auto_reload']);

			/* Merges the above decision into the already existing array */
			$event['data'] = array_merge($event['data'], [
				'dtst_auto_reload'		=> $dtst_auto_reload,
			]);

			/* Send to template */
			$this->template->assign_vars([
				'S_DTST_AUTO_RELOAD'		=> $dtst_auto_reload,
				'S_DTST_UCP_PERMS'			=> (bool) $this->dtst_utils->is_authed(),
			]);
		}
	}

	/**
	 * Updates configuration to Board preferences in UCP.
	 *
	 * @event  core.ucp_prefs_personal_update_data
	 * @param  \phpbb\event\data		$event		The event object
	 * @return void
	 * @access public
	 */
	public function dtst_ucp_prefs_update_data($event)
	{
		/**
		 * Check permissions prior to run the code
		 */
		if ( (bool) $this->dtst_utils->is_authed() )
		{
			$event['sql_ary'] = array_merge($event['sql_ary'], [
				'dtst_auto_reload'		=> $event['data']['dtst_auto_reload'],
			]);
		}
	}
}
