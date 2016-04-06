<?php



#Put together a list of current categories...
$entryarray = array();

$query = "SELECT * FROM " . cms_db_prefix() . "module_" . $this->_GetModuleAlias() . "_categories ORDER BY hierarchy_position";
$dbresult = $db->Execute($query);

$rowclass = 'row1';

while ($dbresult && $row = $dbresult->FetchRow()) {
    $onerow = new stdClass();

    $depth = count(preg_split('/\./', $row['hierarchy']));

    $onerow->id = $row['category_id'];
    $onerow->name = str_repeat('&nbsp;&gt;&nbsp;', $depth - 1) . $this->CreateLink($id, 'admin_editcategory', $returnid, $row['category_name'], array('catid' => $row['category_id']));

    $onerow->editlink = $this->CreateLink($id, 'admin_editcategory', $returnid, cmsms()->variables['admintheme']->DisplayImage('icons/system/edit.gif', $this->Lang('edit'), '', '', 'systemicon'), array('catid' => $row['category_id']));
    $onerow->deletelink = $this->CreateLink($id, 'admin_deletecategory', $returnid, cmsms()->variables['admintheme']->DisplayImage('icons/system/delete.gif', $this->Lang('delete'), '', '', 'systemicon'), array('catid' => $row['category_id']), $this->Lang('areyousure'));

    $onerow->rowclass = $rowclass;

    $entryarray[] = $onerow;

    ($rowclass == "row1" ? $rowclass = "row2" : $rowclass = "row1");
}

$this->smarty->assign_by_ref('items', $entryarray);
$this->smarty->assign('itemcount', count($entryarray));

#Setup links
$this->smarty->assign('addlink', $this->CreateLink($id, 'admin_editcategory', $returnid, $this->Lang('addcategory'), array(), '', false, false, 'class="pageoptions"'));
$this->smarty->assign('reorder', $this->CreateLink($id, 'admin_ordercategory', $returnid, $this->Lang('reorder'), array(), '', false, false, 'class="pageoptions"'));

$this->smarty->assign('categorytext', $this->Lang('category'));
#Display template
echo $this->ModProcessTemplate('categorylist.tpl');

// EOF
?>