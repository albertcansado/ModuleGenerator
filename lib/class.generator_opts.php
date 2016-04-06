<?php

#-------------------------------------------------------------------------
# Module: ModuleGenerator for CMS Made Simple (@kuzmany)
#
#-------------------------------------------------------------------------
# CMS - CMS Made Simple is (c) 2005 by Ted Kulp (wishy@cmsmadesimple.org)
# This project's homepage is: http://www.cmsmadesimple.org
#
#-------------------------------------------------------------------------
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
# Or read it online: http://www.gnu.org/licenses/licenses.html#GPL
#
#-------------------------------------------------------------------------
/**
 * Description of class
 *
 * @author @kuzmany
 */

class generator_opts {

    public static $fielddefs;

    public function __construct() {
        
    }

    public static function init_admin($mod) {

        $smarty = cmsms()->GetSmarty();
        $images_size_admin = trim($mod->GetPreference('images_size_admin'));

        if (strpos($images_size_admin, 'x') !== FALSE) {
            $images_size_admin_array = explode('x', $images_size_admin);
            $smarty->assign('image_size_admin_width', $images_size_admin_array[0]);
            $smarty->assign('image_size_admin_height', $images_size_admin_array[1]);
        } else {
            $smarty->assign('image_size_admin_width', 200);
            $smarty->assign('image_size_admin_height', 0);
        }
        // parameters that can be called in the module tag
        $mod->CreateParameter('action', 'default', $mod->Lang('help_param_action'));
        $mod->CreateParameter('category', '', $mod->Lang('help_param_category'));
        $mod->CreateParameter('sortby', 'create_date', $mod->Lang('help_param_sortby'));
        $mod->CreateParameter('detailtemplate', '', $mod->Lang('help_param_detailtemplate'));
        $mod->CreateParameter('summarytemplate', '', $mod->Lang('help_param_summarytemplate'));
        $mod->CreateParameter('categorytemplate', '', $mod->Lang('help_param_categorytemplate'));
        $mod->CreateParameter('filtertemplate', '', $mod->Lang('help_param_filtertemplate'));
        $mod->CreateParameter('detailpage', '', $mod->Lang('help_param_detailpage'));
        $mod->CreateParameter('item_id', 0, $mod->Lang('help_param_item'));
        $mod->CreateParameter('pagelimit', 0, $mod->Lang('help_param_pagelimit'));
        $mod->CreateParameter('sortorder', 'desc', $mod->Lang('help_param_sortorder'));
        $mod->CreateParameter('depth', 0, $mod->Lang('help_param_depth'));
        $mod->CreateParameter('inline', 0, $mod->Lang('help_param_inline'));
        $mod->CreateParameter('onerow', '', $mod->Lang('help_param_onerow'));
        $mod->CreateParameter('allrow', '', $mod->Lang('help_param_allrow'));
        $mod->CreateParameter('onecount', '', $mod->Lang('help_param_onecount'));
        //$mod->CreateParameter('item_date_from_to', '', $mod->Lang('help_param_item_date_from_to'));
        $mod->CreateParameter('date_start', '', $mod->Lang('help_param_date_start'));
        $mod->CreateParameter('date_end', '', $mod->Lang('help_param_date_end'));
        $mod->CreateParameter('filter_', '', $mod->Lang('help_param_filter'));
    }

    public static function init($mod) {

        $mod->RegisterModulePlugin();
        $mod->RestrictUnknownParams();

        $mod->SetParameterType('page', CLEAN_INT);
        $mod->SetParameterType('pagelimit', CLEAN_INT);
        $mod->SetParameterType('category_id', CLEAN_INT);
        $mod->SetParameterType('category', CLEAN_STRING);
        $mod->SetParameterType('category_alias', CLEAN_STRING);
        $mod->SetParameterType('sortorder', CLEAN_STRING);
        $mod->SetParameterType('sortby', CLEAN_STRING);
        $mod->SetParameterType('categorytemplate', CLEAN_STRING);
        $mod->SetParameterType('summarytemplate', CLEAN_STRING);
        $mod->SetParameterType('detailtemplate', CLEAN_STRING);
        $mod->SetParameterType('detailpage', CLEAN_STRING);
        $mod->SetParameterType('item_id', CLEAN_INT);
        $mod->SetParameterType('inline', CLEAN_INT);
        $mod->SetParameterType('submit', CLEAN_STRING);
        $mod->SetParameterType('junk', CLEAN_STRING);
        $mod->SetParameterType('onerow', CLEAN_STRING);
        $mod->SetParameterType('allrow', CLEAN_STRING);
        $mod->SetParameterType('onecount', CLEAN_STRING);
        $mod->SetParameterType('items', CLEAN_STRING);
        $mod->SetParameterType('item_date_from_to', CLEAN_STRING);
        $mod->SetParameterType('itemlist', CLEAN_STRING);
        $mod->SetParameterType('date_start', CLEAN_STRING);
        $mod->SetParameterType('date_end', CLEAN_STRING);
        $mod->SetParameterType('cd_origpage', CLEAN_STRING);
        $mod->SetParameterType('nocache', CLEAN_INT);
        $mod->SetParameterType('cache_key', CLEAN_STRING);
        $mod->SetParameterType('preview', CLEAN_STRING);
        $mod->SetParameterType('depth', CLEAN_INT);
        $mod->SetParameterType(CLEAN_REGEXP . '/filter_*/', CLEAN_STRING);
        $mod->SetParameterType(CLEAN_REGEXP . '/datefrom*/', CLEAN_STRING);
        $mod->SetParameterType(CLEAN_REGEXP . '/dateto*/', CLEAN_STRING);
        $mod->SetParameterType(CLEAN_REGEXP . '/' . $mod->_GetModuleAlias() . '_*/', CLEAN_STRING);
    }

