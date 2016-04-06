<?php

/*if (!isset($gCms))
    exit;*/

if (!$this->CheckPermission($this->_GetModuleAlias() . '_modify_categories'))
    return;


$catid = '';
if (isset($params['catid'])) {
    $catid = $params['catid'];
}

// Get the category details
$query = 'SELECT * FROM ' . cms_db_prefix() . 'module_'.$this->_GetModuleAlias() . '_categories
           WHERE category_id = ?';
$row = $db->GetRow($query, array($catid));

//Reset all categories using this parent to have no parent (-1)
$query = 'UPDATE ' . cms_db_prefix() . 'module_'.$this->_GetModuleAlias() . '_categories SET parent_id=?, modified_date=' . $db->DBTimeStamp(time()) . ' WHERE parent_id=?';
$db->Execute($query, array(-1, $catid));

//Now remove the category
$query = "DELETE FROM " . cms_db_prefix() . "module_".$this->_GetModuleAlias() . "_categories WHERE category_id = ?";
$db->Execute($query, array($catid));

//And remove it from any articles
$query = "UPDATE " . cms_db_prefix() . "module_".$this->_GetModuleAlias()."_item SET category_id = -1 WHERE category_id = ?";
$db->Execute($query, array($catid));

//Delete all custom fields
$query = "DELETE FROM " . cms_db_prefix() . "module_" . $this->_GetModuleAlias() . "_fieldval WHERE fielddef_id IN (SELECT fielddef_id FROM " . cms_db_prefix() . "module_" . $this->_GetModuleAlias() . "_fielddef WHERE section = 'categories') AND item_id = ?";
$db->Execute($query, array($catid));

//And remove all category files
$dir = generator_tools::filepath_location($this, $catid, false);
if (is_dir($dir)) {
    cge_dir::recursive_rmdir($dir);
}

@$this->SendEvent('CategoryDeleted', array('category_id' => $catid, 'name' => $row['category_name']));
$rparms = array('tab_message' => 'categorydeleted');

generator_tools::update_hierarchy_positions($this);

$this->RedirectToTab($id, 'categorytab', $rparms);
?>
