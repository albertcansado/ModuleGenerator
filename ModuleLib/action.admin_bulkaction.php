<?php

/*if (!isset($gCms))
    exit;*/

if (!$this->CheckPermission($this->_GetModuleAlias() . '_modify_item'))
    return;

$this->SetCurrentTab('itemtab');

if (isset($params['multiselect']) && is_array($params['multiselect']) && count($params['multiselect']) && isset($params['bulkaction'])) {
    for ($i = 0; $i < count($params['multiselect']); $i++) {
        $params['multiselect'][$i] = (int) $params['multiselect'][$i];
    }

    switch ($params['bulkaction']) {
        case 'delete':
            foreach ($params['multiselect'] as $item_id) {
                $res = generator_tools::delete_item($this, (int) $item_id);
                @$this->SendEvent('ItemDeleted', array('item_id' => $item_id));
            }
            break;
        case 'setactive': {
                $query = 'UPDATE ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_item SET active = ?, modified_date = NOW() WHERE item_id IN (';
                $query .= implode(',', $params['multiselect']) . ')';
                $dbr = $db->Execute($query, array(1));
                $search = cms_utils::get_module('Search');
                foreach ($params['multiselect'] as $item_id) {
                    if ($search != FALSE)
                        $search->AddWords($this->GetName(), $item_id, 'item', generator_tools::get_searchable_text($this, $item_id), generator_tools::get_searchable_date_end());
                    @$this->SendEvent('ItemEdited', array('item_id' => $item_id, 'active' => 1));
                }
            }
            break;
        case 'setinactive': {
                $query = 'UPDATE ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_item SET active = ?, modified_date = NOW() WHERE item_id IN (';
                $query .= implode(',', $params['multiselect']) . ')';
                $dbr = $db->Execute($query, array(0));
                //Update search index
                $search = cms_utils::get_module('Search');
                foreach ($params['multiselect'] as $item_id) {
                    if ($search != FALSE)
                        $search->DeleteWords($this->GetName(), $item_id, 'item');
                    @$this->SendEvent('ItemEdited', array('item_id' => $item_id, 'active' => 0));
                }
            }
            break;
    }
}

$this->Setmessage($this->Lang('operation_complete'));
$this->RedirectToTab($id);
#
# EOF
#
?>
