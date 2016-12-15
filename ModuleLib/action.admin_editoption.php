<?php

if (!$this->CheckPermission($this->_GetModuleAlias() . '_modify_option'))
    return;

if (!isset($params["tab"])) {
    $this->SetError($this->Lang('empty_param'));
    $this->RedirectToTab($id, '', array(), 'admin_prefs');
}

if ($params["tab"] == "mode") {
    $prefs = array();

    $prefs[] = "mode";

    if ($params['mode'] === 'advanced') {
        $prefs[] = "sortorder_advanced";
        $prefs[] = "sortby_advanced";
        $prefs[] = "summary_pagelimit_advanced";
    } else if ($params['mode'] === 'simple') {
        $prefs[] = "sortorder_simple";
        $prefs[] = "sortby_simple";
        $prefs[] = "summary_pagelimit_simple";
    }

    extended_tools_opts::set_preferences($prefs, $params, $this);
} else if ($params["tab"] == "optioneditingtab") {

    $prefs = array();
    $prefs[] = "has_gallery";
    $prefs[] = "gallery_sortorder";
    $prefs[] = "preview_admin";
    $prefs[] = "copy_admin";
    $prefs[] = "item_title_edit";
    $prefs[] = "item_date_edit";
    $prefs[] = "item_date_end_edit";
    $prefs[] = "item_category_edit";
    $prefs[] = "recursive";
    $prefs[] = "item_alias_edit";
    $prefs[] = "item_url_edit";
    #Featured
    $prefs[] = "item_featured_edit";
    $prefs[] = "gallery_in_defaulttemplate";
    $prefs[] = "gallery_defaulttemplate_limit";

    $this->RemovePreference('custom_fields_gallery_default');
    if (isset($params["custom_fields_gallery_default"]))
        $this->SetPreference('custom_fields_gallery_default', implode(',', $params["custom_fields_gallery_default"]));

    extended_tools_opts::set_preferences($prefs, $params, $this);
} else if ($params["tab"] == "optionsearchtab") {

    $prefs = array();
    $prefs[] = "search_date_end";
    $prefs[] = "searchable";
    extended_tools_opts::set_preferences($prefs, $params, $this);

    // set admin filters
    if (isset($params['search_custom_fields'])) {
        $query = 'UPDATE ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fielddef SET searchable=? WHERE fielddef_id = ?';
        foreach ($params['search_custom_fields'] as $fielddef_id => $custom_field) {
            $db->Execute($query, array($custom_field, $fielddef_id));
        }
    }

} else if ($params["tab"] == "optiontab") {

    $this->RemovePreference('custom_fields_default');
    if (isset($params["custom_fields_default"]))
        $this->SetPreference('custom_fields_default', implode(',', $params["custom_fields_default"]));

    $prefs = array();
    $prefs[] = "friendlyname";
    $prefs[] = "item_title";
    $prefs[] = "item_singular";
    $prefs[] = "item_plural";
    $prefs[] = "url_prefix";
    $prefs[] = "display_inline";
    $prefs[] = "usergroup_dependence";
    $prefs[] = "item_detail_returnid";
    $prefs[] = "item_category_returnid";
    $prefs[] = "images_size_admin";
    $prefs[] = "has_admin";
    $prefs[] = "admin_section";
    $prefs[] = "filter";
    $prefs[] = "filter_categories";
    $prefs[] = "filter_date";

    // set admin filters
    if (isset($params['filter_custom_fields'])) {
        $query = 'UPDATE ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fielddef SET filter_admin=? WHERE fielddef_id = ?';
        foreach ($params['filter_custom_fields'] as $fielddef_id => $custom_field) {
            $db->Execute($query, array($custom_field, $fielddef_id));
        }
    }

    // Reset preferences when filter is disabled
    if (!(bool)$params['filter']) {
        $preferencesToDelete = [
            $this->_GetModuleAlias() . '_children',
            $this->_GetModuleAlias() . '_pagelimit',
            $this->_GetModuleAlias() . '_sortby',
            $this->_GetModuleAlias() . '_sortorder',
            $this->_GetModuleAlias() . '_hierarchy',
        ];
        $query = 'DELETE FROM ' . cms_db_prefix() . 'userprefs WHERE preference IN (' . implode(', ', $preferencesToDelete) . ')';
        $params['filter_categories'] = 0;
        $params['filter_date'] = 0;
    }

    extended_tools_opts::set_preferences($prefs, $params, $this);
}

$this->RedirectToTab($id, $params["tab"], array(), 'admin_prefs');
?>
