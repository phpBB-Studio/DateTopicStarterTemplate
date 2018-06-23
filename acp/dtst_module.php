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

use phpbbstudio\dtst\ext;

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
		global $phpbb_container, $table_prefix;

		$auth = $phpbb_container->get('auth');
		$config = $phpbb_container->get('config');
		$config_text = $phpbb_container->get('config_text');
		$db = $phpbb_container->get('dbal.conn');
		$language = $phpbb_container->get('language');
		$phpbb_log = $phpbb_container->get('log');
		$request = $phpbb_container->get('request');
		$template = $phpbb_container->get('template');
		$user = $phpbb_container->get('user');

		$dtst_utils = $phpbb_container->get('phpbbstudio.dtst.dtst_utils');
		$dtst_privmsg = $table_prefix . 'dtst_privmsg';

		$phpbb_root_path = $phpbb_container->getParameter('core.root_path');
		$php_ext = $phpbb_container->getParameter('core.php_ext');

		/* Add our lang file */
		$language->add_lang('acp_dtst', 'phpbbstudio/dtst');

		/**
		* Mode Locations
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
						$forum_dtst_preset_location_add[] = $request->variable('forum_dtst_preset_location_add', '', true);

						/* Convert to string the array first */
						$string = implode(" ", $forum_dtst_preset_location_add);

						/* No Emojis */
						$string = $dtst_utils->dtst_strip_emojis($string);

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
							$forum_dtst_preset_location_add = $dtst_utils->dtst_strip_emojis($forum_dtst_preset_location_add);

							/* Correctly adds the new location at the end of the list, to be sorted later */
							$config_text->set('dtst_locations', json_encode(array_merge($forum_dtst_preset_location, $forum_dtst_preset_location_add)));

							/* Log the action */
							$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DTST_LOG_LOCATION_ADDED', time(), $forum_dtst_preset_location_add);

							/* Show success message */
							trigger_error($language->lang('ACP_DTST_LOCATION_ADDED') . adm_back_link($this->u_action));
						}
					}
				break;

				case 'remove_all':
					if (confirm_box(true))
					{
						/* Sort of "truncate table" o_O */
						$forum_dtst_preset_locations = array("",);

						/* Correctly encode back our array and store it */
						$config_text->set('dtst_locations', json_encode($forum_dtst_preset_locations));

						/* Log the action */
						$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DTST_LOG_ALL_LOCATIONS_REMOVED', time());

						/* If the request/action is AJAX */
						if ($request->is_ajax())
						{
							/* Set up a JSON response */
							$json_response = new \phpbb\json_response();

							/* Send a JSON response */
							$json_response->send(array(
								'MESSAGE_TITLE'	=> $language->lang('INFORMATION'),
								'MESSAGE_TEXT'	=> $language->lang('ACP_DTST_ALL_LOCATIONS_REMOVED'),
								'REFRESH_DATA'	=> array(
									'url'	=> '',
									'time'	=> 3,
								),
							));
						}

						/* Show success message when not using AJAX */
						trigger_error($language->lang('ACP_DTST_ALL_LOCATIONS_REMOVED') . adm_back_link($this->u_action));
					}
					else
					{
						confirm_box(false, $user->lang['ACP_REMOVE_PRESET_LOCATIONS_CONFIRM'], build_hidden_fields(array(
							'action'	=> $action))
						);

						/* Redirect if confirm box is cancelled ('No'). */
						redirect($this->u_action);
					}
				break;

				case 'remove':
					if (confirm_box(true))
					{
						/* Pull the array from the DB or cast it if null */
						$forum_dtst_preset_locations = (array) $dtst_utils->dtst_json_decode_locations();

						/* Request the location from the hidden fields */
						$forum_dtst_preset_location_rem[] = $request->variable('location', '', true);

						/* No Emojis for Logs */
						$forum_dtst_preset_location_rem = $dtst_utils->dtst_strip_emojis($forum_dtst_preset_location_rem);

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
						$forum_dtst_preset_location_rem = $request->variable('forum_dtst_preset_location_rem', '', true);

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
				'U_ACTION_REMOVE_ALL'	=> $this->u_action . '&action=remove_all',
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
					$config->set('dtst_sidebar', $request->variable('dtst_sidebar', (int) $config['dtst_sidebar']));

					/* (INT) User ID of the Bot */
					$config->set('dtst_bot', $request->variable('dtst_bot', (int) $config['dtst_bot']));

					/* (BOOL) Use of the Bot - Yes/No */
					$config->set('dtst_use_bot', $request->variable('dtst_use_bot', (int) $config['dtst_use_bot']));

					/* Log the action and return */
					$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DTST_LOG_SETTINGS_SAVED');
					trigger_error($user->lang('ACP_DTST_SETTING_SAVED') . adm_back_link($this->u_action));
				}
			}

			$template->assign_vars(array(
				'S_ERRORS'						=> ($errors) ? true : false,
				'ERRORS_MSG'					=> implode('<br /><br />', $errors),
				'U_ACTION'						=> $this->u_action,

				'DTST_WITHDRAWAL_IF_LOCKED'		=> (bool) $config['dtst_locked_withdrawal'],
				'DTST_SIDEBAR'					=> (bool) $config['dtst_sidebar'],
				// PMS Bot
				'DTST_BOT'						=> $dtst_utils->dtst_bot_select((int) $config['dtst_bot']),
				'DTST_USE_BOT'					=> (bool) $config['dtst_use_bot'],
			));
		}

		/**
		* Mode PMs
		*/
		if ($mode === 'privmsg')
		{
			/* Set the template file */
			$this->tpl_name = 'dtst_privmsg';

			/* Set the page title */
			$this->page_title = $language->lang('ACP_DTST_TITLE');

			/* Request the action */
			$action = $request->variable('action', '');

			/* Do this now and forget */
			$errors = array();

			/* Add the lang file needed by BBCodes */
			$language->add_lang('posting');

			/* Include files needed for displaying BBCodes */
			if (!function_exists('display_custom_bbcodes'))
			{
				include $phpbb_root_path . 'includes/functions_display.' . $php_ext;
			}

			/* Include files needed for displaying Smilies */
			if (!function_exists('generate_smilies'))
			{
				include $phpbb_root_path . 'includes/functions_posting.' . $php_ext;
			}

			/* Add a form key for security */
			add_form_key('phpbbstudio_dtst_privmsg');

			/* Request variables to work with */
			$dtst_pm_status = $request->variable('dtst_pm_status', 0);
			$dtst_pm_isocode = $request->variable('dtst_pm_isocode', '', true);
			$dtst_pm_title = $request->variable('dtst_pm_title', '', true);
			$dtst_pm_message = $request->variable('dtst_pm_message', '', true);

			/**
			 * Drop down constructs
			 */
			$pm_statuses = array(
				ext::DTST_STATUS_PM_APPLY		=> 'application',
				ext::DTST_STATUS_PM_CANCEL		=> 'cancellation',
				ext::DTST_STATUS_PM_WITHDRAWAL	=> 'withdrawal',
			);

			/* Set var */
			$pm_status_options = '';

			foreach ($pm_statuses as $val => $pm_status)
			{
				$pm_status_options .= '<option value="' . $val . '"' . (($val == $dtst_pm_status) ? ' selected="selected"' : '') . '>';
				$pm_status_options .= $language->lang('ACP_DTST_' . strtoupper($pm_status));
				$pm_status_options .= '</option>';
			}

			/**
			 * Returns a list of languages from the DB, those installed
			 * Config [ default_lang ] could be the fall-back in case
			 */
			$sql = 'SELECT lang_id, lang_iso, lang_local_name
					FROM ' . LANG_TABLE . '
					ORDER BY lang_id';
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$lang_isos[$row['lang_iso']] = $row['lang_local_name'];
			}
			$db->sql_freeresult($result);

			/* Set vars */
			$pm_lang_options = '';

			foreach ($lang_isos as $key => $value)
			{
				$selected = ($key === $dtst_pm_isocode) ? ' selected="selected"' : '';
				$pm_lang_options .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
			}

			/* Update the pm title and message */
			if ($action === 'update' && $request->is_ajax())
			{
				/* Query our PMs table */
				$sql = 'SELECT *
						FROM ' . $dtst_privmsg . '
						WHERE dtst_pm_status = ' . (int) $dtst_pm_status . '
							AND dtst_pm_isocode = "' . $db->sql_escape($dtst_pm_isocode) . '"';
				$result = $db->sql_query($sql);
				$pm = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$response = !empty($pm) ? array_change_key_case($pm, CASE_UPPER) : array('DTST_NO_PM' => true);

				$json_response = new \phpbb\json_response;
				$json_response->send($response);
			}

			if ($request->is_set_post('submit'))
			{
				if (!check_form_key('phpbbstudio_dtst_privmsg'))
				{
					trigger_error('FORM_INVALID', E_USER_WARNING);
				}

				if (!$dtst_pm_status)
				{
					$errors[] = $language->lang('ACP_DTST_PM_STATUS_NONE');
				}
				if (empty($dtst_pm_isocode))
				{
					$errors[] = $language->lang('ACP_DTST_PM_ISOCODE_NONE');
				}
				if (empty($dtst_pm_title))
				{
					$errors[] = $language->lang('ACP_DTST_PM_TITLE_EMPTY');
				}
				if (utf8_strlen($dtst_pm_title >= 255))
				{
					$error[] = $language->lang('ACP_DTST_PM_TITLE_LONG');
				}
				if (empty($dtst_pm_message))
				{
					$errors[] = $language->lang('ACP_DTST_PM_MESSAGE_EMPTY');
				}

				/* No errors? Great, let's go. */
				if (!count($errors))
				{
					/* Query our PMs table */
					$sql = 'SELECT *
							FROM ' . $dtst_privmsg . '
							WHERE dtst_pm_status = ' . (int) $dtst_pm_status . '
								AND dtst_pm_isocode = "' . $db->sql_escape($dtst_pm_isocode) . '"';
					$result = $db->sql_query($sql);
					$row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					/* No Emojis */
					$dtst_pm_message = $dtst_utils->dtst_strip_emojis($dtst_pm_message);
					$dtst_pm_title = $dtst_utils->dtst_strip_emojis($dtst_pm_title);

					/* If this PM does not exist let's create it */
					if (!$row)
					{
						$pm_sql = array(
							'dtst_pm_isocode'			=> $dtst_pm_isocode,
							'dtst_pm_status'			=> $dtst_pm_status,
							'dtst_pm_message' 			=> $dtst_pm_message,
							'dtst_pm_title'				=> $dtst_pm_title,
						);

						$sql = 'INSERT INTO ' . $dtst_privmsg . '
							' . $db->sql_build_array('INSERT', $pm_sql);
						$db->sql_query($sql);
					}
					else
					{
						/* Let's update the PM */
						$pm_sql = array(
							'dtst_pm_isocode'			=> $dtst_pm_isocode,
							'dtst_pm_status'			=> $dtst_pm_status,
							'dtst_pm_message' 			=> $dtst_pm_message,
							'dtst_pm_title'				=> $dtst_pm_title,
						);

						$sql = 'UPDATE ' . $dtst_privmsg . '
							SET ' . $db->sql_build_array('UPDATE', $pm_sql) . '
							WHERE dtst_pm_id = ' . (int) $row['dtst_pm_id'];
						$db->sql_query($sql);
					}

					/* Log the action and return */
					$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DTST_LOG_PRIVMSG_SAVED');
					trigger_error($user->lang('ACP_DTST_PRIVMSG_SAVED') . adm_back_link($this->u_action));
				}
			}

			/**
			 * Simply copied from phpBB's compose PM
			  *
			 * @var \phpbb\controller\helper $controller_helper
			 */
			$controller_helper = $phpbb_container->get('controller.helper');

			$bbcode_status	= ($config['allow_bbcode'] && $config['auth_bbcode_pm'] && $auth->acl_get('u_pm_bbcode')) ? true : false;
			$smilies_status	= ($config['allow_smilies'] && $config['auth_smilies_pm'] && $auth->acl_get('u_pm_smilies')) ? true : false;
			$img_status		= ($config['auth_img_pm'] && $auth->acl_get('u_pm_img')) ? true : false;
			$flash_status	= ($config['auth_flash_pm'] && $auth->acl_get('u_pm_flash')) ? true : false;
			$url_status		= ($config['allow_post_links']) ? true : false;

			/* Check if we're previewing PM's */
			$preview = $request->is_set_post('preview');

			if ($preview)
			{
				/* Add the UCP language */
				$user->add_lang('ucp');

				/* Get the text formatters */
				$renderer	= $phpbb_container->get('text_formatter.renderer');
				$parser		= $phpbb_container->get('text_formatter.parser');
				$utils		= $phpbb_container->get('text_formatter.utils');

				/* No Emojis - It doesn't hurt in preview but for the sake of code/overview consistency */
				$dtst_pm_title = $dtst_utils->dtst_strip_emojis($dtst_pm_title);
				$dtst_pm_message = $dtst_utils->dtst_strip_emojis($dtst_pm_message);

				/* It doesn't hurt in preview but for the sake of code/overview consistency */
				$preview_ttl	= htmlspecialchars_decode($dtst_pm_title, ENT_COMPAT);
				$preview_msg	= htmlspecialchars_decode($dtst_pm_message, ENT_COMPAT);

				/* Set up parser settings */
				$bbcode_status ? $parser->enable_bbcodes() : $parser->disable_bbcodes();
				$smilies_status ? $parser->enable_smilies() : $parser->disable_smilies();
				$img_status ? $parser->enable_bbcode('img') : $parser->disable_bbcode('img');
				$flash_status ? $parser->enable_bbcode('flash') : $parser->disable_bbcode('flash');
				$url_status ? $parser->enable_magic_url() : $parser->disable_magic_url();

				/* Parse the message */
				$preview_message = $parser->parse($preview_msg);

				/* Set up unparsed message for edit */
				$preview_message_edit = $utils->unparse($preview_message);

				/* Set up rendered message for display */
				$preview_message_show = $renderer->render($preview_message);

				/* If a sender-bot has been set let's use its user-settings to display in the PM preview */
				if ($config['dtst_use_bot'])
				{
					/* Pull the data for $config['dtst_bot'] */
					$bot_data = $dtst_utils->dtst_sql_users((int) $config['dtst_bot']);
				}
				else
				{
					/* else display the current Admin users data */
					$bot_data = $user->data;
				}

				/* Set up sender's username strings */
				$sender_name = get_username_string('username', $bot_data['user_id'], $bot_data['username'], $bot_data['user_colour']);
				$sender_full = get_username_string('full', $bot_data['user_id'], $bot_data['username'], $bot_data['user_colour']);

				/* Set up tokens */
				$tokens = array(
					'{SENDER_NAME}',
					'{SENDER_NAME_FULL}',
				);

				/* Set up token replacements */
				$token_replacements = array(
					$sender_name,
					$sender_full,
				);

				/* Replace tokens */
				$preview_title	= str_replace(array($tokens[0], $tokens[1]), array($sender_name, $sender_name), $preview_ttl);
				$preview_text	= str_replace($tokens, $token_replacements, $preview_message_show);

				/* Ensure visive consistency in preview */
				$preview_title	= htmlspecialchars_decode($preview_title, ENT_COMPAT);
				$preview_text	= htmlspecialchars_decode($preview_text, ENT_COMPAT);

				if ($bot_data['user_sig'])
				{
					$parse_flags = ($bot_data['user_sig_bbcode_bitfield'] ? OPTION_FLAG_BBCODE : 0) | OPTION_FLAG_SMILIES;
					$preview_sig = generate_text_for_display($bot_data['user_sig'], $bot_data['user_sig_bbcode_uid'], $bot_data['user_sig_bbcode_bitfield'], $parse_flags, true);
				}
				else
				{
					$preview_sig = false;
				}
			}

			/* Set up default values, to not end up with an empty textarea even though the pm exists. */
			if (!$request->is_set_post('submit') && !$request->is_set_post('preview'))
			{
				/* Query our PMs table */
				$sql = 'SELECT *
						FROM ' . $dtst_privmsg . '
						WHERE dtst_pm_status = ' . (int) array_keys($pm_statuses)[0] . '
							AND dtst_pm_isocode = "' . $db->sql_escape(array_keys($lang_isos)[0]) . '"';
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$dtst_pm_title = !empty($row) ? $row['dtst_pm_title'] : '';
				$dtst_pm_message = !empty($row) ? $row['dtst_pm_message'] : '';
			}

			$template->assign_vars(array(
				'S_ERRORS'					=> ($errors) ? true : false,
				'ERRORS_MSG'				=> implode('<br /><br />', $errors),
				'U_ACTION'					=> $this->u_action,

				'S_DTST_PM_MODE'			=> $pm_status_options,
				'S_DTST_PM_LANGS'			=> $pm_lang_options,

				// PMs
				'DTST_PM_TITLE'				=> htmlspecialchars_decode($dtst_pm_title, ENT_COMPAT),
				'DTST_PM_MESSAGE'			=> htmlspecialchars_decode($dtst_pm_message, ENT_COMPAT),

				'U_MORE_SMILIES'			=> append_sid("{$phpbb_root_path}posting.$php_ext", 'mode=smilies'),

				'BBCODE_STATUS'				=> $language->lang(($bbcode_status ? 'BBCODE_IS_ON' : 'BBCODE_IS_OFF'), '<a href="' . $controller_helper->route('phpbb_help_bbcode_controller') . '">', '</a>'),
				'IMG_STATUS'				=> ($img_status) ? $language->lang('IMAGES_ARE_ON') : $language->lang('IMAGES_ARE_OFF'),
				'FLASH_STATUS'				=> ($flash_status) ? $language->lang('FLASH_IS_ON') : $language->lang('FLASH_IS_OFF'),
				'SMILIES_STATUS'			=> ($smilies_status) ? $language->lang('SMILIES_ARE_ON') : $language->lang('SMILIES_ARE_OFF'),
				'URL_STATUS'				=> ($url_status) ? $language->lang('URL_IS_ON') : $language->lang('URL_IS_OFF'),

				'S_BBCODE_ALLOWED'			=> ($bbcode_status) ? 1 : 0,
				'S_SMILIES_ALLOWED'			=> $smilies_status,
				'S_LINKS_ALLOWED'			=> $url_status,
				'S_SHOW_SMILEY_LINK'		=> true,
				'S_BBCODE_IMG'				=> $img_status,
				'S_BBCODE_FLASH'			=> $flash_status,
				'S_BBCODE_QUOTE'			=> true,
				'S_BBCODE_URL'				=> $url_status,

				'DTST_PM_PREVIEW_SIG'		=> $preview ? $preview_sig : '',
				'DTST_PM_PREVIEW_TEXT'		=> $preview ? $preview_text : '',
				'DTST_PM_PREVIEW_TITLE'		=> $preview ? $preview_title : '',
				'DTST_PM_PREVIEW_TIME'		=> $user->format_date(time()),
				'DTST_PM_PREVIEW_USER'		=> $preview ? $sender_full : '',
				'S_DTST_PM_PREVIEW'			=> (bool) $preview,

				'U_DTST_UPDATE'				=> $this->u_action . '&action=update',
			));

			/* Assign tokens array loop */
			$tokens_ary = $language->lang_raw('dtst_tokens');

			foreach ($tokens_ary as $token => $explain)
			{
				$template->assign_block_vars('dtst_tokens', array(
					'TOKEN'		=> '{' . $token . '}',
					'EXPLAIN'	=> $explain,
				));
			}

			/* Build custom bbcodes array*/
			display_custom_bbcodes();

			/* Build smilies */
			generate_smilies('inline', 0);
		}
	}
}
