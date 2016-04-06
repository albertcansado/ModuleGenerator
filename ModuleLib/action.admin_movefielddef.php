<?php

if (!$this->CheckPermission($this->_GetModuleAlias() . '_modify_option'))
    return;

$data = trim(trim($params['serialdata']), ',');
$data = explode(',', $data);

$sorting = 1;
for ($i = 0; $i < count($data); $i++) {
    $item_id = $data[$i];
    if (!$item_id)
        continue;
    $query = 'UPDATE ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fielddef SET position = ? WHERE fielddef_id = ?';
    $db->Execute($query, array($sorting++, $item_id));
    
}

$this->RedirectToTab($id, $params["section"], array(), 'admin_fields');
?>