    public static function init_static_routes($mod) {

        $db = cmsms()->GetDb();

        if ($mod->GetPreference('item_url_edit')) {
            // custom url support
            $query = 'SELECT item_id,url FROM ' . cms_db_prefix() . 'module_' . $mod->_GetModuleAlias() . '_item
                           WHERE active = 1 AND url != \'\'';
            $tmp = $db->GetArray($query);
            if (is_array($tmp)) {
                foreach ($tmp as $one) {
                    self::register_static_route($mod, $one['url'], $one['item_id']);
                }
            }
        }

        $default_detailpage = $mod->GetPreference('item_category_returnid');

        $route = new CmsRoute('/' . $mod->GetPreference('url_prefix', munge_string_to_url($mod->GetName(), true)) . '\/[cC]\/(?P<category_id>[0-9]+)\/(?P<returnid>[0-9]+)\/(?P<junk>.*?)\/(?P<categorytemplate>[0-9]+?)$/', $mod->GetName(), array('action' => 'default'));
        cms_route_manager::add_static($route);
        $route = new CmsRoute('/' . $mod->GetPreference('url_prefix', munge_string_to_url($mod->GetName(), true)) . '\/[cC]\/(?P<category_id>[0-9]+)\/(?P<returnid>[0-9]+)\/(?P<junk>.*?)$/', $mod->GetName(), array('action' => 'default'));
        cms_route_manager::add_static($route);
        $route = new CmsRoute('/' . $mod->GetPreference('url_prefix', munge_string_to_url($mod->GetName(), true)) . '\/[cC]\/(?P<category_id>[0-9]+)\/(?P<junk>.*?)$/', $mod->GetName(), array('action' => 'default', 'returnid' => $default_detailpage));
        cms_route_manager::add_static($route);
        $route = new CmsRoute('/' . $mod->GetPreference('url_prefix', munge_string_to_url($mod->GetName(), true)) . '\/[cC]\/(?P<category_id>[0-9]+)$/', $mod->GetName(), array('action' => 'default', 'returnid' => $default_detailpage));
        cms_route_manager::add_static($route);

        $default_detailpage = $mod->GetPreference('item_detail_returnid');

        $route = new CmsRoute('/' . $mod->GetPreference('url_prefix', munge_string_to_url($mod->GetName(), true)) . '\/(?P<item_id>[0-9]+)\/(?P<returnid>[0-9]+)\/(?P<junk>.*?)\/(?P<detailtemplate>[0-9]+?)$/', $mod->GetName(), array('action' => 'detail'));
        cms_route_manager::add_static($route);
        $route = new CmsRoute('/' . $mod->GetPreference('url_prefix', munge_string_to_url($mod->GetName(), true)) . '\/(?P<item_id>[0-9]+)\/(?P<returnid>[0-9]+)\/(?P<junk>.*?)$/', $mod->GetName(), array('action' => 'detail'));
        cms_route_manager::add_static($route);
        $route = new CmsRoute('/' . $mod->GetPreference('url_prefix', munge_string_to_url($mod->GetName(), true)) . '\/(?P<item_id>[0-9]+)\/(?P<junk>.*?)$/', $mod->GetName(), array('action' => 'detail', 'returnid' => $default_detailpage));
        cms_route_manager::add_static($route);
        $route = new CmsRoute('/' . $mod->GetPreference('url_prefix', munge_string_to_url($mod->GetName(), true)) . '\/(?P<item_id>[0-9]+)$/', $mod->GetName(), array('action' => 'detail', 'returnid' => $default_detailpage));
        cms_route_manager::add_static($route);    
    }

