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

class generator_tools {

    private static $search_date_end = null;
    private static $_geolocator;

    public static $items = null;
    public static $fields_blocklist = array('item_id', 'category_id', 'title', 'alias', 'url', 'position', 'active', 'item_date', 'create_date', 'modified_date', 'category_id', 'category_name', 'category_alias');

    public function __construct() {
        
    }

    public static function get_field_defs($mod, $type = null, $filter_frontend = null, $filter_admin = null, $section = 'items') {
        $parms = array();
        $db = cmsms()->GetDb();

        $query = 'SELECT fielddef_id, name, alias, help, type, position, required, searchable, extra, editview, hidename, filter_frontend, filter_admin FROM ' . cms_db_prefix() . 'module_' . $mod->_GetModuleAlias() . '_fielddef';
        $query .= " WHERE section = ? ";
        $parms[] = $section;

        if ($type) {
            $query .= " AND type  = ?  ";
            $parms[] = $type;
        }
        if ($filter_frontend) {
            $query .= " AND filter_frontend  = ?  ";
            $parms[] = $filter_frontend;
        }
        if ($filter_admin) {
            $query .= " AND filter_admin  = ?  ";
            $parms[] = $filter_admin;
        }
        $query .= "  ORDER BY position";
        $dbresult = $db->GetAll($query, $parms);
        return $dbresult;
    }

    public static function get_field_val($mod, $item_id, $field_alias) {
        $db = cmsms()->GetDb();
        $query = 'SELECT value ' . cms_db_prefix() . 'module_' . $mod->_GetModuleAlias() . '_fieldval ';
        $query .= " WHERE  item_id = ? AND fielddef_id = ?";
        $value = $db->GetOne($query, $parms);
        return $value;
    }

    /**
     *
     * @param string $data
     * @return timestamp 
     */
    public static function process_filter_date($date) {
        $date_from = '';
        switch (strtolower($date)) {
            case "now":
                $date_from = time();
                break;
            default:
                $date_array = explode(':', $date);
                if (count($date_array) == 2) {
                    $date_time = strtotime("+" . $date_array[0] . " day");
                    $date_from = mktime($date_array[1], 0, 0, date("m", $date_time), date("d", $date_time), date("Y", $date_time));
                } elseif (count($date_array) == 1) {
                    $date_from = strtotime("+" . $date_array[0] . " hours");
                }
                break;
        }
        return $date_from;
    }

    /**
     *
     * @param string $data
     * @return timestamp 
     */
    public static function process_filter_date_recursive($date) {
        $date_from = '';
        switch (strtolower($date)) {
            case "now":
                $date_from = "e.value = ? AND date_format(c.item_date,'%H:%i:%s') > ?";
                break;
            default:
                $date_array = explode(':', $date);
                if (count($date_array) == 2) {
                    $date_time = strtotime("+" . $date_array[0] . " day");
                    $date_from = mktime($date_array[1], 0, 0, date("m", $date_time), date("d", $date_time), date("Y", $date_time));
                } elseif (count($date_array) == 1) {
                    $date_from = strtotime("+" . $date_array[0] . " hours");
                }
                break;
        }
        return $date_from;
    }

    private static $_categories = array();

    public static function get_category($mod, $category_id) {
        $db = cmsms()->GetDb();
        if (!isset(self::$_categories[$category_id])) {
            $query = "SELECT * FROM " . cms_db_prefix() . "module_" . $mod->_GetModuleAlias() . "_categories WHERE category_id = ?";
            $category = $db->GetRow($query, array($category_id));
            self::$_categories[$category["category_id"]] = $category;
        }

        return self::$_categories[$category_id];
    }

    /**
     * get category list
     * @param object $mod
     * @return array 
     */
    public static function get_category_list($mod, $where = array(), $paramarray = array(), $field = 'long_name') {
        $db = cmsms()->GetDb();
        $categorylist = array();

        $categorylist[$mod->Lang('allcategories')] = '';

        $query = "SELECT * FROM " . cms_db_prefix() . "module_" . $mod->_GetModuleAlias() . "_categories ";

        if (empty($where) == false) {
            $query = $query . ' WHERE ' . implode(' AND ', $where);
        }

        $query .=" ORDER BY hierarchy_position";
        $dbresult = $db->Execute($query, $paramarray);
        while ($dbresult && $row = $dbresult->FetchRow()) {
            if ($field == false) {
                $categorylist[] = $row;
            } else {
                $categorylist[$row[$field]] = $row[$field];
            }
        }
        return $categorylist;
    }

