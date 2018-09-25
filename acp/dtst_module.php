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
		$dtst_ranks = $table_prefix . 'dtst_ranks';

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
						if (preg_match_all('/[\x{10000}-\x{10FFFF}]/u', $string, $matches))
						{
							$list = implode('<br>', $matches[0]);
							$errors[] = $language->lang('ACP_DTST_ERR_LOCATION_EMOJIS_SUPPORT', $list);
						}

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
							/* No Emojis for Logs, be sure. */
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
						confirm_box(false, $language->lang('ACP_REMOVE_PRESET_LOCATIONS_CONFIRM'), build_hidden_fields(array(
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
					trigger_error($language->lang('ACP_DTST_SETTING_SAVED') . adm_back_link($this->u_action));
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

			/* Query the langs table */
			$lang_isos = $dtst_utils->dtst_langs_sql();

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

				/* No Emojis */
				if (preg_match_all('/[\x{10000}-\x{10FFFF}]/u', $dtst_pm_title, $matches))
				{
					$list = implode('<br>', $matches[0]);
					$errors[] = $language->lang('ACP_DTST_ERR_PM_TITLE_EMOJIS_SUPPORT', $list);
				}
				if (preg_match_all('/[\x{10000}-\x{10FFFF}]/u', $dtst_pm_message, $matches))
				{
					$list = implode('<br>', $matches[0]);
					$errors[] = $language->lang('ACP_DTST_ERR_PM_MESSAGE_EMOJIS_SUPPORT', $list);
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

					/* No Emojis, extra layer.*/
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
					trigger_error($language->lang('ACP_DTST_PRIVMSG_SAVED') . adm_back_link($this->u_action));
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

				/* Ensure visible consistency in preview */
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

		/**
		* Mode LPR Settings
		*/
		if ($mode === 'lpr_settings')
		{
			/* Set the template file */
			$this->tpl_name = 'dtst_lpr_settings';

			/* Set the page title */
			$this->page_title = $language->lang('ACP_DTST_TITLE');

			/* Do this now and forget */
			$errors = array();

			/* Add a form key for security */
			add_form_key('phpbbstudio_dtst_lpr_settings');

			/**
			 * Drop down construct for reputation time
			 */
			$time_modes = array(
				ext::ONE_DAY	=> 'one_day',
				ext::TWO_DAYS	=> 'two_days',
				ext::THREE_DAYS	=> 'three_days',
				ext::ONE_WEEK	=> 'one_week',
				ext::TWO_WEEKS	=> 'two_weeks',
				ext::ONE_MONTH	=> 'one_month',
			);

			$host_time = $rep_time = '';

			foreach ($time_modes as $val => $time_mode)
			{
				$host_time	.= '<option value="' . $val . '"' . (($val == $config['dtst_host_time']) ? ' selected="selected"' : '') . '>';
				$rep_time	.= '<option value="' . $val . '"' . (($val == $config['dtst_rep_time']) ? ' selected="selected"' : '') . '>';

				$host_time	.= $language->lang('ACP_DTST_' . strtoupper($time_mode));
				$rep_time	.= $language->lang('ACP_DTST_' . strtoupper($time_mode));

				$host_time	.= '</option>';
				$rep_time	.= '</option>';
			}

			/**
			 * Let's see what happens if submit
			 */
			if ($request->is_set_post('submit'))
			{
				if (!check_form_key('phpbbstudio_dtst_lpr_settings'))
				{
					trigger_error('FORM_INVALID', E_USER_WARNING);
				}

				/* Emojis's  handling */
				if (preg_match_all('/[\x{10000}-\x{10FFFF}]/u', $request->variable('dtst_rep_name', '', true), $matches))
				{
					$list = implode('<br>', $matches[0]);
					$errors[] = $language->lang('ACP_DTST_ERR_REP_NAME_EMOJIS_SUPPORT', $list);
				}

				/* No errors? Great, let's go. */
				if (!count($errors))
				{
					$config->set('dtst_host_time', $request->variable('dtst_host_time', (int) $config['dtst_host_time']));// int
					$config->set('dtst_rep_time', $request->variable('dtst_rep_time', (int) $config['dtst_rep_time']));// int
					$config->set('dtst_rep_name', $request->variable('dtst_rep_name', (string) $config['dtst_rep_name'], '')); // string
					//$config->set('dtst_rep_rank_starter', $request->variable('dtst_rep_rank_starter', (int) $config['dtst_rep_rank_starter']));// int
					$config->set('dtst_rep_count_up', $request->variable('dtst_rep_count_up', (int) $config['dtst_rep_count_up']));// int
					$config->set('dtst_rep_count_down', $request->variable('dtst_rep_count_down', (int) $config['dtst_rep_count_down']));// int
					$config->set('dtst_rep_count_good', $request->variable('dtst_rep_count_good', (int) $config['dtst_rep_count_good']));// int
					$config->set('dtst_rep_count_bad', $request->variable('dtst_rep_count_bad', (int) $config['dtst_rep_count_bad']));// int
					//$config->set('dtst_rep_points_min', $request->variable('dtst_rep_points_min', (int) $config['dtst_rep_points_min']));// int
					//$config->set('dtst_rep_points_max', $request->variable('dtst_rep_points_max', (int) $config['dtst_rep_points_max']));// int
					$config->set('dtst_show_rep_points', $request->variable('dtst_show_rep_points', (int) $config['dtst_show_rep_points'])); // bool
					$config->set('dtst_show_rep_rank', $request->variable('dtst_show_rep_rank', (int) $config['dtst_show_rep_rank'])); // bool
					$config->set('dtst_show_mod_anon', $request->variable('dtst_show_mod_anon', (int) $config['dtst_show_mod_anon'])); // bool
					//$config->set('dtst_show_reason_anon', $request->variable('dtst_show_reason_anon', (int) $config['dtst_show_reason_anon'])); // bool
					$config->set('dtst_rep_users_page', $request->variable('dtst_rep_users_page', (int) $config['dtst_rep_users_page'])); // int

					/* Log the action and return */
					$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DTST_LOG_REP_SETTINGS_SAVED');
					trigger_error($language->lang('ACP_DTST_LPR_SETTING_SAVED') . adm_back_link($this->u_action));
				}
			}

			$template->assign_vars(array(
				'S_ERRORS'						=> ($errors) ? true : false,
				'ERRORS_MSG'					=> implode('<br /><br />', $errors),
				'U_ACTION'						=> $this->u_action,

				'S_DTST_REP_HOST_TIME'			=> $host_time,
				'DTST_REP_HOST_TIME'			=> (int) $config['dtst_host_time'],
				'S_DTST_REP_TIME'				=> $rep_time,
				'DTST_REP_TIME'					=> (int) $config['dtst_rep_time'],
				'DTST_REP_NAME'					=> (string) $config['dtst_rep_name'],
				//'DTST_REP_RANK_STARTER'			=> (int) $config['dtst_rep_rank_starter'],
				'DTST_REP_COUNT_UP'				=> (int) $config['dtst_rep_count_up'],
				'DTST_REP_COUNT_DOWN'			=> (int) $config['dtst_rep_count_down'],
				'DTST_REP_COUNT_GOOD'			=> (int) $config['dtst_rep_count_good'],
				'DTST_REP_COUNT_BAD'			=> (int) $config['dtst_rep_count_bad'],
				//'DTST_REP_POINTS_MIN'			=> (int) $config['dtst_rep_points_min'],
				//'DTST_REP_POINTS_MAX'			=> ext::DTST_MAX_REP,
				'DTST_SHOW_REP_POINTS'			=> (bool) $config['dtst_show_rep_points'],
				'DTST_SHOW_REP_RANK'			=> (bool) $config['dtst_show_rep_rank'],
				'DTST_SHOW_MOD_ANON'			=> (bool) $config['dtst_show_mod_anon'],
				//'DTST_SHOW_REASON_ANON'			=> (bool) $config['dtst_show_reason_anon'],
				'DTST_USERS_PAGE'				=> (int) $config['dtst_rep_users_page'],
			));
		}

		/**
		* Mode LPR Reputation
		*/
		if ($mode === 'lpr_reputation')
		{
			/* Set the template file */
			$this->tpl_name = 'dtst_lpr_reputation';

			/* Set the page title */
			$this->page_title = $language->lang('ACP_DTST_TITLE');

			/* Do this now and forget */
			$errors = array();

			/* Add a form key for security */
			add_form_key('phpbbstudio_dtst_lpr_reputation');

			if ($request->is_set_post('submit'))
			{
				if (!check_form_key('phpbbstudio_dtst_lpr_reputation'))
				{
					trigger_error('FORM_INVALID', E_USER_WARNING);
				}

				/* No errors? Great, let's go. */
				if (!count($errors))
				{
					$config->set('dtst_rep_points_host', $request->variable('dtst_rep_points_host', (int) $config['dtst_rep_points_host']));// int
					$config->set('dtst_rep_points_noreply', $request->variable('dtst_rep_points_noreply', (int) $config['dtst_rep_points_noreply']));// int
					$config->set('dtst_rep_points_cancel_event', $request->variable('dtst_rep_points_cancel_event', (int) $config['dtst_rep_points_cancel_event']));// int
					$config->set('dtst_rep_points_good', $request->variable('dtst_rep_points_good', (int) $config['dtst_rep_points_good']));// int
					$config->set('dtst_rep_points_attend', $request->variable('dtst_rep_points_attend', (int) $config['dtst_rep_points_attend']));// int
					$config->set('dtst_rep_points_bad', $request->variable('dtst_rep_points_bad', (int) $config['dtst_rep_points_bad']));// int
					$config->set('dtst_rep_points_noshow', $request->variable('dtst_rep_points_noshow', (int) $config['dtst_rep_points_noshow']));// int
					$config->set('dtst_rep_points_up', $request->variable('dtst_rep_points_up', (int) $config['dtst_rep_points_up']));// int
					$config->set('dtst_rep_points_down', $request->variable('dtst_rep_points_down', (int) $config['dtst_rep_points_down']));// int
					$config->set('dtst_rep_points_withdraw', $request->variable('dtst_rep_points_withdraw', (int) $config['dtst_rep_points_withdraw'])); // int

					/* Log the action and return */
					$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DTST_LOG_REP_VALUES_SAVED');
					trigger_error($language->lang('ACP_DTST_LPR_REPUTATION_VALUES_SAVED') . adm_back_link($this->u_action));
				}
			}

			$template->assign_vars(array(
				'S_ERRORS'						=> ($errors) ? true : false,
				'ERRORS_MSG'					=> implode('<br /><br />', $errors),
				'U_ACTION'						=> $this->u_action,

				'DTST_REP_POINTS_HOST'				=> (int) $config['dtst_rep_points_host'],
				'DTST_REP_POINTS_NOREPLY'			=> (int) $config['dtst_rep_points_noreply'],
				'DTST_REP_POINTS_CANCEL_EVENT'		=> (int) $config['dtst_rep_points_cancel_event'],
				'DTST_REP_POINTS_GOOD'				=> (int) $config['dtst_rep_points_good'],
				'DTST_REP_POINTS_ATTEND'			=> (int) $config['dtst_rep_points_attend'],
				'DTST_REP_POINTS_BAD'				=> (int) $config['dtst_rep_points_bad'],
				'DTST_REP_POINTS_NOSHOW'			=> (int) $config['dtst_rep_points_noshow'],
				'DTST_REP_POINTS_UP'				=> (int) $config['dtst_rep_points_up'],
				'DTST_REP_POINTS_DOWN'				=> (int) $config['dtst_rep_points_down'],
				'DTST_REP_POINTS_WITHDRAW'			=> (int) $config['dtst_rep_points_withdraw'],
			));
		}

		/**
		* Mode LPR Ranks
		*/
		if ($mode === 'lpr_ranks')
		{
			/* Set the template file */
			$this->tpl_name = 'dtst_lpr_ranks';

			/* Set the page title */
			$this->page_title = $language->lang('ACP_DTST_TITLE');

			/* Request the action */
			$action = $request->variable('action', '');

			/* Do this now and forget */
			$errors = array();

			/* Add a form key for security */
			add_form_key('phpbbstudio_dtst_lpr_ranks');

			/* Request variables to work with */
			$dtst_rank_isocode = $request->variable('dtst_rank_isocode', '', true);// MAX 255
			$dtst_rank_value = $request->variable('dtst_rank_value', 0);
			$dtst_rank_title = $request->variable('dtst_rank_title', '', true);// MAX 255
			$dtst_rank_desc = $request->variable('dtst_rank_desc', '', true);// MAX 255
			$dtst_rank_bckg = $request->variable('dtst_rank_bckg', '', true);// HexDec color MAX 7
			$dtst_rank_text = $request->variable('dtst_rank_text', '', true);// HexDec color MAX 7

			/**
			 * Drop down constructs
			 */
			$rank_values = array(
				ext::DTST_RANK_ZERO		=> 'zero',
				ext::DTST_RANK_MIN		=> 'min',
				ext::DTST_RANK_ONE		=> 'one',
				ext::DTST_RANK_TWO		=> 'two',
				ext::DTST_RANK_THREE	=> 'three',
				ext::DTST_RANK_FOUR		=> 'four',
				ext::DTST_RANK_FIVE		=> 'five',
				ext::DTST_RANK_SIX		=> 'six',
				ext::DTST_RANK_SEVEN	=> 'seven',
				ext::DTST_RANK_EIGHT	=> 'eight',
				ext::DTST_RANK_NINE		=> 'nine',
				ext::DTST_RANK_TEN		=> 'ten',
			);

			/* Set var */
			$rank_values_options = '';

			foreach ($rank_values as $val => $rank_value)
			{
				$rank_values_options .= '<option value="' . $val . '"' . (($val == $dtst_rank_value) ? ' selected="selected"' : '') . '>';
				$rank_values_options .= $language->lang('ACP_DTST_RANK_' . strtoupper($rank_value));
				$rank_values_options .= '</option>';
			}

			/* Query the langs table */
			$rank_lang_isos = $dtst_utils->dtst_langs_sql();

			/* Set vars */
			$rank_lang_options = '';

			foreach ($rank_lang_isos as $key => $value)
			{
				$selected = ($key === $dtst_rank_isocode) ? ' selected="selected"' : '';
				$rank_lang_options .= '<option value="' . $key . '"' . $selected . '>' . $value . '</option>';
			}

			/* Update the ranks */
			if ($action === 'update' && $request->is_ajax())
			{
				/* Query our ranks table */
				$sql = $dtst_utils->dtst_ranks_sql($dtst_rank_value, $dtst_rank_isocode);
				$result = $db->sql_query($sql);
				$rank = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$response = !empty($rank) ? array_change_key_case($rank, CASE_UPPER) : array('DTST_NO_RANK' => true);

				$json_response = new \phpbb\json_response;
				$json_response->send($response);
			}

			if ($request->is_set_post('submit'))
			{
				if (!check_form_key('phpbbstudio_dtst_lpr_ranks'))
				{
					trigger_error('FORM_INVALID', E_USER_WARNING);
				}

				/* Emojis's  handling */
				if (preg_match_all('/[\x{10000}-\x{10FFFF}]/u', $dtst_rank_title, $matches))
				{
					$list = implode('<br>', $matches[0]);
					$errors[] = $language->lang('ACP_DTST_ERR_TITLE_EMOJIS_SUPPORT', $list);
				}
				if (preg_match_all('/[\x{10000}-\x{10FFFF}]/u', $dtst_rank_desc, $matches))
				{
					$list = implode('<br>', $matches[0]);
					$errors[] = $language->lang('ACP_DTST_ERR_DESC_EMOJIS_SUPPORT', $list);
				}

				if (empty($dtst_rank_title))
				{
					$errors[] = $language->lang('ACP_DTST_ERR_RANK_TITLE_EMPTY');
				}

				if (utf8_strlen($dtst_rank_title >= 15))
				{
					$error[] = $language->lang('ACP_DTST_ERR_RANK_TITLE_LONG');
				}

				if (empty($dtst_rank_desc))
				{
					$errors[] = $language->lang('ACP_DTST_ERR_RANK_DESC_EMPTY');
				}

				if (utf8_strlen($dtst_rank_desc >= 25))
				{
					$error[] = $language->lang('ACP_DTST_ERR_RANK_DESC_LONG');
				}

				/* No errors? Great, let's go. */
				if (!count($errors))
				{
					/* Query our ranks table */
					$sql = $dtst_utils->dtst_ranks_sql($dtst_rank_value, $dtst_rank_isocode);
					$result = $db->sql_query($sql);
					$row = $db->sql_fetchrow($result);
					$db->sql_freeresult($result);

					/* No Emojis we said, let's be sure. */
					$dtst_rank_title = $dtst_utils->dtst_strip_emojis($dtst_rank_title);
					$dtst_rank_desc = $dtst_utils->dtst_strip_emojis($dtst_rank_desc);

					/* If this rank does not exist let's create it */
					if (!$row)
					{
						$rank_sql = array(
							'dtst_rank_isocode'			=> $dtst_rank_isocode,
							'dtst_rank_value'			=> $dtst_rank_value,
							'dtst_rank_title' 			=> $dtst_rank_title,
							'dtst_rank_desc'			=> $dtst_rank_desc,
							'dtst_rank_bckg' 			=> $dtst_rank_bckg,
							'dtst_rank_text'			=> $dtst_rank_text,
						);

						$sql = 'INSERT INTO ' . $dtst_ranks . '
							' . $db->sql_build_array('INSERT', $rank_sql);
						$db->sql_query($sql);
					}
					else
					{
						/* Let's update the rank */
						$rank_sql = array(
							'dtst_rank_isocode'			=> $dtst_rank_isocode,
							'dtst_rank_value'			=> $dtst_rank_value,
							'dtst_rank_title' 			=> $dtst_rank_title,
							'dtst_rank_desc'			=> $dtst_rank_desc,
							'dtst_rank_bckg' 			=> $dtst_rank_bckg,
							'dtst_rank_text'			=> $dtst_rank_text,
						);

						$sql = 'UPDATE ' . $dtst_ranks . '
							SET ' . $db->sql_build_array('UPDATE', $rank_sql) . '
							WHERE dtst_rank_id = ' . (int) $row['dtst_rank_id'];
						$db->sql_query($sql);
					}

					/* Log the action and return */
					$phpbb_log->add('admin', $user->data['user_id'], $user->ip, 'DTST_LOG_REP_RANKS_SAVED');
					trigger_error($language->lang('ACP_DTST_LPR_RANKS_SETTING_SAVED') . adm_back_link($this->u_action));
				}
			}

			/* Set up default values */
			if (!$request->is_set_post('submit'))
			{
				/* Query our ranks table */
				$sql = 'SELECT *
						FROM ' . $dtst_ranks . '
						WHERE dtst_rank_value = ' . (int) array_keys($rank_values)[0] . '
							AND dtst_rank_isocode = "' . $db->sql_escape(array_keys($rank_lang_isos)[0]) . '"';
				$result = $db->sql_query($sql);
				$row = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);

				$dtst_rank_title = !empty($row) ? $row['dtst_rank_title'] : '';
				$dtst_rank_desc = !empty($row) ? $row['dtst_rank_desc'] : '';
				$dtst_rank_bckg = !empty($row) ? $row['dtst_rank_bckg'] : '';
				$dtst_rank_text = !empty($row) ? $row['dtst_rank_text'] : '';
			}

			$template->assign_vars(array(
				'S_ERRORS'						=> ($errors) ? true : false,
				'ERRORS_MSG'					=> implode('<br /><br />', $errors),
				'U_ACTION'						=> $this->u_action,

				'S_DTST_RANK_VALUES'			=> $rank_values_options,
				'S_DTST_RANKS_ISO'				=> $rank_lang_options,

				'DTST_RANK_TITLE'				=> htmlspecialchars_decode($dtst_rank_title, ENT_COMPAT),
				'DTST_RANK_DESC'				=> htmlspecialchars_decode($dtst_rank_desc, ENT_COMPAT),
				'DTST_RANK_BCKG'				=> htmlspecialchars_decode($dtst_rank_bckg, ENT_COMPAT),
				'DTST_RANK_TEXT'				=> htmlspecialchars_decode($dtst_rank_text, ENT_COMPAT),

				'U_DTST_RANK_UPDATE'			=> $this->u_action . '&action=update',
			));
		}
	}
}
