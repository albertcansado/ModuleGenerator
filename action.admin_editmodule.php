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

$name = '';
$attach = '';

if (isset($params["name"])) {
    $name = $params["name"];
}

$db = cmsms()->GetDb();
$config = cmsms()->GetConfig();


if (!$this->CheckAccess()) {
    echo $this->ShowErrors($this->Lang('accessdenied'));
    return;
}



if (empty($name)) {
    $this->RedirectToTab($id, "generator");
}

$module = generator_opts::get_module($name);
if (!$module) {
    $this->RedirectToTab($id, "generator");
}

if (isset($params["submit"])) {

    if (isset($params["attach"]) && is_array($params["attach"])) {
        $attach = implode(',', $params["attach"]);
    }

    $update = generator_opts::updateattach($name, $attach);

    if ($update) {
        $this->SetMessage($this->Lang('info_success'));
    } else {
        $this->SetError($this->Lang('info_error'));
    }
    $this->RedirectToTab($id, "generator");
}




$this->smarty->assign('startform', $this->CreateFormStart($id, 'admin_editmodule', $returnid, 'post', 1, '', '',array('name' => $module["module_name"])));
$this->smarty->assign('endform', $this->CreateFormEnd());


$this->smarty->assign('modulename', $module["module_name"]);

$this->smarty->assign('attach', $this->CreateInputSelectList($id, 'attach[]', generator_opts::get_modules_list(), (empty($module["attach"]) ? array() : explode(',', $module["attach"]))));
$this->smarty->assign('submit', $this->CreateInputSubmit($id, 'submit', lang('submit')));
$this->smarty->assign('cancel', $this->CreateInputSubmit($id, 'cancel', lang('cancel')));

echo $this->ProcessTemplate('editmodule.tpl');
?>

