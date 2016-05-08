<?php

if (!$this->CheckPermission($this->_GetModuleAlias() . '_modify_option'))
    return;


//$sortitems = array();
$sortitems = array();
$sortitems[$this->GetPreference('item_title')] = 'title';
$sortitems[$this->Lang('date')] = 'item_date';
$sortitems[$this->Lang('createddate')] = 'create_date';
$sortitems[$this->Lang('modifieddate')] = 'modified_date';

// custom fields
$fields_for_filter = generator_tools::get_field_defs($this, null, null, null);
$custom_flds = array();
$custom_fields_values = array();
$fielddefs = array();
if (!empty($fields_for_filter)) {
	$notForSorting = ['hr', 'tab', 'keyValue', 'upload_file', 'module', 'lookup', 'dropdown', 'json', 'video', 'dropdown_from_udt', 'dropdownfrommodule', 'module_link', 'static', 'file_picker', 'select_file'];
    foreach ($fields_for_filter as $row) {
    	if (!in_array($row['type'], $notForSorting)) {
        	$sortitems[$row['name']] = 'f:' . $row['alias'];
        }
    }
}

$sortorders = array();
$sortorders[$this->Lang('ascending')] = 'asc';
$sortorders[$this->Lang('descending')] = 'desc';
$thises = array();
$thises[$this->Lang('simple')] = 'simple';
$thises[$this->Lang('advanced')] = 'advanced';

$sortorder_advanced = $this->GetPreference('sortorder_advanced', 'desc');
$sortby_advanced = $this->GetPreference('sortby_advanced', 'create_date');

$sortorder_simple = $this->GetPreference('sortorder_simple', 'desc');
$sortby_simple = $this->GetPreference('sortby_simple', 'position');


$smarty->assign('startform', $this->CreateFormStart($id, 'admin_editoption', $returnid));
$smarty->assign('endform', $this->CreateFormEnd());
$smarty->assign('submit', $this->CreateInputSubmit($id, 'submit', lang('submit')));


$smarty->assign('prompt_mode', $this->Lang('mode'));
$smarty->assign('prompt_advanced', $this->Lang('advanced'));
$smarty->assign('prompt_simple', $this->Lang('simple'));

$smarty->assign('prompt_summary_sorting', $this->Lang('prompt_summary_sorting'));
$smarty->assign('input_summary_sorting_advanced', $this->CreateInputDropdown($id, 'sortby_advanced', $sortitems, -1, $sortby_advanced));

$smarty->assign('prompt_summary_sortorder', $this->Lang('prompt_summary_sortorder'));
$smarty->assign('input_summary_sortorder_advanced', $this->CreateInputDropdown($id, 'sortorder_advanced', $sortorders, -1, $sortorder_advanced));

$smarty->assign('prompt_summary_pagelimit', $this->Lang('prompt_summary_pagelimit'));
$smarty->assign('input_summary_pagelimit_advanced', $this->CreateInputText($id, 'summary_pagelimit_advanced', $this->GetPreference('summary_pagelimit_advanced', 25), 5, 5));

$smarty->assign('prompt_summary_sorting', $this->Lang('prompt_summary_sorting'));
$smarty->assign('input_summary_sorting_simple', $this->CreateInputText($id, 'sortby_simple', 'position', 50, 255, 'disabled="disabled"')
);

$smarty->assign('prompt_summary_sortorder', $this->Lang('prompt_summary_sortorder'));

$smarty->assign('input_summary_sortorder_simple', $this->CreateInputDropdown($id, 'sortorder_simple', $sortorders, -1, $sortorder_simple));

$smarty->assign('prompt_summary_pagelimit', $this->Lang('prompt_summary_pagelimit'));
$smarty->assign('input_summary_pagelimit_simple', $this->CreateInputText($id, 'summary_pagelimit_simple', $this->GetPreference('summary_pagelimit_simple', 25), 5, 5, 'disabled="disabled"'));

$smarty->assign('input_mode', $this->CreateInputDropdown($id, 'mode', $thises, -1, $this->GetPreference('mode', 'advanced')));


echo $this->ModProcessTemplate('modetab.tpl');
?>