    public static function get_categories($mod, $id, &$params, $returnid = -1) {

        $gCms = cmsms();
        $db = cmsms()->GetDb();
        $depth = 1;

        $inline = $mod->GetPreference('display_inline', false);

        if (isset($params['inline']))
            $inline = $params['inline'];

        $default_detailpage = $mod->GetPreference('item_category_returnid', '');
        $detailpage = $default_detailpage;
        $detailpage = extended_tools_opts::detailpage($params, $detailpage);

        $categorytemplate = '';

// sort order
        $sortorder = 'ASC';
        if (isset($params['sortorder'])) {
            switch (strtolower($params['sortorder'])) {
                case 'asc':
                case 'desc':
                    $sortorder = $params['sortorder'];
            }
        }

        $query = '
		SELECT category_id, category_name, category_alias, parent_id, hierarchy, hierarchy_position, long_name
		FROM ' . cms_db_prefix() . 'module_' . $mod->_GetModuleAlias() . '_categories 
		WHERE 1 
	';
        if (isset($params['category']) && $params['category'] != '') {
            $categories = explode(',', $params['category']);
            $query .= ' AND (';
            $count = 0;
            foreach ($categories as $onecat) {
                if ($count > 0) {
                    $query .= ' OR ';
                }
                if (strpos($onecat, '|') !== FALSE || strpos($onecat, '*') !== FALSE)
                    $query .= "upper(long_name) like upper('" . trim(str_replace('*', '%', $onecat)) . "')";
                else
                    $query .= "category_name = '" . trim($onecat) . "'";
                $count++;
            }
            $query .= ') ';
        }

        if (isset($params['category_id']) && $params['category_id'] != '') {
            $query .= ' AND category_id = ' . $params['category_id'];
        }

        if (isset($params['depth']) && $params['depth'] > 0) {
            $toDepth = $params['depth'] - 1;
            $query .= " AND hierarchy REGEXP '^([0-9]{5})([\.][0-9]{5}){" . $toDepth . "}$' ";
        }
        
        $query .= ' ORDER by hierarchy_position ' . $sortorder;
        $dbresult = $db->Execute($query);
        $rowcounter = 0;

        $fieldefs = ''; {
            $tmp = generator_tools::get_fields($mod, false, 'categories');

            if (is_array($tmp)) {
                $fielddefs = array();
                for ($i = 0; $i < count($tmp); $i++) {
                    $obj = $tmp[$i];
                    $fielddefs[$obj->alias] = $obj;
                }
            }
        }

        while ($dbresult && $row = $dbresult->FetchRow()) {
            $q2 = "SELECT COUNT(item_id) as cnt FROM " . cms_db_prefix() . "module_" . $mod->_GetModuleAlias() . "_item WHERE category_id=?";
            if (isset($params['showarchive']) && $params['showarchive'] == true) {
                $q2 .= " AND (item_date < " . $db->DBTimeStamp(time()) . ") ";
            }
            $q2 .= ' AND active = \'1\'';

            $dbres2 = $db->Execute($q2, array($row['category_id']));
            $count = $dbres2->FetchRow();

            //Count children categories items
            $q3 = "SELECT COUNT(item_id) as cnt FROM " . cms_db_prefix() . "module_" . $mod->_GetModuleAlias() . "_item WHERE category_id IN (SELECT category_id FROM " . cms_db_prefix() . "module_" . $mod->_GetModuleAlias() . "_categories WHERE hierarchy_position LIKE ?) AND active = 1";
            $dbres3 = $db->Execute($q3, array($row['hierarchy_position'] . '%'));
            $countAll = $dbres3->FetchRow();

            //Child have another child?
            $q4 = "SELECT COUNT(*) as cnt FROM " . cms_db_prefix() . "module_" . $mod->_GetModuleAlias() . "_categories WHERE parent_id IN (SELECT category_id FROM " . cms_db_prefix() . "module_" . $mod->_GetModuleAlias() . "_categories WHERE hierarchy_position LIKE ?)";
            $dbres4 = $db->Execute($q4, array($row['hierarchy_position'] . '.%'));
            $leaf = $dbres4->FetchRow();

            $row['index'] = $rowcounter++;
            $row['count'] = $count['cnt'];
            $row['countAll'] = $countAll['cnt'];
            $row['leaf'] = ((int)$leaf['cnt'] > 0) ? 1 : 0;
            $row['prevdepth'] = $depth;
            $depth = count(explode('.', $row['hierarchy_position']));
            $row['depth'] = $depth;



            $prettyurl = generator_tools::get_pretty_url($this, generator_tools::get_prefix($mod) . '/c', $row['category_id'], $row['category_alias'], ($default_detailpage == -1 && !isset($params["detailpage"]) ? '' : $detailpage), $categorytemplate);
            $row['url'] = $mod->CreateFrontendLink($id, $returnid, 'default', '', array('category_id' => $row['category_id'], 'categorytemplate' => $categorytemplate), '', true, isset($params["inline"]) ? 1 : 0, '', false, $prettyurl);
            $row["item_id"] = $row["category_id"];
            $row['file_location'] = generator_tools::file_location($mod, $row, false);

            // add custom fields
            $fielddefs = generator_fields::get_processed_fields_values($mod, $row['category_id'], 'categories');
            $fields = array();
            foreach ($fielddefs as $onedef) {
                if (empty($onedef["alias"]) == false && in_array($onedef["alias"], generator_tools::$fields_blocklist) == false) {
                    $row[$onedef["alias"]] = $onedef["value"];
                }
            }
            $row['fields'] = $fielddefs;


            $items[] = $row;
        }
        return $items;
    }

    /**
     * get item
     * @param object $mod
     * @param int $item_id
     * @return array 
     */
    public static function get_items($mod) {
        $db = cmsms()->GetDb();
        $sortorder = $mod->GetPreference('sortorder_' . $mod->GetPreference('mode'), 'desc');
        $sortby = $mod->GetPreference('sortby_' . $mod->GetPreference('mode'), 'position');
        $items = $db->GetAll('SELECT * FROM ' . cms_db_prefix() . 'module_' . $mod->_GetModuleAlias() . '_item ORDER BY ' . $sortorder . ' ' . $sortby, array($item_id));
        return $items;
    }

    /**
     * get item
     * @param object $mod
     * @param int $item_id
     * @return array 
     */
    public static function get_item($mod, $item_id) {
        $data = cms_utils::get_app_data($mod->GetName() . __FUNCTION__ . $item_id);
        if ($data)
            return $data;

        $db = cmsms()->GetDb();
        $value = $db->GetRow('SELECT * FROM ' . cms_db_prefix() . 'module_' . $mod->_GetModuleAlias() . '_item WHERE item_id = ?', array($item_id));
        cms_utils::set_app_data($mod->GetName() . __FUNCTION__ . $item_id, $value);

        return $value;
    }

