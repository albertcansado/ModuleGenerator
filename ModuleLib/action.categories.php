<?php

$smarty = cmsms()->GetSmarty();

// template
$thetemplate = 'category_template' . $this->GetPreference('current_category_template');
if (isset($params['categorytemplate']))
    $thetemplate = 'category_template' . $params['categorytemplate'];

$cache_id = 'c' . $this->GetName() . md5(serialize($params));
$compile_id = '';

if (!$smarty->isCached($this->GetDatabaseResource($thetemplate), $cache_id, $compile_id)) {

    $items = generator_tools::get_categories($this, $id, $params, $returnid);

    // return first row
    if (isset($params["onerow"])) {
        $smarty->assign($params["onerow"], (isset($items[0]) ? $items[0] : ''));
        return;
    }

    //return  all results
    if (isset($params["allrow"])) {
        $smarty->assign($params["allrow"], $items);
        return;
    }


#Display template
    $smarty->assign('count', count($items));
    $smarty->assign('cats', $items);
}


echo $smarty->fetch($this->GetDatabaseResource($thetemplate), $cache_id, $compile_id);
?>