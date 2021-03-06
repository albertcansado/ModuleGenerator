<?php

if (!$this->CheckPermission($this->_GetModuleAlias() . '_modify_option'))
    return;
$admintheme = cmsms()->get_variable('admintheme');


// get field definitions in use
$tmp = $db->GetArray('SELECT DISTINCT fielddef_id FROM ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval');
$usedfielddefs = array();

if (is_array($tmp)) {
    foreach ($tmp as $row) {
        $usedfielddefs[] = $row['fielddef_id'];
    }
}

//$position = $db->GetOne('SELECT max(position) as max_position FROM ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fielddef');
$fields = generator_tools::get_field_defs($this, null, null, null, 'galleries');
$rowclass = 'row1';
$entryarray = array();

if (is_array($fields) && count($fields) > 0) {
    foreach ($fields as $row) {
        $onerow = new stdClass();
        $onerow->id = $row['fielddef_id'];
        $onerow->name = $this->CreateLink($id, 'admin_editfielddef', $returnid, $row['name'], array('fielddef_id' => $row['fielddef_id']));
        $onerow->alias = $row['alias'];
        $onerow->type = $this->Lang($row['type']);
        $onerow->required = $row['required'];
        $onerow->frontend = $row['frontend'];
        $onerow->editview = $row['editview'];
        $onerow->filter_frontend = $row['filter_frontend'];
        $onerow->filter_admin = $row['filter_admin'];
        $onerow->searchable = $row['searchable'];
        $onerow->position = $row['position'];

        // move up
        if ($onerow->position > 1) {
            $onerow->uplink = $this->CreateLink($id, 'admin_movefielddef', $returnid, $admintheme->DisplayImage('icons/system/arrow-u.gif', $this->Lang('up'), '', '', 'systemicon'), array('fielddef_id' => $row['fielddef_id'], 'dir' => 'up'));
        } else {
            $onerow->uplink = '';
        }

        $onerow->editlink = $this->CreateLink($id, 'admin_editfielddef', $returnid, $admintheme->DisplayImage('icons/system/edit.gif', $this->Lang('edit'), '', '', 'systemicon'), array('fielddef_id' => $row['fielddef_id']));

        // only display delete field definition link if field definition is not in use
        $onerow->deletelink = '';
        if (!in_array($row['fielddef_id'], $usedfielddefs)) {
            $onerow->deletelink = $this->CreateLink($id, 'admin_deletefielddef', $returnid, $admintheme->DisplayImage('icons/system/delete.gif', $this->Lang('delete'), '', '', 'systemicon'), array('fielddef_id' => $row['fielddef_id']), $this->Lang('areyousure'));
        }

        $entryarray[] = $onerow;
        $rowclass = ($rowclass == 'row1' ? 'row2' : 'row1');
    }
}

$smarty->assign_by_ref('items', $entryarray);
$smarty->assign('alias', $this->Lang('alias'));
$smarty->assign('itemcount', count($entryarray));
$smarty->assign('addlink', $this->CreateLink($id, 'admin_editfielddef', $returnid, $admintheme->DisplayImage('icons/system/newobject.gif', $this->Lang('addfielddef'), '', '', 'systemicon') . ' ' . $this->Lang('addfielddef')));
$smarty->assign('fieldtext', $this->Lang('fielddef'));
$smarty->assign('typetext', $this->Lang('fielddef_type'));
$smarty->assign('maxlengthtext', $this->Lang('fielddef_max_length'));
$smarty->assign('requiredtext', $this->Lang('fielddef_required'));
$smarty->assign('searchabletext', $this->Lang('fielddef_searchable'));
$smarty->assign('frontendtext', $this->Lang('fielddef_frontend'));
$smarty->assign('editviewtext', $this->Lang('fielddef_editview'));
$smarty->assign('filter_frontendtext', $this->Lang('fielddef_filter_frontend'));
$smarty->assign('filter_admintext', $this->Lang('fielddef_filter_admin'));
$smarty->assign('section', 'galleries');

echo $this->ModProcessTemplate('fielddeftab.tpl');
?>