<?php

if (!$this->CheckPermission($this->_GetModuleAlias() . '_modify_categories'))
    return;

if (isset($params['cancel'])) {
    $this->RedirectToTab($id, 'categorytab');
}

$item_id = $catid = null;
if (isset($params['catid'])) {
    $item_id = $catid = $params['catid'];
}

$parentid = '-1';
if (isset($params['parent'])) {
    $parentid = $params['parent'];
}

$origname = '';
if (isset($params['origname'])) {
    $origname = $params['origname'];
}

$alias = '';
if (isset($params['alias'])) {
    $alias = munge_string_to_url($params['alias'], true);
}


$usergroup = '';
if (isset($params['usergroup'])) {
    $usergroup = $params['usergroup'];
}

// get the custom fields
$filters = new generator_filters($id, $this, 'admin', false);
$tmp = generator_fields::get_fields_for_filters($this, array(), 'categories');
$custom_flds = $filters->process_custom_fields(array(), $tmp);

$name = '';
$errors = array();
if (isset($params['name'])) {
    $name = $params['name'];
    if ($name == '') {
        $errors[] = $this->ShowErrors($this->Lang('nonamegiven'));
    }

    if (empty($alias)) {
        $alias = munge_string_to_url($name, true);
    }

    if (isset($params['customfield'])) {
        foreach ($params['customfield'] as $fldid => $value) {

            if ($value == 'select_default') {
                $params['customfield'][$fldid] = '';
                $value = '';
            }

            // required fields
            if ($value == '' && $custom_flds[$fldid]['required'] && ($custom_flds[$fldid]['editview'] && isset($params["item_id"]))) {
                $errors[] = $this->Lang('required_field_empty') . ' (' . $custom_flds[$fldid]['name'] . ')';
                break; // only display one error message at a time
            }

            // max length
            if (isset($custom_flds[$fldid]['max_length']) && mb_strlen($value) > $custom_flds[$fldid]['max_length']) {
                $errors[] = $this->Lang('too_long') . ' (' . $custom_flds[$fldid]['name'] . ')';
                break;
            }

            // options
            if ($value != '' && isset($custom_flds[$fldid]['options']) && !array_key_exists($value, $custom_flds[$fldid]['options'])) {
                $errors[] = $this->Lang('invalid') . ' (' . $custom_flds[$fldid]['name'] . ')';
                break;
            }
        }
    }

    // display errors if there are any
    if (!empty($errors)) {
        echo $this->ShowErrors($errors);
    } else {

        if ($catid != null) {
            $query = 'UPDATE ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_categories SET category_name = ?,category_alias = ?, usergroup = ?, parent_id = ?, modified_date = ' . $db->DBTimeStamp(time()) . ' WHERE category_id = ?';
            $parms = array($name, $alias, $usergroup, $parentid);
            $parms[] = $catid;
            $db->Execute($query, $parms);
            #@$this->SendEvent('CategoryEdited', array('category_id' => $catid, 'name' => $name));
            $rparms = array('tab_message' => 'categoryupdated');
            #generator_tools::update_hierarchy_positions($this);

            $eventName = 'CategoryEdited';
        } else {
            $time = $db->DBTimeStamp(time());
            $position = $db->GetOne('SELECT MAX(position) FROM ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_categories WHERE parent_id = ?', array(intval($parentid)));
            $query = 'INSERT INTO ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_categories (category_id, category_name, category_alias, usergroup, parent_id, position, create_date, modified_date) VALUES (?,?,?,?,?,?,' . $time . ',' . $time . ')';
            $parms = array($catid, $name, $alias, $usergroup, intval($parentid), $position);
            $db->Execute($query, $parms);
            $catid = $item_id = $db->Insert_ID();
            #@$this->SendEvent('CategoryAdded', array('category_id' => $catid, 'name' => $name));
            $rparms = array('tab_message' => 'categoryadded');
            #generator_tools::update_hierarchy_positions($this);

            $eventName = 'CategoryAdded';
        }

        generator_tools::update_hierarchy_positions($this);
        @$this->SendEvent($eventName, array('category_id' => $catid, 'name' => $name));

        // handle uploading of file fields
        foreach ($custom_flds as $custom_fld) {
            if ($custom_fld['type'] != 'upload_file')
                continue;

            $elem = $id . 'customfield_' . $custom_fld['fielddef_id'];
            // $elem looks like m1_customfield_1
            // check if file was uploaded for this field
            if (isset($_FILES[$elem]) && $_FILES[$elem]['name'] != '') {

                if ($_FILES[$elem]['error'] != 0 || $_FILES[$elem]['tmp_name'] == '') {
                    $errors[] = $this->Lang('error_upload');
                } else {
                    $value = generator_tools::handle_upload($this, $item_id, $elem, $error, $custom_fld['allow'], $row, false);
                    if ($value === false) {
                        $errors[] = $error;
                    } else {
                        // populate $params['customfield']
                        // this would hold the uploaded filename
                        // this is inserted into the database below
                        $params['customfield'][$custom_fld['fielddef_id']] = $value;
                    }
                }
            }
        }
        // handle inserting custom fields into database
        if (isset($params['customfield'])) {
            foreach ($params['customfield'] as $fldid => $value) {

                if (is_array($value)) {
                    if (!empty($value))
                        $value = implode(',', $value);
                    else
                        $value = '';
                }

                // check if row exists to determine whether to insert or update
                $query = 'SELECT value FROM ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval WHERE item_id = ? AND fielddef_id = ?';
                $tmp = $db->GetOne($query, array($item_id, $fldid));
                // row does not exist
                if ($tmp == "") {
                    // only insert row if field value is not empty
                    if ($value != "") {
                        $query = 'INSERT INTO ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval (item_id, fielddef_id, value) VALUES (?, ?, ?)';
                        $result = $db->Execute($query, array($item_id, $fldid, $value));
                    }
                    // row already exists
                } else {
                    // delete row if field value is empty
                    if ($value == "") {
                        $query = 'DELETE FROM ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval WHERE item_id = ? AND fielddef_id = ?';
                        $result = $db->Execute($query, array($item_id, $fldid));
                        // update row
                    } else {
                        $query = 'UPDATE ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval SET value = ? WHERE item_id = ? AND fielddef_id = ?';
                        $result = $db->Execute($query, array($value, $item_id, $fldid));
                    }
                }

            }
        }
        // delete value from file field
        if (isset($params['delete_customfield']) && is_array($params['delete_customfield'])) {
            foreach ($params['delete_customfield'] as $k => $v) {
                if ($v != 'delete')
                    continue; // skip if not deleting anything

                $query = 'SELECT type, value FROM ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval, '  . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fielddef WHERE `' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval`.`item_id` = ? AND `' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval`.`fielddef_id` = ?';
                $row = $db->getRow($query, array($item_id, $k));
                if ($row['type'] === 'upload_file') {
                    $dir = generator_tools::filepath_location($this, $item_id, false);
                    $file = cms_join_path($dir, $row['value']);
                    if (is_dir($dir) && is_file($file)) {
                        unlink($file);
                    }
                }

                $query = 'DELETE FROM ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval WHERE item_id = ? AND fielddef_id = ?';
                $db->Execute($query, array($item_id, $k));
            }
        }


        $this->RedirectToTab($id, 'categorytab', $rparms);
    }
} else {
    $query = 'SELECT * FROM ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_categories WHERE category_id = ?';
    $row = $db->GetRow($query, array($catid));

    if ($row) {
        $name = $row['category_name'];
        $alias = $row['category_alias'];
        $usergroup = $row['usergroup'];
        $parentid = $row['parent_id'];
    }
}


