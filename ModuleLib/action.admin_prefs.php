<?php

echo '<div class="pageoverflow">' .
 $this->CreateImageLink($id, 'defaultadmin', $returnid, $this->Lang('back'), 'icons/system/back.gif', array(), '', '', false) . '</div><br/>';

echo $this->StartTabHeaders();

if ($this->CheckPermission($this->_GetModuleAlias() . '_modify_option')) {
    echo $this->SetTabHeader('mode', $this->Lang('mode'));
    echo $this->SetTabHeader('optiontab', $this->Lang('general'));
    echo $this->SetTabHeader('optioneditingtab', $this->Lang('editing'));
    echo $this->SetTabHeader('optionsearchtab', $this->Lang('search'));
}

echo $this->EndTabHeaders();
echo $this->StartTabContent();

if ($this->CheckPermission($this->_GetModuleAlias() . '_modify_option')) {

    echo $this->StartTab('mode', $params);
    include GENERATOR_MODLIB_PATH . '/function.admin_modetab.php';
    echo $this->EndTab();
    echo $this->StartTab('optiontab', $params);
    include GENERATOR_MODLIB_PATH . '/function.admin_optiontab.php';
    echo $this->EndTab();
    echo $this->StartTab('optioneditingtab', $params);
    include GENERATOR_MODLIB_PATH . '/function.admin_optioneditingtab.php';
    echo $this->EndTab();
    echo $this->StartTab('optionsearchtab', $params);
    include GENERATOR_MODLIB_PATH . '/function.admin_optionsearchtab.php';
    echo $this->EndTab();
}

echo $this->EndTabContent();
?>