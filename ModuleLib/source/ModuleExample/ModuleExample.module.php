<?php

/*

  Author: Ben Malen, <ben@conceptfactory.com.au>
  Co-Maintainer: Simon Radford, <simon@conceptfactory.com.au>
  Web: www.conceptfactory.com.au
  Co-Maintainer: Jonathan Schmid, <hi@jonathanschmid.de>

  ListIt is a CMS Made Simple module that enables the web developer to create
  multiple lists throughout a site. It can be duplicated and given friendly
  names for easier client maintenance.

  When duplicating this module change the 'ListIt' below to the new module
  name, e.g. NewName. Follow the CMSMS module naming conventions, a-z with no
  punctuation characters or spaces. Also change the name of this file to match
  - NewName.module.php. Finally change the name of the ListIt folder to the
  same NewName

 */

if (!class_exists('ModuleGenerator')) {
    $config = cmsms()->GetConfig();
    $generatorFile = cms_join_path($config['root_path'], 'modules', 'ModuleGenerator', 'ModuleGenerator.module.php');
    require $generatorFile;
}

class ModuleExample extends ModuleGenerator {

    public function __construct() {
        spl_autoload_register(array(&$this, '_autoloader'));
        parent::__construct();
    }

    #---------------------
    # Internal autoloader
    #---------------------	

    private final function _autoloader($classname) {
        $config = cmsms()->GetConfig();
        $fn = cms_join_path($config['root_path'], 'modules', 'ModuleGenerator') . "/lib/class.{$classname}.php";
        if (file_exists($fn)) {
            require_once($fn);
        }
    }

    public function GetName() {
        return get_class($this);
    }

    function GetFriendlyName() {
        return $this->GetPreference('friendlyname');
    }

    public function GetVersion() {
        return parent::GetVersion();
    }

    public function GetHelp() {
        return parent::GetHelp();
    }

    public function GetAuthor() {
        return parent::GetAuthor();
    }

    public function GetAuthorEmail() {
        return parent::GetAuthorEmail();
    }

    public function GetChangeLog() {
        return parent::GetChangeLog();
    }

    public function SetParameters() {

        $this->InitializeAdmin();
        $this->InitializeFrontend();
    }

    public function AllowAutoUpgrade() {
        return TRUE;
    }

    public function InitializeAdmin() {

        // auto load for generator_opts
        $config = cmsms()->GetConfig();
        $generator_opts = cms_join_path($config['root_path'], 'modules', 'ModuleGenerator', 'lib', 'class.generator_opts.php');
        require_once($generator_opts);

        generator_opts::init_admin($this);
    }

    public function InitializeFrontend() {

        $config = cmsms()->GetConfig();
        $generator_opts = cms_join_path($config['root_path'], 'modules', 'ModuleGenerator', 'lib', 'class.generator_opts.php');
        require_once($generator_opts);

        generator_opts::init($this);
    }

    public function AllowSmartyCaching() {
        return TRUE;
    }

    public function LazyLoadFrontend() {
        return TRUE;
    }

    public function LazyLoadAdmin() {
        return TRUE;
    }

    public function GetEventDescription($eventname) {
        return parent::GetEventDescription($eventname);
    }

    public function IsPluginModule() {
        return parent::IsPluginModule();
    }

    /**
     * DoAction - default add default params
     * @param type $name
     * @param type $id
     * @param type $params
     * @param type $returnid 
     */
    public function DoAction($name, $id, $params, $returnid = '') {
        global $CMS_ADMIN_PAGE;
        $config = cmsms()->GetConfig();
        $smarty = cmsms()->GetSmarty();
        $db = cmsms()->GetDb();
        
        parent::DoAction('', $id, $params, $returnid);

        switch ($name) {
            default:
                // fix 4 smarty security width templates folder
                if (isset($CMS_ADMIN_PAGE) && $CMS_ADMIN_PAGE == 1) {
                    $templatedir = GENERATOR_MODLIB_PATH . '/templates'; // CMSMS 1.12
                    $smarty->setTemplateDir($templatedir);
                }

                //include framework
                $files = array();
                $files[] = cms_join_path($config['root_path'], 'modules', $this->GetName(), 'action.' . $name . '.php');
                $files[] = cms_join_path(GENERATOR_MODLIB_PATH, 'action.' . $name . '.php');

                foreach ($files as $file) {
                    if (@is_file($file)) {
                        include($file);
                        return;
                    }
                }
        }
    }

    public function HasAdmin() {
        return $this->GetPreference('has_admin', false);
    }

    public function GetAdminSection() {
        return $this->GetPreference('admin_section', 'content');
    }

    public function GetAdminDescription() {
        return parent::GetAdminDescription();
    }

    public function VisibleToAdminUser() {
        return $this->CheckPermission($this->_GetModuleAlias() . '_modify_item');
    }

    public function GetDependencies() {
        return parent::GetDependencies();
    }

    public function MinimumCMSVersion() {
        return parent::MinimumCMSVersion();
    }

    function InstallPostMessage() {
        return parent::InstallPostMessage();
    }

    function UninstallPostMessage() {
        return parent::UninstallPostMessage();
    }

    /**
     *  get module alias
     * @return type 
     */
    public function _GetModuleAlias() {
        $value = cms_utils::get_app_data(get_class() . __FUNCTION__);
        if ($value)
            return $value;

        $value = strtolower($this->GetName());
        cms_utils::set_app_data(get_class() . __FUNCTION__, $value);
        return $value;
    }

    public function GetHeaderHtml() {
        return parent::_ModuleGetHeaderHtml();
    }

    public function SearchResultWithParams($returnid, $item_id, $attr = '', $params = '') {
        if (!$this->GetPreference('searchable'))
            return;
        return generator_tools::get_search_result($this, $returnid, $item_id, $attr, $params);
    }

    public function SearchReindex($module) {
        if (!$this->GetPreference('searchable'))
            return;
        return generator_tools::search_reindex($this, $module);
    }

    public function CreateStaticRoutes() {

        // auto load for generator_opts
        $config = cmsms()->GetConfig();
        $generator_opts = cms_join_path($config['root_path'], 'modules', 'ModuleGenerator', 'lib', 'class.generator_opts.php');
        require_once($generator_opts);
        generator_opts::init_static_routes($this);
    }

    // install
    public function Install() {
        $config = cmsms()->GetConfig();
        $smarty = cmsms()->GetSmarty();
        $db = cmsms()->GetDb();

        $response = FALSE;

        $filename = GENERATOR_MODLIB_PATH . '/method.install.php';
        if (@is_file($filename)) {

            $res = include($filename);
            if ($res == 1 || $res == '') {
                $response = FALSE;
            } else {
                $response = $res;
            }
        }


        return $response;
    }

    public function Upgrade($oldversion, $newversion) {
        $config = cmsms()->GetConfig();
        $smarty = cmsms()->GetSmarty();
        $db = cmsms()->GetDb();

        $response = FALSE;

        $filename = GENERATOR_MODLIB_PATH . '/method.upgrade.php';
        if (@is_file($filename)) {

            $res = include($filename);
            if ($res == 1 || $res == '')
                $response = TRUE;
        }
        return $response;
    }

    public function Uninstall() {
        $config = cmsms()->GetConfig();
        $smarty = cmsms()->GetSmarty();
        $db = cmsms()->GetDb();

        $response = FALSE;

        $filename = GENERATOR_MODLIB_PATH . '/method.uninstall.php';
        if (@is_file($filename)) {

            $res = include($filename);
            if ($res == 1 || $res == '') {
                $response = FALSE;
            } else {
                $response = $res;
            }
        }

        return $response;
    }

}

?>
