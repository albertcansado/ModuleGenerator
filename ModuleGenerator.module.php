<?php

#-------------------------------------------------------------------------
# Module: Module Generatorfor CMS Made Simple (@kuzmany)
#
#-------------------------------------------------------------------------
# CMS - CMS Made Simple is (c) 2005 by Ted Kulp (wishy@cmsmadesimple.org)
# This project's homepage is: http://www.cmsmadesimple.org
# The module's homepage is: http://dev.cmsmadesimple.org/projects/mediacenter/
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

$config = cmsms()->GetConfig();
define('GENERATOR_MODLIB_PATH', cms_join_path($config['root_path'], 'modules', 'ModuleGenerator', 'ModuleLib'));

class ModuleGenerator extends CGExtensions {
    /* ---------------------------------------------------------
      Constructor
      --------------------------------------------------------- */

    public function __construct() {
        $smarty = cmsms()->GetSmarty();
        $config = cmsms()->GetConfig();
        $smarty->assign('generator_templates', cms_join_path($config["root_path"], 'modules', 'ModuleGenerator', 'ModuleLib', 'templates'));
        parent::__construct();
    }

    /* ---------------------------------------------------------
      SetParameters
      --------------------------------------------------------- */

    public function InitializeFrontend() {
        // nothing
    }

    public function SetParameters() {
        if (version_compare(CMS_VERSION, '1.10') < 0) {
            $this->InitializeFrontend();
        }
    }

    function InitializeAdmin() {
        // nothing
    }

    public function AllowSmartyCaching() {
        return TRUE;
    }

    function LazyLoadFrontend() {
        return TRUE;
    }

    function LazyLoadAdmin() {
        return TRUE;
    }

    public function GetName() {
        return get_class();
    }

    public function GetFriendlyName() {
        return $this->Lang('friendlyname');
    }

    public function GetVersion() {
        return '3.0.5';
    }

    public function AllowAutoUpgrade() {
        return FALSE;
    }

    public function GetHelp() {
        return $this->Lang('help');
    }

    public function GetAuthor() {
        return '@kuzmany';
    }

    public function GetAuthorEmail() {
        return 'zdeno@kuzmany.biz';
    }

    public function GetChangeLog() {
        return file_get_contents(dirname(__file__) . '/changelog.inc');
    }

    public function IsPluginModule() {
        return true;
    }

    public function HasAdmin() {
        return true;
    }

    public function GetAdminDescription() {
        return $this->Lang('moddescription');
    }

    public function GetDependencies() {
        return array('CGExtensions' => '1.31', 'ExtendedTools' => '2.0.0', 'CGSmartImage' => '1.9.5');
    }

    public function MinimumCMSVersion() {
        return "1.11.3";
    }

    public function GetEventDescription($eventname) {
        return $this->Lang('event_info_' . $eventname);
    }

    public function GetEventHelp($eventname) {
        return $this->Lang('event_help_' . $eventname);
    }

    public function InstallPostMessage() {
        return $this->Lang('postinstall');
    }

    public function UninstallPostMessage() {
        return $this->Lang('postuninstall');
    }

    public function UninstallPreMessage() {
        return $this->Lang('really_uninstall');
    }

    public function CheckAccess($perm = '1') {
        return $this->CheckPermission($perm);
    }

    public function _ModuleGetHeaderHtml() {
        $module = cms_utils::get_module('ModuleGenerator');
        $output = [];

        // Attach JS libraries
        $scripts = [
            'utils',
            'spectrum.min',
            'keyValue.min',
            'lookup.min',
            'jtTable.min',
            'dropdownAdd.min',
            'jquery.tablednd.min'
        ];

        // Gallery Scripts
        if ($this->GetPreference('has_gallery')) {
            $scripts[] = 'plupload/plupload.full.min';
        }

        $scriptTag = '<script type="text/javascript" src="' . $module->GetModuleURLPath() . '/js/%s.js"></script>';
        foreach ($scripts as $script) {
            $output[] = sprintf($scriptTag, $script);
        }

        // Attach CSS libraries
        $css = [
            'spectrum',
            'fielddef_styles'
        ];
        $cssTag = '<link rel="stylesheet" href="' . $module->GetModuleURLPath() . '/css/%s.css" />';
        foreach ($css as $file) {
            $output[] = sprintf($cssTag, $file);
        }

        return implode("", $output);
    }

    public function ModProcessTemplate($tpl_name) {
        $ok = (strpos($tpl_name, '..') === false);
        if (!$ok)
            return;

        $smarty = cmsms()->GetSmarty();
        $config = cmsms()->GetConfig();
        $result = '';

        $oldcache = $smarty->caching;
        $smarty->caching = $this->can_cache_output() ? Smarty::CACHING_LIFETIME_CURRENT : Smarty::CACHING_OFF;

        $files = array();
        $files[] = cms_join_path($config['root_path'], 'module_custom', $this->GetName(), 'templates');
        $files[] = cms_join_path(GENERATOR_MODLIB_PATH, 'templates');

        $smarty->assign('mod', $this);

        foreach ($files as $file) {
            if (is_readable($file . '/' . $tpl_name)) {
                $smarty->setTemplateDir($file);
                $result = $smarty->fetch($file . '/' . $tpl_name);

                break;
            }
        }

        $smarty->caching = $oldcache;

        return $result;
    }

}

?>
