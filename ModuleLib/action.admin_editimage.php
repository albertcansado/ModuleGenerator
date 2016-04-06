<?php

if (!$this->CheckPermission($this->_GetModuleAlias() . '_modify_item'))
    return;

$item_id = '';
$image_id = '';
$error = false;

if (isset($params['item_id'])) {
    $item_id = (int) $params['item_id'];
}

if (isset($params['image_id'])) {
    $image_id = (int) $params['image_id'];
}

$row = generator_tools::get_item($this, $item_id);
$image = generator_tools::get_image($this, $image_id);

if (empty($item_id) || empty($image_id)) {
    $error = true;
    $this->SetError($this->Lang('empty_param'));
}
if (!isset($image['filename'])) {
    $error = true;
    $this->SetError($this->Lang('image_not_found'));
}
if ($error == true) {
    $this->RedirectToTab($id, '', $params, 'defaultaction');
}

$imagesrc = generator_tools::image_location($this, $row) . '/' . $image['filename'];

$errors = array();
$returnid = '';

if (isset($params['cancel'])) {
    $parms = array();
    $parms['item_id'] = $params['item_id'];
    $this->RedirectToTab($id, 'gallery', $parms, 'admin_edititem');
}

// get the custom fields
$filters = new generator_filters($id, $this, 'admin');
$tmp = generator_fields::get_fields_for_filters($this, array(), 'galleries');
$custom_flds = $filters->process_custom_fields(array(), $tmp);


if (isset($params['submit'])) {
    if (isset($params['customfield'])) {
        foreach ($params['customfield'] as $fldid => $value) {

            if ($value == 'select_default') {
                $params['customfield'][$fldid] = '';
                $value = '';
            }

            // required fields
            if ($value == '' && $custom_flds[$fldid]['required'] && ($custom_flds[$fldid]['editview'] && isset($params["image_id"]))) {
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
                $value = generator_tools::handle_upload($this, $item_id, $elem, $error, $custom_fld['allow'], $row);
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
            $tmp = $db->GetOne($query, array($image_id, $fldid));
            // row does not exist
            if ($tmp == "") {
                // only insert row if field value is not empty
                if ($value != "") {
                    $query = 'INSERT INTO ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval (item_id, fielddef_id, value) VALUES (?, ?, ?)';
                    $result = $db->Execute($query, array($image_id, $fldid, $value));
                }
                // row already exists
            } else {
                // delete row if field value is empty
                if ($value == "") {
                    $query = 'DELETE FROM ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval WHERE item_id = ? AND fielddef_id = ?';
                    $result = $db->Execute($query, array($image_id, $fldid));
                    // update row
                } else {
                    $query = 'UPDATE ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval SET value = ? WHERE item_id = ? AND fielddef_id = ?';
                    $result = $db->Execute($query, array($value, $image_id, $fldid));
                }
            }
        }
    }

// delete value from file field
    if (isset($params['delete_customfield']) && is_array($params['delete_customfield'])) {
        foreach ($params['delete_customfield'] as $k => $v) {
            if ($v != 'delete')
                continue; // skip if not deleting anything

            $query = 'DELETE FROM ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval WHERE item_id = ? AND fielddef_id = ?';
            $db->Execute($query, array($image_id, $k));
        }
    }

    $parms = array();
    $parms['item_id'] = $item_id;
    $this->RedirectToTab($id, 'gallery', $parms, 'admin_edititem');
}


#Display template
$this->smarty->assign('startform', $this->CreateFormStart($id, 'admin_editimage', $returnid, 'post', 'multipart/form-data', false, '', array('item_id' => $item_id, 'image_id' => $image_id)));
$this->smarty->assign('endform', $this->CreateFormEnd());

$this->smarty->assign('submit', $this->CreateInputSubmit($id, 'submit', lang('submit')));
$this->smarty->assign('cancel', $this->CreateInputSubmit($id, 'cancel', lang('cancel')));


$this->smarty->assign('item', $row);
$this->smarty->assign('image', $image);
$this->smarty->assign('imagesrc', $imagesrc);

$custom_flds_obj = $filters->generate($image_id, $params);

if (isset($custom_flds_obj) && count($custom_flds_obj) > 0) {
    $smarty->assign('custom_fielddef', $custom_flds_obj);
}

echo $this->ModProcessTemplate('editimage.tpl');
?>