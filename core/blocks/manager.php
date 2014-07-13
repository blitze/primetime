<?php
/**
 *
 * @package primetime
 * @copyright (c) 2013 Daniel A. (blitze)
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace primetime\primetime\core\blocks;

use Symfony\Component\DependencyInjection\Container;

class manager
{
	/** @var \phpbb\cache\service */
	protected $cache;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver */
	protected $db;

	/** @var Container */
	protected $phpbb_container;

	/** @var \phpbb\request\request_interface */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \primetime\primetime\core\icon_picker */
	protected $icons;

	/** @var \primetime\primetime\core\primetime */
	protected $primetime;

	/** @var \primetime\primetime\core\block_template */
	protected $ptemplate;

	/** @var string */
	private $blocks_table;

	/** @var string */
	private $blocks_config_table;

	/** @var string */
	private $block_routes_table;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string phpEx */
	protected $php_ext;

	/** @var string */
	private $def_icon;

	/** @var string */
	private $style_id = 0;

	/**
	 * Constructor
	 *
	 * @param \phpbb\cache\service						$cache					Cache object
	 * @param \phpbb\config\config						$config					Config object
	 * @param \phpbb\db\driver\factory					$db						Database object
	 * @param \phpbb\request\request_interface			$request				Request object
	 * @param Container									$phpbb_container		Service container
	 * @param \phpbb\template\template					$template				Template object
	 * @param \phpbb\user								$user					User object
	 * @param \primetime\primetime\core\icon_picker		$icons					Primetime icon picker object
	 * @param \primetime\primetime\core\primetime		$primetime				Template object
	 * @param \primetime\primetime\core\template		$ptemplate				Primetime template object
	 * @param string									$blocks_table			Name of the blocks database table
	 * @param string									$blocks_config_table	Name of the blocks_config database table
	 * @param string									$block_routes_table		Name of the block_routes database table
	 * @param string									$php_ext				phpEx
	 */
	public function __construct(\phpbb\cache\driver\driver_interface $cache, \phpbb\config\config $config, \phpbb\db\driver\factory $db, Container $phpbb_container, \phpbb\request\request_interface $request, \phpbb\template\template $template, \phpbb\user $user, \primetime\primetime\core\icon_picker $icons, \primetime\primetime\core\primetime $primetime, \primetime\primetime\core\template $ptemplate, $blocks_table, $blocks_config_table, $block_routes_table, $root_path, $php_ext)
	{
		$this->cache = $cache;
		$this->config = $config;
		$this->db = $db;
		$this->phpbb_container = $phpbb_container;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->icons = $icons;
		$this->primetime = $primetime;
		$this->ptemplate = $ptemplate;
		$this->blocks_table = $blocks_table;
		$this->block_routes_table = $block_routes_table;
		$this->blocks_config_table = $blocks_config_table;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
		$this->def_icon = '';
	}

	/**
	 * Handle the admin bar
	 */
	public function handle($route, $style_id)
	{
		$edit_mode = $this->request->variable('edit_mode', false);

		$page_url = str_replace('../', '', rtrim(build_url(array('edit_mode')), '?'));

		$this->set_style($style_id);

		$asset_path = $this->primetime->asset_path;
		$this->primetime->add_assets(array(
			'js'		=> array(
				'//ajax.googleapis.com/ajax/libs/jqueryui/' . JQUI_VERSION . '/jquery-ui.min.js',
				$asset_path . 'ext/primetime/primetime/assets/js/t.js',
				100 =>  $asset_path . 'ext/primetime/primetime/assets/blocks/manager.js',
			),
			'css'   => array(
				'//ajax.googleapis.com/ajax/libs/jqueryui/' . JQUI_VERSION . '/themes/base/jquery-ui.css',
				$asset_path . 'ext/primetime/primetime/assets/blocks/manager.css',
			)
		));

		$is_default_route = $u_default_route = false;
		if ($this->config['primetime_default_layout'])
		{
			$is_default_route = ($this->config['primetime_default_layout'] === $route) ? true : false;
			$u_default_route = append_sid($asset_path . $this->config['primetime_bconfigdefault_layout']);
		}

		$this->template->assign_vars(array(
			'S_ADMIN_BLOCKS'	=> true,
			'S_EDIT_MODE'		=> $edit_mode,
			'S_IS_DEFAULT'		=> $is_default_route,
			'U_DEFAULT_LAYOUT'	=> $u_default_route,
			'U_EDIT_MODE'		=> append_sid($page_url, 'edit_mode=1'),
			'U_DISP_MODE'		=> $page_url,
			'UA_ROUTE'			=> $route,
			'UA_AJAX_URL'		=> $this->root_path . 'app.' . $this->php_ext)
		);

		if ($edit_mode !== false)
		{
			$this->user->add_lang_ext('primetime/primetime', 'block_manager');

			$lang_set_ext = array();

			/**
			 * Event to load block config language files
			 *
			 * @var	array	lang_set_ext		Array containing entries of format
			 * 					array(
			 * 						'ext_name' => (string) [extension name],
			 * 						'lang_set' => (string|array) [language files],
			 * 					)
			 * 					This is to be used only to add language files that are used when editing block configuartion
			 */
			$vars = array('lang_set_ext');
			extract($this->phpbb_container->get('dispatcher')->trigger_event('primetime.blocks.add_lang', compact($vars)));

			foreach ($lang_set_ext as $ext_lang_pair)
			{
				$this->user->add_lang_ext($ext_lang_pair['ext_name'], $ext_lang_pair['lang_set']);
			}
			unset($lang_set_ext);

			$controller_service = explode(':', $this->phpbb_container->get('symfony_request')->attributes->get('_controller'));

			$ext_name = '';
			if (!empty($controller_service[0]) && $this->phpbb_container->has($controller_service[0]))
			{
				$controller = $this->phpbb_container->get($controller_service[0]);

				list($namespace, $ext) = explode('\\', get_class($controller));
				$ext_name = "$namespace/$ext";
			}

			$this->get_available_blocks();
			$route_info = $this->get_route_info($route);

			$hide_blocks = false;
			$ex_positions = array();

			if (sizeof($route_info))
			{
				$ex_positions = explode(',', $route_info['ex_positions']);
				$hide_blocks = $route_info['hide_blocks'];
			}

			$this->template->assign_vars(array(
				'ICON_PICKER'		=> $this->icons->picker(),
				'UA_EXTENSION'		=> $ext_name,
				'UA_STYLE_ID'		=> $style_id,
				'S_ROUTE_OPS'		=> $this->get_route_options($route),
				'S_HIDE_BLOCKS'		=> $hide_blocks,
				'S_POSITION_OPS'	=> $this->get_position_options($ex_positions))
			);
		};

		return $edit_mode;
	}

	/**
	 * Get all available primetime blocks
	 */
	public function get_available_blocks()
	{
		if (($blocks = $this->cache->get('primetime_available_blocks')) === false)
		{
			$factory = $this->phpbb_container->get('primetime.blocks.factory');

			$blocks = $factory->get_all_blocks();
			$this->cache->put('primetime_available_blocks', $blocks);
		}

		foreach ($blocks as $service => $name)
		{
			$lname = strtoupper(str_replace('.', '_', $name));
			$this->template->assign_block_vars('block', array(
				'NAME'		=> (isset($this->user->lang[$lname])) ? $this->user->lang[$lname] : $name,
				'SERVICE'	=> $service)
			);
		}
	}

	/**
	 * Get all routes with blocks
	 */
	public function get_all_routes()
	{
		if (($routes = $this->cache->get('primetime_block_routes')) === false)
		{
			$sql = 'SELECT *
				FROM ' . $this->block_routes_table . '
				WHERE style = ' . $this->style_id;
			$result = $this->db->sql_query($sql);

			$routes = array();
			while ($row = $this->db->sql_fetchrow($result))
			{
				$routes[$row['style']][$row['route']] = $row;
			}
			$this->db->sql_freeresult($result);

			$this->cache->put('primetime_block_routes', $routes);
		}

		return $routes;
	}

	/**
	 * Get routes with blocks
	 */
	public function get_route_options($ex_route)
	{
		$sql_array = array(
			'SELECT'	=> 'r.route',

			'FROM'	  => array(
				$this->blocks_table			=> 'b',
				$this->block_routes_table	=> 'r',
			),

			'WHERE'	 => 'b.route_id = r.route_id
				AND b.style = ' . $this->style_id .
				(($ex_route) ? " AND r.route <> '" . $this->db->sql_escape($ex_route) . "'" : ''),

			'GROUP_BY'  => 'r.route',

			'ORDER_BY'  => 'r.route',
		);

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);

		$options = '<option value="">' . $this->user->lang['SELECT'] . '</option>';
		while ($row = $this->db->sql_fetchrow($result))
		{
			$options .= '<option value="' . $row['route'] . '">' . $row['route'] . '</option>';
		}
		$this->db->sql_freeresult($result);

		return $options;
	}

	/**
	 * Get route info, if it exists
	 */
	public function get_route_info($route)
	{
		$routes = $this->get_all_routes();

		return (isset($routes[$this->style_id][$route])) ? $routes[$this->style_id][$route] : array();
	}

	/**
	 * Get the id of the current route. If it does not exist, add the route and get the id
	 */
	public function get_route_id($route)
	{
		$routes = $this->get_all_routes();

		return (isset($routes[$this->style_id][$route])) ? $routes[$this->style_id][$route]['route_id'] : $this->add_route($route);
	}

	/**
	 * Add a new route
	 */
	public function add_route($route)
	{
		$ext_name = $this->request->variable('ext', '');

		$sql_data = array(
			'ext_name'		=> $ext_name,
			'route'			=> $route,
			'style'			=> $this->style_id,
			'hide_blocks'	=> false,
			'ex_positions'	=> '',
		);
		$this->db->sql_query('INSERT INTO ' . $this->block_routes_table . ' ' . $this->db->sql_build_array('INSERT', $sql_data));

		$this->cache->destroy('primetime_block_routes');

		return $this->db->sql_nextid();
	}

	/**
	 * Set route preferences
	 */
	public function set_route_prefs($route, $data)
	{
		$route_id = $this->get_route_id($route);
		$blocks = $this->get_blocks($route, 'id');

		$default_prefs = array(
			'hide_blocks'	=> false,
			'ex_positions'	=> '',
		);

		if (sizeof($blocks) || $data != $default_prefs)
		{
			return $this->update_route($route_id, $data);
		}
		else
		{
			$this->delete_route($route_id);
			return array();
		}
	}

	/**
	 * Update route data
	 */
	public function update_route($route_id, $sql_data)
	{
		if (!$route_id)
		{
			return array();
		}

		$this->db->sql_query('UPDATE ' . $this->block_routes_table . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_data) . ' WHERE route_id = ' . (int) $route_id);
		$this->cache->destroy('primetime_block_routes');

		return array_merge(
			$sql_data,
			array('message' => $this->user->lang['ROUTE_UPDATED'])
		);
	}

	/**
	 * Delete a route
	 */
	public function delete_route($route_id)
	{
		$this->db->sql_query('DELETE FROM ' . $this->block_routes_table . '
			WHERE route_id = ' . (int) $route_id . '
				AND style = ' . $this->style_id);

		$this->cache->destroy('primetime_block_routes');
	}

	/**
	 * Get position options
	 */
	public function get_position_options($selected_positions)
	{
		$sql_ary = array(
			'SELECT'	=> 'b.position',
			'FROM'		=> array(
				$this->blocks_table	=> 'b'
			),
			'WHERE'		=> 'b.style = ' . $this->style_id
		);
		$sql = $this->db->sql_build_query('SELECT_DISTINCT', $sql_ary);
		$result = $this->db->sql_query($sql);

		$options = '<option value=""' . ((!sizeof($selected_positions)) ? ' selected="selected"' : '') . '>' . $this->user->lang['NONE'] . '</option>';
		while ($row = $this->db->sql_fetchrow($result))
		{
			$selected = (in_array($row['position'], $selected_positions)) ? ' selected="selected"' : '';
			$options .= '<option value="' . $row['position'] . '"' . $selected . '>' . $row['position'] . '</option>';
		}
		$this->db->sql_freeresult($result);

		return $options;
	}

	/**
	 * Add a primetime block
	 */
	public function add($service, $route)
	{
		$position = $this->request->variable('position', '');
		$weight = $this->request->variable('weight', 0);

		if (!$this->phpbb_container->has($service))
		{
			$this->return_data['errors'] = $this->user->lang['BLOCK_NOT_FOUND'];
			return;
		}

		$block_data = array(
			'icon'			=> '',
			'title'			=> '',
			'name'			=> $service,
			'weight'		=> $weight,
			'position'		=> $position,
			'route_id'		=> $this->get_route_id($route),
			'style'			=> $this->style_id,
			'hide_title'	=> false,
			'no_wrap'		=> false,
		);
		$this->db->sql_query('INSERT INTO ' . $this->blocks_table . ' ' . $this->db->sql_build_array('INSERT', $block_data));
		$block_data['bid'] = $this->db->sql_nextid();

		$b = $this->phpbb_container->get($service);
		$bconfig = $b->get_config(array());

		foreach ($bconfig as $key => $settings)
		{
			if (!is_array($settings))
			{
				continue;
			}
			$block_data['settings'][$key] =& $settings['default'];
		}

		$this->cache->destroy('primetime_blocks');

		return array_merge(
			array('id' => $block_data['bid']),
			$this->display($b, $block_data)
		);
	}

	/**
	 * Update block data
	 */
	public function update($bid, $sql_data, $route)
	{
		if (!$bid)
		{
			return array();
		}

		$this->db->sql_query('UPDATE ' . $this->blocks_table . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_data) . ' WHERE bid = ' . (int) $bid);
		$this->cache->destroy('primetime_blocks');

		$bdata = $this->get_block_data($bid);
		$db_settings = $this->get_block_config($bid);

		if (!$this->phpbb_container->has($bdata['name']))
		{
			return array('errors' => $this->user->lang['BLOCK_NOT_FOUND']);
		}

		$b = $this->phpbb_container->get($bdata['name']);
		$df_settings = $b->get_config($db_settings);

		foreach ($df_settings as $key => $settings)
		{
			if (!is_array($settings))
			{
				continue;
			}

			$db_settings[$key] = (isset($db_settings[$key])) ? $db_settings[$key] : $settings['default'];

			$type = explode(':', $settings['type']);
			if ($db_settings[$key] && ($type[0] == 'checkbox' || $type[0] == 'multi_select'))
			{
				$db_settings[$key] = explode(',', $db_settings[$key]);
			}
			$bdata['settings'][$key] = $db_settings[$key];
		}

		return array_merge(
			$sql_data,
			array(
				'id'		=> $bid,
				'message'	=> $this->user->lang['BLOCK_UPDATED'],
			),
			$this->display($b, $bdata)
		);
	}

	/**
	 * Get Edit Form
	 */
	public function edit($bid)
	{
		if (!function_exists('build_multi_select'))
		{
			include($this->root_path . 'ext/primetime/primetime/blocks.' . $this->php_ext);
		}

		if (!function_exists('build_cfg_template'))
		{
			include($this->root_path . 'includes/functions_acp.' . $this->php_ext);
		}

		if (!$bid)
		{
			return array('errors' => $this->user->lang['BLOCK_NO_ID']);
		}

		$bdata = $this->get_block_data($bid);
		$db_settings = $this->get_block_config($bid);

		$this->template->assign_vars(array(
			'S_ACTIVE'		=> $bdata['status'],
			'S_NO_WRAP'		=> $bdata['no_wrap'],
			'S_HIDE_TITLE'	=> $bdata['hide_title'],
			'S_BLOCK_CLASS'	=> trim($bdata['class']))
		);

		if (!$this->phpbb_container->has($bdata['name']))
		{
			return array('errors' => $this->user->lang['BLOCK_NOT_FOUND']);
		}

		$b = $this->phpbb_container->get($bdata['name']);
		$default_settings = $b->get_config($db_settings);

		// Output relevant settings
		foreach ($default_settings as $config_key => $vars)
		{
			if ((!is_array($vars) && strpos($config_key, 'legend') === false) || (is_array($vars) &&$vars['type'] == 'hidden'))
			{
				continue;
			}

			if (strpos($config_key, 'legend') !== false)
			{
				$this->template->assign_block_vars('options', array(
					'S_LEGEND'	=> $config_key,
					'LEGEND'	=> (isset($this->user->lang[$vars])) ? $this->user->lang[$vars] : $vars)
				);

				continue;
			}

			$type = explode(':', $vars['type']);

			$l_explain = '';
			if ($vars['explain'] && isset($vars['lang_explain']))
			{
				$l_explain = (isset($this->user->lang[$vars['lang_explain']])) ? $this->user->lang[$vars['lang_explain']] : $vars['lang_explain'];
			}
			else if ($vars['explain'])
			{
				$l_explain = (isset($this->user->lang[$vars['lang'] . '_EXPLAIN'])) ? $this->user->lang[$vars['lang'] . '_EXPLAIN'] : '';
			}

			$db_settings[$config_key] = (isset($db_settings[$config_key])) ? $db_settings[$config_key] : $vars['default'];

			if (in_array($type[0], array('checkbox', 'multi_select', 'select')))
			{
				// this looks bad but its the only way without modifying phpbb code
				// this is for select items that do not need to be translated
				$options = $vars['params'][0];
				foreach ($options as $key => $title)
				{
					if (!isset($this->user->lang[$title]))
					{
						$this->user->lang[$title] = $title;
					}
				}

				switch ($type[0])
				{
					case 'select':
						$vars['function'] = (!empty($vars['function'])) ? $vars['function'] : 'build_select';
					break;
					case 'checkbox':
					case 'multi_select':
						$vars['function'] = (!empty($vars['function'])) ? $vars['function'] : (($type[0] == 'checkbox') ? 'build_checkbox' : 'build_multi_select');
						$vars['params'][] = $config_key;
						$type[0] = 'custom';

						if (!empty($db_settings[$config_key]))
						{
							$db_settings[$config_key] = explode(',', $db_settings[$config_key]);
						}
					break;
				}
			}

			$content = build_cfg_template($type, $config_key, $db_settings, $config_key, $vars);

			if (empty($content))
			{
				continue;
			}

			$this->template->assign_block_vars('options', array(
				'KEY'			=> $config_key,
				'TITLE'			=> (isset($this->user->lang[$vars['lang']])) ? $this->user->lang[$vars['lang']] : $vars['lang'],
				'S_EXPLAIN'		=> $vars['explain'],
				'TITLE_EXPLAIN'	=> $l_explain,
				'CONTENT'		=> $content)
			);
			unset($default_settings[$config_key]);
		}
		$bdata['settings'] = $db_settings;

		$this->template->assign_vars(array(
			'S_GROUP_OPS'	=> $this->get_groups('options', $bdata['permission']))
		);

		$this->template->set_filenames(array(
			'block_settings' => 'block_settings.html',
		));

		return array_merge(
			$bdata,
			array(
				'icon'		=> ($bdata['icon']) ? $bdata['icon'] : $this->def_icon,
				'form'		=> $this->template->assign_display('block_settings'),
			),
			$this->display($b, $bdata)
		);
	}

	/**
	 * Save Edit Form
	 */
	public function save($bid, $route)
	{
		if (!function_exists('validate_config_vars'))
		{
			include($this->root_path . 'includes/functions_acp.' . $this->php_ext);
		}

		$bdata = $this->get_block_data($bid);
		$settings = array();

		if (!$this->phpbb_container->has($bdata['name']))
		{
			return array('errors' => $this->user->lang['BLOCK_NOT_FOUND']);
		}

		$b = $this->phpbb_container->get($bdata['name']);
		$df_settings = $b->get_config($settings);

		$class = $this->request->variable('class', '');
		$permission_ary = $this->request->variable('permission', array(0));
		$cfg_array = utf8_normalize_nfc($this->request->variable('config', array('' => ''), true));
		$multi_select = utf8_normalize_nfc($this->request->variable('config', array('' => array('' => ''))));

		$multi_select = array_filter($multi_select);
		foreach ($multi_select as $key => $values)
		{
			$cfg_array[$key] = array_filter($values, 'strlen');
			$cfg_array[$key] = (sizeof($cfg_array[$key])) ? join(',', $cfg_array[$key]) : $df_settings[$key]['default'];
		}

		$errors = array();
		validate_config_vars($df_settings, $cfg_array, $errors);

		if (sizeof($errors))
		{
			return array('errors' => join("\n", $errors));
		}

		$sql_data = array(
			'permission'	=> implode(',', $permission_ary),
			'class'			=> ($class) ? ' ' . $class : '',
			'hide_title'	=> $this->request->variable('hide_title', 0),
			'status'		=> $this->request->variable('status', 0),
			'no_wrap'		=> $this->request->variable('no_wrap', 0),
		);

		if (is_array($cfg_array) && sizeof($cfg_array))
		{
			$sql_ary = array();
			foreach ($cfg_array as $var => $val)
			{
				$bdata['settings'][$var] = $val;
				$sql_ary[] = array(
					'bid'		=> $bid,
					'bvar'		=> $var,
					'bval'		=> $val,
				);
			}

			// just remove old values and replace
			$this->delete_block_config($bid);
			$this->db->sql_multi_insert($this->blocks_config_table, $sql_ary);
		}
		$this->cache->destroy('primetime_blocks');

		if (isset($df_settings['cache_name']))
		{
			$this->cache->destroy($df_settings['cache_name']);
		}

		return $this->update($bid, $sql_data, $route);
	}

	/**
	 * Save all blocks in layout
	 */
	public function save_layout($route)
	{
		$blocks_ary = $removed_blocks = $sql_blocks_ary = array();

		$blocks = $this->request->variable('blocks', array(0 => array('' => '')));

		for ($i = 0, $size = sizeof($blocks); $i < $size; $i++)
		{
			$row = $blocks[$i];
			$row['style'] = $this->style_id;
			$blocks_ary[$row['bid']] = $row;
		}

		$current_blocks = $this->get_blocks($route);

		$sql_blocks_ary = array_intersect_key($current_blocks, $blocks_ary);
		$sql_blocks_ary = array_replace_recursive($sql_blocks_ary, $blocks_ary);
		$removed_blocks = array_keys(array_diff_key($current_blocks, $blocks_ary));

		// Delete all blocks for this route
		$this->delete_blocks(array_keys($current_blocks), false);

		// Remove block settings for deleted blocks
		$this->delete_block_config($removed_blocks);

		// add blocks
		$this->db->sql_multi_insert($this->blocks_table, array_values($sql_blocks_ary));
		$this->cache->destroy('primetime_blocks');

		return array('message' => $this->user->lang['LAYOUT_SAVED']);
	}

	/**
	 * Copy blocks from one route to another
	 */
	public function copy($route, $copy_from)
	{
		if (!$copy_from)
		{
			return array('data' => $this->get_blocks($route));
		}

		// copy route info
		$sql_from_route_info = $this->get_route_info($copy_from);

		$route_id = $this->get_route_id($route);
		$sql_from_route_info['route'] = $route;
		$sql_from_route_info['route_id'] = $route_id;
		$this->update_route($route_id, $sql_from_route_info);

		// copy blocks
		$old_blocks = $this->get_blocks($route, 'id');
		$new_blocks = $this->get_blocks($copy_from, 'data');

		// copy blocks config
		$copied_blocks_config = $this->get_block_config(array_keys($new_blocks), true);

		// delete current blocks
		$this->delete_blocks($old_blocks);

		// get max block id
		$sql = 'SELECT bid FROM ' . $this->blocks_table . ' ORDER BY bid DESC';
		$result = $this->db->sql_query_limit($sql, 1);
		$bid = $this->db->sql_fetchfield('bid');
		$this->db->sql_freeresult($result);

		$sql_blocks = $mapped_ids = array();
		$new_blocks = array_values($new_blocks);

		for ($i = 0, $size = sizeof($new_blocks); $i < $size; $i++)
		{
			$row = $new_blocks[$i];
			$mapped_ids[$row['bid']] = ++$bid;

			$sql_blocks[] = array(
				'bid'			=> $bid,
				'icon'			=> $row['icon'],
				'name'			=> $row['name'],
				'title'			=> $row['title'],
				'route_id'		=> (int) $route_id,
				'position'		=> $row['position'],
				'weight'		=> $row['weight'],
				'style'			=> $row['style'],
				'permission'	=> $row['permission'],
				'class'			=> $row['class'],
				'status'		=> $row['status'],
				'no_wrap'		=> $row['no_wrap'],
				'hide_title'	=> $row['hide_title'],
			);
		}

		$sql_blocks = array_filter($sql_blocks);
		if (sizeof($sql_blocks))
		{
			$this->db->sql_multi_insert($this->blocks_table, $sql_blocks);
		}

		$sql_blocks_config_ary = $db_settings = array();
		for ($i = 0, $size = sizeof($copied_blocks_config); $i < $size; $i++)
		{
			$row = $copied_blocks_config[$i];
			$row['bid'] = (int) $mapped_ids[$row['bid']];
			$sql_blocks_config_ary[] = $row;
			$db_settings[$row['bid']][$row['bvar']] = $row['bval'];
		}

		if (sizeof($sql_blocks_config_ary))
		{
			$this->db->sql_multi_insert($this->blocks_config_table, $sql_blocks_config_ary);
		}

		// Now let's select the new blocks and return data
		$data = array();
		for ($i = 0, $size = sizeof($sql_blocks); $i < $size; $i++)
		{
			$row = $sql_blocks[$i];
			$bid = $row['bid'];
			$db_settings[$bid] = (isset($db_settings[$bid])) ? $db_settings[$bid] : array();

			$b = $this->phpbb_container->get($row['name']);

			$df_settings = $b->get_config($db_settings[$bid]);

			foreach ($df_settings as $key => $settings)
			{
				if (!is_array($settings))
				{
					continue;
				}

				$db_settings[$bid][$key] = (isset($db_settings[$bid][$key])) ? $db_settings[$bid][$key] : $settings['default'];

				$type = explode(':', $settings['type']);
				if ($db_settings[$bid][$key] && ($type[0] == 'checkbox' || $type[0] == 'multi_select'))
				{
					$db_settings[$bid][$key] = explode(',', $db_settings[$bid][$key]);
				}
				$row['settings'][$key] = $db_settings[$bid][$key];
			}

			$data[$row['position']][] = array_merge(
				array(
					'id'			=> $row['bid'],
					'icon'			=> ($row['icon']) ? $row['icon'] : $this->def_icon,
					'class'			=> $row['class'],
					'status'		=> (bool) $row['status'],
					'no_wrap'		=> (bool) $row['no_wrap'],
					'hide_title'	=> (bool) $row['hide_title'],
				),
				$this->display($b, $row)
			);
		}

		$this->cache->destroy('primetime_blocks');

		return array(
			'data'		=> $data,
			'config'	=> $sql_from_route_info,
		);
	}

	/**
	 * Get all blocks for specified route
	 */
	public function get_blocks($route, $return = 'data')
	{
		$sql_array = array(
			'SELECT'	=> 'b.*',

			'FROM'	  => array(
				$this->blocks_table			=> 'b',
				$this->block_routes_table	=> 'r',
			),

			'WHERE'	 => 'b.route_id = r.route_id
				AND b.style = ' . $this->style_id . "
				AND r.route = '" . $this->db->sql_escape($route) . "'",

			'ORDER_BY'  => 'b.position, b.weight ASC',
		);

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);

		$blocks = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$blocks[$row['bid']] = $row;
		}
		$this->db->sql_freeresult($result);

		return ($return == 'id') ? array_keys($blocks) : $blocks;
	}

	/**
	 * Delete all blocks provided by a particular extension
	 */
	public function delete_ext_blocks($ext_name)
	{
		$sql_array = array(
			'SELECT'	=> 'b.bid',

			'FROM'	  => array(
				$this->blocks_table			=> 'b',
				$this->block_routes_table	=> 'r',
			),

			'WHERE'	 => "b.route_id = r.route_id
				AND r.ext_name = '" . $this->db->sql_escape($ext_name) . "'",
		);

		$sql = $this->db->sql_build_query('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);

		$blocks = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$blocks[] = $row['bid'];
		}
		$this->db->sql_freeresult($result);

		$this->delete_blocks($blocks);

		$this->db->sql_query('DELETE FROM ' . $this->block_routes_table . " WHERE ext_name = '" . $this->db->sql_escape($ext_name) . "'");
	}

	public function set_style($style_id)
	{
		$this->style_id = (int) $style_id;
	}

	private function get_block_data($bid)
	{
		$result = $this->db->sql_query('SELECT * FROM ' . $this->blocks_table . ' WHERE bid = ' . (int) $bid);

		$row = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $row;
	}

	private function get_block_config($bid, $raw = false)
	{
		$ids = (is_array($bid)) ? $bid : array($bid);

		$result = $this->db->sql_query('SELECT * FROM ' . $this->blocks_config_table . ' WHERE ' . $this->db->sql_in_set('bid', $ids));

		$bconfig = $data = array();
		while ($row = $this->db->sql_fetchrow($result))
		{
			$data[] = $row;
			$bconfig[$row['bid']][$row['bvar']] = $row['bval'];
		}
		$this->db->sql_freeresult($result);

		return ($raw === false) ? ((!is_array($bid) && sizeof($bconfig)) ? array_shift($bconfig) : $bconfig) : $data;
	}

	private function delete_block_config($bid)
	{
		$bid = (is_array($bid)) ? $bid : array($bid);

		if (!sizeof($bid))
		{
			return;
		}

		$this->db->sql_query('DELETE FROM ' . $this->blocks_config_table . ' WHERE ' . $this->db->sql_in_set('bid', $bid));
	}

	private function delete_blocks($block_ids, $delete_config = true)
	{
		if (!is_array($block_ids))
		{
			$block_ids = array($block_ids);
		}

		$block_ids = array_filter($block_ids);
		if (!sizeof($block_ids))
		{
			return;
		}

		$sql = 'DELETE FROM ' . $this->blocks_table . ' WHERE ' . $this->db->sql_in_set('bid', $block_ids);
		$this->db->sql_query($sql);

		if ($delete_config === true)
		{
			$this->delete_block_config($block_ids);
		}

		$this->cache->destroy('primetime_blocks');
	}

	private function display($block, $settings)
	{
		$block->set_template($this->ptemplate);
		$data = $block->display($settings, true);

		return array(
			'title'		=> (!empty($settings['title'])) ? $settings['title'] : ((isset($this->user->lang[$data['title']])) ? $this->user->lang[$data['title']] : $data['title']),
			'content'	=> (!empty($data['content'])) ? $data['content'] : $this->user->lang['BLOCK_NO_DATA']
		);
	}

	private function get_groups($mode = 'data', $selected = '')
	{
		if (!is_array($selected))
		{
			$selected = explode(',', $selected);
		}

		$sql = 'SELECT group_id, group_name, group_type
			FROM ' . GROUPS_TABLE;
		$result = $this->db->sql_query($sql);

		$data = array();
		$selected = array_filter($selected);
		$options = '<option value="0"' . ((!sizeof($selected)) ? ' selected="selected"' : '') . '>' . $this->user->lang['ALL'] . '</option>';

		while ($row = $this->db->sql_fetchrow($result))
		{
			$selected_option = (in_array($row['group_id'], $selected)) ? ' selected="selected"' : '';
			$group_name = ($row['group_type'] == GROUP_SPECIAL) ? $this->user->lang['G_' . $row['group_name']] : ucfirst($row['group_name']);

			$data[$row['group_id']] = $group_name;
			$options .= '<option' . (($row['group_type'] == GROUP_SPECIAL) ? ' class="sep"' : '') . ' value="' . $row['group_id'] . '"' . $selected_option . '>' . $group_name . '</option>';
		}
		$this->db->sql_freeresult($result);

		return ($mode == 'data') ? $data : $options;
	}
}
