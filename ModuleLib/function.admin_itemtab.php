<?php

if (!$this->CheckPermission($this->_GetModuleAlias() . '_modify_item'))
    return;

$where = array();
$categorylongnamelist = array();
$paramsarray = array();
$admintheme = cmsms()->get_variable('admintheme');

$smarty->assign('module', $this);

$start_category = '';

$categorylongnamelist = generator_tools::get_private_categories($this);

//
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
$uid = get_userid(false);
$hierarchy = '';
$children = 0;
$pagelimit = 25;
$sortby = 'create_date';
$sortorder = 'desc';
$pagenumber = 1;
$fields_viewable = array();
$field_names = array();
$field_modules = array();
$field_images = array();
$custom_fields = '';
$date_to = time();

if (!$this->GetPreference('item_date_edit'))
    unset($sortitems[$this->Lang('date')]);


// custom fields
$fields_for_filter = generator_tools::get_field_defs($this, null, null, null);
$custom_flds = array(1);
$custom_fields_values = array();
$fielddefs = array();
if (empty($fields_for_filter) == false) {
    foreach ($fields_for_filter as $row) {
        if ($row["filter_admin"]) {
            $custom_flds[$row['fielddef_id']] = $row;
            $custom_fields_values[$row['fielddef_id']] = get_preference($uid, $this->_GetModuleAlias() . '_customfields' . $row['fielddef_id'], '');
        }
        $fielddefs[$row['alias']] = cge_array::to_object($row);
        $sortitems[$row['name']] = 'f:' . $row['alias'];
    }
}

//
// Get preferences
//
$hierarchy = get_preference($uid, $this->_GetModuleAlias() . '_hierarchy', '');
$children = get_preference($uid, $this->_GetModuleAlias() . '_children', 0);
$pagelimit = get_preference(
    $uid,
    $this->_GetModuleAlias() . '_pagelimit',
    $this->GetPreference('summary_pagelimit_advanced', 25)
);
$sortby = get_preference($uid, $this->_GetModuleAlias() . '_sortby', 'item_date');
$sortorder = get_preference($uid, $this->_GetModuleAlias() . '_sortorder', 'desc');
$date_from = get_preference($uid, $this->_GetModuleAlias() . '_date_from', time() - (3600 * 24 * 7));
$date_to = get_preference($uid, $this->_GetModuleAlias() . '_date_to', time() + (3600 * 24 * 7));


if (empty($tmp))
    $tmp = $this->GetPreference('custom_fields_default');
if (!empty($tmp))
    $custom_fields = explode(',', $tmp);

//
// Handle Get parameters
//
if (isset($params['pagenumber'])) {
    $pagenumber = (int) $params['pagenumber'];
}

//
// Handle form submit
//
if (isset($params['submit'])) {
    if ($this->GetPreference('mode') != "simple") {
        $pagelimit = (int) $params['input_pagelimit'];
        $sortby = trim($params['input_sortby']);
        $sortorder = trim($params['input_sortorder']);
        // store them as user preferences
        set_preference($uid, $this->_GetModuleAlias() . '_children', $children);
        set_preference($uid, $this->_GetModuleAlias() . '_pagelimit', $pagelimit);
        set_preference($uid, $this->_GetModuleAlias() . '_sortby', $sortby);
        set_preference($uid, $this->_GetModuleAlias() . '_sortorder', $sortorder);
    }

    if ($this->GetPreference('filter_categories')) {
        $children = (int) $params['input_children'];
        set_preference($uid, $this->_GetModuleAlias() . '_children', $children);
        $hierarchy = trim($params['hierarchy']);
        set_preference($uid, $this->_GetModuleAlias() . '_hierarchy', $hierarchy);
    }

    if ($this->GetPreference('filter_date')) {
        $date_from = mktime(0, 0, 0, $params['datefromMonth'], $params['datefromDay'], $params['datefromYear']);
        $date_to = mktime(23, 59, 59, $params['datetoMonth'], $params['datetoDay'], $params['datetoYear']);
        set_preference($uid, $this->_GetModuleAlias() . '_date_from', $date_from);
        set_preference($uid, $this->_GetModuleAlias() . '_date_to', $date_to);
    }

    if (isset($params['customfield'])) {
        set_preference($uid, $this->_GetModuleAlias() . '_custom_fields', implode(',', $custom_fields));
        foreach ($params['customfield'] as $fldid => $value) {
            set_preference($uid, $this->_GetModuleAlias() . '_customfields' . $fldid, $value);
            $custom_fields_values[$fldid] = $value;
        }
    }
}


