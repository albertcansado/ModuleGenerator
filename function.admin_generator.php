<?php

# Module: Multilanguage CMS
# Zdeno Kuzmany (zdeno@kuzmany.biz) kuzmany.biz
#
#-------------------------------------------------------------------------
# CMS - CMS Made Simple is (c) 2009 by Ted Kulp (wishy@cmsmadesimple.org)
# This project's homepage is: http://www.cmsmadesimple.org
# The module's homepage is: http://dev.cmsmadesimple.org/projects/skeleton/
#
#-------------------------------------------------------------------------
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
# Or read it online: http://www.gnu.org/licenses/licenses.html#GPL
#
#-------------------------------------------------------------------------

if (!isset($gCms))
    exit;

$admintheme = cmsms()->get_variable('admintheme');

$entryarray = array();
$modules = generator_opts::get_modules();

$urlext = '?' . CMS_SECURE_PARAM_NAME . '=' . $_SESSION[CMS_USER_KEY];

foreach ($modules as $module) {
    $onerow = cge_array::to_object($module);
    $mod = cms_utils::get_module($module["module_name"]);
    if ($mod) {
        $onerow->modulelink = $mod->CreateImageLink($id, 'defaultadmin', $returnid, $mod->GetName(), 'icons/system/expand.gif', array(), '', '', false);
        $onerow->uninstalllink = '<a onclick="return confirm(\''.$this->Lang('areyousure').'\');" href="'.$config['admin_url'] . '/listmodules.php' . $urlext .'&action=uninstall&module='.$mod->GetName().'">'.$this->Lang('uninstall').'</a>';
    } else {
        $onerow->installlink = '<a onclick="return confirm(\''.$this->Lang('areyousure').'\');" href="'.$config['admin_url'] . '/listmodules.php' . $urlext .'&action=install&module='.$module["module_name"].'">'.$this->Lang('install').'</a>';
    }

    $onerow->attachmodules = generator_opts::get_attach_modules($module["attach"]);
    $onerow->editlink = $this->CreateLink($id, 'admin_editmodule', $returnid, $admintheme->DisplayImage('icons/system/edit.gif', lang('edit'), '', '', 'systemicon'), array('name' => $module['module_name']));
    $onerow->regeneratelink = $this->CreateLink($id, 'do_admin_generator', $returnid, $admintheme->DisplayImage('icons/system/export.gif', $this->Lang('regenerate'), '', '', 'systemicon'), array('name' => $module['module_name'], 'owerwrite' => 1, 'submit' => 1));
    $onerow->deletelink = $this->CreateLink($id, 'admin_deletemodule', $returnid, $admintheme->DisplayImage('icons/system/delete.gif', lang('delete'), '', '', 'systemicon'), array('name' => $module['module_name']), $this->Lang('areyousure'));
    $entryarray[] = $onerow;
}


$this->smarty->assign_by_ref('items', $entryarray);
$this->smarty->assign('itemcount', count($entryarray));


echo $this->ProcessTemplate('generator.tpl');
?>