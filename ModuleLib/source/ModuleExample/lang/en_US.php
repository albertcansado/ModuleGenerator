<?php

/* --- do not edit ------------------------------------------------------ */
/* To make this module easy to duplicate, the module name and module alias
 * is taken from the module directory name that this lang file is contained.
 * This is required because the module object cannot be accessed without
 * This is required because the module object cannot be accessed without
 * knowing its name.
 */
$config=cmsms()->GetConfig();
$dir = str_replace('\\' , '/', dirname(__FILE__));
preg_match('/\/modules\/(.+)\//', $dir, $matches);
$module_name = $matches[1];
$module_alias = strtolower($matches[1]);
$rooturl = $config['root_url'];
include(cms_join_path($config["root_path"],'modules','ModuleGenerator','ModuleLib','lang','en_US.php'));
?>