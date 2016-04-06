<?php

#-------------------------------------------------------------------------
# Module: Uploads -= allow users to upload stuff, a pseudo file manager" module
# Author: Robert Campbell <rob@techcom.dyndns.org>
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
# Or read it online: http:	//www.gnu.org/licenses/licenses.html#GPL
#
#-------------------------------------------------------------------------

if (!$this->CheckPermission($this->_GetModuleAlias() . '_modify_categories'))
    return;


if (isset($params['cancel']) || !isset($_POST['data'])) {
    $this->RedirectToTab($id, 'categorytab');
}

if (isset($_POST['data'])) {
    
    function ordercontent_get_node_rec($str, $prefix = 'category_') {
        $gCms = cmsms();
        $tree = $gCms->GetHierarchyManager();

        if (!is_numeric($str) && startswith($str, $prefix)) {
            $str = substr($str, strlen($prefix));
        }

        $id = (int) $str;
        $tmp = $tree->find_by_tag('id', $id);
        $content = '';
        if ($tmp) {
            $content = $tmp->getContent();
            if ($content) {
                $rec = aray();
                $rec['id'] = (int) $str;
            }
        }
    }

    function ordercontent_create_flatlist($tree, $parent_id = -1) {
        $data = array();
        $order = 1;
        foreach ($tree as &$node) {
            if (is_array($node) && count($node) == 2) {
                $pid = substr($node[0], strlen('category_'));
                $data[] = array('id' => $pid, 'parent_id' => $parent_id, 'order' => $order);
                if (isset($node[1]) && is_array($node[1])) {
                    $data = array_merge($data, ordercontent_create_flatlist($node[1], $pid));
                }
            } else {
                $pid = substr($node, strlen('category_'));
                $data[] = array('id' => $pid, 'parent_id' => $parent_id, 'order' => $order);
            }
            $order++;
        }
        return $data;
    }

    $data = json_decode($_POST['data']);


    // step 1, create a flat list of the content items, and their new orders, and new parents.
    $data = ordercontent_create_flatlist($data);



    // step 2. merge in old orders, and old parents.
    $gCms = cmsms();
    $data2 = array();

    // all categories
    $query = "SELECT category_id,parent_id,position FROM " . cms_db_prefix() . "module_" . $this->_GetModuleAlias() . "_categories ORDER BY hierarchy_position";
    $categories = $db->GetAll($query);
    $cat_array = array();
    foreach ($categories as $category) {
        $cat_array[$category["category_id"]] = array($category["parent_id"],$category["position"]);
    }


    for ($i = 0; $i < count($data); $i++) {
        $rec = & $data[$i];
        $old_category = $cat_array[$rec['id']];
        if ($old_category) {
            $rec['old_parent'] = $old_category[0];
            $rec['old_order'] = $old_category[1];

            if (($rec['old_parent'] != $rec['parent_id']) ||
                    $rec['old_order'] != $rec['order']) {
                $data2[] = $rec;
            }
        }
    }

    
    // do the updates
    if (count($data2) > 0) {
        $db = $gCms->GetDb();
        $query = 'UPDATE ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_categories content SET position = ?, parent_id = ? WHERE category_id = ?';
        for ($i = 0; $i < count($data2); $i++) {
            $rec = & $data2[$i];
            $db->Execute($query, array($rec['order'], $rec['parent_id'], $rec['id']));
            @$this->SendEvent('CategoryEdited', array('category_id' => $rec['id']));
        }
        generator_tools::update_hierarchy_positions($this);
        @$this->SendEvent('CategoryReordered', array());
    } else {
        echo lang('nothingtodo');
        return;
    }
}


#
# EOF
#
?>