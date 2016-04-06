<?php

if (!$this->CheckPermission($this->_GetModuleAlias() . '_modify_option'))
    return;

$fielddef_id = '';

if (isset($params['fielddef_id'])) {
    $fielddef_id = (int) $params['fielddef_id'];
}

if (empty($fielddef_id)) {
    $this->SetError($this->Lang('empty_param'));
    $this->RedirectToTab($id, '', array(), 'admin_fields');
}

// get details
$query = 'SELECT * FROM ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fielddef WHERE fielddef_id = ?';
$row = $db->GetRow($query, array($fielddef_id));

// delete field definitions
$query = 'DELETE FROM ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fielddef WHERE fielddef_id = ?';
$db->Execute($query, array($fielddef_id));

// delete field values
$query = 'DELETE FROM ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval WHERE fielddef_id = ?';
$db->Execute($query, array($fielddef_id));


// all done
$this->Setmessage($this->Lang('deleted'));
$this->RedirectToTab($id, $row["section"], array(), 'admin_fields');
?>