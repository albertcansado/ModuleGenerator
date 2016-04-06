<?php

if (!$this->CheckPermission($this->_GetModuleAlias() . '_modify_item'))
    return;

$data = trim(trim($params['serialdata']), ',');
if ($this->GetPreference('sortorder_simple', 'desc') == 'desc') {
    $data = array_reverse(explode(',', $data));
} else {
    $data = (explode(',', $data));
}



$items = array();
$sorting = 1;
for ($i = 0; $i < count($data); $i++) {
    $item_id = $data[$i];
    if (!$item_id)
        continue;

    $sorting++;

    
    $item_id_old = isset($items[$sorting]) ? $items[$sorting] : null ;

    if ($item_id == $item_id_old)
        continue;

    $item = generator_tools::get_item($this, $item_id);
    $items[$item["position"]] = $item["item_id"];
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

foreach ($new_sorting as $key => $item_id) {
    $query = 'UPDATE ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_item SET position = ? WHERE item_id = ?';
    $db->Execute($query, array($key, $item_id));
    @$this->SendEvent('ItemEdited', array('item_id' => $item_id));
}



@$this->SendEvent('ItemsReordered', array());
$this->RedirectToTab($id, 'itemtab');
?>