    /**
     * get item
     * @param object $mod
     * @param int $image_id
     * @return array 
     */
    public static function get_image($mod, $image_id) {
        $data = cms_utils::get_app_data($mod->GetName() . __FUNCTION__ . $image_id);
        if ($data)
            return $data;

        $db = cmsms()->GetDb();
        $value = $db->GetRow('SELECT * FROM ' . cms_db_prefix() . 'module_' . $mod->_GetModuleAlias() . '_images WHERE image_id = ?', array($image_id));
        cms_utils::set_app_data($mod->GetName() . __FUNCTION__ . $image_id, $value);

        return $value;
    }

    public static function get_searchable_date_end() {
        return self::$search_date_end;
    }

    public static function get_searchable_text($mod, $item_id) {

        $results = array();

        $item = self::get_item($mod, $item_id);

        if ($item['active'] != 1)
            return array();

        $results[] = $item['title'];
        $results[] = $item['alias'];

// date end for search results
        self::$search_date_end = null;
        if ($mod->GetPreference('search_date_end')) {
            $item = self::get_item($mod, $item_id);
            self::$search_date_end = strtotime($item["item_date_end"]);
        }

        $defs = generator_fields::get_processed_fields_values($mod, $item_id);

        foreach ($defs as $onedef) {

// skip
            if (!$onedef["searchable"])
                continue;

//serachable - textbox, textarea, dropdown
            switch ($onedef["type"]) {
                case 'textbox':
                case 'textarea':
                case 'dropdown':
                    if (isset($onedef["value"])) {
                        $results[] = $onedef["value"];
                    }
                    break;
            }
        }
        return implode(' ', $results);
    }

    public static function search_reindex($mod, $module) {
        $db = cmsms()->GetDb();

        $query = 'SELECT item_id FROM ' . cms_db_prefix() . 'module_' . $mod->_GetModuleAlias() . '_item where active = 1';
        $items = $db->GetArray($query, array());

        if (!$items)
            return;

        foreach ($items as $item) {
            $module->AddWords($mod->GetName(), $item["item_id"], 'item', self::get_searchable_text($mod, $item["item_id"]), self::get_searchable_date_end());
        }
    }

    public static function get_search_result($mod, $returnid, $item_id, $attr = '') {
        $result = array();

        if ($attr != 'item')
            return $result;

        $detailtemplate = '';
        if (isset($params["detailtemplate"]))
            $detailtempalte = $params["detailtemplate"];

        $inline = $mod->GetPreference('display_inline', 0);
        if (isset($params["inline"]))
            $detailtempalte = $params["inline"];

        /* if ($mod->GetPreference('item_detail_returnid', 0)) {
          $returnid = '';
          } */


        $row = self::get_item($mod, $item_id);
        if ($row) {

//0 position is the prefix displayed in the list results.
            $result[0] = $mod->GetFriendlyName();

//1 position is the title
            $result[1] = $row['title'];

//2 position is the URL to the title.

            if (empty($row["url"]) == false) {
                $prettyurl = $row["url"];
            } else {
                $prettyurl = generator_tools::get_pretty_url($mod, generator_tools::get_prefix($mod), $row['item_id'], $row['alias'], isset($params["detailpage"]) ? $detailpage : $returnid, $detailtemplate);
            }

            $result[2] = $mod->CreateFrontendLink('cntnt01', $returnid, 'detail', '', array('item_id' => $row['item_id'], 'detailtemplate' => $detailtemplate), '', true, $inline, '', false, $prettyurl);
        }

        return $result;
    }

    public static function route($mod, $filename) {
        $config = cmsms()->GetConfig();
        $smarty = cmsms()->GetSmarty();
        $pathinfo = pathinfo($filename);
        $filaneam = $pathinfo["basename"];
        $smarty->assign('generator_templates', cms_join_path($config["root_path"], 'modules', 'ModuleGenerator', 'ModuleLib', 'templates'));
        return cms_join_path($config["root_path"], 'modules', 'ModuleGenerator', 'ModuleLib', $pathinfo["basename"]);
    }

    public static function detailpage(&$params, $detailpage) {

        $gcms = cmsms();
        return $detailpage;
    }

    public static function create_parent_dropdown($mod, $id, $catid = -1, $selectedvalue = -1) {
        $db = cmsms()->GetDb();

        $longname = '';

        $items['(' . $mod->Lang('none') . ')'] = '-1';

        $query = "SELECT hierarchy, long_name FROM " . cms_db_prefix() . "module_" . $mod->_GetModuleAlias() . "_categories WHERE category_id = ?";
        $dbresult = $db->Execute($query, array($catid));

        while ($dbresult && $row = $dbresult->FetchRow()) {
            $longname = $row['hierarchy'] . '%';
        }
        $query = "SELECT category_id, category_name, hierarchy, long_name FROM " . cms_db_prefix() . "module_" . $mod->_GetModuleAlias() . "_categories WHERE hierarchy not like ? ORDER by hierarchy";
        $dbresult = $db->Execute($query, array($longname));
        while ($dbresult && $row = $dbresult->FetchRow()) {
            $items[$row['long_name']] = $row['category_id'];
        }
        return $mod->CreateInputDropdown($id, 'parent', $items, -1, $selectedvalue);
    }

