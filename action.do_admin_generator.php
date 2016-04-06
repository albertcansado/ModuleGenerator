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

$db = cmsms()->GetDb();
$config = cmsms()->GetConfig();


if (!$this->CheckAccess()) {
    echo $this->ShowErrors($this->Lang('accessdenied'));
    return;
}

if (isset($params['cancel'])) {
    $this->RedirectToTab($id, "generator");
}

$name = '';
if (isset($params['name'])) {
    $name = munge_string_to_url($params['name']);
}

$attach = '';
if (isset($params['attach']) && is_array($params['attach'])) {
    $attach = implode(',', $params['attach']);
}

if (empty($name) == true) {
    $this->SetError($this->Lang('missingrequired'));
    $this->RedirectToTab($id, "generator");
}

//Validate is a valid name for Class
if (!preg_match('/^[a-z_]\w+$/i', $name)) {
    $this->SetError($this->Lang('invalidformat'));
    $this->RedirectToTab($id, "create_new");
}

//create copy dir
if (isset($params['submit'])) {
    /**
     * Usage of the copy directory class
     */
    $source_dir = cms_join_path($config["root_path"], 'modules', $this->GetName(), 'ModuleLib', 'source', 'ModuleExample');
    $dest_dir = cms_join_path($config["root_path"], 'modules', $name);

    if (!isset($params["owerwrite"]) && is_dir($dest_dir)) {
        $this->SetError($this->Lang('error_module_exists'));
        $this->RedirectToTab($id, "generator");
    }

    cge_dir::recursive_remove_directory($dest_dir);
    $cp = new copy_dir();

// set the directory to copy
    if (!$cp->setCopyFromDir($source_dir)) {
        die($cp->viewError());
    }

// set the directory to copy to

    if (!$cp->setCopyToDir($dest_dir)) {
        die($cp->viewError());
    }

    $cp->copySubFolders(true); // include sub folders when copying
    $cp->overWriteFiles(true); // overwrite existing files

    $cp->setCopyCallback('updateCopyProgress');

    if (!$cp->createCopy(true)) { // create a copy and recurse through sub folders
        die($cp->viewError());
    }

    //renmae file class
    $final_class = cms_join_path($dest_dir, $name . '.module.php');
    if (is_file($final_class))
        unlink($final_class);
    $rename = rename(cms_join_path($dest_dir, 'ModuleExample.module.php'), $final_class);

    if (!$rename) {
        die($this->Lang('error_rename_dir'));
    }

    //  rewrite module content
    $data = @file_get_contents($final_class);
    if ($data) {
        $data = str_replace('ModuleExample', $name, $data);
        file_put_contents($final_class, $data);
    } else {
        die($this->Lang('error_replace_string'));
    }

    // create dir
    $uploadDir = cms_join_path($config["uploads_path"], '_' . strtolower($name));
    cge_dir::mkdirr($uploadDir);
    file_put_contents($uploadDir . '/.htaccess', "#Disable listing directory\nOptions All -Indexes");

    if (!isset($params["owerwrite"]))
        generator_opts::insert($name, $attach);
    else
        generator_opts::updatedate($name);

    //@$this->SendEvent('LangEdited', array('compid' => $cid));
    $this->SetMessage($this->Lang('info_success'));
    //redirect
    $this->RedirectToTab($id, "generator");
}
?>

