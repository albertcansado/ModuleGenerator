<?php

// Setup
//
$pagelimits = array('2' => 2, '5' => 5, '25' => 25, '100' => 100, '500' => 500);
$sortitems = array();
$sortitems[$this->Lang('date')] = 'item_date';
$sortitems[$this->Lang('createddate')] = 'create_date';
$sortitems[$this->Lang('modifieddate')] = 'modified_date';
$sortorders = array();
$sortorders[$this->Lang('ascending')] = 'asc';
$sortorders[$this->Lang('descending')] = 'desc';
$inline = isset($params["inline"]) ? $params["inline"] : 0;

$hierarchy = get_parameter_value($params, $this->_GetModuleAlias() . '_hierarchy', '');
$children = get_parameter_value($params, $this->_GetModuleAlias() . '_children', 0);
$pagelimit = get_parameter_value($params, $this->_GetModuleAlias() . '_pagelimit', 25);
$sortby = get_parameter_value($params, $this->_GetModuleAlias() . '_sortby', 'item_date');
$sortorder = get_parameter_value($params, $this->_GetModuleAlias() . '_sortorder', 'desc');
$date_from = get_parameter_value($params, $this->_GetModuleAlias() . '_date_from', time() - (3600 * 24 * 7));
$date_to = get_parameter_value($params, $this->_GetModuleAlias() . '_date_to', time());


$categorylist = generator_tools::get_category_list($this);
if ($inline) {
    $smarty->assign('formstart', $this->CGCreateFormStart($id, 'filter', $returnid, array(), $inline, 'GET'));
} else {
    $smarty->assign('formstart', $this->CGCreateFormStart($id, 'default', $returnid, array(), $inline, 'GET'));
}
$smarty->assign('formend', $this->CreateFormEnd());
$smarty->assign('input_hierarchy', $this->CreateInputDropdown($id, 'input_hierarchy', $categorylist, -1, $hierarchy));
$smarty->assign('input_children', $this->CreateInputYesNoDropdown($id, 'input_children', $children));
$smarty->assign('input_sortby', $this->CreateInputDropdown($id, 'input_sortby', $sortitems, -1, $sortby));
$smarty->assign('input_sortorder', $this->CreateInputDropdown($id, 'input_sortorder', $sortorders, -1, $sortorder));
$smarty->assign('input_pagelimit', $this->CreateInputDropdown($id, 'input_pagelimit', $pagelimits, -1, $pagelimit));

$smarty->assign('date_from', $date_from);
$smarty->assign('date_to', $date_to);

$smarty->assign('input_submit', $this->CreateInputSubmit($id, 'submit', $this->Lang('submit')));

// custom fields
$fields_for_filter = generator_tools::get_field_defs($this, null, 1, null);
$custom_flds = array(1);
$custom_fields_values = array();
foreach ($fields_for_filter as $row) {
    $custom_flds[$row['fielddef_id']] = $row;
    $custom_fields_values[$row['fielddef_id']] = get_preference($uid, $this->_GetModuleAlias() . '_customfields' . $row['fielddef_id'], '');
}


// get the custom fields
$filters = new generator_filters($id, $this, 'admin');
$custom_flds = $filters->process_custom_fields(array(), $custom_flds);
$custom_flds_obj = $filters->generate($item_id, $params, $custom_fields_values, 1);
if (isset($custom_flds_obj) && count($custom_flds_obj) > 0) {
    $smarty->assign('custom_fielddef', $custom_flds_obj);
}

// smarty params
foreach ($params as $key => $value) {
    if ($key == 'mact' || $key == 'action')
        continue;
    $smarty->assign('param_' . $key, $value);
}


// template
$thetemplate = 'filter_template' . $this->GetPreference('current_filter_template');
if (isset($params['filtertemplate'])) {
    $thetemplate = 'filter_template' . $params['filtertemplate'];
}

//
// Process the template
//

ob_start();
$this->DoAction('default', $id, $params, $returnid);
$output = ob_get_contents();
ob_end_clean();
$smarty->assign('results', $output);

echo $this->ProcessTemplateFromDatabase($thetemplate);
?>