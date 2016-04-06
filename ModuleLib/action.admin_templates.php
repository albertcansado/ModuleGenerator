<?php

#-------------------------------------------------------------------------
# Module: Uploads -= allow users to upload stuff, a pseudo file manager" module
# Author: Robert Campbell <rob@techcom.dyndns.org>
#
#-------------------------------------------------------------------------
# CMS - CMS Made Simple is (c) 2005 by Ted Kulp (wishy@cmsmadesimple.org)
# This project's homepage is: http://www.cmsmadesimple.org
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
# Or read it online: http:	//www.gnu.org/licenses/licenses.html#GPL
#
#-------------------------------------------------------------------------

echo '<div class="pageoverflow">' .
 $this->CreateImageLink($id, 'defaultadmin', $returnid, $this->Lang('back'), 'icons/system/back.gif', array(), '', '', false) . '</div><br/>';

echo $this->StartTabHeaders();
if ($this->CheckPermission('Modify Templates')) {
    echo $this->SetTabHeader('category_template', $this->Lang('category_template'));
    echo $this->SetTabHeader('summary_template', $this->Lang('summary_template'));
    echo $this->SetTabHeader('detail_template', $this->Lang('detail_template'));
    echo $this->SetTabHeader('filter_template', $this->Lang('filter_template'));
    //echo $this->SetTabHeader('default_templates', $this->Lang('default_templates'));
}
echo $this->EndTabHeaders();
echo $this->StartTabContent();
if ($this->CheckPermission('Modify Templates')) {
    // the category template tab
    echo $this->StartTab('category_template');
    echo $this->ShowTemplateList($id, $returnid, 'category_template', 'default_category_template', 'category_template', 'current_category_template', $this->Lang('addedit_category_template'), 'admin_templates', 'admin_templates');
    echo $this->EndTab();

    // the summary template tab
    echo $this->StartTab("summary_template");
    echo $this->ShowTemplateList($id, $returnid, 'summary_template', 'default_summary_template', 'summary_template', 'current_summary_template', $this->Lang('addedit_summary_template'), 'admin_templates', 'admin_templates');
    echo $this->EndTab();

    // the detail template tab
    echo $this->StartTab("detail_template");
    echo $this->ShowTemplateList($id, $returnid, 'detail_template', 'default_detail_template', 'detail_template', 'current_detail_template', $this->Lang('addedit_detail_template'), 'admin_templates', 'admin_templates');
    echo $this->EndTab();

    // the filter template tab
    echo $this->StartTab("filter_template");
    echo $this->ShowTemplateList($id, $returnid, 'filter_template', 'default_filter_template', 'filter_template', 'current_filter_template', $this->Lang('addedit_filter_template'), 'admin_templates', 'admin_templates');
    echo $this->EndTab();


    // the default templates tab
    /* echo $this->StartTab('default_templates');

      echo $this->GetDefaultTemplateForm($this, $id, $returnid, 'category_sysdefault', 'admin_templates', 'default_templates', $this->Lang('title_category_sysdefault'), 'orig_category_template.tpl', $this->Lang('info_sysdefault'));
      echo $this->GetDefaultTemplateForm($this, $id, $returnid, 'summary_sysdefault', 'admin_templates', 'default_templates', $this->Lang('title_summary_sysdefault'), 'orig_summary_template.tpl', $this->Lang('info_sysdefault'));
      echo $this->GetDefaultTemplateForm($this, $id, $returnid, 'detail_sysdefault', 'admin_templates', 'default_templates', $this->Lang('title_detail_sysdefault'), 'orig_detail_template.tpl', $this->Lang('info_sysdefault'));


      echo '<div style="border-bottom: 1px solid black; width: 80%;"></div>';

      echo $this->EndTab(); */
}
echo $this->EndTabContent();

#
# EOF
#
?>