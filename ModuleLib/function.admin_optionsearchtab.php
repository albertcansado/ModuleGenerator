<?php

if (!$this->CheckPermission($this->_GetModuleAlias() . '_modify_option'))
    return;

$smarty->assign('startform', $this->CreateFormStart($id, 'admin_editoption', $returnid));
$smarty->assign('endform', $this->CreateFormEnd());
$smarty->assign('submit', $this->CreateInputSubmit($id, 'submit', lang('submit')));

$smarty->assign('prompt_searchable', $this->Lang('prompt_searchable'));
$smarty->assign('input_searchable', $this->CreateInputCheckbox($id, 'searchable', 1, $this->GetPreference('searchable')));

$smarty->assign('searchable', $this->GetPreference('searchable'));
$smarty->assign('searchable_link', $this->CreateLink($id, 'admin_search_reindex', $returnid, '', array(), '', true));

$smarty->assign('prompt_search_date_end', $this->Lang('prompt_search_date_end'));
$smarty->assign('input_search_date_end', $this->CreateInputCheckbox($id, 'search_date_end', 1, $this->GetPreference('search_date_end')));

$fields = generator_fields::get_field_defs($this);
$search_custom_fields = array();
if (!empty($fields)) {
	$notForSorting = ['hr', 'tab', 'keyValue', 'upload_file', 'module', 'lookup', 'dropdown', 'json', 'video', 'dropdown_from_udt', 'dropdownfrommodule', 'module_link', 'static', 'file_picker', 'select_file'];
    foreach ($fields as $field) {
    	if (!in_array($field['type'], $notForSorting)) {
	        $search_custom_fields[$field['name']] =
	                $this->CreateInputHidden($id, 'search_custom_fields[' . $field['fielddef_id'] . ']', 0)
	                . $this->CreateInputCheckbox($id, 'search_custom_fields[' . $field['fielddef_id'] . ']', 1, $field['searchable']);
	    }
    }
}
$smarty->assign('search_custom_fields', $search_custom_fields);

echo $this->ModProcessTemplate('optionsearchtab.tpl');
?>
