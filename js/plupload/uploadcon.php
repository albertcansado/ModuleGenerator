<?php

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (empty($_POST['item_id']) || empty($_POST['m'])) {
	die();
}

$response = [
	'status' => 'ok',
	'data' => []
];
try {
	// Load CMSMS
	include_once '../../../../include.php';

	// Clear names
	$m = filter_var(urldecode($_POST['m']), FILTER_SANITIZE_STRING);
	$item_id = filter_var($_POST['item_id'], FILTER_SANITIZE_STRING);
	$name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
	$row = filter_var($_POST['row'], FILTER_SANITIZE_STRING);
	
	$filename = 'file';

	// Call Module
	$module = cms_utils::get_module($m);
	if (!$module) {
		throw new Exception("Module not exist");
	}

	// Upload File
	$result = generator_tools::handle_upload($module, $item_id, $filename, $error);

	if (!$result) {
		throw new Exception($error);
	} else {
		$response['data'] = [
			'filename' => $result,
			'url' => generator_tools::file_location($module, ['item_id' => $item_id]) . '/' . $result,
			'row' => $row
		];
	}
} catch (Exception $e) {
	$response['status'] = 'error';
	$response['data'] = [
		'msg' => $e->getMessage()
	];
}

cge_utils::send_ajax_and_exit($response);