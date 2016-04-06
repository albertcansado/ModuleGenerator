<?php

if (!$this->CheckPermission($this->_GetModuleAlias() . '_modify_option'))
    return;

$smarty->assign('startform', $this->CreateFormStart($id, 'admin_editoption', $returnid));
$smarty->assign('endform', $this->CreateFormEnd());
$smarty->assign('submit', $this->CreateInputSubmit($id, 'submit', lang('submit')));
$smarty->assign('prompt_default_action_params', $this->Lang('prompt_default_action_params'));
$smarty->assign('input_default_action_params', $this->CreateInputText($id, 'default_action_params', $this->GetPreference('default_action_params', ''), 50));


$smarty->assign('prompt_item_title_edit', $this->Lang('prompt_item_title_edit'));
$smarty->assign('input_item_title_edit', $this->CreateInputCheckbox($id, 'item_title_edit', 1, $this->GetPreference('item_title_edit')));

$smarty->assign('prompt_item_alias_edit', $this->Lang('prompt_item_alias_edit'));
$smarty->assign('input_item_alias_edit', $this->CreateInputCheckbox($id, 'item_alias_edit', 1, $this->GetPreference('item_alias_edit')));

$smarty->assign('prompt_item_url_edit', $this->Lang('prompt_item_url_edit'));
$smarty->assign('input_item_url_edit', $this->CreateInputCheckbox($id, 'item_url_edit', 1, $this->GetPreference('item_url_edit')));

$smarty->assign('prompt_item_date_edit', $this->Lang('prompt_item_date_edit'));
$smarty->assign('input_item_date_edit', $this->CreateInputCheckbox($id, 'item_date_edit', 1, $this->GetPreference('item_date_edit')));

$smarty->assign('prompt_item_date_end_edit', $this->Lang('prompt_item_date_end_edit'));
$smarty->assign('input_item_date_end_edit', $this->CreateInputCheckbox($id, 'item_date_end_edit', 1, $this->GetPreference('item_date_end_edit')));

$smarty->assign('prompt_item_category_edit', $this->Lang('prompt_item_category_edit'));
$smarty->assign('input_item_category_edit', $this->CreateInputCheckbox($id, 'item_category_edit', 1, $this->GetPreference('item_category_edit', 0)));

## Featured
$smarty->assign('prompt_item_featured_edit', $this->Lang('prompt_item_featured_edit'));
$smarty->assign('input_item_featured_edit', $this->CreateInputCheckbox($id, 'item_featured_edit', 1, $this->GetPreference('item_featured_edit', 0)));

$smarty->assign('prompt_recursive', $this->Lang('prompt_recursive'));
$smarty->assign('input_recursive', $this->CreateInputCheckbox($id, 'recursive', 1, $this->GetPreference('recursive', 0)));

$smarty->assign('input_has_gallery', $this->CreateInputCheckbox($id, 'has_gallery', 1, $this->GetPreference('has_gallery')));
$smarty->assign('input_gallery_sortorder', $this->CreateInputDropdown($id, 'gallery_sortorder', $sortorders, -1, $this->GetPreference('gallery_sortorder', 'desc')));

$smarty->assign('prompt_custom_fields_gallery_default', $this->Lang('prompt_custom_fields_default_admin'));
$smarty->assign('custom_fields_gallery_default', explode(',', $this->GetPreference('custom_fields_gallery_default')));

$smarty->assign('input_gallery_in_defaulttemplate', $this->CreateInputCheckbox($id, 'gallery_in_defaulttemplate', 1, $this->GetPreference('gallery_in_defaulttemplate', 0)));
$smarty->assign('input_gallery_defaulttemplate_limit', $this->CreateInputText($id, 'gallery_defaulttemplate_limit', $this->GetPreference('gallery_defaulttemplate_limit', 1), 50));

$smarty->assign('input_preview_admin', $this->CreateInputCheckbox($id, 'preview_admin', 1, $this->GetPreference('preview_admin')));
$smarty->assign('input_copy_admin', $this->CreateInputCheckbox($id, 'copy_admin', 1, $this->GetPreference('copy_admin')));


$all_fields = generator_tools::get_fields($this, false, 'galleries');
$fields_viewable  = array();
if (is_array($all_fields)) {
    for ($i = 0; $i < count($all_fields); $i++) {
        switch ($all_fields[$i]['type']) {
            case 'textarea':
                break;
            case 'static':
                break;
            case 'tab':
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



if (isset($fields_viewable) && count($fields_viewable)) {
    $smarty->assign('fields_gallery_viewable', $fields_viewable);
}

echo $this->ModProcessTemplate('optioneditingtab.tpl');
?>
