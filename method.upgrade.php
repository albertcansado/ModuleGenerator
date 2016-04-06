<?php
$db = cmsms()->GetDb();
$taboptarray = array('mysql' => 'TYPE=MyISAM');
$dict = NewDataDictionary($db);
$config = cmsms()->GetConfig();

switch ($oldversion) {
    case '1.0':
        $modules = generator_opts::get_modules();
        foreach ($modules as $module) {
            $mobject = cms_utils::get_module($module["module_name"]);
            if ($mobject) {
                $sqlarray = $dict->AddColumnSQL(cms_db_prefix() . 'module_' . $mobject->_GetModuleAlias() . '_item', 'recursive C(255)');
                $dict->ExecuteSQLArray($sqlarray);

                $sqlarray = $dict->AddColumnSQL(cms_db_prefix() . 'module_' . $mobject->_GetModuleAlias() . '_item', 'item_date_end ' . CMS_ADODB_DT);
                $dict->ExecuteSQLArray($sqlarray);

                $fields = '
    item_id I KEY NOT null,
    value I KEY NOT null
';
                $sqlarray = $dict->CreateTableSQL(cms_db_prefix() . 'module_' . $mobject->_GetModuleAlias() . '_item_extra', $fields, $taboptarray);
                $dict->ExecuteSQLArray($sqlarray);
            }
        }
    case '1.1':
        $modules = generator_opts::get_modules();
        foreach ($modules as $module) {
            $mobject = cms_utils::get_module($module["module_name"]);
            if ($mobject) {
                $sqlarray = $dict->AddColumnSQL(cms_db_prefix() . 'module_' . $mobject->_GetModuleAlias() . '_categories', 'usergroup I');
                $dict->ExecuteSQLArray($sqlarray);
                $mobject->CreatePermission($mobject->_GetModuleAlias() . '_all_category_visbile', $mobject->Lang('all_category_visbile'));
            }
        }
    case '1.2':
        $modules = generator_opts::get_modules();
        foreach ($modules as $module) {
            $mobject = cms_utils::get_module($module["module_name"]);
            if ($mobject) {

                $sqlarray = $dict->CreateIndexSQL(cms_db_prefix() . $mobject->_GetModuleAlias() . '_url', cms_db_prefix() . 'module_' . $mobject->_GetModuleAlias() . '_item', 'url');
                $dict->ExecuteSQLArray($sqlarray);
            }
        }
    case '1.3':
        $modules = generator_opts::get_modules();
        foreach ($modules as $module) {
            $mobject = cms_utils::get_module($module["module_name"]);
            if ($mobject) {
                $sqlarray = $dict->AddColumnSQL(cms_db_prefix() . 'module_' . $mobject->_GetModuleAlias() . '_categories', 'extra2 X');
                $dict->ExecuteSQLArray($sqlarray);
                $mobject->SetPreference('category_extra2_label', $mobject->Lang('extra'));
            }
        }
    case '1.4':
        $modules = generator_opts::get_modules();
        foreach ($modules as $module) {
            $mobject = cms_utils::get_module($module["module_name"]);
            if ($mobject) {
                $mobject->CreateEvent('ItemAdded');
                $mobject->CreateEvent('ItemEdited');
                $mobject->CreateEvent('ItemDeleted');
                $mobject->CreateEvent('CategoryAdded');
                $mobject->CreateEvent('CategoryEdited');
                $mobject->CreateEvent('CategoryDeleted');
                $mobject->CreateEvent('ItemsReordered');
                $mobject->CreateEvent('CategoriesReordered');
            }
        }
    case '1.5':
        $modules = generator_opts::get_modules();
        foreach ($modules as $module) {
            $mobject = cms_utils::get_module($module["module_name"]);
            if ($mobject) {
                $sqlarray = $dict->AddColumnSQL(cms_db_prefix() . 'module_' . $mobject->_GetModuleAlias() . '_fielddef', 'editview I(1)');
                $dict->ExecuteSQLArray($sqlarray);
            }
        }
    case '1.6':
        $modules = generator_opts::get_modules();
        foreach ($modules as $module) {
            $mobject = cms_utils::get_module($module["module_name"]);
            if ($mobject) {
                $sqlarray = $dict->AddColumnSQL(cms_db_prefix() . 'module_' . $mobject->_GetModuleAlias() . '_fielddef', 'filter_frontend I(1)');
                $dict->ExecuteSQLArray($sqlarray);
                $sqlarray = $dict->AddColumnSQL(cms_db_prefix() . 'module_' . $mobject->_GetModuleAlias() . '_fielddef', 'filter_admin I(1)');
                $dict->ExecuteSQLArray($sqlarray);
            }
        }
    case '1.8.1':
        $modules = generator_opts::get_modules();

        foreach ($modules as $module) {
            $mobject = cms_utils::get_module($module["module_name"]);
            if ($mobject) {
                $position = 0;

                $db->Execute('ALTER TABLE `' . cms_db_prefix() . 'module_' . $mobject->_GetModuleAlias() . '_categories`  CHANGE COLUMN `category_id` `category_id` INT(11) NOT NULL AUTO_INCREMENT FIRST');


                // copy new files
                $file = 'action.admin_fields.php';
                $from = cms_join_path($config["root_path"], 'modules', 'ModuleGenerator', 'ModuleLib', 'source', 'ModuleExample', $file);
                $to = cms_join_path($config["root_path"], 'modules', $mobject->GetName(), $file);
                copy($from, $to);
                $file = 'function.admin_fielddeftab_categories.php';
                $from = cms_join_path($config["root_path"], 'modules', 'ModuleGenerator', 'ModuleLib', 'source', 'ModuleExample', $file);
                $to = cms_join_path($config["root_path"], 'modules', $mobject->GetName(), $file);
                copy($from, $to);
                $file = 'action.admin_ordercategory.php';
                $from = cms_join_path($config["root_path"], 'modules', 'ModuleGenerator', 'ModuleLib', 'source', 'ModuleExample', $file);
                $to = cms_join_path($config["root_path"], 'modules', $mobject->GetName(), $file);
                copy($from, $to);

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
                    die($this->Lang('error_replace_string'));
                }



                // update database
                $sqlarray = $dict->AddColumnSQL(cms_db_prefix() . 'module_' . $mobject->_GetModuleAlias() . '_fielddef', 'section  C(10)');
                $dict->ExecuteSQLArray($sqlarray);

                $query = 'UPDATE ' . cms_db_prefix() . 'module_' . $mobject->_GetModuleAlias() . '_fielddef SET section = "items" ';
                $result = $db->Execute($query, array());


                $categories = generator_tools::get_category_list($mobject, array(), array(), false);

                if (empty($categories) == false) {

                    $help = '';
                    $type = 'textbox';
                    $required = 0;
                    $editview = 0;
                    $frontend = 1;
                    $filter_frontend = 0;
                    $admin_admin = 0;
                    $searchable = 0;
                    $extra = '';
                    $section = 'categories';



                    $extra1 = false;
                    $extra2 = false;
                    foreach ($categories as $category) {

                        if ($category["extra"]) {

                            if ($extra1 == false) {

                                $name = $mobject->GetPreference('category_extra_label', $mobject->Lang('extra'));
                                $alias = 'extra';

                                $query = 'INSERT INTO ' . cms_db_prefix() . 'module_' . $mobject->_GetModuleAlias() . '_fielddef (name, alias, help, type, position, required, frontend, editview, filter_frontend, filter_admin, searchable, extra, section) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
                                $result = $db->Execute($query, array($name, $alias, $help, $type, $position++, $required, $frontend, $editview, $filter_frontend, $admin_admin, $searchable, $extra, $section));
                                $extra1id = $db->Insert_ID();
                                $extra1 = true;
                            }


                            $query = 'INSERT INTO ' . cms_db_prefix() . 'module_' . $mobject->_GetModuleAlias() . '_fieldval (item_id, fielddef_id, value) VALUES (?, ?, ?)';
                            $result = $db->Execute($query, array($category["category_id"], $extra1id, $category["extra"]));
                        }
                        if ($category["extra2"]) {

                            if ($extra2 == false) {
                                $name = $mobject->GetPreference('category_extra2_label', $mobject->Lang('extra'));
                                $alias = 'extra2';

                                $query = 'INSERT INTO ' . cms_db_prefix() . 'module_' . $mobject->_GetModuleAlias() . '_fielddef (name, alias, help, type, position, required, frontend, editview, filter_frontend, filter_admin, searchable, extra, section) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
                                $result = $db->Execute($query, array($name, $alias, $help, $type, $position++, $required, $frontend, $editview, $filter_frontend, $admin_admin, $searchable, $extra, $section));
                                $extra2id = $db->Insert_ID();
                                $extra2 = true;
                            }

                            $query = 'INSERT INTO ' . cms_db_prefix() . 'module_' . $mobject->_GetModuleAlias() . '_fieldval (item_id, fielddef_id, value) VALUES (?, ?, ?)';
                            $result = $db->Execute($query, array($category["category_id"], $extra2id, $category["extra2"]));
                        }
                    }
                }
            }
        }
    case '1.9.2':

        $modules = generator_opts::get_modules();

        foreach ($modules as $module) {
            $mobject = cms_utils::get_module($module["module_name"]);
            if ($mobject) {


                $fields = '
     `image_id` int(10) I KEY AUTO,
  `item_id` int(10) DEFAULT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `position` int(10) DEFAULT
';
                $sqlarray = $dict->CreateTableSQL(cms_db_prefix() . 'module_' . $mobject->_GetModuleAlias() . '_images', $fields, $taboptarray);
                $dict->ExecuteSQLArray($sqlarray);

                // copy new files
                $file = 'action.admin_imagesbulkaction.php';
                $from = cms_join_path($config["root_path"], 'modules', 'ModuleGenerator', 'ModuleLib', 'source', 'ModuleExample', $file);
                $to = cms_join_path($config["root_path"], 'modules', $mobject->GetName(), $file);
                copy($from, $to);
                $file = 'action.admin_moveimages.php';
                $from = cms_join_path($config["root_path"], 'modules', 'ModuleGenerator', 'ModuleLib', 'source', 'ModuleExample', $file);
                $to = cms_join_path($config["root_path"], 'modules', $mobject->GetName(), $file);
                copy($from, $to);

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
                    die($this->Lang('error_replace_string'));
                }
            }
        }
    case '2.1':
        $modules = generator_opts::get_modules();
        foreach ($modules as $module) {
            $mobject = cms_utils::get_module($module["module_name"]);
            if ($mobject) {
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
                    die($this->Lang('error_replace_string'));
                }
            }
        }
    case '2.2.1':
        $modules = generator_opts::get_modules();

        foreach ($modules as $module) {
            $mobject = cms_utils::get_module($module["module_name"]);
            if ($mobject) {

                $files = array();
                $files[] = 'action.admin_prefs.php';
                $files[] = 'function.admin_modetab.php';
                $files[] = 'function.admin_optioneditingtab.php';
                $files[] = 'function.admin_optionsearchtab.php';
                $files[] = 'function.admin_optionsearchtab.php';
                $files[] = 'function.admin_optiontab.php';
                $files[] = 'templates/optionsearchtab.tpl';
                $files[] = 'templates/optioneditingtab.tpl';
                $files[] = 'templates/modetab.tpl';
                generator_upgrade_proccess::copy_files($mobject, $files, true);
            }
        }
    case '2.2.4':
        $modules = generator_opts::get_modules();

        foreach ($modules as $module) {
            $mobject = cms_utils::get_module($module["module_name"]);
            if ($mobject) {
                $files = array();
                generator_upgrade_proccess::copy_files($mobject, $files, true);
                $old = cms_join_path($config["uploads_path"], $mobject->_GetModuleAlias());
                $new = cms_join_path($config["uploads_path"], '_' . $mobject->_GetModuleAlias());
                if (is_dir($old) && is_dir($new))
                    cge_dir::recursive_rmdir($new);
                rename($old, $new);
            }
        }
    case '2.3':
        $modules = generator_opts::get_modules();
        foreach ($modules as $module) {
            $mobject = cms_utils::get_module($module["module_name"]);
            if ($mobject) {
                //remove all unnecessary files
                $dest = cms_join_path($config["root_path"], 'modules', $mobject->GetName());
                $templates = cms_join_path($dest, 'templates');
                cge_dir::recursive_rmdir($templates);
                @unlink($templates);
                $files = cge_dir::file_list_regexp($dest, 'php');
                if ($files) {
                    foreach ($files as $file) {
                        @unlink(cms_join_path($dest, $file));
                    }
                }
                // copy *.module files
                $files = array();
                generator_upgrade_proccess::copy_files($mobject, $files, true);
            }
        }
    case '2.3.1':
        $dest = cms_join_path($config["root_path"], 'modules', 'ModuleGenerator', 'ModuleLib', 'source', 'ModuleExample');
        $templates = cms_join_path($dest, 'templates');
        cge_dir::recursive_rmdir($templates);
        @rename($dest . '/ModuleExample.module.php', $dest . '/ModuleExample.module.tmp');
        $files = cge_dir::file_list_regexp($dest, 'php');
        if ($files) {
            foreach ($files as $file) {
                @unlink(cms_join_path($dest, $file));
            }
        }
        @rename($dest . '/ModuleExample.module.tmp', $dest . '/ModuleExample.module.php');
    case '3.0.0':
        $modules = generator_opts::get_modules();
        foreach ($modules as $module) {
            $mobject = cms_utils::get_module($module["module_name"]);
            if ($mobject) {
                $sqlarray = $dict->AlterColumnSQL(cms_db_prefix() . 'module_' . $mobject->_GetModuleAlias() . '_fielddef', 'extra TEXT');
                $dict->ExecuteSQLArray($sqlarray);

                $mobject->CreateEvent('DropdownOptionAdded');
            }
        }
        $current_version = "3.0.1";
}
?>