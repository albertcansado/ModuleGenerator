<?php

if (!$this->CheckPermission($this->_GetModuleAlias() . '_modify_item'))
    return;


$smarty = cmsms()->GetSmarty();
$db = cmsms()->GetDb();

$errors = array();
$item_id = null;
$title = '';
$alias = '';
$url = '';
$active = 1;
$featured = 0;
$usedcategory = '';
$date = time();
$date_end = null;
$recur_weekdays = array();
$recur_period = null;
$recursive = '';
$inline = '';
$detailpage = '';
$returnid = '';
$fields_viewable = array();
$field_names = array();
$field_modules = array();
$field_images = array();
$custom_fields = '';


if (empty($tmp))
    $tmp = $this->GetPreference('custom_fields_gallery_default');
if (!empty($tmp))
    $custom_fields_gallery = explode(',', $tmp);

if (isset($params['cancel'])) {
    $params = array('active_tab' => 'itemtab');
    $this->Redirect($id, 'defaultadmin', $returnid, $params);
}


// fields view
$all_fields = generator_tools::get_fields($this, false, 'galleries');
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
    if (count($fields_viewable) && isset($custom_fields_gallery) &&  is_array($custom_fields_gallery)) {
        // now trim down the custom fields
        // to make sure that something hasn't been deleted.
        $tmp = array();
        foreach ($custom_fields_gallery as $fid) {
            if (in_array($fid, array_keys($fields_viewable))) {
                $tmp[] = $fid;
            }
        }
        $custom_fields_gallery = $tmp;
    } else {
        $custom_fields_gallery = array();
    }
}
$all_fields = cge_array::to_hash($all_fields, 'name');
if (count($fields_viewable)) {
    $smarty->assign('fields_viewable', $fields_viewable);
    $smarty->assign('field_names', $field_names);
    $smarty->assign('field_modules', $field_modules);
    $smarty->assign('field_images', $field_images);
}

$smarty->assign('custom_fields_gallery', $custom_fields_gallery);

// if $params['item_id'] is supplied and exists, populate with values from database
if (isset($params['item_id'])) {
    $item_id = (int) $params['item_id'];
    $query = 'SELECT * FROM ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_item WHERE item_id = ?';
    $result = $db->Execute($query, array($item_id));
    if (!$result)
        die('FATAL SQL ERROR: ' . $db->ErrorMsg() . '<br/>QUERY: ' . $db->sql);

    if ($result->RecordCount() == 0) {
        $errors[] = $this->Lang('nosuchid', array($item_id));
        $item_id = null;
    } else {
        $row = $result->FetchRow();
        $title = $row['title'];
        $alias = $row['alias'];
        $date = $db->UnixTimeStamp($row['item_date']);
        $date_end = $db->UnixTimeStamp($row['item_date_end']);
        $url = $row['url'];
        $recursive = $row['recursive'];
        $active = $row['active'];
        $featured = $row['featured'];
        $usedcategory = $row['category_id'];

        //extra options load
        $recur_weekdays = $recursive_option = $db->GetCol('SELECT value FROM ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_item_extra WHERE item_id = ?', array($item_id));
    }
}

// get category list
$where = array();
$paramsarray = array();
$categorylongnamelist = generator_tools::get_private_categories($this);
if (empty($categorylongnamelist) == false) {

    $wherestatement = array();
    foreach ($categorylongnamelist as $lonname) {
        $wherestatement[] = 'long_name LIKE  ?';
        $paramsarray[] = $lonname . '%';
    }
    $where[] = '(' . implode(' OR ', $wherestatement) . ')';
}
$categorylist = generator_tools::get_category_list($this, $where, $paramsarray, false);
$categorylist = array_slice($categorylist, 1);
$categorylist = extended_tools_opts::to_hash($categorylist, 'long_name', 'category_id');

// get the custom fields
$filters = new generator_filters($id, $this, 'admin');
$custom_flds = $filters->process_custom_fields();

