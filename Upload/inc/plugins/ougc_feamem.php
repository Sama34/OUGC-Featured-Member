<?php 

/***************************************************************************
 *
 *   OUGC Featured Member plugin (/inc/plugins/ougc_feamem.php)
 *	 Author: Omar Gonzalez
 *   Copyright: © 2012 Omar Gonzalez
 *   
 *   Website: http://community.mybb.com/user-25096.html
 *
 *   Shows a member information anywhere in the forum.
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
	$plugins->add_hook('admin_style_templates_set', 'ougc_feamem_load_lang');
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
		'description'	=> $lang->ougc_feamem_d,
		'website'		=> 'http://community.mybb.com/user-25096.html',
		'author'		=> 'Omar G.',
		'authorsite'	=> 'http://community.mybb.com/user-25096.html',
		'version'		=> '1.0',
		'guid'			=> '',
		'compatibility' => '16*',
		'plv'			=> 11
	);
}

// _activate function
function ougc_feamem_activate()
{
	global $lang, $PL;
	ougc_feamem_load_pl();
	ougc_feamem_deactivate();

	// Add our settings
	$PL->settings('ougc_feamem', 'OUGC Featured Member', $lang->setting_group_ougc_feamem_desc, array(
		'time'	=> array(
			'title'			=> $lang->setting_ougc_feamem_time,
			'description'	=> $lang->setting_ougc_feamem_time_desc,
			'optionscode'	=> 'text',
			'value'			=> 24,
		),
		'groups'	=> array(
			'title'			=> $lang->setting_ougc_feamem_groups,
			'description'	=> $lang->setting_ougc_feamem_groups_desc,
			'optionscode'	=> 'text',
			'value'			=> '',
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
			'value'			=> 1,
		),
		'avatar'	=> array(
			'title'			=> $lang->setting_ougc_feamem_avatar,
			'description'	=> $lang->setting_ougc_feamem_avatar_desc,
			'optionscode'	=> 'text',
			'value'			=> $GLOBALS['settings']['bburl'].'/images/avatars/invalid_url.gif',
		),
		'avatardim'	=> array(
			'title'			=> $lang->setting_ougc_feamem_avatardim,
			'description'	=> $lang->setting_ougc_feamem_avatardim_desc,
			'optionscode'	=> 'text',
			'value'			=> '85|85',
		),
		'maxavatardim'	=> array(
			'title'			=> $lang->setting_ougc_feamem_maxavatardim,
			'description'	=> $lang->setting_ougc_feamem_maxavatardim_desc,
			'optionscode'	=> 'text',
			'value'			=> '40x40',
		),
	));

	// Insert template/group
	$PL->templates('ougcfeamem', '<lang:ougc_feamem>', array(
		''	=> '<div style="float: right;">
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
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
</table>
</div>',
		'avatar'	=> '<br /><a href="{$user[\'profilelink\']}" title="{$user[\'username\']}"><img src="{$avatar[\'image\']}" width="{$avatar[\'width\']}" height="{$avatar[\'height\']}" alt="{$user[\'username\']}" /></a>',
		'awards_award'	=> '<img src="{$apkeys[\'path\']}{$award[\'image\']}" alt="{$award[\'name\']}" title="{$award[\'name\']}" />',
		'awards'	=> '<br /><b>{$lang->ougc_feamem_awards}</b><br />{$awards}',
		'groupimage'	=> '<br /><img src="{$displaygroup[\'image\']}" alt="{$usertitle}" title="{$usertitle}" />',
		'newpoints_shop_item'	=> '<a href="{$mybb->settings[\'bburl\']}/newpoints.php?action=shop&amp;shop_action=view&amp;iid={$iid}"><img src="{$mybb->settings[\'bburl\']}/{$item[\'icon\']}" title="{$item[\'name\']}"></a>',
		'newpoints_shop'	=> '<br />{$shop_items}',
		'starimages_star'	=> '<img src="{$starimage}" border="0" alt="*" />',
		'starimages'	=> '<br />{$userstars}',
	));

	require_once MYBB_ROOT.'inc/adminfunctions_templates.php';
	find_replace_templatesets('index', '#'.preg_quote('{$header}').'#i', '{$header}<!--OUGC_FEAMEM-->');
}

// _deactivate function
function ougc_feamem_deactivate()
{
	require_once MYBB_ROOT.'inc/adminfunctions_templates.php';
	find_replace_templatesets('index', '#'.preg_quote('<!--OUGC_FEAMEM-->').'#i', '', 0);
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

	return isset($cache->cache['ougc_feamem']);
}

// _uninstall function
function ougc_feamem_uninstall()
{
	global $db, $cache, $lang, $PL;
	ougc_feamem_load_pl();

	$PL->settings_delete('ougc_feamem');
	$PL->templates_delete('ougcfeamem');
	$PL->cache_delete('ougc_feamem');
}

// Loads language file
function ougc_feamem_load_lang()
{
	global $lang;

	isset($lang->ougc_feamem) or $lang->load('ougc_feamem');
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

	if(my_strpos($page, '<!--OUGC_FEAMEM-->'))
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

		if(empty($user) || empty($data['time']) || $data['time'] <= $hourstime or true) // DEBUG
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

				$where = $and = '';
				if(!empty($mybb->settings['ougc_feamem_groups']))
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
					$where .= '(';
					foreach((array)$gids as $gid)
					{
						$gid = (int)$gid;
						$where .= $or.'usergroup=\''.$gid.'\' OR ';
						if($mysql)
						{
							$where .= 'CONCAT(\',\',additionalgroups,\',\') LIKE \'%,'.$gid.',%\'';
						}
						else
						{
							$where .= '\',\'||additionalgroups||\',\' LIKE \'%,'.$gid.',%\'';
						}
						$or = ' OR ';
					}
					$where .= ')';
					$and = ' AND ';
				}

				if(empty($mybb->settings['ougc_feamem_away']))
				{
					$where .= $and.'away=\'0\'';
				}
	
				$query = $db->simple_select('users', 'uid', $where);
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

			$data['time'] = TIME_NOW;
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

		$profilelink = build_profile_link($user['username'], $user['uid']);
		$username_formatted = format_name($user['username'], $user['usergroup'], $user['displaygroup']);
		$profilelink_formatted = build_profile_link($username_formatted, $user['uid']);

		// Format avatar
		$avatar = '';
		if((bool)$mybb->user['showavatars'] || !$mybb->user['uid'])
		{
			$avatar['image'] = htmlspecialchars_uni($user['avatar']);
			if(my_strpos($avatar['image'], 'http') === false)
			{
				$avatar['image'] = $mybb->settings['bburl'].'/'.$avatar['image'];
			}

			$dimensions = explode('|', $user['avatardimensions']);
			if(isset($dimensions[0]) && isset($dimensions[1]))
			{
				list($maxwidth, $maxheight) = explode('x', my_strtolower($mybb->settings['ougc_feamem_maxavatardim']));
				if($dimensions[0] > (int)$maxwidth || $dimensions[1] > (int)$maxheight)
				{
					require_once MYBB_ROOT.'inc/functions_image.php';
					$scale = scale_image($dimensions[0], $dimensions[1], (int)$maxwidth, (int)$maxheight);
				}
			}

			$avatar['width'] = (int)(!empty($scale['width']) ? $scale['width'] : $dimensions[0]);
			$avatar['height'] = (int)(!empty($scale['height']) ? $scale['height'] : $dimensions[1]);

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

			$gids = '';
			if(isset($mybb->input['ougc_feamem_groups']) && is_array($mybb->input['ougc_feamem_groups']))
			{
				$gids = implode(',', (array)array_filter(array_map('intval', $mybb->input['ougc_feamem_groups'])));
			}

			$mybb->input['upsetting']['ougc_feamem_groups'] = $gids;

			if(isset($mybb->input['ougc_feamem_reset']))
			{
				ougc_feamem_cache_update();
				unset($mybb->input['ougc_feamem_reset']);
			}
			elseif($mybb->input['upsetting']['ougc_feamem_groups'] != $settings['ougc_feamem_groups'] || $mybb->input['upsetting']['ougc_feamem_uid'] != $settings['ougc_feamem_uid'] || $mybb->input['upsetting']['ougc_feamem_away'] != $settings['ougc_feamem_away'])
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

	if(empty($user['avatar']) || empty($user['avatardimensions']))
	{
		$user['avatar'] = $settings['ougc_feamem_avatar'];
		$user['avatardimensions'] = $settings['ougc_feamem_avatardim'];
	}

	$user['username'] = htmlspecialchars_uni($user['username']);
	$user['profilelink'] = get_profile_link($user['uid']);

	if(!$user['displaygroup'])
	{
		$user['displaygroup'] = $user['usergroup'];
	}
}

function ougc_feamem_formcontainer_output_row(&$args)
{
	if($args['row_options']['id'] == 'row_setting_ougc_feamem_groups')
	{
		global $form, $settings, $lang;
		ougc_feamem_load_lang();

		$args['content'] = $form->generate_group_select('ougc_feamem_groups[]', explode(',', $settings['ougc_feamem_groups']), array('multiple' => true, 'size' => 5));
	}
}

function ougc_feamem_output_submit_wrapper(&$args)
{
	global $page, $form;

	$args[0] = $form->generate_submit_button('Reset User', array('name' => 'ougc_feamem_reset')).$args[0];
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