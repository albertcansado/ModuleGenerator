<?php

$db = cmsms()->GetDb();
$item = '';
$inline = $this->GetPreference('display_inline', false);

// do nothing
if (!isset($params['item_id']) && $id != '_preview_')
    return;
$default_detailpage = $this->GetPreference('item_detail_returnid', '');
$detailpage = extended_tools_opts::detailpage($params, $default_detailpage);
if ($detailpage == $default_detailpage)
    unset($params["detailpage"]);


// template
$detailtemplate = $thetemplate = 'detail_template' . $this->GetPreference('current_detail_template');
if (isset($params['detailtemplate']) && !empty($params['detailtemplate'])) {
    $detailtemplate = $thetemplate = 'detail_template' . $params['detailtemplate'];
}
$item_id = $params['item_id'];
$cache_id = 'd' . $this->GetName() . md5(serialize($params));
$compile_id = 'd' . $item_id;

if (isset($params['inline'])) {
    $inline = $params['inline'];
}

if ($id == '_preview_' && isset($_SESSION['item_preview']) && isset($params['preview'])) {
    // see if our data matches.
    if (md5(serialize($_SESSION['item_preview'])) == $params['preview']) {
        $fname = TMP_CACHE_LOCATION . '/' . $_SESSION['item_preview']['fname'];
        if (file_exists($fname) && (md5_file($fname) == $_SESSION['item_preview']['checksum'])) {
            $row = $params = unserialize(file_get_contents($fname));
            generator_opts::fill_item_from_formparams($this, $row, $params);
            $item = cge_array::to_object($row);
            generator_fields::alias_to_object(generator_opts::$fielddefs, $item);
        }
    }
} else {

    if (!$smarty->isCached($this->GetDatabaseResource($detailtemplate), $cache_id, $compile_id)) {
// SELECT FROM
        $query = 'SELECT A.*, B.category_id, B.category_name, B.category_alias FROM ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_item A';
// LEFT JOIN
        $query .= ' LEFT JOIN ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_categories B ON A.category_id = B.category_id';
// WHERE
        $query .= ' WHERE A.active = 1 AND item_id = ? LIMIT 1';
        $tmp_id = 'd' . md5($this->GetName() . $query . serialize($params) . $returnid);
        $row = cms_utils::get_app_data($tmp_id);
    }
    if (!$row) {
        $row = $db->GetRow($query, array($params['item_id']));

        cms_utils::set_app_data($tmp_id, $row);
    }
    if (!$row) {
        if (isset($params["onerow"])) {
            $smarty->assign($params["onerow"], '');
        }
        return;
    }

    $item = cge_array::to_object($row);
    $item->file_location = generator_tools::file_location($this, $row);

// category file location
    $item->category_file_location = generator_tools::file_location($this, $row['category_id'], false);

// item fields
    $fielddefs = generator_fields::get_processed_fields_values($this, $row['item_id']);
    generator_fields::alias_to_object($fielddefs, $item);

    $item->fields = $fielddefs;
    $item->fielddefs = $fielddefs;

// category fields
    $fielddefs = generator_fields::get_processed_fields_values($this, $row['category_id'], 'categories');
    generator_fields::alias_to_object($fielddefs, $item);
    $item->categoryfields = $fielddefs;
    $item->categoryfielddefs = $fielddefs;

    $dtpage = ($default_detailpage == -1 && !isset($params["detailpage"]) ? $detailpage : (isset($params["detailpage"]) ? $detailpage : $default_detailpage ));
    if (empty($row["url"]) == false) {
        $prettyurl = $row["url"];
    } else {
        $prettyurl = generator_tools::get_pretty_url($this, generator_tools::get_prefix($this), $row['item_id'], $row['alias'], $dtpage, $detailtemplate);
    }

    $images = '';
    if ($this->GetPreference('has_gallery')) {
        $item_gallery = new generator_item_gallery($this, $row["item_id"]);
        $images = $item_gallery->load_files();
    }

    $item->images = $images;


    $item->url = $item->link = $this->CreateFrontendLink($id, $dtpage, 'detail', '', array('item_id' => $row['item_id'], 'detailtemplate' => $detailtemplate), '', true, $inline, '', false, $prettyurl);
    $item->urlclean = $item->linkclean = $this->CreateFrontendLink($id, $dtpage, 'detail', '', array('item_id' => $row['item_id'], 'detailtemplate' => ''), '', true, $inline, '', false, $prettyurl);

    $prettyurl = generator_tools::get_pretty_url($this, generator_tools::get_prefix($this) . '/c', $row['category_id'], $row['category_alias'], ($default_detailpage == -1 && !isset($params["detailpage"]) ? $detailpage : ''), $categorytemplate);
    $item->category_link = $item->category_url = $this->CreateFrontendLink($id, $returnid, 'default', '', array('category_id' => $row['category_id'], 'categorytemplate' => $categorytemplate), '', true, isset($params["inline"]) ? 1 : 0, '', false, $prettyurl);


    if (isset($params["onerow"])) {
        $smarty->assign($params["onerow"], $item);
        return;
    }
}

$smarty->assign('item', $item);
//
// Process the template
//

echo $smarty->fetch($this->GetDatabaseResource($detailtemplate), $cache_id, $compile_id);
?>