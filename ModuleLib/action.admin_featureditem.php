<?php

if (!$this->CheckPermission($this->_GetModuleAlias() . '_modify_item'))
    return;

if (!isset($params['approve']) || !isset($params['item_id'])) {
    die('missing parameter, this should not happen');
}

$item_id = (int) $params['item_id'];
$featured = (bool) $params['approve'];

$query = 'UPDATE ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_item SET featured = ? WHERE item_id = ?';
$db->Execute($query, array($featured, $item_id));

@$this->SendEvent('ItemEdited', array('item_id' => $item_id, 'featured' => $featured));

// all done
$this->Redirect($id, 'defaultadmin', $returnid, array('active_tab' => 'itemtab'));
?>