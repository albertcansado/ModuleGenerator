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

class generator_fields {

    private static $_fields = array();
    private static $_fieldsvals = array();

    public function __construct() {}

    public static function get_fields($mod) {
        if (isset(self::$_fields[$mod->GetName()]))
            return self::$_fields[$mod->GetName()];

        $db = cmsms()->GetDb();
        $query = 'SELECT * FROM ' . cms_db_prefix() . 'module_' . $mod->_GetModuleAlias() . '_fielddef ORDER BY position';
        $fields = self::$_fields[$mod->GetName()] = $db->GetAll($query, array());
        return $fields;
    }

    public static function get_field_defs($mod, $section = 'items', $type = null, $filter_frontend = null, $filter_admin = null) {
        $fields = self::get_fields($mod);
        if (empty($fields))
            return;

        $tmp_id = __FUNCTION__ . $mod->GetName() . $type . $filter_frontend . $filter_admin . $section;
        if (isset(self::$_fields[$tmp_id]))
            return self::$_fields[$tmp_id];

        $tmp_fields = $fields;
        foreach ($fields as $key => $field) {
            if ($section != $field['section']) {
                unset($tmp_fields[$key]);
                continue;
            }

            if ($type != null && $type != $field['type']) {
                unset($tmp_fields[$key]);
                continue;
            }

            if ($filter_frontend != null && $filter_frontend != $field['filter_frontend']) {
                unset($tmp_fields[$key]);
                continue;
            }

            if ($filter_admin != null && $filter_admin != $field['filter_admin']) {
                unset($tmp_fields[$key]);
                continue;
            }
        }
        return self::$_fields[$tmp_id] = $tmp_fields;
    }

    public static function get_fields_for_filters($mod, $blocktypes = array(), $section = 'items') {
        $fields = self::get_field_defs($mod, $section, null, null, null);
        if (!$fields)
            return null;
        $custom_flds = array();
        foreach ($fields as $row) {
            if (in_array($row["type"], $blocktypes))
                continue;
            $custom_flds[$row['fielddef_id']] = $row;
        }
        return $custom_flds;
    }

    public static function get_fields_values($mod, $item_id, $params = array()) {

        $config = cmsms()->GetConfig();
        $db = cmsms()->GetDb();
        $fieldvals = array();

        $tmp_id = md5(serialize(__FUNCTION__ . $mod->GetName() . $item_id . json_encode($params)));
        if (isset(self::$_fieldsvals[$tmp_id]))
            return self::$_fieldsvals[$tmp_id];

        $query = 'SELECT B.*,A.value
		FROM ' . cms_db_prefix() . 'module_' . $mod->_GetModuleAlias() . '_fieldval A, ' . cms_db_prefix() . 'module_' . $mod->_GetModuleAlias() . '_fielddef B
		WHERE A.fielddef_id = B.fielddef_id AND A.item_id = ? ORDER BY B.position';
        $rows = $db->GetArray($query, array($item_id));
        if ($rows) {
            foreach ($rows as $fieldval) {
                $fieldvals[$fieldval['fielddef_id']] = $fieldval;
            }
        }

        self::$_fieldsvals[$tmp_id] = $fieldvals;
        if (isset($params['customfield'])) {
            foreach ($params['customfield'] as $fldid => $value) {
                if (!isset($fieldvals[$fldid])) {
                    continue;
                }

                if ($fieldvals[$fldid]['type'] === 'checkbox') {
                    $fieldvals[$fldid]['value'] = $value;
                } else {
                    if (empty($value)) {
                        $fieldvals[$fldid]['value'] = '';
                    } else if (is_array($value)) {
                        $fieldvals[$fldid]['value'] = implode(',', $value);
                    } else {
                        $fieldvals[$fldid]['value'] = $value;
                    }
                }
            }
        }

        return $fieldvals;
    }

    /**
     *
     * @param type $item_id
     * @return string
     */
    public static function get_processed_fields_values($mod, $item_id, $section = 'items', $key = 'alias') {

        $tmp_id = md5(serialize(__FUNCTION__ . $mod->GetName() . $item_id . $key . $section));
        if (isset(self::$_fieldsvals[$tmp_id]))
            return self::$_fieldsvals[$tmp_id];

        $rows = self::get_fields_values($mod, $item_id);
        foreach ($rows as $key2 => $row) {

            if ($section != $row['section']) {
                unset($rows[$key2]);
                continue;
            }
        }

        $fielddefs = array();
        foreach ($rows as $row) {
            $fielddefs[$row[$key]] = array(
                'fielddef_id' => $row['fielddef_id'],
                'name' => $row['name'],
                'alias' => $row['alias'],
                'value' => generator_filters::process_fields_value($row),
                'searchable' => $row['searchable'],
                'type' => $row['type']
            );

            if (isset($row['multiple'])) {
                $fielddefs[$row[$key]]['multiple'] = $row['multiple'];
            }
        }
        self::$_fieldsvals[$tmp_id] = $fielddefs;
        return $fielddefs;
    }

    public static function alias_to_object($fielddefs, &$item) {
        foreach ($fielddefs as $key => $onedef) {
            if (empty($onedef["alias"]) == false && in_array($onedef["alias"], generator_tools::$fields_blocklist) == false) {
                $value = ($onedef['type'] === 'dropdown' && !is_array($onedef['value'])) ? $onedef["value"]["value"] : $onedef["value"];
                if (is_object($item)) {
                    $item->$onedef["alias"] = $value;
                } elseif (is_array($item)) {
                    $item[$onedef["alias"]] = $value;
                }
            }
        }
    }

}

?>
