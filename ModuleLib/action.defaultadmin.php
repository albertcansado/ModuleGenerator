<?php

if (!$this->CheckPermission($this->_GetModuleAlias() . '_modify_item'))
    return;
// top nav
echo '<div class="pageoverflow" style="text-align: right; float:right; width: 40%;">';
if ($this->CheckPermission('Modify Templates')) {

    echo $this->CreateImageLink($id, 'admin_fields', $returnid, $this->Lang('fielddefs'), 'icons/topfiles/siteadmin.gif', array(), '', '', false);
    echo $this->CreateImageLink($id, 'admin_templates', $returnid, $this->Lang('templates'), 'icons/topfiles/template.gif', array(), '', '', false);
}
if ($this->CheckPermission($this->_GetModuleAlias() . '_modify_option')) {
    echo $this->CreateImageLink($id, 'admin_prefs', $returnid, $this->Lang('options'), 'icons/topfiles/preferences.gif', array(), '', '', false);
}
echo '</div>';

$thisule = generator_opts::get_module($this->GetName());
$attach = generator_opts::get_attach_modules($thisule["attach"]);
if ($attach) {
    $attachmodules = explode(',', $attach);
    echo '<div class="pageoverflow" style="text-align: right; float:left; width: 50%;">';
    foreach ($attachmodules as $attachmodule) {
        $thisule = cms_utils::get_module(trim($attachmodule));
        if ($thisule) {
            echo $thisule->CreateImageLink($id, 'defaultadmin', $returnid, $thisule->GetPreference('friendlyname'), 'icons/system/expand.gif', array(), '', '', false);
        }
    }
    echo '</div>';
}


echo '<br style="clear:both" />';

if (isset($params['message'])) {
    echo $this->ShowMessage($this->Lang($params['message']));
}

if (isset($params['errors']) && count($params['errors'])) {
    echo $this->ShowErrors($params['errors']);
}


echo $this->StartTabHeaders();
echo $this->SetTabHeader('itemtab', $this->GetPreference('item_plural', ''));

if ($this->CheckPermission($this->_GetModuleAlias() . '_modify_categories')) {
    echo $this->SetTabHeader('categorytab', $this->Lang('categories'));
}

echo $this->EndTabHeaders();
echo $this->StartTabContent();

echo $this->StartTab('itemtab', $params);
include GENERATOR_MODLIB_PATH . '/function.admin_itemtab.php';
echo $this->EndTab();
if ($this->CheckPermission($this->_GetModuleAlias() . '_modify_categories')) {
    echo $this->StartTab('categorytab', $params);
    include GENERATOR_MODLIB_PATH . '/function.admin_categorytab.php';
    echo $this->EndTab();
}

echo $this->EndTabContent();
?>