// if simple mode, reset all prefs
if ($this->GetPreference('mode') === "simple") {
    $sortorder = $this->GetPreference('sortorder_simple', 'desc');
    $sortby = $this->GetPreference('sortby_simple', 'position');
    $pagelimit = $this->GetPreference('summary_pagelimit_simple', 1000);
}
// get category list
$where = array();
$paramsarray = array();
if (empty($categorylongnamelist) == false) {

    $wherestatement = array();
    foreach ($categorylongnamelist as $lonname) {
        $wherestatement[] = 'long_name LIKE  ?';
        $paramsarray[] = $lonname . ' %';
    }
    $where[] = '(' . implode(' OR ', $wherestatement) . ')';
}
$categorylist = generator_tools::get_category_list($this, $where, $paramsarray);

// fields view
$all_fields = generator_tools::get_fields($this, false);


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
                $field_names[$all_fields[$i]['fielddef_id']] = $all_fields[$i]['alias'];

                // if module, get module func
                if ($all_fields[$i]['type'] == "module") {
                    $instructions = generator_tools::get_extra($all_fields[$i]['extra']);
                    $custommoduleparams = generator_tools::get_extra_moduleparams($instructions);
                    if (is_array($custommoduleparams) && !empty($custommoduleparams) && cms_utils::get_module($custommoduleparams["module"])) {
                        $field_modules[$all_fields[$i]['fielddef_id']] = $custommoduleparams["module"];
                    }
                }
                // if module, get module func
                if ($all_fields[$i]['type'] == "upload_file") {
                    $field_images[$all_fields[$i]['fielddef_id']] = 1;
                }
                break;
        }
    }
    if (count($fields_viewable) && is_array($custom_fields)) {
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
$all_fields = cge_array::to_hash($all_fields, 'name');
if (count($fields_viewable)) {
    $smarty->assign('fields_viewable', $fields_viewable);
    $smarty->assign('field_names', $field_names);
    $smarty->assign('field_modules', $field_modules);
    $smarty->assign('field_images', $field_images);
}

$smarty->assign('custom_fields', $custom_fields);

$smarty->assign('formstart', $this->CreateFormStart($id, 'defaultadmin'));
$smarty->assign('formend', $this->CreateFormEnd());



$smarty->assign('input_hierarchy', $this->CreateInputDropdown($id, 'hierarchy', $categorylist, -1, $hierarchy));

$smarty->assign('input_children', $this->CreateInputYesNoDropdown($id, 'input_children', $children));
$smarty->assign('input_sortby', $this->CreateInputDropdown($id, 'input_sortby', $sortitems, -1, $sortby));
$smarty->assign('input_sortorder', $this->CreateInputDropdown($id, 'input_sortorder', $sortorders, -1, $sortorder));
$smarty->assign('input_pagelimit', $this->CreateInputDropdown($id, 'input_pagelimit', $pagelimits, -1, $pagelimit));

//
// Build the query
//
$fields = array();
$fields[] = 'A.*';
$fields[] = 'B.long_name';
$prefix1 = "SELECT <FIELDS> FROM " . cms_db_prefix() . "module_" . $this->_GetModuleAlias() . "_item A ";
$prefix2 = "SELECT COUNT(A.item_id) FROM " . cms_db_prefix() . "module_" . $this->_GetModuleAlias() . "_item A ";
$where = array();
$joins = array();
$qparms = array();

if (is_array($custom_fields) && count($custom_fields) && count($fields_viewable)) {
    for ($j = 0; $j < count($custom_fields); $j++) {
        $fid = $custom_fields[$j];
        $fields[] = "Fv{$j}.value AS 'Fld__{$field_names[$fid]}'";
        $joins[] = 'LEFT OUTER JOIN ' . cms_db_prefix() . "module_" . $this->_GetModuleAlias() . "_fieldval Fv{$j}
                    ON Fv{$j}.item_id = A.item_id AND Fv{$j}.fielddef_id = ?";
        $qparms[] = $custom_fields[$j];
    }
}


$joins[] = 'LEFT JOIN ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_categories B
                  ON A.category_id = B.category_id';

if (!empty($hierarchy)) {

    $where[] = 'B.long_name LIKE ?';
    if ($children) {
        $qparms[] = $hierarchy . '%';
    } else {
        $qparms[] = $hierarchy;
    }
} elseif (empty($categorylongnamelist) == false) {
    foreach ($categorylongnamelist as $longname) {
        $where[] = 'B.long_name LIKE ?';
        $qparms[] = $longname . '%';
    }
}

if ($this->GetPreference('filter_date') && $date_from && $date_to) {
    $where[] = " A.item_date  > ? and A.item_date  < ? ";
    $qparms[] = trim($db->DBTimeStamp($date_from), "'");
    $qparms[] = trim($db->DBTimeStamp($date_to), "'");
}

// procces values from params
if ($custom_fields_values) {
    foreach ($custom_fields_values as $fldid => $value) {
        if (empty($value))
            continue;

        $j = 'c' . $fldid;
        $joins[] = 'LEFT OUTER JOIN ' . cms_db_prefix() . "module_" . $this->_GetModuleAlias() . "_fieldval Fv{$j}
                    ON Fv{$j}.item_id = A.item_id AND Fv{$j}.fielddef_id = ?";
        //array_unshift($qparms, $fldid);
        $qparms[] = $fldid;
        $where[] = ' Fv' . $j . '.value  = ?';
        $qparms[] = $value;
    }
}


// handle funky custom field sort orders
if (startswith($sortby, 'f:')) {
    $fieldname = substr($sortby, strlen('f:'));
    if (isset($fielddefs[$fieldname])) {
        $fid = $fielddefs[$fieldname]->fielddef_id;
        $joins[] = 'LEFT JOIN ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval fv
                  ON fv.item_id = A.item_id';
        $where[] = 'fv.fielddef_id = ?';
        $qparms[] = $fid;
        $order = " ORDER BY fv.value $sortorder";
    }
} else {
    $order = " ORDER BY $sortby $sortorder";
}

$str = implode(' ', $joins);
if (count($where)) {
    $str .= ' WHERE ' . implode(' AND ', $where);
}
$query1 = str_replace('<FIELDS>', implode(',', $fields), $prefix1) . $str . $order;
$query2 = $prefix2 . $str;

//
// Setup start element, and count pages
//
$totalcount = $db->GetOne($query2, $qparms);
$pagecount = (int) ($totalcount / $pagelimit);
if (($totalcount % $pagelimit) != 0)
    $pagecount++;
$startelement = ($pagenumber - 1) * $pagelimit;

//
// Begin the output
//
$smarty->assign('pagenumber', $pagenumber);
$smarty->assign('pagecount', $pagecount);
$smarty->assign('totalrows', $totalcount);
if ($pagenumber > 1) {
    $parms = array('pagenumber' => 1);
    $smarty->assign('firstpage_url', $this->CreateURL($id, 'defaultadmin', '', $parms));
    $parms = array('pagenumber' => $pagenumber - 1);
    $smarty->assign('prevpage_url', $this->CreateURL($id, 'defaultadmin', '', $parms));
}
if ($pagenumber < $pagecount) {
    $parms = array('pagenumber' => $pagenumber + 1);
    $smarty->assign('nextpage_url', $this->CreateURL($id, 'defaultadmin', '', $parms));
    $parms = array('pagenumber' => $pagecount);
    $smarty->assign('lastpage_url', $this->CreateURL($id, 'defaultadmin', '', $parms));
}

$position = $db->GetOne('SELECT max(position) as max_position FROM ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_item');

$entryarray = array();
$dbresult = $db->SelectLimit($query1, $pagelimit, $startelement, $qparms);
//echo $db->sql;
while ($dbresult && $row = $dbresult->FetchRow()) {
    $onerow = cge_array::to_object($row);

    // move up
    if ($row['position'] > 1) {
        $onerow->uplink = $this->CreateLink($id, 'admin_moveitem', $returnid, $admintheme->DisplayImage('icons/system/arrow-u.gif', $this->Lang('up'), '', '', 'systemicon'), array('item_id' => $row['item_id'], 'dir' => 'up'));
    } else {
        $onerow->uplink = '';
    }

    // move down
    if ($position > $row['position']) {
        $onerow->downlink = $this->CreateLink($id, 'admin_moveitem', $returnid, $admintheme->DisplayImage('icons/system/arrow-d.gif', $this->Lang('down'), '', '', 'systemicon'), array('item_id' => $row['item_id'], 'dir' => 'down'));
    } else {
        $onerow->downlink = '';
    }

    // approve
    if ($row['active']) {
        $onerow->approve = $this->CreateLink($id, 'admin_approveitem', $returnid, $admintheme->DisplayImage('icons/system/true.gif', $this->Lang('revert'), '', '', 'systemicon'), array('approve' => 0, 'item_id' => $row['item_id']));
    } else {
        $onerow->approve = $this->CreateLink($id, 'admin_approveitem', $returnid, $admintheme->DisplayImage('icons/system/false.gif', $this->Lang('approve'), '', '', 'systemicon'), array('approve' => 1, 'item_id' => $row['item_id']));
    }

    // featured
    if ($row['featured']) {
        $onerow->featured = $this->CreateLink($id, 'admin_featureditem', $returnid, $admintheme->DisplayImage('icons/system/true.gif', $this->Lang('revert_featured'), '', '', 'systemicon'), array('approve' => 0, 'item_id' => $row['item_id']));
    } else {
        $onerow->featured = $this->CreateLink($id, 'admin_featureditem', $returnid, $admintheme->DisplayImage('icons/system/false.gif', $this->Lang('approve_featured'), '', '', 'systemicon'), array('approve' => 1, 'item_id' => $row['item_id']));
    }

    $onerow->edit_url = $this->CreateLink($id, 'admin_edititem', $returnid, $row['title'], array('item_id' => $row['item_id']), '', true);
    $onerow->editlink = $this->CreateLink($id, 'admin_edititem', $returnid, $admintheme->DisplayImage('icons/system/edit.gif', $this->Lang('edit'), '', '', 'systemicon'), array('item_id' => $row['item_id']));
    $onerow->deletelink = $this->CreateLink($id, 'admin_deleteitem', $returnid, $admintheme->DisplayImage('icons/system/delete.gif', $this->Lang('delete'), '', '', 'systemicon'), array('item_id' => $row['item_id']), $this->Lang('areyousure'));
    $onerow->file_location = generator_tools::file_location($this, $row);
    $onerow->filepath_location = generator_tools::filepath_location($this, $row);

    $entryarray[] = $onerow;
}

$this->smarty->assign_by_ref('items', $entryarray);
$this->smarty->assign('itemcount', count($entryarray));

$smarty->assign('date_from', $date_from);
$smarty->assign('date_to', $date_to);


$this->smarty->assign('addlink', $this->CreateLink($id, 'admin_edititem', $returnid, $admintheme->DisplayImage('icons/system/newobject.gif', $this->Lang('add') . ' ' . $this->GetPreference('item_singular', ''), '', '', 'systemicon') . ' ' . $this->Lang('add') . ' ' . $this->GetPreference('item_singular', '')));
$this->smarty->assign('idtext', $this->Lang('id'));


$smarty->assign('formstart2', $this->CreateFormStart($id, 'admin_bulkaction', $returnid));
$smarty->assign('formend2', $this->CreateFormEnd());
$bulkactions = array();
$bulkactions['delete'] = $this->Lang('delete');
$bulkactions['setinactive'] = $this->Lang('setdraft');
$bulkactions['setactive'] = $this->Lang('setpublished');

if ($this->GetPreference('copy_admin')) {
    $bulkactions['duplicate'] = lang('copy');
}

if ($this->GetPreference('item_category_edit')) {
    $bulkactions['category'] = $this->Lang('setcategory');
}

$smarty->assign('bulkactions', $bulkactions);

// get the custom fields
$filters = new generator_filters($id, $this, 'admin');
$custom_flds = $filters->process_custom_fields(array(), $custom_flds);
$custom_flds_obj = $filters->generate(null, $params, $custom_fields_values);
if (isset($custom_flds_obj) && count($custom_flds_obj) > 0) {
    $smarty->assign('custom_fielddef', $custom_flds_obj);
}

echo $this->ModProcessTemplate('itemtab.tpl');
?>
