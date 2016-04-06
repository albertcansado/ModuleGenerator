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
/* CREATE TABLE `cms_module_galeria_images` (
  `image_id` INT(10) NULL AUTO_INCREMENT,
  `filename` VARCHAR(255) NULL DEFAULT NULL,
  `posiiton` INT NULL DEFAULT NULL,
  PRIMARY KEY (`image_id`)
  )
  COLLATE='utf8_general_ci'
  ENGINE=MyISAM; */

class generator_item_gallery {

    private $_dir;
    private $_mod;
    private $_item_id;
    private $_db;
    private $_smarty;
    private $_config;
    private $_extensions = 'jpg,gif,png';

    public function __construct($mod, $item_id) {

        $this->_db = cmsms()->GetDb();
        $this->_smarty = cmsms()->GetSmarty();
        $this->_config = cmsms()->GetConfig();
        $this->_mod = $mod;
        $this->_item_id = $item_id;

        $config = cmsms()->GetConfig();

        $row = array();
        $row['item_id'] = $item_id;
        $this->_dir = generator_tools::imagepath_location($this->_mod, $row);
    }

    public function load_files($limit = null) {

        //$this->_clean_up();
        $query = 'SELECT *  FROM ' . cms_db_prefix() . 'module_' . $this->_mod->_GetModuleAlias() . '_images WHERE item_id = ?  ORDER BY position ' . $this->_mod->GetPreference('gallery_sortorder');
        $qparms = array();
        $qparms[] = $this->_item_id;

        if (!is_null($limit)) {
            $query .= ' LIMIT ' . (int)$limit;
        }

        $files = $this->_db->GetAll($query, $qparms);
        if ($files) {
            foreach ($files as $key => $file) {
                $files[$key]["file_location"] = generator_tools::image_location($this->_mod, $file);

                // category fields
                $fielddefs = generator_fields::get_processed_fields_values($this->_mod, $file['image_id'], 'galleries');
                $files[$key]['fields'] = $fielddefs;

                $fielddefs = generator_fields::get_processed_fields_values($this->_mod, $file['image_id'], 'galleries', 'fielddef_id');
                $files[$key]['fieldsbyid'] = $fielddefs;
            }
        }
        return $files;
    }

    public function delete_image($image_id) {
        $query = 'SELECT filename FROM ' . cms_db_prefix() . 'module_' . $this->_mod->_GetModuleAlias() . '_images WHERE image_id =?';
        $filename = $this->_db->GetOne($query, array($image_id));
        #echo 'test';
        if (!$filename)
            return;
        unlink(cms_join_path($this->_dir, $filename));
        $query = 'DELETE FROM ' . cms_db_prefix() . 'module_' . $this->_mod->_GetModuleAlias() . '_images WHERE image_id =?';
        $this->_db->Execute($query, array($image_id));
    }

    public function clean_up() {

        cge_dir::mkdirr($this->_dir);
        $files = cge_dir::get_file_list($this->_dir, $this->_extensions);

        $query = 'SELECT MAX(position) FROM ' . cms_db_prefix() . 'module_' . $this->_mod->_GetModuleAlias() . '_images WHERE item_id = ?';
        $max_position = $this->_db->GetOne($query, array($this->_item_id));
        if (!$max_position)
            $max_position = 1;

        if (empty($files) == false) {
            foreach ($files as $filename) {
                $query = 'SELECT filename FROM ' . cms_db_prefix() . 'module_' . $this->_mod->_GetModuleAlias() . '_images WHERE item_id = ? and filename = ?';
                $is = $this->_db->GetOne($query, array($this->_item_id, $filename));
                if ($is)
                    continue;

                $query = 'INSERT INTO ' . cms_db_prefix() . 'module_' . $this->_mod->_GetModuleAlias() . '_images (item_id, filename, position) VALUES (?,?,?)';
                $max_position++;
                $this->_db->Execute($query, array($this->_item_id, $filename, $max_position));
            }
        }
    }

}

?>
