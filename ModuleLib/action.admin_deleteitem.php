<?php

if (!$this->CheckPermission($this->_GetModuleAlias() . '_modify_item'))
    return;

$item_id = '';

if (isset($params['item_id'])) {
    $item_id = (int) $params['item_id'];
}

if (empty($item_id)) {
    $this->SetError($this->Lang('empty_param'));
    $this->RedirectToTab($id, 'defaulaction', $params);
}

generator_tools::delete_item($this, $item_id);

$this->Setmessage($this->Lang('deleted'));
$this->RedirectToTab($id, 'defaultadmin', $params);
?>