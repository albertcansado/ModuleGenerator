<?php

if (!$this->CheckPermission($this->_GetModuleAlias() . '_modify_option'))
    return;

$search = cms_utils::get_module('Search');
if ($search != FALSE) {
    $this->SearchReindex($search);
}

$this->RedirectToTab($id, 'optiontab');
?>