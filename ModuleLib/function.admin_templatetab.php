<?php

if (!$this->CheckPermission($this->_GetModuleAlias() . '_modify_option'))
    return;
$admintheme = cmsms()->get_variable('admintheme');

$query = 'SELECT tpl_id, name, content, type FROM ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_template';
$result = $db->Execute($query);
$items = Array();
$rowclass = 'row1';

while ($result && $row = $result->FetchRow()) {
    $tmp = new StdClass();
    $tmp->link = $this->CreateLink($id, 'admin_edittemplate', $returnid, $row['name'], array('tpl_id' => $row['tpl_id']));
    $tmp->delete = $this->CreateLink($id, 'admin_deletetemplate', $returnid, $admintheme->DisplayImage('icons/system/delete.gif', $this->Lang('delete'), '', '', 'systemicon'), array('tpl_id' => $row['tpl_id']), $this->Lang('areyousure'));
    $tmp->edit = $this->CreateLink($id, 'admin_edittemplate', $returnid, $admintheme->DisplayImage('icons/system/edit.gif', $this->Lang('edit'), '', '', 'systemicon'), array('tpl_id' => $row['tpl_id']));
    $tmp->rowclass = $rowclass;

    $i = isset($items[$row['type']]) ? count($items[$row['type']]) : 0;
    $items[$row['type']][$i] = $tmp;

    $rowclass = ($rowclass == 'row1' ? 'row2' : 'row1');
}

$smarty->assign('itemcount', count($items));
$smarty->assign('items', $items);
$smarty->assign('detailtemplates', $this->Lang('detailtemplates'));
$smarty->assign('summarytemplates', $this->Lang('summarytemplates'));
$smarty->assign('header_name', $this->Lang('template'));
$smarty->assign('adddetaillink', $this->CreateLink($id, 'admin_edittemplate', $returnid, $admintheme->DisplayImage('icons/system/newobject.gif', $this->Lang('addtemplate'), '', '', 'systemicon') . ' ' . sprintf($this->Lang('addtemplate'), 'Detail'), array('type' => 'detail')));
$smarty->assign('addsummarylink', $this->CreateLink($id, 'admin_edittemplate', $returnid, $admintheme->DisplayImage('icons/system/newobject.gif', $this->Lang('addtemplate'), '', '', 'systemicon') . ' ' . sprintf($this->Lang('addtemplate'), 'Summary'), array('type' => 'summary')));

echo $this->ModProcessTemplate('templatetab.tpl');
?>