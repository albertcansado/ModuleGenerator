<?php

/*if (!isset($gCms))
    exit;*/

if (!$this->CheckPermission($this->_GetModuleAlias() . '_modify_item'))
    return;

$redirect = true;

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
        case 'duplicate':
            $queryItem = 'INSERT INTO ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_item (category_id, title, alias, url, recursive, position, active, featured, item_date, item_date_end, create_date, modified_date)
SELECT category_id, title, alias, url, recursive, (SELECT MAX(position) + 1 FROM ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_item WHERE 1=1), active, featured, item_date, item_date_end, NOW(), NOW() FROM ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_item WHERE item_id = ?';

            $queryFields = 'INSERT INTO ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval (item_id, fielddef_id, value) SELECT ?, fielddef_id, value FROM ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval WHERE item_id = ? AND fielddef_id IN (SELECT fielddef_id FROM ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fielddef WHERE section = "items" AND type NOT IN (\'upload_file\'))';
            foreach ($params['multiselect'] as $item_id) {
                // Copy Item
                if (!$db->Execute($queryItem, array($item_id))) {
                    echo 'error';
                    exit;
                }
                $lastId = $db->Insert_ID();

                // Copy fielddefs
                if (!$db->Execute($queryFields, array($lastId, $item_id))) {
                    echo 'error Copy fielddefs';
                    exit;
                }

                @$this->SendEvent('ItemAdded', array('item_id' => $lastId));
            }
            break;

        case 'category':
            if (!isset($params['category'])) {

                $redirect = false;

                // Get Category List
                $where = array();
                $paramsarray = array();
                $categorylongnamelist = generator_tools::get_private_categories($this);

                if (empty($categorylongnamelist) == false) {

                    $wherestatement = array();
                    foreach ($categorylongnamelist as $lonname) {
                        $wherestatement[] = 'long_name LIKE ?';
                        $paramsarray[] = $lonname . '%';
                    }
                    $where[] = '(' . implode(' OR ', $wherestatement) . ')';
                }
                $categorylist = generator_tools::get_category_list($this, $where, $paramsarray, false);
                $categorylist = array_slice($categorylist, 1);
                $categorylist = extended_tools_opts::to_hash($categorylist, 'long_name', 'category_id');

                // Get items
                $items = generator_tools::get_itemsById($this, $params['multiselect']);

                // Form
                $smarty->assign('formstart', $this->CreateFormStart($id, 'admin_bulkaction', $returnid));
                $smarty->assign('formend', $this->CreateFormEnd());

                // Inputs
                $smarty->assign('input_bulkaction', $this->CreateInputHidden($id, 'bulkaction', $params['bulkaction']));
                $smarty->assign('input_action', $this->CreateInputHidden($id, 'action', $params['action']));
                $smarty->assign('prompt_category', $this->Lang('category'));
                $smarty->assign('input_category', $this->CreateInputDropdown($id, 'category', $categorylist, -1));

                // Btn
                $smarty->assign('submit', $this->CreateInputSubmit($id, 'submit', lang('submit')));
                $smarty->assign('cancel', $this->CreateInputSubmit($id, 'cancel', lang('cancel')));

                // Other
                $smarty->assign('items', $items);
                $smarty->assign('multiselect', $params['multiselect']);

                echo $this->ModProcessTemplate('bulkaction.tpl');
            } else {
                // Change category
                if (!empty($params['submit']) && $params['submit'] === 'Submit') {
                    $query = 'UPDATE ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_item SET category_id = ?, modified_date = NOW() WHERE item_id IN (' . implode(',', $params['multiselect']) . ')';
                    $db->Execute($query, array($params['category']));
                }
            }
            break;
    }
}

if ($redirect) {
    $this->SetCurrentTab('itemtab');
    $this->Setmessage($this->Lang('operation_complete'));
    $this->RedirectToTab($id);
}
#
# EOF
#
?>
