<?php

if (!$this->CheckPermission($this->_GetModuleAlias() . '_modify_item'))
    return;

if (!isset($params['approve']) || !isset($params['item_id'])) {
    die('missing parameter, this should not happen');
}

$item_id = (int) $params['item_id'];
$active = (bool) $params['approve'];

$query = 'UPDATE ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_item SET active = ? WHERE item_id = ?';
$db->Execute($query, array($active, $item_id));

$search = cms_utils::get_search_module();
if (is_object($search)) {
    if ($active) {
        $search->DeleteWords($this->GetName(), $item_id, 'item');
    } else {

      $search->AddWords($this->GetName(), $item_id, 'item', generator_tools::get_searchable_text($this, $item_id), generator_tools::get_searchable_date_end());
    }
}

@$this->SendEvent('ItemEdited', array('item_id' => $item_id, 'active' => $active));

// all done
$this->Redirect($id, 'defaultadmin', $returnid, array('active_tab' => 'itemtab'));
?>