    /**
     *  
     * @param object $mod 
     */
    public static function update_hierarchy_positions($mod) {
        $db = cmsms()->GetDb();

        $query = "SELECT category_id, category_name,parent_id FROM " . cms_db_prefix() . "module_" . $mod->_GetModuleAlias() . "_categories ORDER BY position, category_id";
        $dbresult = $db->Execute($query);
        $counter = array();
        while ($dbresult && $row = $dbresult->FetchRow()) {
            $current_hierarchy_position = "";
            $current_hierarchy_position2 = "";
            $position = "";
            $current_long_name = "";
            $content_id = $row['category_id'];
            $current_parent_id = $row['category_id'];
            $count = 0;
            (isset($counter[$row['parent_id']])) ? $counter[$row['parent_id']]++ : $counter[$row['parent_id']] = 1;
            $position = $counter[$row['parent_id']];

            while ($current_parent_id > -1) {
                $query = "SELECT category_id, category_name, parent_id, position FROM " . cms_db_prefix() . "module_" . $mod->_GetModuleAlias() . "_categories WHERE category_id = ?";
                $row2 = $db->GetRow($query, array($current_parent_id));
                if ($row2) {
                    $current_hierarchy_position = str_pad($row2['category_id'], 5, '0', STR_PAD_LEFT) . "." . $current_hierarchy_position;
                    $current_hierarchy_position2 = str_pad($row2['position'], 5, '0', STR_PAD_LEFT) . "." . $current_hierarchy_position2;
                    $current_long_name = $row2['category_name'] . ' | ' . $current_long_name;
                    $current_parent_id = $row2['parent_id'];
                    $count++;
                } else {
                    $current_parent_id = 0;
                }
            }




            if (strlen($current_hierarchy_position) > 0) {
                $current_hierarchy_position = substr($current_hierarchy_position, 0, strlen($current_hierarchy_position) - 1);
            }

            if (strlen($current_hierarchy_position2) > 0) {
                $current_hierarchy_position2 = substr($current_hierarchy_position2, 0, strlen($current_hierarchy_position2) - 1);
            }

            if (strlen($current_long_name) > 0) {
                $current_long_name = substr($current_long_name, 0, strlen($current_long_name) - 3);
            }

            $query = "UPDATE " . cms_db_prefix() . "module_" . $mod->_GetModuleAlias() . "_categories SET hierarchy = ?, position = ?, hierarchy_position = ?, long_name = ? WHERE category_id = ?";

            $db->Execute($query, array($current_hierarchy_position, $position, $current_hierarchy_position2, $current_long_name, $content_id));
        }
    }

    public static function safeName($name = null, $ext = null) {
        if (is_null($name)) return false;
        return preg_replace("/(?:[^\w\.-]+)/", "_", basename($name, $ext));
    }

    /**
     * Handle upload
     * @param mixed $itemid The item ID, used to form the upload directory name
     * @param string $fieldname The upload field name, for example, m1_customfield_1
     * @param boolean $isItem True if file owns a Item, False if owns to Category
     * @param string &$error Holds any errors that may occur
     * @param array $allow Allowed file extensions
     * @return bool|string Returns filename on successful upload or false on failure
     */
    public static function handle_upload($mod, $itemid, $fieldname, &$error, $allow = array(), $isItem = true) {


        $allow = !empty($allow) ? $allow : array('jpg', 'gif', 'png');
        $config = cmsms()->GetConfig();
        $p = cms_join_path($config['uploads_path'], '_' . $mod->_GetModuleAlias());

        //Join Category folder to path
        if (!$isItem) {
            $p = cms_join_path($p, '_categories');
        }

        if (!is_dir($p)) {
            $res = @mkdir($p);

            if ($res === false) {
                $error = $mod->Lang('error_mkdir', $p);
                return false;
            }
        }

        $p = cms_join_path($p, 'id' . $itemid);

        if (!is_dir($p)) {
            if (@mkdir($p) === false) {
                $error = $mod->Lang('error_mkdir', $p);
                return false;
            }
        }

        if ($_FILES[$fieldname]['size'] > $config['max_upload_size']) {
            $error = $mod->Lang('error_filesize');
            return false;
        }

        $filename = self::safeName(basename($_FILES[$fieldname]['name']));
        $dest = cms_join_path($p, $filename);
        $ext = strtolower(substr(strrchr($filename, '.'), 1));

        if (!in_array($ext, $allow)) {
            $error = $mod->Lang('error_invalidfiletype');
            return false;
        }

        if (@cms_move_uploaded_file($_FILES[$fieldname]['tmp_name'], $dest) === false) {
            $error = $mod->Lang('error_movefile', $dest);
            return false;
        }

        return $filename;
    }

    /**
     *
     * @param object $module
     * @param boolean $frontend
     * @return type 
     */
    public static function get_fields($module, $frontend = true, $section = 'items') {

        $queryarray = array();
        $parmsarray = array();

        $value = cms_utils::get_app_data($module->GetName() . __FUNCTION__ . $section);
        if ($value)
            return $value;

        $db = cmsms()->GetDb();
        $query = 'SELECT * FROM ' . cms_db_prefix() . 'module_' . $module->_GetModuleAlias() . '_fielddef';

        $queryarray[] = ' section = ?';
        $parmsarray[] = $section;


        if ($frontend == true) {
            $queryarray[] = ' frontend = ?';
            $parmsarray[] = 1;
        }
// WHERE
        $query .= ( empty($queryarray) == false ? ' WHERE ' . implode(' AND ', $queryarray) : '' );

        $value = $db->GetArray($query, $parmsarray);
        cms_utils::set_app_data($module->GetName() . __FUNCTION__ . $section, $value);

        return $value;
    }

    public static function image_location($module, $row) {

        $config = cmsms()->GetConfig();
        $rel_path = '_' . $module->_GetModuleAlias() . '/id' . $row['item_id'] . '/gallery';
        cge_dir::mkdirr(cms_join_path($config["uploads_path"], $rel_path));
        $value = $config['uploads_url'] . '/' . $rel_path;
        return $value;
    }

    public static function imagepath_location($module, $row) {

        $config = cmsms()->GetConfig();
        $rel_path = cms_join_path('_' . $module->_GetModuleAlias(), 'id' . $row['item_id'], 'gallery');
        cge_dir::mkdirr(cms_join_path($config["uploads_path"], $rel_path));
        $value = cms_join_path($config['uploads_path'], $rel_path);
        return $value;
    }

