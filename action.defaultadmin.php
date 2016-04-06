<?php

# Module: Module Generator
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

$config = cmsms()->GetConfig();

if (!is_writable($config['root_path'] . DIRECTORY_SEPARATOR . 'modules')) {
    echo $this->ShowErrors($this->Lang('module_dir_permission'));
}

## TAB HEADERS from CGE
echo $this->StartTabHeaders();
if ($this->CheckAccess()) {
    echo $this->SetTabHeader('generator', $this->Lang('generator'));
    echo $this->SetTabHeader('create_new', $this->Lang('create_new'));
}

echo $this->EndTabHeaders();

#
#The content of the tabs
#
echo $this->StartTabContent();

if ($this->CheckAccess()) {
    echo $this->StartTab('generator');
    include(dirname(__FILE__) . '/function.admin_generator.php');
    echo $this->EndTab();
    echo $this->StartTab('create_new');
    include(dirname(__FILE__) . '/function.admin_create_new.php');
    echo $this->EndTab();
}

echo $this->EndTabContent();
?>