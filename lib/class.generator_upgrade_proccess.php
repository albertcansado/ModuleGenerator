
<?php

#-------------------------------------------------------------------------
# Module: ModuleGenerator for CMS Made Simple (@kuzmany)
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
# Or read it online: http://www.gnu.org/licenses/licenses.html#GPL
#
#-------------------------------------------------------------------------
/**
 * Description of class
 *
 * @author @kuzmany
 */

class generator_upgrade_proccess {

    public function __construct() {
        
    }

    public static function copy_files($mobject, $files = array(), $init = false) {

        $config = cmsms()->GetConfig();
        if (empty($files) == false) {
            foreach ($files as $file) {
                $from = cms_join_path($config["root_path"], 'modules', 'ModuleGenerator', 'ModuleLib', 'source', 'ModuleExample', $file);
                $to = cms_join_path($config["root_path"], 'modules', $mobject->GetName(), $file);
                copy($from, $to);
            }
        }
        if ($init == true) {
            $file = 'ModuleExample.module.php';
            $from = cms_join_path($config["root_path"], 'modules', 'ModuleGenerator', 'ModuleLib', 'source', 'ModuleExample', $file);
            $to = cms_join_path($config["root_path"], 'modules', $mobject->GetName(), $mobject->GetName() . '.module.php');
            copy($from, $to);

            //  rewrite module content
            $data = @file_get_contents($to);
            if ($data) {
                $data = str_replace('ModuleExample', $mobject->GetName(), $data);
                file_put_contents($to, $data);
            } else {
                die($mobject->Lang('error_replace_string'));
            }
        }
    }

}

?>