    public static function register_static_route($mod, $url, $item_id) {
        // add static url
        $detailpage = $mod->GetPreference('item_detail_returnid', -1);
        if ($detailpage == -1) {
            $contentops = cmsms()->GetContentOperations();
            $detailpage = $contentops->GetDefaultContent();
        }
        cms_route_manager::del_static('', $mod->GetName(), $item_id);
        if ($url != '') {
            $parms = array('action' => 'detail', 'returnid' => $detailpage,
                'item_id' => $item_id);
            $route = CmsRoute::new_builder($url, $mod->GetName(), $item_id, $parms, TRUE);
            cms_route_manager::add_static($route);
        }
    }

    public static function get_module($name) {
        $module = cms_utils::get_module('ModuleGenerator');
        $db = cmsms()->GetDb();
        return $db->GetRow('SELECT * FROM ' . cms_db_prefix() . 'module_generator WHERE module_name = ?', array($name));
    }

    public static function delete($name) {
        $module = cms_utils::get_module('ModuleGenerator');
        $db = cmsms()->GetDb();
        return $db->Execute('DELETE FROM ' . cms_db_prefix() . 'module_generator WHERE module_name = ?', array($name));
    }

    public static function updateattach($name, $attach) {
        $module = cms_utils::get_module('ModuleGenerator');
        $db = cmsms()->GetDb();
        return $db->Execute('UPDATE ' . cms_db_prefix() . 'module_generator SET attach = ?, modified_date = NOW() WHERE module_name = ?', array($attach, $name));
    }

    public static function updatedate($name) {
        $module = cms_utils::get_module('ModuleGenerator');
        $db = cmsms()->GetDb();
        return $db->Execute('UPDATE ' . cms_db_prefix() . 'module_generator SET modified_date = NOW() WHERE module_name = ?', array($name));
    }

    public static function insert($name, $attach = '') {
        $module = cms_utils::get_module('ModuleGenerator');
        $db = cmsms()->GetDb();
        return $db->Execute('INSERT INTO ' . cms_db_prefix() . 'module_generator (module_name,attach, create_date, modified_date) VALUES (?,?,NOW(),NOW())', array($name, $attach));
    }

    public static function get_modules_list() {
        return extended_tools_opts::to_hash(self::get_modules(), 'module_name', 'id');
    }

    public static function get_modules() {
        $items = array();
        $module = cms_utils::get_module('ModuleGenerator');
        $db = cmsms()->GetDb();
        $items = $db->GetArray('SELECT * FROM ' . cms_db_prefix() . 'module_generator');
        return $items;
    }

    public static function get_attach_modules($attachmodules) {
        $items = array();
        $module = cms_utils::get_module('ModuleGenerator');
        $db = cmsms()->GetDb();
        $items = $db->GetArray('SELECT id,module_name FROM ' . cms_db_prefix() . 'module_generator WHERE FIND_IN_SET(id,?) > 0', array($attachmodules));
        if (!$items)
            return;

        $items = extended_tools_opts::to_hash($items, 'module_name', 'id');
        $modules = array_keys($items);

        return implode(', ', $modules);
    }

    public static function fill_item_from_formparams($mod, array &$row, $params) {
        foreach ($params as $key => $value) {
            switch ($key) {
                default:
                    $row[$key] = $value;
                    break;
            }
        }

        // get category params
        $category = generator_tools::get_category($mod, $row["category"]);
        $row["category_name"] = $category["category_name"];
        $row["category_alias"] = $category["category_alias"];
        $row['file_location'] = generator_tools::file_location($mod, $row);

        
        // get custom fields
        if (isset($params['customfield']) && is_array($params['customfield'])) {
            self::$fielddefs = $fielddefs = generator_fields::get_processed_fields_values($mod, $row["item_id"]);                    
            foreach (self::$fielddefs as $field) {
                self::$fielddefs[$field["fielddef_id"]] = $field;
            }
            foreach ($params['customfield'] as $key => $value) {
                if (!isset(self::$fielddefs[$key]))
                    continue;
                if (is_array($value)) {
                    self::$fielddefs[self::$fielddefs[$key]["alias"]]["value"] = implode(',', $value);
                } else {
                    self::$fielddefs[self::$fielddefs[$key]["alias"]]["value"] = $value;
                }
                unset(self::$fielddefs[$key]);
            }
        }           
    }

}

?>
