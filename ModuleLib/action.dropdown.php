<?php
//if( !isset($gCms) ) exit;

/*var_dump($params['f']);
var_dump($params['k']);
var_dump($params['n']);*/

function searchValueStartWith($array = array(), $neddle = '') {
    $size = count($array);
    $found = false;
    $k = -1;
    while ($k < $size && !$found) {
        $k++;
        $haystack = $array[$k];
        $found = strpos($haystack, $neddle) === 0;
        
    }

    return (!$found) ? -1 : $k;
}

function response($data = array(), $status = 'success') {
    // Clear handlers
    $handlers = ob_list_handlers();
    for ($cnt = 0; $cnt < sizeof($handlers); $cnt++) { ob_end_clean(); }
    
    // Push Headers
    header('Pragma: public');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Cache-Control: private', false);
    header('Content-Type: application/json');
    
    // Send Content
    echo json_encode(compact('status', 'data'));
}

try {

    if (empty($params['n'])) {
        throw new Exception("name is required");
    }

    $name = $params['n'];
    $key = !empty($params['k']) ? $params['k'] : $name;
    $key = generator_tools::generate_alias($key); // Generate valid alias

    // Find fielddef
    $query = 'SELECT extra FROM ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fielddef WHERE fielddef_id = ? LIMIT 1';
    $result = $db->GetRow($query, array($params['f']));

    if (!$result) {
        throw new Exception(sprintf("fielddef %s not found", $params['f']));
    }

    // extract options from extra field
    $extra = (!empty($result['extra'])) ? explode(';', $result['extra']) : array();
    $options = (!empty($extra)) ? generator_tools::get_extra_options($extra) : array();

    // Find if key exists
    if (array_key_exists($key, $options)) {
        throw new Exception(sprintf("option %s already exist", $name));
    }

    // Set option
    $options[$key] = $name;
    
    // 
    $optionsAux = '';
    foreach ($options as $key => $value) {
        if (!empty($optionsAux)) {
            $optionsAux .= ',';
        }
        $optionsAux .= $key . '=' . $value;
    }
    
    $extra[searchValueStartWith($extra, 'options')] = 'options[' . $optionsAux . ']';


    // Update extra fielddef extra field
    $queryUpdate = 'UPDATE ' . cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fielddef SET extra = ? WHERE fielddef_id = ?';
    $result = $db->Execute($queryUpdate, array(implode(';', $extra), $params['f']));
    if (!$result) {
        throw new Exception("error updating options");
    }

    // Send event
    @$this->SendEvent('DropdownOptionAdded', array('fielddef_id' => $params['f'], 'key' => $key, 'name' => $name, 'module' => $this->GetName()));

    response(compact('key', 'name'));

} catch (Exception $e) {
    response(array(
        'msg' => $e->getMessage()
    ), 'error');
}

exit;