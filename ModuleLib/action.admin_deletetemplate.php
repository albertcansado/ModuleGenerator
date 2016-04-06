<?php
if (!$this->CheckPermission($this->_GetModuleAlias() . '_modify_option')) return;

$tpl_id = '';

if (isset($params['tpl_id'])) {
	$tpl_id = (int)$params['tpl_id'];
}

// get details
$query = 'SELECT * FROM ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_template WHERE tpl_id = ?';
$row = $db->GetRow($query, array($tpl_id));

// delete record
$query = 'DELETE FROM ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_template WHERE tpl_id = ?';
$db->Execute($query, array($tpl_id));

// all done
$this->Redirect($id, 'defaultadmin', $returnid, array('active_tab' => 'templatetab', 'message' => 'deleted'));

?>