    /**
    * @param isItem True if we find location of item. False if we find category's location
    *
    */
    public static function file_location($module, $row, $isItem = true) {

        $config = cmsms()->GetConfig();
        $id = (is_array($row)) ? $row['item_id'] : $row;

        $rel_path = '_' . $module->_GetModuleAlias();
        if (!$isItem) {
            $rel_path = cms_join_path($rel_path, '_categories');
        }
        $rel_path = cms_join_path($rel_path, 'id' . $id);
        #$rel_path = '_' . $module->_GetModuleAlias() . '/id' . $row['item_id'];
        cge_dir::mkdirr(cms_join_path($config["uploads_path"], $rel_path));
        $value = $config['uploads_url'] . '/' . $rel_path;
        return $value;
    }

    public static function filepicker_location($module) {

        $config = cmsms()->GetConfig();
        $rel_path = '_' . $module->_GetModuleAlias() . '/filepicker/';
        cge_dir::mkdirr(cms_join_path($config["uploads_path"], $rel_path));
        $value = $rel_path;
        return $value;
    }

    public static function filepath_location($module, $row, $isItem = true) {

        $config = cmsms()->GetConfig();
        $rel_path = '_' . $module->_GetModuleAlias();
        $id = (is_int($row)) ? $row : $row['item_id'];

        if (!$isItem) {
            $rel_path = cms_join_path($rel_path, '_categories');
        }
        $rel_path = cms_join_path($rel_path, 'id' . $id);
        cge_dir::mkdirr(cms_join_path($config["uploads_path"], $rel_path));
        $value = cms_join_path($config['uploads_path'], $rel_path);
        return $value;
    }

    public static function check_row($mod, $fieldid, $fieldvalue) {

        $db = cmsms()->GetDb();
        $smarty = cmsms()->GetSmarty();

        $query = 'SELECT A.item_id FROM ' . cms_db_prefix() . 'module_' . $mod->_GetModuleAlias() . '_item A';
        $query .= ' LEFT JOIN ' . cms_db_prefix() . 'module_' . $mod->_GetModuleAlias() . '_fieldval FVA ON A.item_id = FVA.item_id AND FVA.fielddef_id = ? ';
        $query .= ' WHERE A.active = 1 AND FVA.value = ? ';
        return $db->GetOne($query, array($fieldid, $fieldvalue));
    }

