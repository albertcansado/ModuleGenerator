<?php

echo '<div class="pageoverflow">' .
 $this->CreateImageLink($id, 'defaultadmin', $returnid, $this->Lang('back'), 'icons/system/back.gif', array(), '', '', false) . '</div><br/>';

echo $this->StartTabHeaders();
if ($this->CheckPermission($this->_GetModuleAlias() . '_modify_option')) {
    echo $this->SetTabHeader('items', $this->Lang('items'));
    echo $this->SetTabHeader('categories', $this->Lang('categories'));
    echo $this->SetTabHeader('galleries', $this->Lang('galleries'));
}
echo $this->EndTabHeaders();
echo $this->StartTabContent();
if ($this->CheckPermission($this->_GetModuleAlias() . '_modify_option')) {
    echo $this->StartTab('items', $params);
    include GENERATOR_MODLIB_PATH . '/function.admin_fielddeftab.php';
    echo $this->EndTab();
    echo $this->StartTab('categories', $params);
    include GENERATOR_MODLIB_PATH . '/function.admin_fielddeftab_categories.php';
    echo $this->EndTab();
    echo $this->StartTab('galleries', $params);
    include GENERATOR_MODLIB_PATH . '/function.admin_fielddeftab_galleries.php';
    echo $this->EndTab();
}

echo $this->EndTabContent();

#
# EOF
#
?>