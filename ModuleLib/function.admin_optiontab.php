<?php

if (!$this->CheckPermission($this->_GetModuleAlias() . '_modify_option'))
    return;

$smarty->assign('startform', $this->CreateFormStart($id, 'admin_editoption', $returnid));
$smarty->assign('endform', $this->CreateFormEnd());
$smarty->assign('submit', $this->CreateInputSubmit($id, 'submit', lang('submit')));

$smarty->assign('prompt_friendlyname', $this->Lang('prompt_friendlyname'));
$smarty->assign('input_friendlyname', $this->CreateInputText($id, 'friendlyname', $this->GetPreference('friendlyname', ''), 50));

$smarty->assign('prompt_item_title', $this->Lang('prompt_item_title'));
$smarty->assign('input_item_title', $this->CreateInputText($id, 'item_title', $this->GetPreference('item_title', ''), 50));

$smarty->assign('prompt_item_singular', $this->Lang('prompt_item_singular'));
$smarty->assign('input_item_singular', $this->CreateInputText($id, 'item_singular', $this->GetPreference('item_singular', ''), 50));

$smarty->assign('prompt_item_plural', $this->Lang('prompt_item_plural'));
$smarty->assign('input_item_plural', $this->CreateInputText($id, 'item_plural', $this->GetPreference('item_plural', ''), 50));

$smarty->assign('prompt_url_prefix', $this->Lang('prompt_url_prefix'));
$smarty->assign('input_url_prefix', $this->CreateInputText($id, 'url_prefix', $this->GetPreference('url_prefix', ''), 50));

$smarty->assign('prompt_display_inline', $this->Lang('prompt_display_inline'));
$smarty->assign('input_display_inline', $this->CreateInputCheckbox($id, 'display_inline', 1, $this->GetPreference('display_inline')));

$smarty->assign('prompt_usergroup_dependence', $this->Lang('prompt_usergroup_dependence'));
$smarty->assign('input_usergroup_dependence', $this->CreateInputCheckbox($id, 'usergroup_dependence', 1, $this->GetPreference('usergroup_dependence')));

$smarty->assign('prompt_has_admin', $this->Lang('prompt_has_admin'));
$smarty->assign('input_has_admin', $this->CreateInputCheckbox($id, 'has_admin', 1, $this->GetPreference('has_admin')));

$smarty->assign('prompt_admin_section', $this->Lang('prompt_admin_section'));
$smarty->assign('input_admin_section', $this->CreateInputText($id, 'admin_section', $this->GetPreference('admin_section', 'content'), 50));


$contentops = cmsms()->GetContentOperations();

$smarty->assign('prompt_item_detail_returnid', $this->Lang('prompt_item_detail_returnid'));
$smarty->assign('input_item_detail_returnid', $contentops->CreateHierarchyDropdown('', $this->GetPreference('item_detail_returnid', -1), $id . 'item_detail_returnid'));

$smarty->assign('prompt_item_category_returnid', $this->Lang('prompt_item_category_returnid'));
$smarty->assign('input_item_category_returnid', $contentops->CreateHierarchyDropdown('', $this->GetPreference('item_category_returnid', -1), $id . 'item_category_returnid'));

$smarty->assign('prompt_custom_fields_default', $this->Lang('prompt_custom_fields_default_admin'));
$smarty->assign('custom_fields_default', explode(',', $this->GetPreference('custom_fields_default')));

$smarty->assign('input_filter', $this->CreateInputCheckbox($id, 'filter', 1, $this->GetPreference('filter')));
$smarty->assign('input_filter_categories', $this->CreateInputCheckbox($id, 'filter_categories', 1, $this->GetPreference('filter_categories')));

$smarty->assign('prompt_filter_date', $this->Lang('prompt_filter_date'));
$smarty->assign('input_filter_date', $this->CreateInputCheckbox($id, 'filter_date', 1, $this->GetPreference('filter_date')));

$fields = generator_fields::get_field_defs($this);
$filter_custom_fields = array();
if (!empty($fields)) {
    $notForSorting = ['hr', 'tab', 'keyValue', 'upload_file', 'module', 'lookup', 'dropdown', 'json', 'video', 'dropdown_from_udt', 'dropdownfrommodule', 'module_link', 'static', 'file_picker', 'select_file'];
    foreach ($fields as $field) {
        if (!in_array($field['type'], $notForSorting)) {
                $filter_custom_fields[$field['name']] =
                $this->CreateInputHidden($id, 'filter_custom_fields[' . $field['fielddef_id'] . ']', 0)
                . $this->CreateInputCheckbox($id, 'filter_custom_fields[' . $field['fielddef_id'] . ']', 1, $field['filter_admin']);
        }
    }
}
$smarty->assign('filter_custom_fields', $filter_custom_fields);


$all_fields = generator_tools::get_fields($this, false);
if (is_array($all_fields)) {
    for ($i = 0, $j = count($all_fields); $i < $j; $i++) {
        switch ($all_fields[$i]['type']) {
            case 'textarea':
            case 'static':
            case 'tab':
            case 'key_value':
            case 'hr':
            case 'json':
            case 'video':
                break;
            case 'upload_file':
                //print_r($all_fields[$i]['extra']);
                // Only image upload_file
                if ( (bool)preg_match('/(jpg|png|jpeg|gif)+/i', $all_fields[$i]['extra']) ) {
                    $fields_viewable[$all_fields[$i]['fielddef_id']] = $all_fields[$i]['name'];
                }
                break;
            default:
                $fields_viewable[$all_fields[$i]['fielddef_id']] = $all_fields[$i]['name'];
                break;
        }
    }
    if (isset($custom_fields) && count($fields_viewable) && is_array($custom_fields)) {
        // now trim down the custom fields
        // to make sure that something hasn't been deleted.
        $tmp = array();
        foreach ($custom_fields as $fid) {
            if (in_array($fid, array_keys($fields_viewable))) {
                $tmp[] = $fid;
            }
        }
        $custom_fields = $tmp;
    } else {
        $custom_fields = array();
    }
}
if (isset($fields_viewable) && count($fields_viewable))
    $smarty->assign('fields_viewable', $fields_viewable);


echo $this->ModProcessTemplate('optiontab.tpl');
?>
