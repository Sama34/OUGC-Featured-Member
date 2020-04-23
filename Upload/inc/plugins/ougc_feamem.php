<?php 

/***************************************************************************
 *
 *	OUGC Featured Member plugin (/inc/plugins/ougc_feamem.php)
 *	Author: Omar Gonzalez
 *	Copyright: © 2012 - 2020 Omar Gonzalez
 *
 *	Website: https://ougc.network
 *
 *	Shows a member information anywhere in the forum.
 *
 ***************************************************************************

****************************************************************************
	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
****************************************************************************/

// Die if IN_MYBB is not defined, for security reasons.
defined('IN_MYBB') or die('This file cannot be accessed directly.');

// PLUGINLIBRARY
defined('PLUGINLIBRARY') or define('PLUGINLIBRARY', MYBB_ROOT.'inc/plugins/pluginlibrary.php');

// Run the required hooks.
if(defined('IN_ADMINCP'))
{
	$plugins->add_hook('admin_config_settings_start', 'ougc_feamem_load_lang');
	$plugins->add_hook('admin_config_settings_change', 'ougc_feamem_settings_change');

	// Cache manager
	$funct = create_function('', '
			control_object($GLOBALS[\'cache\'], \'
			function update_ougc_feamem()
			{
				ougc_feamem_cache_update("user");
			}
		\');
	');
	$plugins->add_hook('admin_tools_cache_start', $funct);
	$plugins->add_hook('admin_tools_cache_rebuild', $funct);
	unset($funct);
}
elseif(defined('THIS_SCRIPT'))
{
	global $ougc_feamem_templs;
	$ougc_feamem_templs = array('ougcfeamem', 'ougcfeamem_avatar', 'ougcfeamem_starimages', 'ougcfeamem_starimages_star', 'ougcfeamem_groupimage', 'ougcfeamem_newpoints_shop_item', 'ougcfeamem_newpoints_shop', 'ougcfeamem_awards_award', 'ougcfeamem_awards');

	if(THIS_SCRIPT == 'portal.php' || THIS_SCRIPT == 'index.php')
	{
		global $templatelist;

		if(isset($templatelist))
		{
			$templatelist .= ',';
		}
		else
		{
			$templatelist = '';
		}

		$templatelist .= implode(',', (array)$ougc_feamem_templs);
	}

	$plugins->add_hook('pre_output_page', 'ougc_feamem');
}

// Array of information about the plugin.
function ougc_feamem_info()
{
	global $lang;
	ougc_feamem_load_lang();

	return array(
		'name'			=> 'OUGC Featured Member',
		'description'	=> $lang->setting_group_ougc_feamem_desc,
		'website'		=> 'https://ougc.network',
		'author'		=> 'Omar G.',
		'authorsite'	=> 'https://ougc.network',
		'version'		=> '1.8.22',
		'versioncode'	=> 1822,
		'compatibility'	=> '18*',
		'codename'		=> 'ougc_feamem',
		'plv'			=> 13
	);
}

// _activate function
function ougc_feamem_activate()
{
	global $lang, $PL, $cache;
	ougc_feamem_load_pl();
	ougc_feamem_deactivate();

	// Add our settings
	$PL->settings('ougc_feamem', $lang->setting_group_ougc_feamem, $lang->setting_group_ougc_feamem_desc, array(
		'time'	=> array(
			'title'			=> $lang->setting_ougc_feamem_time,
			'description'	=> $lang->setting_ougc_feamem_time_desc,
			'optionscode'	=> 'text',
			'value'			=> 24,
		),
		'groups'	=> array(
			'title'			=> $lang->setting_ougc_feamem_groups,
			'description'	=> $lang->setting_ougc_feamem_groups_desc,
			'optionscode'	=> 'groupselect',
			'value'			=> -1,
		),
		'uid'	=> array(
			'title'			=> $lang->setting_ougc_feamem_uid,
			'description'	=> $lang->setting_ougc_feamem_uid_desc,
			'optionscode'	=> 'text',
			'value'			=> '',
		),
		'away'	=> array(
			'title'			=> $lang->setting_ougc_feamem_away,
			'description'	=> $lang->setting_ougc_feamem_away_desc,
			'optionscode'	=> 'yesno',
			'value'			=> 0,
		),
		'maxavatardim'	=> array(
			'title'			=> $lang->setting_ougc_feamem_maxavatardim,
			'description'	=> $lang->setting_ougc_feamem_maxavatardim_desc,
			'optionscode'	=> 'text',
			'value'			=> '40x40',
		),
		'ignorefeatured'	=> array(
			'title'			=> $lang->setting_ougc_feamem_ignorefeatured,
			'description'	=> $lang->setting_ougc_feamem_ignorefeatured_desc,
			'optionscode'	=> 'yesno',
			'value'			=> 1,
		),
		'ignoredhistory'	=> array(
			'title'			=> 'Ignored History',
			'description'	=> 'Edit this under your own risk',
			'optionscode'	=> 'textarea',
			'value'			=> '',
		),
		'createthread'	=> array(
			'title'			=> $lang->setting_ougc_feamem_createthread,
			'description'	=> $lang->setting_ougc_feamem_createthread_desc,
			'optionscode'	=> 'yesno',
			'value'			=> 0,
		),
		'thread_fid'	=> array(
			'title'			=> $lang->setting_ougc_feamem_thread_fid,
			'description'	=> $lang->setting_ougc_feamem_thread_fid_desc,
			'optionscode'	=> 'forumselectsingle',
			'value'			=> 2,
		),
		'thread_subject'	=> array(
			'title'			=> $lang->setting_ougc_feamem_thread_subject,
			'description'	=> $lang->setting_ougc_feamem_thread_subject_desc,
			'optionscode'	=> 'text',
			'value'			=> 'Congralutations {USERNAME}, for being member of the day. ({DATE})',
		),
		'thread_message'	=> array(
			'title'			=> $lang->setting_ougc_feamem_thread_message,
			'description'	=> $lang->setting_ougc_feamem_thread_message_desc,
			'optionscode'	=> 'textarea',
			'value'			=> 'Congralutations {USERNAME}, for being member of the day.

All users, please congratulate our new member of the day as well :)',
		),
		'thread_prefix'	=> array(
			'title'			=> $lang->setting_ougc_feamem_thread_prefix,
			'description'	=> $lang->setting_ougc_feamem_thread_prefix_desc,
			'optionscode'	=> 'text',
			'value'			=> '',
		),
		'thread_icon'	=> array(
			'title'			=> $lang->setting_ougc_feamem_thread_,
			'description'	=> $lang->setting_ougc_feamem_thread_icon_desc,
			'optionscode'	=> 'text',
			'value'			=> '',
		),
		'thread_uid'	=> array(
			'title'			=> $lang->setting_ougc_feamem_thread_uid,
			'description'	=> $lang->setting_ougc_feamem_thread_uid_desc,
			'optionscode'	=> 'text',
			'value'			=> '',
		),
		'thread_closed'	=> array(
			'title'			=> $lang->setting_ougc_feamem_thread_closed,
			'description'	=> $lang->setting_ougc_feamem_thread_closed_desc,
			'optionscode'	=> 'yesno',
			'value'			=> 0,
		),
		'thread_sticky'	=> array(
			'title'			=> $lang->setting_ougc_feamem_thread_sticky,
			'description'	=> $lang->setting_ougc_feamem_thread_sticky_desc,
			'optionscode'	=> 'yesno',
			'value'			=> 0,
		),
		//CREATE POST
		/*'thread_sticky_time'	=> array(
			'title'			=> $lang->setting_ougc_feamem_thread_sticky_time,
			'description'	=> $lang->setting_ougc_feamem_thread_sticky_time_desc,
			'optionscode'	=> '',
			'value'			=> '',
		),
		'thread_visible'	=> array(
			'title'			=> $lang->setting_ougc_feamem_thread_visible,
			'description'	=> $lang->setting_ougc_feamem_thread_visible_desc,
			'optionscode'	=> '',
			'value'			=> '',
		),*/
	));
	// Insert template/group
	$PL->templates('ougcfeamem', 'OUGC Featured Member', array(
		''	=> '<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead">
<strong>{$lang->ougc_feamem_title}</strong>
</td>
</tr>
<tr>
<td class="trow1" style="text-align:center;">
<span class="largetext">{$profilelink_formatted}</span>{$avatar}<br />
{$usertitle}{$groupimage}{$userstars}{$awards}
</td>
</tr>
</table><br />',
		'avatar'	=> '<br /><a href="{$user[\'profilelink\']}" title="{$user[\'username\']}"><img src="{$avatar[\'image\']}" {$avatar[\'width_height\']} /></a>',
		'awards_award'	=> '<img src="{$apkeys[\'path\']}{$award[\'image\']}" alt="{$award[\'name\']}" title="{$award[\'name\']}" />',
		'awards'	=> '<br /><b>{$lang->ougc_feamem_awards}</b><br />{$awards}',
		'groupimage'	=> '<br /><img src="{$displaygroup[\'image\']}" alt="{$usertitle}" title="{$usertitle}" />',
		'newpoints_shop_item'	=> '<a href="{$mybb->settings[\'bburl\']}/newpoints.php?action=shop&amp;shop_action=view&amp;iid={$iid}"><img src="{$mybb->settings[\'bburl\']}/{$item[\'icon\']}" title="{$item[\'name\']}"></a>',
		'newpoints_shop'	=> '<br />{$shop_items}',
		'starimages_star'	=> '<img src="{$starimage}" border="0" alt="*" />',
		'starimages'	=> '<br />{$userstars}',
	));

	require_once MYBB_ROOT.'inc/adminfunctions_templates.php';
	find_replace_templatesets('portal', '#'.preg_quote('{$welcome}').'#i', '{$welcome}<!--OUGC_FEAMEM-->');

	// Insert/update version into cache
	$plugins = $cache->read('ougc_plugins');
	if(!$plugins)
	{
		$plugins = array();
	}

	$info = ougc_feamem_info();

	if(!isset($plugins['feamem']))
	{
		$plugins['feamem'] = $info['versioncode'];
	}

	/*~*~* RUN UPDATES START *~*~*/

	/*~*~* RUN UPDATES END *~*~*/

	$plugins['feamem'] = $info['versioncode'];
	$cache->update('ougc_plugins', $plugins);
}

// _deactivate function
function ougc_feamem_deactivate()
{
	require_once MYBB_ROOT.'inc/adminfunctions_templates.php';
	find_replace_templatesets('index', '#'.preg_quote('<!--OUGC_FEAMEM-->').'#i', '', 0);
	find_replace_templatesets('portal', '#'.preg_quote('<!--OUGC_FEAMEM-->').'#i', '', 0);
}

// _install function
function ougc_feamem_install()
{
	global $cache, $lang;
	ougc_feamem_load_pl();

	$cache->update('ougc_feamem', 0);
}

// _is_installed function
function ougc_feamem_is_installed()
{
	global $cache;

	if(!($plugins = $cache->read('ougc_plugins')))
	{
		return false;
	}

	return isset($plugins['feamem']);
}

// _uninstall function
function ougc_feamem_uninstall()
{
	global $db, $cache, $lang, $PL;
	ougc_feamem_load_pl();

	$PL->settings_delete('ougc_feamem');
	$PL->templates_delete('ougcfeamem');
	$PL->cache_delete('ougc_feamem');

	// Delete version from cache
	$plugins = (array)$cache->read('ougc_plugins');

	if(isset($plugins['feamem']))
	{
		unset($plugins['feamem']);
	}

	if(!empty($plugins))
	{
		$cache->update('ougc_plugins', $plugins);
	}
	else
	{
		$PL->cache_delete('ougc_plugins');
	}
}

// Loads language file
function ougc_feamem_load_lang()
{
	global $lang;

	isset($lang->setting_group_ougc_feamem) or $lang->load('ougc_feamem');
}

// Load and check PluginLibrary
function ougc_feamem_load_pl()
{
	global $lang;
	ougc_feamem_load_lang();
	$info = ougc_feamem_info();

	if(!file_exists(PLUGINLIBRARY))
	{
		flash_message($lang->sprintf($lang->ougc_feamem_plreq, $info['plv']), 'error');
		admin_redirect('index.php?module=config-plugins');
		exit;
	}

	global $PL;
	$PL or require_once PLUGINLIBRARY;

	if($PL->version < $info['plv'])
	{
		flash_message($lang->sprintf($lang->ougc_feamem_plold, $info['plv'], $PL->version), 'error');
		admin_redirect('index.php?module=config-plugins');
		exit;
	}
}

// Actual magic
function ougc_feamem(&$page)
{
	global $mybb;

	if(my_strpos($page, '<!--OUGC_FEAMEM-->') && $mybb->settings['ougc_feamem_groups'] != '')
	{
		global $mybb, $theme, $lang, $templates, $ougc_awards, $ougc_feamem_templs, $groupscache;
		ougc_feamem_load_lang();

		// will remove 0 from the cache (i.e: fresh install)
		$data = array_filter((array)$mybb->cache->read('ougc_feamem'));
		$user = &$data['user'];

		$apkeys = ougc_feamem_apkeys();

		// Rebuild cache
		if(my_strpos($mybb->settings['ougc_feamem_time'], '*') === false)
		{
			$hourstime = TIME_NOW-(60*60*(int)$mybb->settings['ougc_feamem_time']);
		}
		else
		{
			$vrts = implode('*', (array)array_map('intval', explode('*', $mybb->settings['ougc_feamem_time'])));
			eval('$hours = (int)('.$vrts.');');
		}

		if(empty($user) || empty($data['time']) || $data['time'] <= $hourstime)
		{
			global $db;

			if(!empty($mybb->settings['ougc_feamem_uid']))
			{
				if(my_strpos($mybb->settings['ougc_feamem_uid'], 'username:') === false)
				{
					$uid = explode(':', $mybb->settings['ougc_feamem_uid']);
					$uid = (int)$mybb->settings['ougc_feamem_uid'];
					$data['user'] = get_user($uid);
				}
				else
				{
					$username = explode(':', $mybb->settings['ougc_feamem_uid']);
					$query = $db->simple_select('users', '*', 'LOWER(username)=\''.$db->escape_string(my_strtolower((string)$username[1])).'\'', array('limit' => 1));
					$data['user'] = $db->fetch_array($query);
				}
			}
			else
			{
				$uids = array();

				$where = array();
				if($mybb->settings['ougc_feamem_groups'] != -1)
				{
					$gids = array_filter(array_unique(explode(',', $mybb->settings['ougc_feamem_groups'])));

					$mysql = true;
					switch($db->type)
					{
						case 'pgsql':
						case 'sqlite':
							$mysql = false;
							break;
					}

					$or = '';
					$sql_where .= '(';
					foreach((array)$gids as $gid)
					{
						$gid = (int)$gid;
						$sql_where .= $or.'usergroup=\''.$gid.'\' OR ';
						if($mysql)
						{
							$sql_where .= 'CONCAT(\',\',additionalgroups,\',\') LIKE \'%,'.$gid.',%\'';
						}
						else
						{
							$sql_where .= '\',\'||additionalgroups||\',\' LIKE \'%,'.$gid.',%\'';
						}
						$or = ' OR ';
					}
					$sql_where .= ')';
					$where[] = $sql_where;
				}

				if(empty($mybb->settings['ougc_feamem_away']))
				{
					$where[] = 'away=\'0\'';
				}

				if($mybb->settings['ougc_feamem_ignorefeatured'] && $mybb->settings['ougc_feamem_ignoredhistory'])
				{
					$uids = explode(',', $mybb->settings['ougc_feamem_ignoredhistory']);
					$uids = implode('\',\'', array_filter(array_unique(array_map('intval', $uids))));
					$where[] = 'uid NOT IN (\''.$uids.'\')';
					$uids = array();
				}

				$query = $db->simple_select('users', 'uid', implode(' AND ', $where));
				while($uid = $db->fetch_field($query, 'uid'))
				{
					$uids[(int)$uid] = (int)$uid;
				}

				if(!($uid = (int)$uids[array_rand($uids)]))
				{
					return false;
				}

				$data['user'] = get_user($uid);
			}

			if(empty($user['uid']))
			{
				return false;
			}

			ougc_feamem_clean($user);

			// Create a thread
			$data['tid'] = ougc_feamem_create_thread(array(
				'uid'		=> $user['uid'],
				'username'	=> $user['username']
			), $data['errors']);

			$data['time'] = TIME_NOW;

			// ignore already featured memebers
			if($mybb->settings['ougc_feamem_ignorefeatured'])
			{
				$uids = explode(',', $mybb->settings['ougc_feamem_ignoredhistory']);
				$uids[] = $user['uid'];
				$uids = implode(',', array_filter(array_unique(array_map('intval', $uids))));
				$uids = $db->escape_string($uids);
				$db->update_query('settings', array('value'	=> $uids), 'name=\'ougc_feamem_ignoredhistory\'');
				rebuild_settings();
			}

			$mybb->cache->update('ougc_feamem', $data);
		}

		// Cache some templates if not already
		$templs_cached = false;
		foreach((array)$ougc_feamem_templs as $templ)
		{
			if(isset($templates->cache[$templ]))
			{
				$templs_cached = true;
				break;
			}
		}

		if(!$templs_cached)
		{
			$templates->cache(implode(',', (array)$ougc_feamem_templs));
		}

		$user['displaygroup'] or $user['displaygroup'] = $user['usergroup'];

		$user['username'] = htmlspecialchars_uni($user['username']);
		$user['profilelink'] = get_profile_link($user['uid']);

		$profilelink = build_profile_link($user['username'], $user['uid']);
		$username_formatted = format_name($user['username'], $user['usergroup'], $user['displaygroup']);
		$profilelink_formatted = build_profile_link($username_formatted, $user['uid']);

		// Format avatar
		$avatar = '';
		if((bool)$mybb->user['showavatars'] || !$mybb->user['uid'])
		{
			$avatar = format_avatar($user['avatar'], $user['avatardimensions'], $settings['maxavatardim']);

			eval('$avatar = "'.$templates->get('ougcfeamem_avatar').'";');
		}

		// Format newpoints points
		if(isset($user['newpoints']) && function_exists('newpoints_format_points'))
		{
			$newpoints = newpoints_format_points($user['newpoints']);
		}

		// Format refferals
		$referrals = my_number_format($user['referrals']);

		// Format reputation
		$reputation = get_reputation($user['reputation'], $user['uid']);

		// Format time online
		$timeonline = $lang->ougc_feamem_none_registered;
		if($user['timeonline'] > 0)
		{
			$timeonline = nice_time($user['timeonline']);
		}

		// Format registration date
		$memregdate = my_date($mybb->settings['dateformat'], $user['regdate']);

		// Format awards
		$awards = '';
		if(!empty($user[$apkeys['var']]) && function_exists($apkeys['function']) && is_array($user[$apkeys['var']]))
		{
			$cachedawards = $mybb->cache->read($apkeys['cache']);
			foreach($user[$apkeys['var']] as $aid)
			{
				if(!empty($cachedawards[$aid]))
				{
					$award = $cachedawards[$aid];
					if(is_object(${$apkeys['cache']}))
					{
						$awards .= $apkeys['function']($award, 'ougc_feamem_award');
					}
					else
					{
						$award['aid'] = (int)$award['id'];
						$award['name'] = htmlspecialchars_uni($award['awname']);
						$award['description'] = $award['reason'] = '';
						$award['image'] = htmlspecialchars_uni($award['awimg']);
						eval('$awards .= "'.$templates->get('ougcfeamem_awards_award').'";');
					}
				}
			}
			eval('$awards = "'.$templates->get('ougcfeamem_awards').'";');
		}

		// Grab some fields from the user's displaygroup
		is_array($groupscache) or $groupscache = $mybb->cache->read('usergroups');
		
		$displaygroup = array();
		foreach(array('usertitle', 'stars', 'starimage', 'image') as $field)
		{
			$displaygroup[$field] = $groupscache[$user['displaygroup']][$field];
		}

		// Format usergroup info
		$stars = 0;
		$starimage = $userstars = $groupimage = '';
		if(!trim($user['usertitle']) && trim($displaygroup['usertitle']))
		{
			// probably should check usergroup permissions but mybb itself doesn't so meh
			$user['usertitle'] = $displaygroup['usertitle'];
		}
		if(!trim($user['usertitle']) && is_array($usertitles = $mybb->cache->read('usertitles')))
		{
			foreach((array)$usertitles as $title)
			{
				if($user['postnum'] >= $title['posts'])
				{
					$user['usertitle'] = $title['title'];
					$stars = $title['stars'];
					$starimage = $title['starimage'];
					break;
				}
			}
		}

		$usertitle = htmlspecialchars_uni($user['usertitle']);

		if($displaygroup['stars'] || $displaygroup['usertitle'])
		{
			$stars = $displaygroup['stars'];
		}
		elseif(!$user['stars'])
		{
			if(!is_array($usertitles))
			{
				$usertitles = $mybb->cache->read('usertitles');
			}

			if(is_array($usertitles))
			{
				foreach((array)$usertitles as $title)
				{
					if($user['postnum'] >= $title['posts'])
					{
						$stars = $title['stars'];
						$starimage = $title['starimage'];
						break;
					}
				}
			}
		}

		if(!$starimage)
		{
			$starimage = $displaygroup['starimage'];
		}

		if(!empty($displaygroup['image']))
		{
			$groupimage = $displaygroup['image'];
		}

		if($starimage)
		{
			$starimage = str_replace('{theme}', $theme['imgdir'], $starimage);
			for($i = 0; $i < $stars; ++$i)
			{
				eval('$userstars .= "'.$templates->get('ougcfeamem_starimages_star').'";');
			}
			eval('$userstars = "'.$templates->get('ougcfeamem_starimages').'";');
		}

		if($groupimage)
		{
			if($mybb->user['language'])
			{
				$language = $mybb->user['language'];
			}
			else
			{
				$language = $mybb->settings['bblanguage'];
			}

			$displaygroup['image'] = str_replace('{lang}', $language, $displaygroup['image']);
			$displaygroup['image'] = str_replace('{theme}', $theme['imgdir'], $displaygroup['image']);
			eval('$groupimage = "'.$templates->get('ougcfeamem_groupimage').'";');
		}

		// Format newpoints items
		$shop_items = '';
		if(!empty($user['newpoints_items_cache']) && function_exists('newpoints_lang_load'))
		{
			newpoints_lang_load('newpoints_shop');
			foreach((array)$user['newpoints_items_cache'] as $iid => $item)
			{
				if(empty($item['icon']))
				{
					$item['icon'] = 'images/newpoints/default.png';
				}
				$item['name'] = htmlspecialchars_uni($item['name']);
				eval('$shop_items .= "'.$templates->get('ougcfeamem_newpoints_shop_item').'";');
			}
			eval('$shop_items = "'.$templates->get('ougcfeamem_newpoints_shop').'";');
		}

		eval('$user = "'.$templates->get('ougcfeamem').'";');
				
		$page = str_replace('<!--OUGC_FEAMEM-->', $user, $page);
	}
}

// Language support for settings
function ougc_feamem_settings_change()
{
	global $db, $mybb;

	$query = $db->simple_select('settinggroups', 'name', 'gid=\''.(int)$mybb->input['gid'].'\'');
	$groupname = $db->fetch_field($query, 'name');
	if($groupname == 'ougc_feamem')
	{
		global $plugins;
		ougc_feamem_load_lang();

		if($mybb->request_method == 'post')
		{
			global $settings;

			$rebuild = false;
			if(($mybb->input['upsetting']['ougc_feamem_groups'] == 'custom' || $mybb->input['upsetting']['ougc_feamem_groups'] == 'all') && $settings['ougc_feamem_groups'] != -1)
			{
				$rebuild = true;
			}
			if(($mybb->input['upsetting']['ougc_feamem_groups'] == 'custom' || $mybb->input['upsetting']['ougc_feamem_groups'] == 'none') && $settings['ougc_feamem_groups'] != '')
			{
				$rebuild = true;
			}

			if(isset($mybb->input['ougc_feamem_reset']))
			{
				ougc_feamem_cache_update();
				unset($mybb->input['ougc_feamem_reset']);
			}
			elseif($rebuild || $mybb->input['upsetting']['ougc_feamem_uid'] != $settings['ougc_feamem_uid'] || $mybb->input['upsetting']['ougc_feamem_away'] != $settings['ougc_feamem_away'])
			{
				ougc_feamem_cache_update();
			}

			return;
		}

		$plugins->add_hook('admin_formcontainer_output_row', 'ougc_feamem_formcontainer_output_row');
		$plugins->add_hook('admin_form_output_submit_wrapper', 'ougc_feamem_output_submit_wrapper');
	}
}

function ougc_feamem_cache_update($key='time')
{
	global $cache;

	$d = $cache->read('ougc_feamem');

	if($key == 'user' && !empty($d['user']['uid']))
	{
		$d['user'] = get_user($d['user']['uid']);
		if(!empty($d['user']['uid']))
		{
			ougc_feamem_clean($d['user']);
		}
		else
		{
			$key = 'time';
		}
	}

	if($key == 'time' && isset($d[$key]))
	{
		$d[$key] = 0;
	}

	$cache->update('ougc_feamem', $d);
}

// Suport for multiple awards plugins
function ougc_feamem_apkeys()
{
	global $cache;

	$pluginlist = $cache->read('plugins');
	if(!empty($pluginlist['active']['ougc_awards']))
	{
		return array('cache' => 'ougc_awards', 'column' => 'ougc_awards', 'setting' => 'ougc_awards_profile', 'var' => 'ougc_awards_cache', 'db_table' => 'ougc_awards_users', 'db_field' => 'aid', 'db_uidfield' => 'awuid', 'column_aids' => true, 'db_options' => array('order' => 'date', 'order_dir' => 'desc', 'limit' => (int)$mybb->settings['ougc_awards_profile']), 'function' => 'ougc_awards_format_award', 'path' => '');
	}

	if(!empty($pluginlist['active']['my_awards']))
	{
		return array('cache' => 'myawards', 'column' => 'awards', 'setting' => 'myawardsenable', 'var' => 'myawards_cache', 'db_table' => 'myawards_users', 'db_field' => 'awid', 'db_uidfield' => 'awuid', 'column_aids' => false, 'db_options' => array('order' => 'awutime', 'order_dir' => 'desc', 'limit' => 10), 'function' => 'my_awards_info', 'path' => 'uploads/awards/');
	}
}

function ougc_feamem_clean(&$user)
{
	global $db, $settings;

	$apkeys = ougc_feamem_apkeys();

	unset($user['password'], $user['salt'], $user['loginkey'], $user['logoutkey']);

	// Cache awards
	if((bool)$user[$apkeys['column']] && (bool)$settings[$apkeys['setting']])
	{
		$where = $apkeys['db_uidfield'].'=\''.(int)$user['uid'].'\'';
		if($apkeys['column_aids'])
		{
			$where .= 'AND aid IN (\''.implode('\',\'', (array)array_filter(array_unique(array_map('intval', explode(',', $user[$apkeys['column']]))))).'\')';
		}
		$query = $db->simple_select($apkeys['db_table'], $apkeys['db_field'], $where, $apkeys['db_options']);
		while($award = $db->fetch_array($query))
		{
			$user[$apkeys['var']][(int)$award[$apkeys['db_field']]] = (int)$award[$apkeys['db_field']];
		}
	}

	// Format newpoints items
	if(!empty($settings['newpoints_shop_itemsprofile']) && !empty($user['newpoints_items']))
	{
		// We want to save queries but newpoints should probably cache the items
		$user['newpoints_shop_items_count'] = 0;
		if($items = unserialize($user['newpoints_items']))
		{
			$user['newpoints_shop_items_count'] = count($items);
			$query = $db->simple_select('newpoints_shop_items', 'iid, name, icon', 'visible=\'1\' AND iid IN (\''.implode('\',\'', (array)array_filter(array_unique(array_map('intval', $items)))).'\')', array('limit' => (int)$settings['newpoints_shop_itemsprofile']));
			while($item = $db->fetch_array($query))
			{
				$user['newpoints_items_cache'][$item['iid']] = array('name' => $item['name'], 'icon' => $item['icon']);
			}
		}
	}

	if(isset($user['wiki_permissions']))
	{
		unset($user['wiki_permissions']);
	}
}

function ougc_feamem_formcontainer_output_row(&$args)
{
	global $form, $settings, $lang;
	ougc_feamem_load_lang();

	#static $unset_prefix = false;

	if($args['row_options']['id'] == 'row_setting_ougc_feamem_ignoredhistory')
	{
		$args['row_options']['id'] .= '" style="display: none;';
	}

	if($args['row_options']['id'] == 'row_setting_ougc_feamem_thread_prefix')
	{
		$args['content'] = build_prefix_select('all', (int)$settings['ougc_feamem_thread_prefix']); // FID should probably be updated via ajax or jQ

		if($args['content'] === false)
		{
			$args['content'] = $lang->setting_ougc_feamem_thread_prefix_empty;
		}
		else
		{
			$args['content'] = str_replace('name="threadprefix"', 'name="upsetting[ougc_feamem_thread_prefix]"', $args['content']);
		}

		/*if($args['content'] === false)
		{
			// No prefixes
			control_object($args['this'], '
				function construct_cell($data, $extra=array())
				{
					_dump(true, $data, $extra);
					$this->_cells[] = array("data" => $data, "extra" => $extra);
				}
			');
			#$args['content']['skip_construct'] = $unset_prefix = true;
			#return ' return "";';
		}*/
	}

	if($args['row_options']['id'] == 'row_setting_ougc_feamem_thread_icon')
	{
		global $templates;

		$mybb->input['icon'] = (int)$settings['ougc_feamem_thread_icon'];

		$templates->cache['posticons'] = '{$iconlist}';
		$args['content'] = str_replace(array('src="images', 'name="icon"'), array('src="../images', 'name="upsetting[ougc_feamem_thread_icon]"'), get_post_icons());
	}
}

function ougc_feamem_output_submit_wrapper(&$args)
{
	global $page, $form;

	$args[0] = $form->generate_submit_button('Reset User', array('name' => 'ougc_feamem_reset')).$args[0];

	echo '<script type="text/javascript">
	Event.observe(window, "load", function() {
		Load_OUGC_Feamem_Peekers();			
	});

	function Load_OUGC_Feamem_Peekers()
	{
		new Peeker($$(".setting_ougc_feamem_createthread"), $("row_setting_ougc_feamem_thread_fid"), /1/, true);
		new Peeker($$(".setting_ougc_feamem_createthread"), $("row_setting_ougc_feamem_thread_subject"), /1/, true);
		new Peeker($$(".setting_ougc_feamem_createthread"), $("row_setting_ougc_feamem_thread_message"), /1/, true);
		new Peeker($$(".setting_ougc_feamem_createthread"), $("row_setting_ougc_feamem_thread_prefix"), /1/, true);
		new Peeker($$(".setting_ougc_feamem_createthread"), $("row_setting_ougc_feamem_thread_icon"), /1/, true);
		new Peeker($$(".setting_ougc_feamem_createthread"), $("row_setting_ougc_feamem_thread_uid"), /1/, true);
		new Peeker($$(".setting_ougc_feamem_createthread"), $("row_setting_ougc_feamem_thread_closed"), /1/, true);
		new Peeker($$(".setting_ougc_feamem_createthread"), $("row_setting_ougc_feamem_thread_sticky"), /1/, true);
	}
</script>';
}

// Create a thread by administrator settings
function ougc_feamem_create_thread($user, $errors=array(), $tid=0)
{
	global $settings;

	if(!is_array($errors))
	{
		$errors = array();
	}

	$sets = array(
		'fid'		=> (int)$settings['ougc_feamem_thread_fid'],
		'subject'	=> trim($settings['ougc_feamem_thread_subject']),
		'message'	=> trim($settings['ougc_feamem_thread_message']),
		'prefix'	=> (int)$settings['ougc_feamem_thread_prefix'],
		'icon'		=> (int)$settings['ougc_feamem_thread_icon'],
		'uid'		=> (int)$settings['ougc_feamem_thread_uid'],
		'closed'	=> (int)(bool)$settings['ougc_feamem_thread_closed'],
		'sticky'	=> (int)(bool)$settings['ougc_feamem_thread_sticky'],
		'username'	=> '',
	);

	// Verify settings
	$forum = get_forum($sets['fid']);
	if(!(isset($forum['type']) && $forum['type'] == 'f'))
	{
		$errors[] = 'invalid_forum';
		return false;
	}
	unset($forum);
	if(empty($sets['subject']))
	{
		$errors[] = 'invalid_subject';
		return false;
	}
	if(empty($sets['message']))
	{
		$errors[] = 'invalid_message';
		return false;
	}

	if($sets['prefix'])
	{
		$prefix = build_prefixes($sets['prefix']);
		if(empty($prefix['pid']))
		{
			$sets['prefix'] = 0;
		}
		unset($prefix);
	}
	if($sets['icon'])
	{
		global $cache;

		$icons = $cache->read('posticons');
		if(empty($icons[$sets['icon']]))
		{
			$sets['icon'] = 0;
		}
		unset($icons);
	}

	global $db;

	// Get the correct thread author UID and USERNAME
	if(my_strpos($sets['uid'], 'username:') !== false)
	{
		$username = explode(':', $sets['uid']);
		$username = $db->escape_string(my_strtolower($username[1]));
		$query = $db->simple_select('users', 'uid, username', 'username=\''.$username.'\'');
		$author = $db->fetch_array($query);
		$sets['uid'] = $author['uid'];
		$sets['username'] = $author['username'];

		if(empty($sets['uid']))
		{
			$errors[] = 'invalid_thread_user';
			return false;
		}

		unset($username, $author);
	}

	if($sets['uid'] == -1)
	{
		global $lang;

		isset($lang->guest) or $lang->guest = 'Guest';
		$sets['uid'] = 0;
		$sets['username'] = $lang->guest;
	}
	elseif($sets['uid'] && !$sets['username'])
	{
		$query = $db->simple_select('users', 'username', 'uid=\''.(int)$sets['uid'].'\'');
		$sets['username'] = (string)$db->fetch_field($query, 'username');
		if(empty($sets['username']))
		{
			$errors[] = 'invalid_thread_user';
			return false;
		}
	}
	else
	{
		$sets['uid'] = $user['uid'];
		$sets['username'] = $user['username'];
	}

	$date = my_date('Y-m-d', TIME_NOW);

	$sets['subject'] = str_replace(array('{USERNAME}', '{DATE}'), array($user['username'], $date), $sets['subject']);
	$sets['message'] = str_replace(array('{USERNAME}', '{DATE}'), array($user['username'], $date), $sets['message']);

	// Set the thread data 
	$threaddata = array(
		'fid'		=> $sets['fid'],
		'subject'	=> $sets['subject'],
		'message'	=> $sets['message'],
		'prefix'	=> $sets['prefix'],
		'icon'		=> $sets['icon'],
		'uid'		=> $sets['uid'],
		'username'	=> $sets['username'],
		'posthash'	=> md5($sets['uid'].mt_rand()),
		'savedraft'	=> 0,
		'options'	=> array(
			'signature'				=> 1,
			'subscriptionmethod'	=> '',
			'disablesmilies'		=> 0
		)
	);

	require_once MYBB_ROOT.'inc/datahandlers/post.php';
	$posthandler = new PostDataHandler('insert');

	$posthandler->action = 'thread';
	$posthandler->admin_override = true;

	$posthandler->set_data($threaddata);

	if(!$posthandler->validate_thread())
	{
		$errors['posthandler'] = $posthandler->get_friendly_errors();
		return false;
	}

	$thread = $posthandler->insert_thread();
	$thread['tid'] = (int)$thread['tid'];

	// Close / stick the thread
	$updatedata = array();
	if($sets['closed'])
	{
		$updatedata['closed'] = 1;
	}
	if($sets['sticky'])
	{
		$updatedata['sticky'] = 1;
	}

	if($updatedata)
	{
		$db->update_query('threads', $updatedata, 'tid=\''.$thread['tid'].'\'');
	}

	// Mark thread read if current user is the author
	/*if($mybb->user['uid'] == $sets['uid'])
	{
		require_once MYBB_ROOT.'inc/functions_indicators.php';
		mark_thread_read($thread['tid'], $sets['fid']);
	}*/

	return $thread['tid'];
}

// control_object by Zinga Burga from MyBBHacks ( mybbhacks.zingaburga.com ), 1.62
if(!function_exists('control_object'))
{
	function control_object(&$obj, $code)
	{
		static $cnt = 0;
		$newname = '_objcont_'.(++$cnt);
		$objserial = serialize($obj);
		$classname = get_class($obj);
		$checkstr = 'O:'.strlen($classname).':"'.$classname.'":';
		$checkstr_len = strlen($checkstr);
		if(substr($objserial, 0, $checkstr_len) == $checkstr)
		{
			$vars = array();
			// grab resources/object etc, stripping scope info from keys
			foreach((array)$obj as $k => $v)
			{
				if($p = strrpos($k, "\0"))
				{
					$k = substr($k, $p+1);
				}
				$vars[$k] = $v;
			}
			if(!empty($vars))
			{
				$code .= '
					function ___setvars(&$a) {
						foreach($a as $k => &$v)
							$this->$k = $v;
					}
				';
			}
			eval('class '.$newname.' extends '.$classname.' {'.$code.'}');
			$obj = unserialize('O:'.strlen($newname).':"'.$newname.'":'.substr($objserial, $checkstr_len));
			if(!empty($vars))
			{
				$obj->___setvars($vars);
			}
		}
		// else not a valid object or PHP serialize has changed
	}
}
