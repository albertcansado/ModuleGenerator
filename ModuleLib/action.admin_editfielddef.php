<?php

if (!$this->CheckPermission($this->_GetModuleAlias() . '_modify_option'))
    return;


$db = $this->GetDb();
$fielddef_id = null;
$name = '';
$alias = '';
$help = '';
$type = '';
$required = 0;
$editview = 0;
$hidename = 0;
$admin_admin = 0;
$extra = '';
$section = 'items';
$errors = array();

if (isset($params['cancel'])) {
    $params = array('active_tab' => 'fielddeftab');
    $this->RedirectToTab($id, (isset($params['section']) ? $params['section'] : $section), array(), 'admin_fields');
}

// if $params['fielddef_id'] is supplied and exists, populate with values from database

if (isset($params['fielddef_id'])) {
    $fielddef_id = (int) $params['fielddef_id'];
    $query = 'SELECT * FROM ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fielddef WHERE fielddef_id = ?';
    $row = $db->GetRow($query, array($fielddef_id));

    if ($row) {
        $name = $row['name'];
        $alias = $row['alias'];
        $help = $row['help'];
        $type = $row['type'];
        $required = $row['required'];
        $editview = $row['editview'];
        $hidename = $row['hidename'];
        $extra = $row['extra'];
        $section = $row['section'];
    } else {
        $fielddef_id = null;
    }
}

// handle submit or apply
if (isset($params['submit']) || isset($params['apply'])) {
    $name = $params['name'];
    $alias = $params['alias'];
    $help = $params['help'];
    $type = $params['type'];
    $required = (isset($params['required']) ? 1 : 0);
    $editview = (isset($params['editview']) ? 1 : 0);
    $hidename = (isset($params['hidename']) ? 1 : 0);
    $extra = $params['extra'];
    $section = $params['section'];


    // check name
    if ($name == '') {
        $errors[] = $this->Lang('fielddef_name_empty');
    }

    // generate alias if not supplied
    if ($alias == '') {
        $alias = generator_tools::generate_alias($name);
    }

    // check alias
    if (!generator_tools::is_valid_alias($alias)) {
        $errors[] = $this->Lang('alias_invalid');
    }

    if (isset($fielddef_id)) {
        $query = 'SELECT fielddef_id FROM ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fielddef WHERE alias = ? AND fielddef_id != ?';
        $exists = $db->GetOne($query, array($alias, $fielddef_id));
    } else {
        $query = 'SELECT fielddef_id FROM ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fielddef WHERE alias = ?';
        $exists = $db->GetOne($query, array($alias));
    }

    if ($exists) {
        $errors[] = $this->Lang('fielddef_name_exists');
    }

    if (empty($errors)) {
        // update
        if (isset($fielddef_id)) {
            $query = 'UPDATE ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fielddef SET name = ?, alias = ?, help = ?, type = ?, required = ?, editview = ?, hidename = ?, extra = ?, section = ? WHERE fielddef_id = ?';
            $result = $db->Execute($query, array($name, $alias, $help, $type, $required, $editview, $hidename, $extra, $section, $fielddef_id));
            if (!$result)
                die('FATAL SQL ERROR: ' . $db->ErrorMsg() . '<br/>QUERY: ' . $db->sql);

            // insert
        } else {
            $position = $db->GetOne('SELECT max(position) + 1 FROM ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fielddef');

            if ($position == null) {
                $position = 1;
            }

            $query = 'INSERT INTO ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fielddef (name, alias, help, type, position, required, editview, hidename, extra, section) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
            $result = $db->Execute($query, array($name, $alias, $help, $type, $position, $required, $editview, $hidename, $extra, $section));
            if (!$result)
                die('FATAL SQL ERROR: ' . $db->ErrorMsg() . '<br/>QUERY: ' . $db->sql);

            // populate $fielddef_id for newly inserted item
            $fielddef_id = $db->Insert_ID();
        }

        if (!isset($params['apply'])) {
            $this->RedirectToTab($id, $section, array(), 'admin_fields');
        } else {
            echo $this->ShowMessage($this->Lang('changessaved'));
        }
    }
}

// display errors if there are any
if (!empty($errors)) {
    echo $this->ShowErrors($errors);
}

$smarty->assign('title', (isset($fielddef_id) ? $this->Lang('editfielddef') : $this->Lang('addfielddef')));
$smarty->assign('startform', $this->CreateFormStart($id, 'admin_editfielddef', $returnid));
$smarty->assign('endform', $this->CreateFormEnd());

$smarty->assign('nametext', $this->Lang('fielddef_name'));
$smarty->assign('inputname', $this->CreateInputText($id, 'name', $name, 20, 255));

$smarty->assign('alias', $this->Lang('alias'));
$smarty->assign('input_alias', $this->CreateInputText($id, 'alias', $alias, 20, 255));

$smarty->assign('helptext', $this->Lang('fielddef_help'));
$smarty->assign('inputhelp', $this->CreateInputText($id, 'help', $help, 100, 255));

$smarty->assign('typetext', $this->Lang('fielddef_type'));
$smarty->assign('inputtype', $this->CreateInputDropdown($id, 'type', array_flip(generator_tools::get_field_types($this)), -1, $type));

$smarty->assign('userviewtext', $this->Lang('fielddef_required'));
$smarty->assign('input_userview', $this->CreateInputcheckbox($id, 'required', 1, $required));

$smarty->assign('editview_text', $this->Lang('fielddef_editview'));
$smarty->assign('input_editview', $this->CreateInputcheckbox($id, 'editview', 1, $editview));

$smarty->assign('hidename_text', $this->Lang('fielddef_hidename'));
$smarty->assign('input_hidename', $this->CreateInputcheckbox($id, 'hidename', 1, $hidename));

$smarty->assign('extra', $this->Lang('extra'));
$smarty->assign('input_extra', $this->CreateInputText($id, 'extra', $extra, 100, 255));

$smarty->assign('input_section', $this->CreateInputDropdown($id, 'section', array_flip(generator_tools::get_field_sections($this)), -1, $section));

if (isset($fielddef_id)) {
    $smarty->assign('fielddef_id', $this->CreateInputHidden($id, 'fielddef_id', $fielddef_id));
}

$smarty->assign('submit', $this->CreateInputSubmit($id, 'submit', lang('submit')));
$smarty->assign('apply', $this->CreateInputSubmit($id, 'apply', lang('apply')));
$smarty->assign('cancel', $this->CreateInputSubmit($id, 'cancel', lang('cancel')));

echo $this->ModProcessTemplate('editfielddef.tpl');
?>
