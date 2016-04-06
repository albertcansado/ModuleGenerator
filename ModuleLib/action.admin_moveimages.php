<?php

if (!$this->CheckPermission($this->_GetModuleAlias() . '_modify_item'))
    return;

$data = trim(trim($params['serialdata']), ',');
if ($this->GetPreference('gallery_sortorder') == 'desc') {
    $data = array_reverse(explode(',', $data));
} else {
    $data = (explode(',', $data));
}
$items = array();
$sorting = 0;
for ($i = 0; $i < count($data); $i++) {
    $image_id = $data[$i];
    if (!$image_id)
        continue;

    $sorting++;

    $image_id_old = $items[$sorting];

    if ($image_id == $image_id_old)
        continue;

    $item = generator_tools::get_image($this, $image_id);
    $items[$item["position"]] = $item["image_id"];
}

ksort($items);
$items2 = array();
foreach ($items as $key => $item) {
    $items2[] = $key;
}


$index = 0;
$new_sorting = array();
foreach ($data as $key => $d) {
    $new_sorting[$items2[$key]] = $d;
}

foreach ($new_sorting as $key => $image_id) {
    $query = 'UPDATE ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_images SET position = ? WHERE image_id = ?';
    $db->Execute($query, array($key, $image_id));
}

$this->RedirectToTab($id, 'gallery', array('item_id' => $params["item_id"]), 'admin_edititem');
?>