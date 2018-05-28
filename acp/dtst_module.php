<?php
/**
 *
 * Date Topic Event Calendar. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, phpBB Studio, https://www.phpbbstudio.com
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace phpbbstudio\dtst\acp;

/**
 * Date Topic Event Calendar ACP module.
 */
class dtst_module
{
	public $page_title;
	public $tpl_name;
	public $u_action;

	/**
	 * @param $id
	 * @param $mode
	 */
	public function main($id, $mode)
	{
		global $phpbb_container;

		$config = $phpbb_container->get('config');
		$config_text = $phpbb_container->get('config_text');
		$language = $phpbb_container->get('language');
		$phpbb_log = $phpbb_container->get('log');
		$request = $phpbb_container->get('request');
		$template = $phpbb_container->get('template');
		$user = $phpbb_container->get('user');

		$dtst_utils = $phpbb_container->get('phpbbstudio.dtst.dtst_utils');

		/* Add our lang file */
		$language->add_lang('acp_dtst', 'phpbbstudio/dtst');

		/**
		* Mode Locationss
		*/
		if ($mode === 'locations')
		{
			/* Set the template file */
			$this->tpl_name = 'dtst_locations';

			/* Set the page title */
			$this->page_title = $language->lang('ACP_DTST_TITLE');

			/* Request the action */
			$action = $request->variable('action', '');

			/* Do this now and forget */
			$errors = array();

			/* Add a form key for security */
			add_form_key('phpbbstudio_dtst_add');

			switch ($action)
			{
				case 'add':
					if ($submit = $request->is_set_post('forum_dtst_preset_location_addition'))
					{
						/* Pull the array from the DB or cast it if null */
						$forum_dtst_preset_location = (array) $dtst_utils->dtst_json_decode_locations();

						/* Get locations values from the form */
						$forum_dtst_preset_location_add[] = trim($request->variable('forum_dtst_preset_location_add', '', true));

						/* Convert to string the array first */
						$string = implode(" ", $forum_dtst_preset_location_add);

						/* No Emojis */
						$string = preg_replace('/[\x{10000}-\x{10FFFF}]/u', "", $string);

						/**
						 * The first element of the array can not be overwritten despite is empty
						 * Search through the array if the user input already exist
						 */
						if (in_array($string, $forum_dtst_preset_location))
						{
							$errors[] = $language->lang('ACP_DTST_ERR_LOCATION_EXISTS');
						}

						/**
						 * Check the number of max chars allowed.
						 * https://en.wikipedia.org/wiki/List_of_long_place_names
						 */
						if (utf8_strlen($string) >= 86)
						{
							$errors[] = $language->lang('ACP_DTST_ERR_LONG_LOCATION');
						}

						/**
						 * Check the number of min chars allowed.
						 * https://en.wikipedia.org/wiki/List_of_short_place_names
						 */
						if (utf8_strlen($string) < 1)
						{
							$errors[] = $language->lang('ACP_DTST_ERR_SHORT_LOCATION');
						}

						/* Check the form key for security */
						if (!check_form_key('phpbbstudio_dtst_add'))
						{
							$errors[] = $language->lang('FORM_INVALID');
						}

						/* No errors? Great, let's go. */
						if (!count($errors))
						{
							/* No Emojis for Logs */
							$forum_dtst_preset_location_add = preg_replace('/[\x{10000}-\x{10FFFF}]/u', "", $forum_dtst_preset_location_add);

							/* Correctly adds the new location at the end of the list, to be sorted later */
							$config_text->set('dtst_locations', json_encode(array_merge($forum_dtst_preset_location, $forum_dtst_preset_location_add)));

							/* Log the action */
							$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DTST_LOG_LOCATION_ADDED', time(), $forum_dtst_preset_location_add);

							/* Show success message */
							trigger_error($language->lang('ACP_DTST_LOCATION_ADDED') . adm_back_link($this->u_action));
						}
					}
				break;

				case 'remove':
					if (confirm_box(true))
					{
						/* Pull the array from the DB or cast it if null */
						$forum_dtst_preset_locations = (array) $dtst_utils->dtst_json_decode_locations();

						/* Request the location from the hidden fields */
						$forum_dtst_preset_location_rem[] = trim($request->variable('location', '', true));

						/* No Emojis for Logs */
						$forum_dtst_preset_location_rem = preg_replace('/[\x{10000}-\x{10FFFF}]/u', "", $forum_dtst_preset_location_rem);

						/* Convert to string the array first */
						$string = implode(" ", $forum_dtst_preset_location_rem);

						/* Search through the array if the user input already exist */
						if (in_array($string, $forum_dtst_preset_locations))
						{
							/* Remove the element and reindex the numerical array as it should be */
							$forum_dtst_preset_locations = array_merge(array_diff($forum_dtst_preset_locations, $forum_dtst_preset_location_rem));
						}

						/* Correctly encode back our array and store it */
						$config_text->set('dtst_locations', json_encode($forum_dtst_preset_locations));

						/* Log the action */
						$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DTST_LOG_LOCATION_REMOVED', time(), $forum_dtst_preset_location_rem);

						/* If the request/action is AJAX */
						if ($request->is_ajax())
						{
							/* Set up a JSON response */
							$json_response = new \phpbb\json_response();

							/* Send a JSON response */
							$json_response->send(array(
								'MESSAGE_TITLE'	=> $language->lang('INFORMATION'),
								'MESSAGE_TEXT'	=> $language->lang('ACP_DTST_LOCATION_REMOVED'),
								'REFRESH_DATA'	=> array(
									'url'	=> '',
									'time'	=> 3,
								),
							));
						}

						/* Show success message when not using AJAX */
						trigger_error($language->lang('ACP_DTST_LOCATION_REMOVED') . adm_back_link($this->u_action));
					}
					else
					{
						/* Get locations values from the form */
						$forum_dtst_preset_location_rem = trim($request->variable('forum_dtst_preset_location_rem', '', true));

						/* The first element of the array can not be deleted */
						if (empty($forum_dtst_preset_location_rem))
						{
							/* Add it to the errors array */
							$errors[] = $language->lang('ACP_DTST_ERR_LOCATION_EMPTY');

							/* If the request/action is AJAX */
							if ($request->is_ajax())
							{
								/* Set up a new JSON response */
								$json_response = new \phpbb\json_response();

								/* Send a JSON response */
								$json_response->send(array(
									'MESSAGE_TITLE'	=> $language->lang('ERROR'),
									'MESSAGE_TEXT'	=> $language->lang('ACP_DTST_ERR_LOCATION_EMPTY'),
								));
							}
						}

						confirm_box(false, 'ACP_REMOVE_PRESET_LOCATION', build_hidden_fields(array(
							'action'	=> $action,
							'location'	=> $forum_dtst_preset_location_rem,
						)));

						/* Redirect if confirm box is cancelled ('No'). */
						redirect($this->u_action);
					}
				break;
			}

			$template->assign_vars(array(
				'S_ERRORS'				=> ($errors) ? true : false,
				'ERRORS_MSG'			=> implode('<br /><br />', $errors),

				'U_ACTION_ADD'			=> $this->u_action . '&action=add',
				'U_ACTION_REMOVE'		=> $this->u_action . '&action=remove',

				/* The function is already "htmlspecialchars_decode"'d */
				'DTST_LOCATION'			=> $dtst_utils->dtst_location_preset_select(),
			));
		}

		/**
		* Mode Settings
		*/
		if ($mode === 'settings')
		{
			/* Set the template file */
			$this->tpl_name = 'dtst_settings';

			/* Set the page title */
			$this->page_title = $language->lang('ACP_DTST_TITLE');

			/* Request the action */
			$action = $request->variable('action', '');

			/* Do this now and forget */
			$errors = array();

			/* Add a form key for security */
			add_form_key('phpbbstudio_dtst_settings');

			if ($request->is_set_post('submit'))
			{
				if (!check_form_key('phpbbstudio_dtst_settings'))
				{
					trigger_error('FORM_INVALID', E_USER_WARNING);
				}

				/* No errors? Great, let's go. */
				if (!count($errors))
				{
					$config->set('dtst_locked_withdrawal', $request->variable('dtst_locked_withdrawal', (int) $config['dtst_locked_withdrawal']));

					/* Log the action and return */
					$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DTST_LOG_SETTINGS_SAVED');
					trigger_error($user->lang('DTST_LOG_SETTINGS_SAVED') . adm_back_link($this->u_action));
				}
			}

			$template->assign_vars(array(
				'S_ERRORS'				=> ($errors) ? true : false,
				'ERRORS_MSG'			=> implode('<br /><br />', $errors),
				'U_ACTION'				=> $this->u_action,

				'ACP_SETTINGS_WITHDRAWAL_IF_LOCKED'		=> (bool) $config['dtst_locked_withdrawal'],
			));
		}
	}
}