/* ------------------------------------------------------------------------ */
// handle submit or apply
if (isset($params['preview'])) {
    // save data for preview.
    unset($params['apply']);
    unset($params['preview']);
    unset($params['submit']);
    unset($params['cancel']);
    unset($params['ajax']);
    unset($params['copy']);

    $tmpfname = tempnam(TMP_CACHE_LOCATION, $this->GetName() . '_preview');
    file_put_contents($tmpfname, serialize($params));

    $detail_returnid = $this->GetPreference('item_detail_returnid', -1);
    if ($detail_returnid <= 0) {
        // now get the default content id.
        $detail_returnid = ContentOperations::get_instance()->GetDefaultContent();
    }
    if (isset($params['previewpage']) && (int) $params['previewpage'] > 0) {
        $detail_returnid = (int) $params['previewpage'];
    }

    $_SESSION['item_preview'] = array('fname' => basename($tmpfname), 'checksum' => md5_file($tmpfname));
    $tparms = array('preview' => md5(serialize($_SESSION['item_preview'])));
    if (isset($params['detailtemplate'])) {
        $tparms['detailtemplate'] = trim($params['detailtemplate']);
    }
    $url = $this->create_url('_preview_', 'detail', $detail_returnid, $tparms, TRUE);

    $response = '<?xml version="1.0"?>';
    $response .= '<EditArticle>';
    if (isset($error) && $error != '') {
        $response .= '<Response>Error</Response>';
        $response .= '<Details><![CDATA[' . $error . ']]></Details>';
    } else {
        $response .= '<Response>Success</Response>';
        $response .= '<Details><![CDATA[' . $url . ']]></Details>';
    }
    $response .= '</EditArticle>';

    $handlers = ob_list_handlers();
    for ($cnt = 0; $cnt < sizeof($handlers); $cnt++) {
        ob_end_clean();
    }
    header('Content-Type: text/xml');
    echo $response;
    exit;
} else if (isset($params['submit']) || isset($params['apply']) || isset($params['copy'])) {

    if (isset($params["copy"])) {
        $item_id = null;
    }

    $title = $params['title'];
    if (isset($params['date_Month'])) {
        $date = mktime($params['date_Hour'], $params['date_Minute'], $params['date_Second'], $params['date_Month'], $params['date_Day'], $params['date_Year']);
    }
    if (isset($params['date_end_Month'])) {
        $date_end = mktime($params['date_end_Hour'], $params['date_end_Minute'], $params['date_end_Second'], $params['date_end_Month'], $params['date_end_Day'], $params['date_end_Year']);
    }


    if (isset($params["recur_period"])) {
        $recursive = $params["recur_period"];
    }


    /* alias create */
    if (!$item_id || ($item_id && !isset($params["alias"])))
        $alias = munge_string_to_url($params['title'], true);
    elseif (!isset($params["alias"]) && $item_id)
        $alias = munge_string_to_url($params['title'], true);
    elseif (isset($params["alias"]))
        $alias = munge_string_to_url($params['alias'], true);
    else
        $alias = munge_string_to_url($params['title'], true);

    if (isset($params["url"]))
        $url = $params["url"];


    $active = (isset($params['active']) ? 1 : 0);
    $featured = (isset($params['featured']) ? 1 : 0);
    $usedcategory = (isset($params['category']) ? $params['category'] : '');

    // check title
    if ($title == '' && $this->GetPreference('item_title_edit')) {
        $errors[] = $this->Lang('item_title_empty');

        // check custom fields... these look like m1_customfield[5]
    } elseif (isset($params['customfield'])) {
        foreach ($params['customfield'] as $fldid => $value) {

            if ($value == 'select_default') {
                $params['customfield'][$fldid] = '';
                $value = '';
            }

            // required fields
            if ($value == '' && $custom_flds[$fldid]['required']) {
                if ($custom_flds[$fldid]['editview'] && !isset($params["item_id"]))
                    continue;
                $errors[] = $this->Lang('required_field_empty') . ' (' . $custom_flds[$fldid]['name'] . ')';
                break; // only display one error message at a time
            }

            // max length
            if (isset($custom_flds[$fldid]['max_length']) && mb_strlen($value) > $custom_flds[$fldid]['max_length']) {
                $errors[] = $this->Lang('too_long') . ' (' . $custom_flds[$fldid]['name'] . ')';
                break;
            }

            // options
            if ($value != '' && isset($custom_flds[$fldid]['options'])) {
                $validOpt = true;
                
                // Multiple or not...
                if (isset($custom_flds[$fldid]['multiple'])) {
                    $validOpt = !array_diff_key(array_flip($value), $custom_flds[$fldid]['options']);
                } else {
                    $validOpt = array_key_exists($value, $custom_flds[$fldid]['options']);
                }

                if (!$validOpt) {
                    $errors[] = $this->Lang('invalid') . ' (' . $custom_flds[$fldid]['name'] . ')';
                    break;
                }
            }
        }
    }

    if (empty($errors) && $url != '') {
        // check for starting or ending slashes
        if (startswith($url, '/') || endswith($url, '/')) {
            $errors[] = $this->Lang('error_invalidurl');
        }

        $url = munge_string_to_url($url, false, true);
        /* if (empty($errors)) {
          // check for invalid chars.
          $translated = munge_string_to_url($url, false, true);
          if (strtolower($translated) != strtolower($url)) {
          $errors[] = $this->Lang('error_invalidurl');
          }
          } */
        if (empty($errors)) {
            // make sure this url isn't taken.
            $url = trim($url, " /\t\r\n\0\x08");
            cms_route_manager::load_routes();
            $route = cms_route_manager::find_match($url);
            if ($route) {
                if (isset($item_id)) {
                    $dflts = $route->get_defaults();
                    if ($route->is_content() ||
                            $route->get_dest() != $this->GetName() ||
                            !isset($dflts['item_id']) ||
                            $dflts['item_id'] != $item_id) {
                        // we're adding an article, not editing... any matching route is bad.
                        $errors[] = $this->Lang('error_invalidurl');
                    } else {
                        //$errors[] = $this->Lang('error_invalidurl');
                    }
                }
            }
        }
    }

    // title and required fields have values, let's continue
    if (empty($errors)) {
        // k or update table items
        if (!isset($item_id)) {
            // find position before inserting new item
            $position = $db->GetOne('SELECT max(position) + 1 FROM ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_item');

            if ($position == null) {
                $position = 1;
            }

            // insert item
            $query = 'INSERT INTO ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_item (title, alias, url, recursive, category_id, position, active, featured, item_date, item_date_end, create_date, modified_date ) VALUES (?, ?, ?, ?, ?, ?, ? , ?, ?, ?,  NOW(), NOW())';
            $result = $db->Execute($query, array($title, $alias, $url, $recursive, $usedcategory, $position, $active, $featured, trim($db->DBTimeStamp($date), "'"), trim($db->DBTimeStamp($date_end), "'")));
            if (!$result)
                die('FATAL SQL ERROR: ' . $db->ErrorMsg() . '<br/>QUERY: ' . $db->sql);

            // populate $item_id for newly inserted item
            $item_id = $db->Insert_ID();
            $update = false;
        } else {
            // update item
            $query = 'UPDATE ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_item SET title = ?, alias = ?, url = ?, recursive = ?, category_id = ?, active = ?, featured = ?, item_date= ?, item_date_end= ? WHERE item_id = ?';
            $result = $db->Execute($query, array($title, $alias, $url, $recursive, $usedcategory, $active, $featured, trim($db->DBTimeStamp($date), "'"), trim($db->DBTimeStamp($date_end), "'"), $item_id));
            if (!$result)
                die('FATAL SQL ERROR: ' . $db->ErrorMsg() . '<br/>QUERY: ' . $db->sql);

            $update = true;
        }

        // create recursive settings
        if ($this->GetPreference('recursive') && $recursive == 'daily') {
            if (isset($params["recur_weekdays"]) && is_array($params["recur_weekdays"])) {
                $recur_weekdays = $params["recur_weekdays"];
                $query = 'DELETE FROM ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_item_extra WHERE item_id = ?';
                $db->Execute($query, array($item_id));
                foreach ($recur_weekdays as $day) {
                    $query = 'INSERT INTO ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_item_extra (item_id,value) VALUES (?,?)';
                    $db->Execute($query, array($item_id, $day));
                }
            }
        }

        // handle uploading of file fields
        if (empty($custom_flds) == false) {
            foreach ($custom_flds as $custom_fld) {
                if ($custom_fld['type'] != 'upload_file') {
                    continue;
                }


                $elem = $id . 'customfield_' . $custom_fld['fielddef_id'];
                // $elem looks like m1_customfield_1
                // check if file was uploaded for this field
                if (isset($_FILES[$elem]) && $_FILES[$elem]['name'] != '') {

                    if ($_FILES[$elem]['error'] != 0 || $_FILES[$elem]['tmp_name'] == '') {
                        $errors[] = $this->Lang('error_upload');
                    } else {
                        $value = generator_tools::handle_upload($this, $item_id, $elem, $error, $custom_fld['allow']);

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

                if (!$result)
                    die('FATAL SQL ERROR: ' . $db->ErrorMsg() . '<br/>QUERY: ' . $db->sql);
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
                    $dir = generator_tools::filepath_location($this, $item_id);
                    $file = cms_join_path($dir, $row['value']);
                    if (is_dir($dir) && is_file($file)) {
                        unlink($file);
                    }
                }
                
                $query = 'DELETE FROM ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval WHERE item_id = ? AND fielddef_id = ?';
                $db->Execute($query, array($item_id, $k));
            }
        }

        // additional check is needed since file upload could have produced errors
        if (empty($errors)) {
            // show saved message

            if ($update) {
                @$this->SendEvent('ItemEdited', array('item_id' => $item_id, 'category_id' => $usedcategory, 'title' => $title, 'active' => $active, 'featured' => $featured, 'item_date' => trim($db->DBTimeStamp($date), "'"), 'item_date_end' => trim($db->DBTimeStamp($date_end), "'"), 'recursive' => $recursive));
            } else {
                @$this->SendEvent('ItemAdded', array('item_id' => $item_id, 'category_id' => $usedcategory, 'title' => $title, 'active' => $active, 'featured' => $featured, 'item_date' => trim($db->DBTimeStamp($date), "'"), 'item_date_end' => trim($db->DBTimeStamp($date_end), "'"), 'recursive' => $recursive));
            }

            // add static url
            generator_opts::register_static_route($this, $url, $item_id);

            if (!isset($params['apply'])) {
                $this->Redirect($id, 'defaultadmin', $returnid, array('active_tab' => 'itemtab', 'message' => 'changessaved'));
            } else {
                echo $this->ShowMessage($this->Lang('changessaved'));
            }

            $thisule = cms_utils::get_search_module();
            if (is_object($thisule)) {
                if ($active != 1) {
                    $thisule->DeleteWords($this->GetName(), $item_id, 'item');
                } else {
                    $thisule->AddWords($this->GetName(), $item_id, 'item', generator_tools::get_searchable_text($this, $item_id), generator_tools::get_searchable_date_end());
                }
            }
        }
    }
} // end submit or apply


/* ------------------------------------------------------------------------ */

// display errors if there are any
if (!empty($errors)) {
    echo $this->ShowErrors($errors);
}

// hidden value required when editing an existing item
if (isset($item_id)) {
    $smarty->assign('item_id', $this->CreateInputHidden($id, 'item_id', $item_id));
    $smarty->assign('itemid', $item_id);
}

$smarty->assign('title', (isset($item_id) ? $this->Lang('edit') : $this->Lang('add')) . ' ' . $this->GetPreference('item_singular', ''));
$smarty->assign('formid', $id);
$smarty->assign('startform', $this->CreateFormStart($id, 'admin_edititem', $returnid, 'post', 'multipart/form-data'));
$smarty->assign('endform', $this->CreateFormEnd());

$smarty->assign('prompt_title', $this->GetPreference('item_title', ''));
$smarty->assign('input_title', $this->CreateInputText($id, 'title', $title, 50));

$smarty->assign('prompt_alias', $this->Lang('alias'));
$smarty->assign('input_alias', $this->CreateInputText($id, 'alias', $alias, 50));

$smarty->assign('prompt_url', $this->Lang('url'));
$smarty->assign('input_url', $this->CreateInputText($id, 'url', $url, 50));

$smarty->assign('prompt_category', $this->Lang('category'));
$smarty->assign('input_category', $this->CreateInputDropdown($id, 'category', $categorylist, -1, $usedcategory));

$smarty->assign('prompt_active', $this->Lang('active'));
$smarty->assign('input_active', $this->CreateInputcheckbox($id, 'active', 1, $active));

$smarty->assign('prompt_featured', $this->Lang('featured'));
$smarty->assign('input_featured', $this->CreateInputcheckbox($id, 'featured', 1, $featured));

$smarty->assign('prompt_date', $this->Lang('date'));
$smarty->assign('date', $date);
$smarty->assign('dateprefix', $id . 'date_');

$smarty->assign('prompt_date_end', $this->Lang('prompt_item_date_end_edit'));
$smarty->assign('dateend', $date_end);
$smarty->assign('dateendprefix', $id . 'date_end_');

$smarty->assign('recursive', $recursive);
$smarty->assign('recur_options', array(
    $this->Lang('none') => '',
    $this->Lang('daily') => 'daily'));

$weekdays = array($this->Lang('sunday') => '0',
    $this->Lang('monday') => '1',
    $this->Lang('tuesday') => '2',
    $this->Lang('wednesday') => '3',
    $this->Lang('thursday') => '4',
    $this->Lang('friday') => '5',
    $this->Lang('saturday') => '6');

$smarty->assign('input_weekdays', $this->CreateInputSelectList($id, 'recur_weekdays[]', $weekdays, empty($recur_weekdays) ? array() : $recur_weekdays, 7));



$smarty->assign('submit', $this->CreateInputSubmit($id, 'submit', lang('submit')));
$smarty->assign('apply', $this->CreateInputSubmit($id, 'apply', lang('apply')));
$smarty->assign('cancel', $this->CreateInputSubmit($id, 'cancel', lang('cancel')));
if ($this->GetPreference('copy_admin'))
    $smarty->assign('copy', $this->CreateInputSubmit($id, 'copy', lang('copy'), '', '', $this->Lang('areyousure')));



$custom_flds_obj = $filters->generate($item_id, $params);

if (isset($custom_flds_obj) && count($custom_flds_obj) > 0) {
    $smarty->assign('custom_fielddef', $custom_flds_obj);
}

$smarty->assign('mod', $this);


$smarty->assign('tabheader_article', $this->SetTabHeader('article', $this->Lang('article')));
$smarty->assign('start_tab_headers', $this->StartTabHeaders());
$smarty->assign('end_tab_headers', $this->EndTabHeaders());

$smarty->assign('end_tab_content', $this->EndTabContent());
$smarty->assign('start_tab_content', $this->StartTabContent());
$smarty->assign('start_tab_article', $this->StartTab('article', $params));
$smarty->assign('end_tab_article', $this->EndTab());

// tab stuff.
if ($this->GetPreference('preview_admin')) {
    $smarty->assign('tabheader_preview', $this->SetTabHeader('preview', $this->Lang('preview')));

    $smarty->assign('start_tab_preview', $this->StartTab('preview', $params));
    $smarty->assign('end_tab_preview', $this->EndTab());

    $smarty->assign('warning_preview', $this->Lang('warning_preview'));
    $contentops = cmsms()->GetContentOperations();
    $smarty->assign('preview_returnid', $contentops->CreateHierarchyDropdown('', $this->GetPreference('item_detail_returnid', -1), 'preview_returnid'));
    {
        $tmp = $this->ListTemplates();
        $tmp2 = array();
        for ($i = 0; $i < count($tmp); $i++) {
            if (startswith($tmp[$i], 'detail_template')) {
                $x = substr($tmp[$i], 15);
                $tmp2[$x] = $x;
            }
        }
        $smarty->assign('prompt_detail_template', $this->Lang('detail_template'));
        $smarty->assign('prompt_detail_page', $this->Lang('detail_page'));
        $smarty->assign('detail_templates', $tmp2);
        $smarty->assign('cur_detail_template', $this->GetPreference('current_detail_template'));
    }
}

$detail_returnid = $default_detailpage = $this->GetPreference('item_detail_returnid', -1);
$detailtemplate = '';
$admintheme = $themeObject = cmsms()->get_variable('admintheme');

if (isset($row)) {
    if (empty($row["url"]) == false) {
        $prettyurl = $row["url"];
    } else {
        $prettyurl = generator_tools::get_pretty_url($this, generator_tools::get_prefix($this), $row['item_id'], $row['alias'], ($default_detailpage == -1 && !isset($params["detailpage"]) ? $detailpage : (isset($params["detailpage"]) ? $detailpage : '' )), $detailtemplate);
    }
    $preview_link = $this->CreateFrontendLink($id, $detail_returnid, 'detail', $themeObject->DisplayImage('icons/system/view.gif'), array('item_id' => $row['item_id'], 'detailtemplate' => $detailtemplate), '', false, $inline, 'target="_blank"', false, $prettyurl);
    $smarty->assign('preview_view', $preview_link);

    if ($this->GetPreference('has_gallery')) {

        $p = generator_tools::imagepath_location($this, $row);
        //cge_dir::mkdirr($p);
        $smarty->assign('gallery_path', $p);
        $smarty->assign('redirect_url', $this->CreateLink($id, 'admin_edititem', $returnid, '', array('cg_activetab' => 'gallery', 'item_id' => $item_id), '', true));

        $item_gallery = new generator_item_gallery($this, $item_id);
        $item_gallery->clean_up();
        $files = $item_gallery->load_files();
        if (empty($files) == false) {
            foreach ($files as $key => $file) {

                $files[$key]["image_location"] = generator_tools::image_location($this, $row);
                $files[$key]["imagepath_location"] = generator_tools::imagepath_location($this, $row);
                $files[$key]["deletelink"] = $this->CreateLink($id, 'admin_deleteimage', $returnid, $admintheme->DisplayImage('icons/system/delete.gif', $this->Lang('delete'), '', '', 'systemicon'), array('item_id' => $row['item_id'], 'image_id' => $file['image_id']), $this->Lang('areyousure'));
                $files[$key]["editlink"] = $this->CreateLink($id, 'admin_editimage', $returnid, $admintheme->DisplayImage('icons/system/edit.gif', $this->Lang('edit'), '', '', 'systemicon'), array('item_id' => $row['item_id'], 'image_id' => $file['image_id']));
            }
        }
        $smarty->assign('gallery', $files);
    }
}

# Dropdown Add link
$smarty->assign('dropdownLink', html_entity_decode($this->create_url($id,'dropdown')));

echo $this->ModProcessTemplate('edititem.tpl');
?>
