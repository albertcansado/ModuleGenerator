<?php

$db = cmsms()->GetDb();
$dict = NewDataDictionary($db);
$taboptarray = array('mysql' => 'TYPE=MyISAM');


// create item table

$fields = '
    item_id I KEY AUTO,
	category_id I,
    title C(255),
	alias C(255),
	url C(255),
	recursive C(50),
    position I,
    active I(1),
    featured I(1),
    item_date ' . CMS_ADODB_DT . ',
    item_date_end ' . CMS_ADODB_DT . ',
    create_date ' . CMS_ADODB_DT . ',
	modified_date ' . CMS_ADODB_DT . '
';

$sqlarray = $dict->CreateTableSQL(cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_item', $fields, $taboptarray);
$dict->ExecuteSQLArray($sqlarray);

$sqlarray = $dict->CreateIndexSQL(cms_db_prefix() . $this->_GetModuleAlias() . '_url', cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_item', 'url');
$dict->ExecuteSQLArray($sqlarray);
$sqlarray = $dict->CreateIndexSQL(cms_db_prefix() . $this->_GetModuleAlias() . '_category_id', cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_item', 'category_id');
$dict->ExecuteSQLArray($sqlarray);


// create category table
$fields = "
	category_id I KEY AUTO,
	category_name C(255),
	category_alias C(255),
	parent_id I,
	hierarchy C(255),
        position I,
        hierarchy_position C(255),
	long_name X,
        usergroup I,
	create_date " . CMS_ADODB_DT . ",
	modified_date " . CMS_ADODB_DT . "
";

$sqlarray = $dict->CreateTableSQL(cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_categories', $fields, $taboptarray);
$dict->ExecuteSQLArray($sqlarray);


// create fielddef table

$fields = '
    fielddef_id I KEY AUTO,
    name C(255),
    alias C(255),
    help C(255),
    type C(50),
    section C(10),
    position I,
    required I(1),
    editview I(1),
    hidename I(1),
    filter_frontend I(1),
    filter_admin I(1),
    searchable I(1),
    extra X
';

$sqlarray = $dict->CreateTableSQL(cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fielddef', $fields, $taboptarray);
$dict->ExecuteSQLArray($sqlarray);

// create fieldval table

$fields = '
    item_id I KEY NOT null,
    fielddef_id I KEY NOT null,
    value X
';

$sqlarray = $dict->CreateTableSQL(cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval', $fields, $taboptarray);
$dict->ExecuteSQLArray($sqlarray);

// create item extra table

$fields = '
    item_id I KEY NOT null,
    value I KEY NOT null,
';

$sqlarray = $dict->CreateTableSQL(cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_item_extra', $fields, $taboptarray);
$dict->ExecuteSQLArray($sqlarray);

$fields = '
    `image_id` int(10) I KEY AUTO,
  `item_id` int(10) DEFAULT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `position` int(10) DEFAULT NULL
';
$sqlarray = $dict->CreateTableSQL(cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_images', $fields, $taboptarray);
$dict->ExecuteSQLArray($sqlarray);

// category template
$fn = cms_join_path(dirname(__FILE__), 'templates', 'orig_category_template.tpl');
if (file_exists($fn)) {
    $template = file_get_contents($fn);
    $this->SetPreference('default_category_template', $template);
    $this->SetTemplate('category_templateSample', $template);
    $this->SetPreference('current_category_template', 'Sample');
}

// summary template
$fn = cms_join_path(dirname(__FILE__), 'templates', 'orig_summary_template.tpl');
if (file_exists($fn)) {
    $template = file_get_contents($fn);
    $this->SetPreference('default_summary_template', $template);
    $this->SetTemplate('summary_templateSample', $template);
    $this->SetPreference('current_summary_template', 'Sample');
}

// detail template
$fn = cms_join_path(dirname(__FILE__), 'templates', 'orig_detail_template.tpl');
if (file_exists($fn)) {
    $template = file_get_contents($fn);
    $this->SetPreference('default_detail_template', $template);
    $this->SetTemplate('detail_templateSample', $template);
    $this->SetPreference('current_detail_template', 'Sample');
}

// filter template
$fn = cms_join_path(dirname(__FILE__), 'templates', 'orig_filter_template.tpl');
if (file_exists($fn)) {
    $template = file_get_contents($fn);
    $this->SetPreference('default_filter_template', $template);
    $this->SetTemplate('filter_templateSample', $template);
    $this->SetPreference('current_filter_template', 'Sample');
}


// set preferences
$this->SetPreference('url_prefix', munge_string_to_url($this->GetName(), true));
$this->SetPreference('friendlyname', $this->GetName());
$this->SetPreference('item_singular', $this->Lang('item'));
$this->SetPreference('item_plural', $this->Lang('items'));
$this->SetPreference('item_title', $this->Lang('item_title'));
$this->SetPreference('item_title_edit', 1);
$this->SetPreference('item_category_edit', 1);
$this->SetPreference('recursive', 0);
#Featured
$this->SetPreference('item_featured_edit', 0);

$this->SetPreference('mode', 'advanced');
$this->SetPreference('sortorder_advanced', 'desc');
$this->SetPreference('sortby_advanced', 'create_date');
$this->SetPreference('summary_pagelimit_advanced', 25);

$this->SetPreference('sortorder_simple', 'desc');
$this->SetPreference('sortby_simple', 'position');
$this->SetPreference('summary_pagelimit_simple', 10000);

$this->SetPreference('has_admin', 1);
$this->SetPreference('searchable', 1);
$this->SetPreference('images_size_admin', '200x');

$this->SetPreference('gallery_in_defaulttemplate', 0);
$this->SetPreference('gallery_defaulttemplate_limit', 1);

$this->CreateEvent('ItemAdded');
$this->CreateEvent('ItemEdited');
$this->CreateEvent('ItemDeleted');
$this->CreateEvent('CategoryAdded');
$this->CreateEvent('CategoryEdited');
$this->CreateEvent('CategoryDeleted');
$this->CreateEvent('ItemsReordered');
$this->CreateEvent('CategoriesReordered');
$this->CreateEvent('DropdownOptionAdded');

// set permissions
$this->CreatePermission($this->_GetModuleAlias() . '_modify_item', $this->Lang('modify_item'));
$this->CreatePermission($this->_GetModuleAlias() . '_modify_categories', $this->Lang('modify_categories'));
$this->CreatePermission($this->_GetModuleAlias() . '_all_category_visbile', $this->Lang('all_category_visbile'));
$this->CreatePermission($this->_GetModuleAlias() . '_modify_option', $this->Lang('modify_option'));

// Force Register Module
$this->RegisterModulePlugin(true);
?>
