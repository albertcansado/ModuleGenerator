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
$smarty->assign('startform', $this->CreateFormStart($id, 'admin_ordercategory_do', $returnid));
$smarty->assign('endform', $this->CreateFormEnd());


$query = "SELECT * FROM " . cms_db_prefix() . "module_" . $this->_GetModuleAlias() . "_categories ORDER BY hierarchy_position";
$dbresult = $db->Execute($query);


$rowclass = 'row1';
while ($dbresult && $row = $dbresult->FetchRow()) {
    $onerow = cge_array::to_object($row);
    $entryarray[] = $onerow;
}
$smarty->assign_by_ref('items', $entryarray);
$smarty->assign('itemcount', count($entryarray));

echo $this->ModProcessTemplate('ordercategory.tpl');
#
# EOF
#
?>