    /**
     * Has Prefix
     * @param string $string The string in question
     * @param array $prefix Array of prefixes to match against
     * @return bool Returns true if prefix is found, or false otherwise
     */
    public static function has_prefix($string, $prefixes) {
        if (!is_string($string))
            return;
        if (!is_array($prefixes))
            return;

        foreach ($prefixes as $prefix) {
            if (strpos($string, $prefix) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get Extra: Exclude Prefix
     * @param array $instructions Instructions
     * @return array Returns array of prefixes to exclude if specified
     */
    public static function get_extra_exclude_prefix($instructions) {
        if (!is_array($instructions))
            return;

        foreach ($instructions as $instruction) {
// dir[thumb_]
// dir[thumb_,notme]
            if (preg_match('/^exclude_prefix\[([^,]+(,[^,]+)*)\]$/i', $instruction, $matches)) {
                return explode(',', $matches[1]);
            }
        }
    }

    /**
     * Get Extra: Directory
     * @param array $instructions Instructions
     * @return string Returns the directory if specified
     */
    public static function get_extra_dir($instructions) {
        if (!is_array($instructions))
            return;

        foreach ($instructions as $instruction) {
// dir[path/to/dir]
            if (preg_match('/^dir\[(.+)\]$/i', $instruction, $matches)) {
                return $matches[1];
            }
        }
    }

    /**
     * Get Extra: Allowed File Extensions
     * @param array $instructions Instructions
     * @return array Returns allowed file extensions if specified
     */
    public static function get_extra_allow($instructions) {
        if (!is_array($instructions))
            return array();

        foreach ($instructions as $instruction) {
// allow[pdf,gif,png,jpeg,jpg]
            if (preg_match('/^allow\[(.+)\]$/i', $instruction, $matches)) {
                return explode(',', $matches[1]);
            }
        }

        return array();
    }

    /**
     * Get Extra: Date Format
     * @param array $instructions Instructions
     * @return string Returns date format to be used with the jquery
     * datepicker if specified
     */
    public static function get_extra_date_format($instructions) {
        if (!is_array($instructions))
            return;

        foreach ($instructions as $instruction) {
// dateformat[dd/mm/yy]
            if (preg_match('/^dateformat\[(.+)\]$/i', $instruction, $matches)) {
                return $matches[1];
            }
        }
    }

    /**
     * Get Extra: Row
     * @param array $instructions Instructions
     * @return int Returns maximum length if specified
     */
    public static function get_extra_rows($instructions) {
        if (!is_array($instructions))
            return;


        foreach ($instructions as $instruction) {
// max_length[20]
            if (preg_match('/^rows\[([0-9]+)\]$/i', $instruction, $matches)) {
                return (int) $matches[1];
            }
        }
    }

    /**
     * Get Extra: Row
     * @param array $instructions Instructions
     * @return int Returns maximum length if specified
     */
    public static function get_extra_cols($instructions) {
        if (!is_array($instructions))
            return;

        foreach ($instructions as $instruction) {
// max_length[20]
            if (preg_match('/^cols\[([0-9]+)\]$/i', $instruction, $matches)) {
                return (int) $matches[1];
            }
        }
    }

    /**
     * Get Extra: Max Length
     * @param array $instructions Instructions
     * @return int Returns maximum length if specified
     */
    public static function get_extra_max_length($instructions) {
        if (!is_array($instructions))
            return;

        foreach ($instructions as $instruction) {
// max_length[20]
            if (preg_match('/^max_length\[([0-9]+)\]$/i', $instruction, $matches)) {
                return (int) $matches[1];
            }
        }
    }

    /**
     * Get Extra: Size
     * @param array $instructions Instructions
     * @return int Returns size if specified
     */
    public static function get_extra_size($instructions) {
        if (!is_array($instructions))
            return;

        foreach ($instructions as $instruction) {
// size[20]
            if (preg_match('/^size\[([0-9]+)\]$/i', $instruction, $matches)) {
                return (int) $matches[1];
            }
        }
    }

    /**
     * Get Extra: Multiple
     * @param array $instructions Instructions
     * @return int Returns size if specified
     */
    public static function get_extra_multiple($instructions) {
        if (!is_array($instructions))
            return;

        foreach ($instructions as $instruction) {
// size[20]
            if (preg_match('/^multiple\[([0-9]+)\]$/i', $instruction, $matches)) {
                return (int) $matches[1];
            }
        }
    }

    /**
     * Get Extra: Select default
     * @param array $instructions Instructions
     * @return int Returns size if specified
     */
    public static function get_extra_selectdefault($instructions) {
        if (!is_array($instructions))
            return;

        foreach ($instructions as $instruction) {
// size[20]
            if (preg_match('/^select_default\[(.+)\]$/i', $instruction, $matches)) {
                return $matches[1];
            }
        }
    }

    /**
     * Get Extra: WYSIWYG
     * @param array $instructions Instructions
     * @return bool Returns true if WYSIWYG is to be enabled, or false otherwise
     */
    public static function get_extra_wysiwyg($instructions) {
        if (!is_array($instructions))
            return;

        foreach ($instructions as $instruction) {
// wysiwyg[1]
// wysiwyg[0]
// wysiwyg[true]
// wysiwyg[false]
            if (preg_match('/^wysiwyg\[(.+)\]$/i', $instruction, $matches)) {
                if ($matches[1] == 'true' || $matches[1] == '1') {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get Extra: Module params
     * @param array $instructions Instructions
     * @return array Returns an array of options if specified
     */
    public static function get_extra_moduleparams($instructions) {
        if (!is_array($instructions))
            return;

        $params = array();

        foreach ($instructions as $instruction) {
// options[apple=Apple]
// options[apple=Apple,banana=Banana]
            if (preg_match('/^(.+)\[([^,]+=[^,]+(,[^,]+=[^,]+)*)\]$/i', $instruction, $matches)) {

                $params["module"] = $matches[1];

                foreach (explode(',', $matches[2]) as $pair) {
                    $key_val = explode('=', $pair);
                    $params[trim($key_val[0])] = $key_val[1];
                }
            }
        }


        return $params;
    }


    public static function get_extra_module_view($instructions) {
        if (!is_array($instructions)) {
            return;
        }

        $params = array();

        foreach ($instructions as $instruction) {
            if (preg_match('/^module_view\=\[([^\]]*)\]$/i', $instruction, $matches)) {
                $options = explode(',', $matches[1]);
                if (!empty($options)) {
                    $options = array_map('trim', $options);
                    $params = array(
                        'format' => array_shift($options),
                        'params' => $options
                    );
                }
            }
        }

        return $params;
    }

    /**
     * Get Extra: UDT
     * @param array $instructions Instructions
     * @return array Returns an array of options if specified
     */
    public static function get_extra_moduleudt($instructions) {
        if (!is_array($instructions))
            return;

        foreach ($instructions as $instruction) {
            if (preg_match('/^udt\[(.+)\]$/i', $instruction, $matches)) {
                return $matches[1];
            }
        }
    }

    /**
     * Get Extra: Module Options
     * @param array $instructions Instructions
     * @return array Returns an array of options if specified
     */
    public static function get_extra_moduleoptions($instructions, $mod) {
        if (!is_array($instructions))
            return;

        $options = array();
        foreach ($instructions as $instruction) {
// {cms_module}
            $modulecontent = $instruction;
            foreach (explode(',', $modulecontent) as $pair) {
                if (!$pair)
                    continue;
                $key_val = explode('=', $pair);
                $options[trim($key_val[0])] = $key_val[1];
            }
        }
        if (!empty($options))
            return $options;
    }

    /**
     * Get Extra: Page
     * @param array $instructions Instructions
     * @return array Returns an array of options if specified
     */
    public static function get_extra_page($instructions) {
        if (!is_array($instructions))
            return;

        $options = array();
        $settings = array();

        foreach ($instructions as $instruction) {
            if (isset($modulecontent)) {
                foreach (explode(',', $modulecontent) as $pair) {
                    $key_val = explode('=', $pair);
                    switch ($key_val[0]) {
                        case "childrenof":
                            $settings["childrenof"] = $key_val[1];
                            break;
                        case "start_page":
                            $settings["start_page"] = $key_val[1];
                            break;
                    }
                }
            }
        }


        $pages = cmsms()->GetContentOperations()->GetAllContent();
        $array = array();

        if (isset($settings['childrenof'])) {
            $childrenof = extended_tools_opts::get_page_id_from_alias($settings['childrenof']);
        }

        if (isset($settings['start_page'])) {
            $start_page = extended_tools_opts::get_page_id_from_alias($settings['start_page']);
        }

        foreach ($pages as $page) {
            if (
                    (!isset($start_page) && !isset($childrenof))
                    ||
                    (isset($childrenof) && ($page->ParentId() == $childrenof)) // List of all childrens
                    ||
                    (isset($start_page) && (strpos($page->IdHierarchy(), $start_page . '.') === 0)) // List of all descendants
            ) {
                $options[$page->Id()] = $page->Hierarchy() . '. - ' . $page->Name();
            }
        }


        if (!empty($options))
            return $options;
    }

    /**
     * Get Extra: Options
     * @param array $instructions Instructions
     * @return array Returns an array of options if specified
     */
    public static function get_extra_options($instructions) {
        if (!is_array($instructions))
            return;

        $options = array();

        foreach ($instructions as $instruction) {
// options[apple=Apple]
// options[apple=Apple,banana=Banana]
            if (preg_match('/^options\[([^,]+=[^,]+(,[^,]+=[^,]+)*)\]$/i', $instruction, $matches)) {
                foreach (explode(',', $matches[1]) as $pair) {
                    $key_val = explode('=', $pair);
                    $options[trim($key_val[0])] = $key_val[1];
                }
            }
        }

        if (!empty($options))
            return $options;
    }

    /**
    * Get Extra: Key Name
    * @param array $instructions Instructions
    * @return string Returns key's name if specified
    */
    public static function get_extra_keyName($instructions) {
        if (!is_array($instructions))
            return;
// key=labelName
        foreach ($instructions as $instruction) {
            if (preg_match('/^key\=(.+)$/i', $instruction, $matches)) {
                return $matches[1];
            }
        }
    }

    /**
    * Get Extra: Value Name
    * @param array $instructions Instructions
    * @return string Returns value's name if specified
    */
    public static function get_extra_valueName($instructions) {
        if (!is_array($instructions))
            return;
// value=valueName
        foreach ($instructions as $instruction) {
            if (preg_match('/^value\=(.+)$/i', $instruction, $matches)) {
                return $matches[1];
            }
        }
    }

// Functions for Table - JSON
//
    /**
    * Get Extra: Headers
    * @param array $instructions Instructions
    * @return string Returns value's name if specified
    */
    public static function get_extra_tableheaders($instructions) {
        if (!is_array($instructions))
            return;

        $options = array();

        foreach ($instructions as $instruction) {
// headers[apple=Apple]
// headers[apple=Apple,banana=Banana]
            if (preg_match('/^headers\[([^,]+(,?[^,]+)*)*\]$/i', $instruction, $matches)) {
                foreach (explode(',', $matches[1]) as $pair) {
                    $aux = explode('=', $pair);
                    if (!isset($aux[1])) {
                        $aux[1] = trim(strtolower($aux[0]));
                    }
                    $options[$aux[1]] = trim($aux[0]);
                }
            }
        }

        if (!empty($options))
            return $options;
    }

    /**
     * Get Extra
     * @param string $extra
     * @return array Returns an array of instructions if specified
     */
    public static function get_extra($extra, $mod = null) {
        if (!is_string($extra))
            return;

        $extra = trim($extra);

        if ($mod != null && is_object($mod))
            $extra = $mod->ProcessTemplateFromData($extra);


        $smarty = cmsms()->GetSmarty();
        $instructions = explode(';', trim($extra, ';'));
        if (!empty($instructions))
            return $instructions;
    }

    /**
     * Is valid alias
     * @param string $alias
     * @return bool Returns true if string in question is a valid alias, or
     * false otherwise
     */
    public static function is_valid_alias($alias) {
        if (!is_string($alias))
            return;

// check alias
// http://www.php.net/manual/en/language.variables.basics.php
        if (preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $alias)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Generate alias
     * @param string $name The string that the alias will be generated from
     * @return string Returns the alias
     */
    public static function generate_alias($name) {
        if (!is_string($name))
            return;

        $alias = $name;

// replace multiple spaces with single underscore
        $alias = preg_replace('/ +/', '_', $alias);

// replace accented characters
        $unwanted_array = array(
            'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y'
        );
        $alias = strtr($alias, $unwanted_array);

// leave alphabetic characters and unscores,
// replace everything else with an underscore
        $alias = preg_replace('/[^a-zA-Z_]/', '_', $alias);

// replace multiple underscores with single underscore
        $alias = preg_replace('/_+/', '_', $alias);

// convert to lowercase
        $alias = strtolower($alias);

// remove underscore from start and end
        $alias = trim($alias, '_');

        return $alias;
    }

    /**
     * Get field types
     * @return array Returns an array of field types
     */
    public static function get_field_types() {

        $module = cms_utils::get_module('ModuleGenerator');

        $types = array(
            'textbox' => $module->Lang('textbox'),
            'checkbox' => $module->Lang('checkbox'),
            'textarea' => $module->Lang('textarea'),
            'select_date' => $module->Lang('select_date'),
            'upload_file' => $module->Lang('upload_file'),
            'select_file' => $module->Lang('select_file'),
            'file_picker' => $module->Lang('file_picker'),
            'dropdown' => $module->Lang('dropdown'),
            'dropdown_from_udt' => $module->Lang('dropdown_from_udt'),
            'dropdownfrommodule' => $module->Lang('dropdownfrommodule'),
            'module' => $module->Lang('module'),
            'module_link' => $module->Lang('module_link'),
            'page' => $module->Lang('page'),
            'color_picker' => $module->Lang('color_picker'),
            'static' => $module->Lang('static'),
            'tab' => $module->Lang('tab'),
            'key_value' => $module->Lang('key_value'),
            'hr' => $module->Lang('hr'),
            'lookup' => $module->Lang('lookup'),
            'json' => $module->Lang('json')
        );

        return $types;
    }

    /**
     * Get field sections
     * @return array Returns an array of field sections
     */
    public static function get_field_sections() {

        $module = cms_utils::get_module('ModuleGenerator');

        $sections = array(
            'items' => $module->Lang('items'),
            'categories' => $module->Lang('categories'),
            'galleries' => $module->Lang('galleries')
        );

        return $sections;
    }

    public static function get_prefix($mod) {
        return $mod->GetPreference('url_prefix', munge_string_to_url($mod->GetName(), true));
    }

    public static function get_pretty_url($mod, $prefix, $item_id, $alias, $returnid = null, $detailtemplate = null) {
        $db = cmsms()->GetDb();

        $parms = array();

        $parms[] = $prefix;
        $parms[] = $item_id;
        if ($returnid)
            $parms[] = $returnid;
        if ($alias)
            $parms[] = $alias;
        if ($detailtemplate != null)
            $parms[] = $detailtemplate;

        return implode('/', $parms);
    }

    public static function get_private_categories($mod) {

        $categorylongnamelist = array();

        $db = cmsms()->GetDB();
        $userops = cmsms()->GetUserOperations();

        $userid = get_userid();
        $user_id = $userid;
        $adminuser = ($userops->UserInGroup($userid, 1) || $userid == 1);
//mle support
        if ($mod->GetPreference('usergroup_dependence') && !$mod->CheckPermission($mod->_GetModuleAlias() . '_all_category_visbile')) {

            if ($adminuser != 1) {


                $query = "SELECT group_id FROM " . cms_db_prefix() . "user_groups where user_id=?";
                $result = $db->Execute($query, array($user_id));
                if ($result) {
                    $groups = array();
                    $wherestatement = array();

                    while ($result && $row = $result->FetchRow()) {
                        $wherestatement [] = " usergroup = ? ";
                        $paramsarray[] = $row["group_id"];
                    }
                    $where[] = '( ' . implode(' OR ', $wherestatement) . ')';
                }

                $categorylist = generator_tools::get_category_list($mod, $where, $paramsarray);
                $categorylongnamelist = array_slice($categorylist, 1);
            }
        }
        return $categorylongnamelist;
    }

    /**
     * Delete item
     * @param object $mod
     * @param int $item_id 
     */
    public static function delete_item($mod, $item_id) {

        $db = cmsms()->GetDb();

// get details
        $query = 'SELECT * FROM ' . cms_db_prefix() . 'module_' . $mod->_GetModuleAlias() . '_item WHERE item_id = ?';
        $row = $db->GetRow($query, array($item_id));
        if (!$row)
            return false;

        $dir = self::filepath_location($mod, $row);
        if (is_dir($dir)) {
            cge_dir::recursive_rmdir($dir);
        }

// delete item
        $query = 'DELETE FROM ' . cms_db_prefix() . 'module_' . $mod->_GetModuleAlias() . '_item WHERE item_id = ?';
        $db->Execute($query, array($item_id));

// delete field values
        $query = "DELETE FROM " . cms_db_prefix() . "module_" . $mod->_GetModuleAlias() . "_fieldval WHERE fielddef_id IN (SELECT fielddef_id FROM " . cms_db_prefix() . "module_" . $mod->_GetModuleAlias() . "_fielddef WHERE section = 'items') AND item_id = ?";
        $db->Execute($query, array($item_id));

// delete field values
        /*$query = 'DELETE FROM ' . cms_db_prefix() . 'module_' . $mod->_GetModuleAlias() . '_item_extra WHERE item_id = ?';
        $db->Execute($query, array($item_id));*/

        //Update search index
        $module = cms_utils::get_module('Search');
        if ($module != FALSE) {
            $module->DeleteWords($mod->GetName(), $item_id, 'item');
        }
        @$mod->SendEvent('ItemDeleted', array('item_id' => $item_id));
    }

    /**
     * Delete image
     * @param object $mod
     * @param int $image_id 
     */
    public static function delete_image($mod, $image_id) {
        
        $db = cmsms()->GetDb();

        $query = 'DELETE FROM ' . cms_db_prefix() . 'module_' . $mod->_GetModuleAlias() . '_images WHERE image_id = ?';
        $db->Execute($query, array($image_id));

        return true;
    }

    /**
     * Get Extra: br
     * @param array $instructions Instructions
     * @return bool Returns true if br is to be enabled, or false otherwise
     */
    public static function get_extra_br($instructions) {
        if (!is_array($instructions))
            return;

        foreach ($instructions as $instruction) {
// br[1]
// br[0]
// br[true]
// br[false]
            if (preg_match('/^br\[(.+)\]$/i', $instruction, $matches)) {
                if ($matches[1] == 'true' || $matches[1] == '1') {
                    return true;
                }
            }
        }

        return false;
    }

    public static function get_extra_customBool($instructions, $varname = '') {
        if (!is_array($instructions))
            return;

        foreach ($instructions as $instruction) {
            if (preg_match('/^' . $varname . '\[(.+)\]$/i', $instruction, $matches)) {
                if ($matches[1] == 'true' || $matches[1] == '1') {
                    return true;
                }
            }
        }

        return false;
    }

// Functions for address lookup field definition
//
//
    private static function get_geolocator() {
        if( self::$_geolocator != null ) return self::$_geolocator;
        
        $module_list = ModuleOperations::get_modules_with_capability('geolocate');
        
        if( is_array($module_list) && count($module_list) ) {
            foreach( $module_list as $mname ) {
                $res = cms_utils::get_module($mname);
                if( is_object($res) && method_exists($res,'GetIconList') && method_exists($res,'GetIconsFull') ) {
                    self::$_geolocator = $res;
                    return $res;
                }
            }
        }
    }

    public static function can_geolocate() {
        $obj = self::get_geolocator();
        return is_object($obj);
    }

    public static function geolocate($address) {
        if( !$address ) return;
        
        $obj = self::get_geolocator();
        if( !is_object($obj) ) return;
        
        return $obj->GetCoordsFromAddress($address);
    }

}

?>
