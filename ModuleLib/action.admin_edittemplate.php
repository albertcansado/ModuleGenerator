<?php
if (!$this->CheckPermission($this->_GetModuleAlias() . '_modify_option'))
	return;

if (isset($params['cancel'])) {
	$params = array(
		'active_tab' => 'templatetab'
	);
	$this->Redirect($id, 'defaultadmin', $returnid, $params);
}

$tpl_id       = null;
$templatename = '';
$templatetext = '';
$errors       = array();

// if $params['tpl_id'] is supplied and exists, populate with values from database
if (isset($params['tpl_id'])) {
	$tpl_id = (int) $params['tpl_id'];
	$query  = 'SELECT name, content FROM ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_template WHERE tpl_id = ?';
	$result = $db->Execute($query, array(
		$tpl_id
	));
	
	if ($result && $row = $result->FetchRow()) {
		$templatename = $row['name'];
		$templatetext = $row['content'];
	}
}

// handle submit or apply
if (isset($params['submit']) || isset($params['apply'])) {
	$templatename = $params['template_name'];
	$templatetext = $params['template_text'];
	if (!isset($tpl_id)) {
		$templatetype = $params['template_type'];
	}
	
	if ($templatename == '') {
		$errors[] = $this->Lang('template_name_empty');
	}
	
	if (empty($errors)) {
		if (isset($tpl_id)) {
			$query  = 'UPDATE ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_template SET name = ?, content=? WHERE tpl_id = ?';
			$result = $db->Execute($query, array(
				$templatename,
				$templatetext,
				$tpl_id
			));
			if (!$result)
				die('FATAL SQL ERROR: ' . $db->ErrorMsg() . '<br/>QUERY: ' . $db->sql);
		} else {
			$query  = 'INSERT INTO ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_template (name, content, type) VALUES (?, ?, ?)';
			$result = $db->Execute($query, array(
				$templatename,
				$templatetext,
				$templatetype
			));
			if (!$result)
				die('FATAL SQL ERROR: ' . $db->ErrorMsg() . '<br/>QUERY: ' . $db->sql);
			
			// populate $tpl_id for newly inserted item
			$tpl_id = $db->Insert_ID();
		}
		
		if (!isset($params['apply'])) {
			$params = array(
				'tab_message' => 'changessaved',
				'active_tab' => 'templatetab'
			);
			$this->Redirect($id, 'defaultadmin', '', $params);
		} else {
			echo $this->ShowMessage($this->Lang('changessaved'));
		}
	}
}

// display errors if there are any
if (!empty($errors)) {
	echo $this->ShowErrors($errors);
}

if (isset($tpl_id)) {
	$smarty->assign('idfield', $this->CreateInputHidden($id, 'tpl_id', $tpl_id));
}

$smarty->assign('title', (isset($tpl_id) ? $this->Lang('edittemplate') : sprintf($this->Lang('addtemplate'), ucfirst($params['type']))));
$smarty->assign('startform', $this->CreateFormStart($id, 'admin_edittemplate', $returnid));
$smarty->assign('endform', $this->CreateFormEnd());

$smarty->assign('prompt_template', $this->Lang('template'));
$smarty->assign('input_template', $this->CreateTextArea(false, $id, $templatetext, 'template_text'));

$smarty->assign('prompt_name', $this->Lang('template_name'));
$smarty->assign('input_name', $this->CreateInputText($id, 'template_name', $templatename, 40));

if (!isset($tpl_id)) {
	$smarty->assign('input_type', $this->CreateInputHidden($id, 'template_type', $params['type']));
}

$smarty->assign('submit', $this->CreateInputSubmit($id, 'submit', lang('submit')));
$smarty->assign('apply', $this->CreateInputSubmit($id, 'apply', lang('apply')));
$smarty->assign('cancel', $this->CreateInputSubmit($id, 'cancel', lang('cancel')));

echo $this->ModProcessTemplate('edittemplate.tpl');

?>