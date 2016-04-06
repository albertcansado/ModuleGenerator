<?php
$db = cmsms()->GetDb();

$dict = NewDataDictionary($db);

// drop tables
$sqlarray = $dict->DropTableSQL(cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_item');
$dict->ExecuteSQLArray($sqlarray);

$sqlarray = $dict->DropTableSQL(cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fielddef');
$dict->ExecuteSQLArray($sqlarray);

$sqlarray = $dict->DropTableSQL(cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_categories');
$dict->ExecuteSQLArray($sqlarray);

$sqlarray = $dict->DropTableSQL(cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval');
$dict->ExecuteSQLArray($sqlarray);

$sqlarray = $dict->DropTableSQL(cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_images');
$dict->ExecuteSQLArray($sqlarray);

// remove preferences
$this->RemovePreference();

// remove permissions
$this->RemovePermission($this->_GetModuleAlias() . '_modify_item');
$this->RemovePermission($this->_GetModuleAlias() . '_modify_categories');
$this->RemovePermission($this->_GetModuleAlias() . '_all_category_visbile');
$this->RemovePermission($this->_GetModuleAlias() . '_modify_option');

?>