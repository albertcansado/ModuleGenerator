<?php

if (!$this->CheckPermission($this->_GetModuleAlias() . '_modify_item'))
    return;

$this->SetCurrentTab('gallery');

if (!isset($params["item_id"]))
    return;

$item_id = $params["item_id"];

$item_gallery = new generator_item_gallery($this, $item_id);

if (isset($params['multiselect']) && is_array($params['multiselect']) && count($params['multiselect'])) {
    for ($i = 0; $i < count($params['multiselect']); $i++) {
        $params['multiselect'][$i] = (int) $params['multiselect'][$i];
    }

    foreach ($params['multiselect'] as $image_id) {
        $res = $item_gallery->delete_image($image_id);
        
    }
}
$this->Setmessage($this->Lang('operation_complete'));
$this->RedirectToTab($id, 'gallery', array('item_id' => $item_id), 'admin_edititem');
#
# EOF
#
?>
