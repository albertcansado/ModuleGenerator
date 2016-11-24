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

class generator_items {

    private $_mod = null;
    private $_rawdata = array();

    public function __construct($mod) {
        $this->_mod = $mod;
    }

    public function getdata($key) {
        $res = null;
        if (isset($this->_rawdata[$key])) {
            $res = $this->_rawdata[$key];
        }
        return $res;
    }

    public function setdata($key, $value) {
        $this->_rawdata[$key] = $value;
    }

    public function set_params_from_array($row) {

        $mod = $this->_mod;
        $db = cmsms()->GetDb();

        foreach ($row as $key => $value) {
            $this->_rawdata[$key] = $value;
        }
    }

    private function _get_item_position() {

        $mod = $this->_mod;
        $db = cmsms()->GetDb();
        $position = $db->GetOne('SELECT max(position) + 1 FROM ' . cms_db_prefix() . 'module_' . $mod->_GetModuleAlias() . '_item');

        if ($position == null) {
            $position = 1;
        }
        return $position;
    }

    public function save() {

        $mod = $this->_mod;
        $db = cmsms()->GetDb();

        if (!isset($this->_rawdata["item_id"])) {
            // find position before inserting new item
            $position = $this->_get_item_position();
            $this->_rawdata["position"] = $position;

            // insert item
            $query = 'INSERT INTO ' . cms_db_prefix() . 'module_' . $mod->_GetModuleAlias() . '_item (title, alias, url, recursive, category_id, position, active, item_date, item_date_end, create_date, modified_date ) VALUES (?, ?, ?, ?, ?, ?, ? , ?, ?,  NOW(), NOW())';
            $result = $db->Execute($query, array(
                $this->_rawdata["title"],
                $this->_rawdata["alias"],
                $this->_rawdata["url"],
                $this->_rawdata["recursive"],
                $this->_rawdata["category_id"],
                $this->_rawdata["position"],
                $this->_rawdata["active"],
                trim($db->DBTimeStamp($this->_rawdata["date"]), "'"),
                trim($db->DBTimeStamp($this->_rawdata["date_end"]), "'")));
            if (!$result)
                throw new Exception('FATAL SQL ERROR: ' . $db->ErrorMsg() . '<br/>QUERY: ' . $db->sql);

            // populate $item_id for newly inserted item
            $item_id = $db->Insert_ID();
            $this->_rawdata["item_id"] = $item_id;
            $this->_rawdata["update"] = false;
        } else {
            // update item
            $query = 'UPDATE ' . cms_db_prefix() . 'module_' . $mod->_GetModuleAlias() . '_item SET title = ?, alias = ?, url = ?, recursive = ?, category_id = ?, active = ?, item_date= ?, item_date_end= ? WHERE item_id = ?';
            $result = $db->Execute($query, array(
                $this->_rawdata["title"],
                $this->_rawdata["alias"],
                $this->_rawdata["url"],
                $this->_rawdata["recursive"],
                $this->_rawdata["category_id"],
                $this->_rawdata["active"],
                trim($db->DBTimeStamp($this->_rawdata["date"]), "'"),
                trim($db->DBTimeStamp($this->_rawdata["date_end"]), "'"),
                $this->_rawdata["item_id"]));
            if (!$result)
                throw new Exception('FATAL SQL ERROR: ' . $db->ErrorMsg() . '<br/>QUERY: ' . $db->sql);

            $this->_rawdata["update"] = true;
        }

        $this->_save_custom_fields();
    }

    private function _save_custom_fields() {

        $mod = $this->_mod;
        $db = cmsms()->GetDb();

        if (!isset($this->_rawdata["customfield"]) || !isset($this->_rawdata["item_id"]))
            return;

        if (isset($this->_rawdata["customfield"])) {
            foreach ($this->_rawdata["customfield"] as $fldid => $value) {

                if (is_array($value)) {
                    if (!empty($value))
                        $value = implode(',', $value);
                    else
                        $value = '';
                }

                // check if row exists to determine whether to insert or update
                $query = 'SELECT value FROM ' . cms_db_prefix() . 'module_' . $mod->_GetModuleAlias() . '_fieldval WHERE item_id = ? AND fielddef_id = ?';
                $tmp = $db->GetOne($query, array($this->_rawdata["item_id"], $fldid));


                // row does not exist
                if ($tmp == "") {
                    // only insert row if field value is not empty
                    if ($value != "") {
                        $query = 'INSERT INTO ' . cms_db_prefix() . 'module_' . $mod->_GetModuleAlias() . '_fieldval (item_id, fielddef_id, value) VALUES (?, ?, ?)';
                        $result = $db->Execute($query, array($this->_rawdata["item_id"], $fldid, $value));
                    }

                    // row already exists
                } else {
                    // delete row if field value is empty
                    if ($value == "") {
                        $query = 'DELETE FROM ' . cms_db_prefix() . 'module_' . $mod->_GetModuleAlias() . '_fieldval WHERE item_id = ? AND fielddef_id = ?';
                        $result = $db->Execute($query, array($this->_rawdata["item_id"], $fldid));
                        // update row
                    } else {
                        $query = 'UPDATE ' . cms_db_prefix() . 'module_' . $mod->_GetModuleAlias() . '_fieldval SET value = ? WHERE item_id = ? AND fielddef_id = ?';
                        $result = $db->Execute($query, array($value, $this->_rawdata["item_id"], $fldid));
                    }
                }

                if (!$result)
                    throw new Exception('FATAL SQL ERROR: ' . $db->ErrorMsg() . '<br/>QUERY: ' . $db->sql);
            }
        }
    }

}

?>
