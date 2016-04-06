<?php

if (!$this->CheckPermission($this->_GetModuleAlias() . '_modify_item'))
    return;

$item_id = '';
$image_id = '';

if (isset($params['item_id'])) {
    $item_id = (int) $params['item_id'];
}

if (isset($params['image_id'])) {
    $image_id = (int) $params['image_id'];
}

if (empty($item_id) || empty($image_id)) {
    $this->SetError($this->Lang('empty_param'));
    $this->RedirectToTab($id, 'defaulaction', $params);
}


$row = generator_tools::get_item($this, $item_id);
$image = generator_tools::get_image($this, $image_id);
$imagepath = generator_tools::imagepath_location($this, $row);

$delete = unlink(cms_join_path($imagepath, $image['filename']));
if (!$delete) {
    $this->SetError($this->Lang('image_not_deleted'));
} else {
	generator_tools::delete_image($this, $image_id);
    $this->SetMessage($this->Lang('image_deleted'));
}

$this->RedirectToTab($id, 'edititem', $params);

//$this->RedirectToTab($id, 'defaultadmin', $params);
?>