#Display template
$this->smarty->assign('startform', $this->CreateFormStart($id, 'admin_editcategory', $returnid, 'post', 'multipart/form-data'));
$this->smarty->assign('endform', $this->CreateFormEnd());
$this->smarty->assign('nametext', $this->Lang('name'));
$this->smarty->assign('aliastext', $this->Lang('alias'));

$this->smarty->assign('inputname', $this->CreateInputText($id, 'name', $name, 20, 255));
$this->smarty->assign('inputalias', $this->CreateInputText($id, 'alias', $alias, 20, 255));

$groupops = cmsms()->GetGroupOperations();
$grouplist = $groupops->LoadGroups();
$usergroups = extended_tools_opts::object_to_hash($grouplist, 'name', 'id');

$usergroups = array_merge(array(lang('none') => ''), $usergroups);
$this->smarty->assign('inputusergroup', $this->CreateInputDropdown($id, 'usergroup', $usergroups, -1, $usergroup));

$this->smarty->assign('parentdropdown', generator_tools::create_parent_dropdown($this, $id, $catid, $parentid));
$this->smarty->assign('hidden', $this->CreateInputHidden($id, 'catid', $catid) .
        $this->CreateInputHidden($id, 'origname', $name));
$this->smarty->assign('submit', $this->CreateInputSubmit($id, 'submit', lang('submit')));
$this->smarty->assign('cancel', $this->CreateInputSubmit($id, 'cancel', lang('cancel')));
$this->smarty->assign('parenttext', lang('parent'));
$this->smarty->assign('usergrouptext', lang('group'));

$custom_flds_obj = $filters->generate($item_id, $params);
if (isset($custom_flds_obj) && count($custom_flds_obj) > 0) {
    $smarty->assign('custom_fielddef', $custom_flds_obj);
}

echo $this->ModProcessTemplate('editcategory.tpl');
?>
