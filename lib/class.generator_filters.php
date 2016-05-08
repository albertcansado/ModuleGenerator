<?php

#-------------------------------------------------------------------------
# Module: ModuleGenerator for CMS Made Simple (@kuzmany)
#
#-------------------------------------------------------------------------
# CMS - CMS Made Simple is (c) 2005 by Ted Kulp (wishy@cmsmadesimple.org)
# This project's homepage is: http://www.cmsmadesimple.org
#
#-------------------------------------------------------------------------
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
# Or read it online: http://www.gnu.org/licenses/licenses.html#GPL
#
#-------------------------------------------------------------------------
/**
 * Description of class
 *
 * @author @kuzmany
 */

class generator_filters {

    private $_type;
    private $_mod;
    private $_id;
    private $_isItem;
    private $_instance = null;
    public $custom_fields = array();

    public function __construct($id = null, $mod, $type = 'admin', $isItem = true) {

        if ($this->_instance != null && is_object($this->_instance))
            return $this->_instance;

        $this->_id = $id;
        $this->_type = $type;
        $this->_mod = $mod;
        $this->_isItem = $isItem;
        $this->_instance = $this;
    }

    public function process_custom_fields($blocktypes = array(), $custom_flds = array()) {

        if (is_array($custom_flds) && empty($custom_flds))
            $custom_flds = generator_fields::get_fields_for_filters($this->_mod, $blocktypes);



        if ($custom_flds) {
            foreach ($custom_flds as $custom_fld => $setting) {
                // get extra commands
                $instructions = generator_tools::get_extra($custom_flds[$custom_fld]['extra'], $this->_mod);

                switch ($setting['type']) {
                    case 'textbox':
                        $custom_flds[$custom_fld]['size'] = generator_tools::get_extra_size($instructions);
                        $custom_flds[$custom_fld]['max_length'] = generator_tools::get_extra_max_length($instructions);
                        break;

                    case 'dropdown':
                        $custom_flds[$custom_fld]['options'] = generator_tools::get_extra_options($instructions);
                        $custom_flds[$custom_fld]['allowAdd'] = generator_tools::get_extra_customBool($instructions, 'allowAdd');
                        $custom_flds[$custom_fld]['multiple'] = generator_tools::get_extra_customBool($instructions, 'multiple');
                        $custom_flds[$custom_fld]['size'] = generator_tools::get_extra_size($instructions);
                        break;

                    case 'dropdown_from_udt':
                        $custom_flds[$custom_fld]['udt'] = generator_tools::get_extra_moduleudt($instructions);
                        break;

                    case 'dropdownfrommodule':
                        $custom_flds[$custom_fld]['options'] = generator_tools::get_extra_moduleoptions($instructions, $mod);
                        break;

                    case 'module':
                        $custom_flds[$custom_fld]['select_default'] = generator_tools::get_extra_selectdefault($instructions);
                        $custom_flds[$custom_fld]['multiple'] = generator_tools::get_extra_customBool($instructions, 'multiple');
                        $custom_flds[$custom_fld]['params'] = generator_tools::get_extra_moduleparams($instructions);
                        $custom_flds[$custom_fld]['module_view'] = generator_tools::get_extra_module_view($instructions);
                        $custom_flds[$custom_fld]['size'] = generator_tools::get_extra_size($instructions);
                        break;

                    case 'module_link':
                        //$custom_flds[$custom_fld]['link'] = $instructions[0];
                        break;

                    case 'color_picker':
                        break;

                    case 'page':
                        $custom_flds[$custom_fld]['options'] = generator_tools::get_extra_page($instructions);
                        break;

                    case 'static':

                        break;

                    case 'tab':

                        break;

                    case 'checkbox':

                        break;

                    case 'textarea':
                        $custom_flds[$custom_fld]['cols'] = generator_tools::get_extra_cols($instructions);
                        $custom_flds[$custom_fld]['rows'] = generator_tools::get_extra_rows($instructions);
                        $custom_flds[$custom_fld]['max_length'] = generator_tools::get_extra_max_length($instructions);
                        $custom_flds[$custom_fld]['wysiwyg'] = generator_tools::get_extra_customBool($instructions, 'wysiwyg');
                        break;

                    case 'select_date':
                        $custom_flds[$custom_fld]['size'] = generator_tools::get_extra_size($instructions);
                        $custom_flds[$custom_fld]['max_length'] = generator_tools::get_extra_max_length($instructions);
                        $custom_flds[$custom_fld]['dateformat'] = generator_tools::get_extra_date_format($instructions);
                        break;

                    case 'upload_file':
                        $custom_flds[$custom_fld]['allow'] = generator_tools::get_extra_allow($instructions);
                        break;

                    case 'file_picker':
                        $custom_flds[$custom_fld]['params'] = generator_tools::get_extra_moduleparams($instructions);
                        break;

                    case 'select_file':
                        $custom_flds[$custom_fld]['allow'] = generator_tools::get_extra_allow($instructions);
                        $custom_flds[$custom_fld]['dir'] = generator_tools::get_extra_dir($instructions);
                        $custom_flds[$custom_fld]['exclude_prefix'] = generator_tools::get_extra_exclude_prefix($instructions);
                        break;
                    case 'key_value':
                        $custom_flds[$custom_fld]['keyName'] = generator_tools::get_extra_keyName($instructions);
                        $custom_flds[$custom_fld]['valueName'] = generator_tools::get_extra_valueName($instructions);
                        break;

                    case 'hr':
                        $custom_flds[$custom_fld]['br'] = generator_tools::get_extra_customBool($instructions, 'br');
                        break;

                    case 'lookup':
                        break;

                    case 'json':
                        $custom_flds[$custom_fld]['headers'] = generator_tools::get_extra_tableheaders($instructions);
                        break;
                    case 'video':
                        $custom_flds[$custom_fld]['size'] = generator_tools::get_extra_size($instructions);
                        $custom_flds[$custom_fld]['max_length'] = generator_tools::get_extra_max_length($instructions);
                        break;
                }
            }
        }
        $this->custom_fields = $custom_flds;
        return $this->custom_fields;
    }

    public static function process_fields_value(&$field = array()) {
        if (empty($field)) {
            return;
        }

        switch ($field['type']) {
            case 'key_value':
                $newValue = array();
                $auxValue = explode(';', $field['value']);
                foreach ($auxValue as $key => $v) {
                    $auxValue2 = explode(':', $v);
                    array_push($newValue, array(
                        'key' => rawurldecode($auxValue2[1]),
                        'value' => rawurldecode($auxValue2[2])
                    ));
                }
                break;
            case 'lookup':

                $values = array_map('strrev', explode(',', strrev($field['value']), 3));
                if (is_array($values) && count($values) == 3) {
                    $newValue = array(
                        'address' => $values[2],
                        'latitude' => $values[1],
                        'longitude' => $values[0],
                    );
                }
                break;
            case 'dropdown':
                $extra = explode(';', $field['extra']);
                $options = generator_tools::get_extra_options($extra);
                $multiple = generator_tools::get_extra_multiple($extra);

                if (!empty($multiple) && is_int($multiple)) {
                    // Mutiple options
                    $keys = explode(',', $field['value']);
                    $newValue = array();
                    foreach ($keys as $key) {
                        $newValue[] = array(
                            'key' => $key,
                            'value' => $options[$key]
                        );
                    }
                    $field['multiple'] = 1;
                } else {
                    // Single option
                    $newValue = array(
                        'key' => $field['value'],
                        'value' => $options[$field['value']]
                    );
                }
                break;
            case 'json':
                $newValue = array_values(json_decode($field['value'], true));
                break;
            case 'video':
                $newValue = json_decode($field['value'], true);
                break;
            case 'textbox':
            case 'dropdown_from_udt':
            case 'dropdownfrommodule':
            case 'module':
            case 'module_link':
            case 'color_picker':
            case 'page':
            case 'static':
            case 'tab':
            case 'checkbox':
            case 'textarea':
            case 'select_date':
            case 'upload_file':
            case 'file_picker':
            case 'select_file':
            case 'hr':
            default:
                $newValue = $field['value'];
                break;
        }

        return $newValue;
    }

    public function generate($item_id = null, $params = array(), $fieldvals = array(), $frontend = null) {

        if (!$this->custom_fields)
            return;

        $config = cmsms()->GetConfig();

        $custom_flds_obj = array();

        $id = $this->_id;
        $mod = $this->_mod;
        if (empty($fieldvals)) {
            $fieldvals = generator_fields::get_fields_values($mod, $item_id, $params);
        }

        foreach ($this->custom_fields as $custom_fld) {
            if (!$custom_fld['fielddef_id'])
                continue;

            $obj = new StdClass();
            $name = 'customfield[' . $custom_fld['fielddef_id'] . ']';
            $obj->fielddef_id = $custom_fld['fielddef_id'];
            $obj->prompt = ($custom_fld['required'] ? '*' : '') . $custom_fld['name'];
            $obj->label = $custom_fld['name'];
            $obj->alias = $custom_fld['alias'];
            $obj->editview = $custom_fld['editview'];
            $obj->hidename = $custom_fld['hidename'];
            $obj->name = $id . $custom_fld['alias'];
            $obj->type = $custom_fld['type'];

            $value = '';
            if (isset($fieldvals[$custom_fld['fielddef_id']])) {
                $value = $fieldvals[$custom_fld['fielddef_id']];
                if (is_array($value) && isset($value['value'])) {
                    $value = $value['value'];
                }
            }
            /*if ( is_array($fieldvals[$custom_fld['fielddef_id']]) ) {
                $value = (isset($fieldvals[$custom_fld['fielddef_id']]['value']) ? $fieldvals[$custom_fld['fielddef_id']]['value'] : '');
            } else {
                $value = (isset($fieldvals[$custom_fld['fielddef_id']]) ? $fieldvals[$custom_fld['fielddef_id']] : '');
            }*/
            #$value = (isset($fieldvals[$custom_fld['fielddef_id']]['value']) ? $fieldvals[$custom_fld['fielddef_id']]['value'] : '');
            #$value = (isset($fieldvals[$custom_fld['fielddef_id']]) ? $fieldvals[$custom_fld['fielddef_id']] : '');
            $obj->item_id = $item_id;
            $obj->value = $value;
            $obj->help = $custom_fld['help'];
            $obj->extra = $custom_fld['extra'];
            $obj->filter = $id . 'filter_' . $custom_fld['fielddef_id'] . '_equal';
            switch ($custom_fld['type']) {
                case 'textbox':
                    $size = !empty($custom_fld['size']) ? $custom_fld['size'] : 50;
                    $front_end_type = 'like';
                    if ($frontend) {
                        $name = 'filter_' . $custom_fld['fielddef_id'] . '_' . $front_end_type;
                        $value = (isset($params['filter_' . $custom_fld['fielddef_id'] . '_' . $front_end_type]) ? $params['filter_' . $custom_fld['fielddef_id'] . '_' . $front_end_type] : '');
                    }

                    if (!empty($custom_fld['max_length'])) {
                        $obj->field = $mod->CreateInputText($id, $name, $value, $size, $custom_fld['max_length']);
                    } else {
                        $obj->field = $mod->CreateInputText($id, $name, $value, $size);
                    }

                    break;

                case 'module':

                    $options = array();
                    $items = array();
                    if (is_array($custom_fld['params']) && !empty($custom_fld['params'])) {
                        if (($mod->GetName() != $custom_fld['params']["module"] && $module = cms_utils::get_module($custom_fld['params']["module"]) OR ($mod->GetName() == $custom_fld['params']["module"] && $module = $mod))) {
                            unset($custom_fld['params']["module"]);
                            $custom_fld['params']["items"] = true;
                            $module->DoAction('default', $id, $custom_fld['params'], '');

                            $items = generator_tools::$items;
                            $obj_has_key = (!empty($custom_fld['module_view'])) ? $custom_fld['module_view'] : 'title';
                            $options = extended_tools_opts::object_to_hash($items, $obj_has_key, 'item_id');

                            generator_tools::$items = null;
                        }
                    }

                    if (isset($custom_fld['multiple']) && (bool)$custom_fld['multiple'] && !$frontend) {
                        $size = (!empty($custom_fld['size'])) ? (int)$custom_fld['size'] : 6;
                        if (isset($custom_fld['select_default'])) {
                            $options = array($custom_fld['select_default'] => '') + $options;
                            $obj->field = $mod->CreateInputSelectList($id, $name . "[]", $options, (!empty($value) ? explode(',', $value) : array(0 => '')), $size);
                        } else {
                            $obj->field = $mod->CreateInputHidden($id, $name . "[]", '') . $mod->CreateInputSelectList($id, $name . "[]", $options, (!empty($value) ? explode(',', $value) : array(0 => '')), $size);
                        }
                    } else {
                        $options = array($this->_mod->Lang('select_default') => '') + $options;

                        $front_end_type = 'equal';
                        if (isset($custom_fld['multiple']) && is_int($custom_fld['multiple']))
                            $front_end_type = 'inset';

                        if ($frontend) {
                            $name = 'filter_' . $custom_fld['fielddef_id'] . '_' . $front_end_type;
                            $value = (isset($params['filter_' . $custom_fld['fielddef_id'] . '_' . $front_end_type]) ? $params['filter_' . $custom_fld['fielddef_id'] . '_' . $front_end_type] : '');
                            $obj->filter = $id . $name;
                        }
                        $obj->value = $value;
                        $obj->items = $items;
                        $obj->options = $options;
                        $obj->field = $mod->CreateInputDropdown($id, $name, $options, -1, $value);
                    }
                    break;
                case 'dropdown':
                    $isMultiple = (isset($custom_fld['multiple']) && is_int($custom_fld['multiple']) && !$frontend);
                    $size = (!empty($custom_fld['size'])) ? (int)$custom_fld['size'] : 6;

                    $options = array();
                    if (!$isMultiple) {
                        $options[$this->_mod->Lang('select_default')] = '';
                    }

                    if (is_array($custom_fld['options']) && !empty($custom_fld['options'])) {
                        $options = $options + array_flip($custom_fld['options']);
                    }

                    $front_end_type = 'equal';

                    if ($frontend) {
                        $name = 'filter_' . $custom_fld['fielddef_id'] . '_' . $front_end_type;
                        $value = (isset($params['filter_' . $custom_fld['fielddef_id'] . '_' . $front_end_type]) ? $params['filter_' . $custom_fld['fielddef_id'] . '_' . $front_end_type] : '');
                    }

                    $obj->options = $options;

                    if ($isMultiple) {
                        $obj->field = $mod->CreateInputSelectList($id, $name, $options, explode(',', $obj->value), $size);
                    } else {
                        $obj->field = $mod->CreateInputDropdown($id, $name, $options, -1, $value);
                    }

                    if ((bool)$custom_fld['allowAdd']) {
                        $button_tpl = '<button class="{{class}}" data-field="{{field}}" data-fieldName="{{fieldName}}">
                            <span class="ui-button-icon-primary ui-icon ui-icon-plus"></span>
                            <span class="ui-button-text">{{text}}</span>
                            </button>';
                        $button = str_replace(
                            array(
                                '{{class}}',
                                '{{field}}',
                                '{{text}}',
                                '{{fieldName}}'
                            ),
                            array(
                                'dropdown__btn ui-button ui-widget ui-state-default ui-corner-all ui-button-text-icon-primary js-dropdown-add',
                                $custom_fld['fielddef_id'],
                                'Add',//$this->_mod->Lang('Add')
                                $custom_fld['name']
                            ),
                            $button_tpl
                        );

                        //$script = '<script>new DropdownAdd({el: document.querySelector("#' . $selectorName . '")});</script>';
                        $obj->field = '<div>' . $obj->field . $button . '</div>';
                    }

                    break;
                case 'dropdown_from_udt':
                    $options = array();
                    $options[$this->_mod->Lang('select_default')] = '';
                    $optionstmp = UserTagOperations::get_instance()->CallUserTag($custom_fld['udt'], $tmp);
                    if (is_array($optionstmp) && empty($optionstmp) == false)
                        $options = $options + $optionstmp;


                    $front_end_type = 'equal';

                    if ($frontend) {
                        $name = 'filter_' . $custom_fld['fielddef_id'] . '_' . $front_end_type;
                        $value = (isset($params['filter_' . $custom_fld['fielddef_id'] . '_' . $front_end_type]) ? $params['filter_' . $custom_fld['fielddef_id'] . '_' . $front_end_type] : '');
                    }

                    $obj->options = $options;
                    $obj->field = $mod->CreateInputDropdown($id, $name, $options, -1, $value);
                    break;

                case 'dropdownfrommodule':
                    $options = array();
                    $options[$this->_mod->Lang('select_default')] = 'select_default';
                    if (is_array($custom_fld['options']) && !empty($custom_fld['options'])) {
                        $options = array_merge($options, array_flip($custom_fld['options']));
                    }

                    $obj->field = $mod->CreateInputDropdown($id, $name, $options, -1, $value);
                    break;

                case 'page':
                    $options = array();
                    $options[$this->_mod->Lang('select_default')] = 'select_default';
                    if (is_array($custom_fld['options']) && !empty($custom_fld['options'])) {
                        $options = array_merge($options, array_flip($custom_fld['options']));
                    }

                    $obj->field = $mod->CreateInputDropdown($id, $name, $options, -1, $value);
                    break;

                case 'color_picker':
                    $txt = '';
                    $tmp = '<input type="color" data-hex="true" name="%s" value="%s"/>';
                    $txt .= sprintf($tmp, $id . $name, $value);
                    $obj->field = $txt;
                    break;

                case 'module_link':
                    $obj->field = $custom_fld['link'];
                    break;

                case 'checkbox':
                    $front_end_type = 'equal';
                    if ($frontend) {
                        $name = 'filter_' . $custom_fld['fielddef_id'] . '_' . $front_end_type;
                        $value = (isset($params['filter_' . $custom_fld['fielddef_id'] . '_' . $front_end_type]) ? $params['filter_' . $custom_fld['fielddef_id'] . '_' . $front_end_type] : '');
                    }
                    $obj->field = $mod->CreateInputHidden($id, $name, '0') . $mod->CreateInputCheckbox($id, $name, '1', $value);
                    break;

                case 'textarea':
                    $obj->field = $mod->CreateTextArea($custom_fld['wysiwyg'], $id, $value, $name, '', '', '', '', $custom_fld['cols'], $custom_fld['rows']);
                    break;

                case 'static':
                    $obj->field = false;
                    break;

                case 'tab':
                    $obj->field = false;
                    break;

                case 'select_date':

                    $size = !empty($custom_fld['size']) ? $custom_fld['size'] : 20;

                    $selector = '#' . $id . str_replace(array('[', ']'), array('\\\[', '\\\]'), $name);
                    $datepicker = '
<script type="text/javascript">
jQuery(document).ready(function($){
    $("' . $selector . '").datepicker({' . (!empty($custom_fld['dateformat']) ? 'dateFormat: \'' . htmlentities($custom_fld['dateformat']) . '\'' : '') . '});
});
</script>';

                    if (!empty($custom_fld['max_length'])) {
                        $obj->field = $datepicker . $mod->CreateInputText($id, $name, $value, $size, $custom_fld['max_length']);
                    } else {
                        $obj->field = $datepicker . $mod->CreateInputText($id, $name, $value, $size);
                    }

                    break;

                case 'upload_file':
                    $name = 'customfield_' . $custom_fld['fielddef_id'];
                    if ($value != '') {
                        $obj->delete_file = $mod->CreateInputCheckbox($id, 'delete_customfield[' . $custom_fld['fielddef_id'] . ']', 'delete') . ' ' . $this->_mod->Lang('delete');
                        $obj->file_location = generator_tools::file_location($mod, array('item_id' => $item_id), $this->_isItem);
                        $obj->filepath_location = generator_tools::filepath_location($mod, array('item_id' => $item_id), $this->_isItem);
                        $obj->is_image = getImageSize(cms_join_path(generator_tools::filepath_location($mod, array('item_id' => $item_id), $this->_isItem), $value));
                        $obj->filename = $value;
                    }

                    $obj->field = $mod->CreateFileUploadInput($id, $name);

                    break;

                case 'select_file':
                    $p = cms_join_path($config['uploads_path'], $custom_fld['dir']);

                    if (!is_dir($p)) {
                        $res = @mkdir($p);
                    }

                    $images = array();
                    $images[$this->_mod->Lang('select_default')] = 'select_default';

                    $allowed = (!empty($custom_fld['allow']) ? $custom_fld['allow'] : array());

                    if (empty($allowed) == false) {
                        if ($handle = opendir($p)) {
                            while (false !== ($file = readdir($handle))) {
                                if ($file != '.' && $file != '..'
                                        && !generator_tools::has_prefix($file, $custom_fld['exclude_prefix'])
                                ) {
                                    $ext = strtolower(substr(strrchr($file, '.'), 1));

                                    if (in_array($ext, $allowed)) {
                                        $images[$file] = $file;
                                    }
                                }
                            }

                            closedir($handle);
                        }
                    }

                    $obj->field = $mod->CreateInputDropdown($id, $name, $images, -1, $value);
                    break;


                case 'file_picker':
                    $custommoduleparams = (!empty($custom_fld['params']) ? $custom_fld['params'] : array());

                    if (empty($custommoduleparams)) {
                        $module = cms_utils::get_module('GBFilePicker');
                        $custommoduleparams["dir"] = generator_tools::filepicker_location($mod);
                        $custommoduleparams["mode"] = 'browser';
                        $custommoduleparams["media_type"] = 'file';
                    } else {
                        $module = cms_utils::get_module($custommoduleparams["module"]);
                        unset($custommoduleparams["module"]);
                    }
                    //'dir' => generator_tools::filepicker_location($mod)
                    if ($module) {
                        if ($module->GetName() == 'GBFilePicker') {
                            $obj->field = $module->CreateFilePickerInput($mod, $id, $name, $value, $custommoduleparams
                            );
                        }
                    }
                    break;

                case 'key_value':
                    $admintheme = cmsms()->get_variable('admintheme');
                    $themeImgPath = "themes/{$admintheme->themeName}/images/icons/";

                    $size = !empty($custom_fld['size']) ? $custom_fld['size'] : 50;
                    $max_length = !empty($custom_fld['max_length']) ? $custom_fld['max_length'] : '255';

                    $keyName = !empty($custom_fld['keyName']) ? $custom_fld['keyName'] : $mod->Lang("keyValue_key_name");
                    $valueName = !empty($custom_fld['valueName']) ? $custom_fld['valueName'] : $mod->Lang("keyValue_value_name");


                    $kv_table = '<table cellspacing="0" class="pagetable cms_sortable tablesorter js-tablesorter" id="kv-table' . $custom_fld['fielddef_id'] .'">
                    <colgroup>
                        <col span="2">
                        <col span="3" style="width: 50px;">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>' . $keyName . '</th>
                            <th>' . $valueName . '</th>
                            <th class="pageicon {sorter: false}" style="width: 50px;">&nbsp;</th>
                            <th class="pageicon {sorter: false}" style="width: 50px;">&nbsp;</th>
                            <th class="pageicon {sorter: false}" style="width: 50px;">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>';
                    if ( !empty($value) ) {
                        $values = explode(';', $value);
                        $rowclass = 'row1';
                        foreach ($values as $key => $v) {
                            $aux = explode(':', $v);
                            $kv_table .= '<tr class="' . $rowclass . '" data-id="' . $aux[0] . '">';
                            $kv_table .= '<td class="kv-col0">' . rawurldecode($aux[1]) . '</td>';
                            $kv_table .= '<td class="kv-col1">' . rawurldecode($aux[2]) . '</td>';
                            $kv_table .= '<td class="kv-col2"><input type="checkbox" /></td>';
                            $kv_table .= '<td class="kv-col3"><a href="#" class="kv-edit">' . $admintheme->DisplayImage("icons/system/edit.gif") . '</a></td>';
                            $kv_table .= '<td class="kv-col4"><a href="#" class="kv-delete">' . $admintheme->DisplayImage("icons/system/delete.gif") . '</a></td>';
                            $kv_table .= '</tr>';
                            $rowclass = ($rowclass == 'row1' ? 'row2' : 'row1');
                        }
                    }
                    $kv_table .= '</tbody></table>';

                    $kv_html = '<input type="hidden" id="kv-hidden' . $custom_fld['fielddef_id'] . '" name="' . $id . $name . '" value="' . $value  .'" />';
                    $kv_html .= $mod->CreateInputTextWithLabel($id, 'label'.$custom_fld['fielddef_id'], '', $size, $max_length, '', $keyName) . '<br>';
                    $kv_html .= $mod->CreateInputTextWithLabel($id, 'value'.$custom_fld['fielddef_id'], '', $size, $max_length, '', $valueName) . '<input type="checkbox" id="kv-title' . $custom_fld['fielddef_id'] .'" />' . $mod->Lang('keyValue_checkbox_name') . '<br>';
                    $kv_html .= '<button id="' . $id .'kv-save' . $custom_fld['fielddef_id'] .'" class="ui-button ui-state-default ui-corner-all ui-button-text-icon-primary"><span class="ui-button-icon-primary ui-icon ui-icon-disk"></span><span class="ui-button-text">' . $mod->Lang("keyValue_add") . '</span></button>';

                    $kv_saveOrder = '<button id="kv-saveOrder' . $custom_fld['fielddef_id'] . '" class="ui-button ui-state-default ui-corner-all ui-button-text-icon-primary"><span class="ui-button-icon-primary ui-icon ui-icon-disk"></span><span class="ui-button-text">' . $mod->Lang("save_order") . '</span></button>';

                    $kv_script = '<script>var a = new KeyValue({
                        table: document.getElementById("kv-table' . $custom_fld['fielddef_id'] . '"),
                        inputLabel: document.getElementById("' . $id .'label' . $custom_fld['fielddef_id'] .'"),
                        inputValue: document.getElementById("' . $id .'value' . $custom_fld['fielddef_id']. '"),
                        checkTitle: document.getElementById("kv-title' . $custom_fld['fielddef_id'] .'"),
                        btn: document.getElementById("' . $id . 'kv-save' . $custom_fld['fielddef_id']. '"),
                        saveOrder: document.getElementById("kv-saveOrder' . $custom_fld['fielddef_id'] . '"),
                        hiddenInput: document.getElementById("kv-hidden' . $custom_fld['fielddef_id'] . '"),
                        icons: {
                            deleteIcon: "' . $themeImgPath . 'system/delete.gif",
                            editIcon: "' . $themeImgPath . 'system/edit.gif",
                            cancelIcon: "' . $themeImgPath . 'extra/red.gif",
                            saveIcon: "' . $themeImgPath . 'extra/green.gif",
                        },
                        emptyText: "' . $mod->Lang('keyValue_empty_table') . '"
                    });</script>';

                    $obj->field = $kv_html . $kv_table . $kv_saveOrder . $kv_script;

                    break;

                case 'hr':
                    $obj->field = ($custom_fld['br']) ? '<br/>' : '';
                    $obj->field .= '<hr style="display:block; border:0 none; background:#ccc;" />';
                    break;

                case 'lookup':
                    $lu_extra = '';
                    $lu_script = '';

                    $values = array_map('strrev', explode(',', strrev($value), 3));

                    $tmp = '<input type="text" id="%s" class="cms_textfield" name="%s" value="%s" size="50" maxlength="255" />';
                    $fieldName = $id . $name . '[]';
                    $lu_field = sprintf($tmp, 'lookup_input' . $custom_fld['fielddef_id'], $fieldName, (is_array($values) && isset($values[2])) ? $values[2] : $value);

                    if ( !generator_tools::can_geolocate() ) {
                        $obj->help = $mod->Lang('geolocate_module_required');
                    } else {
                        $tmp1 = '<label>%s</label>&nbsp;&nbsp;<input type="text" id="%s" class="cms_textfield" name="%s" value="%s" size="20" maxlength="50" readonly />&nbsp;&nbsp;&nbsp;';

                        $lu_extra .= '<button id="lookup_save' . $custom_fld['fielddef_id'] .'" class="ui-button ui-state-default ui-corner-all ui-button-text-icon-primary"><span class="ui-button-icon-primary ui-icon ui-icon-arrow-4"></span><span class="ui-button-text">' . $mod->Lang("lookup_calculate") . '</span></button><br />';
                        $lu_extra .= sprintf($tmp1, $mod->Lang("lookup_latitude"), 'lookup_lat' . $custom_fld['fielddef_id'],  $fieldName, (is_array($values) && isset($values[1])) ? $values[1] : '');
                        $lu_extra .= sprintf($tmp1, $mod->Lang("lookup_longitude"), 'lookup_lon' . $custom_fld['fielddef_id'],  $fieldName, (is_array($values) && isset($values[0])) ? $values[0] : '');

                        $lu_script = '<script>new Lookup({
                            url: "' . str_replace('amp;','', $mod->CreateLink($id, 'geolocate', '', '', array('showtemplate' => 'false', 'address' => ''), '', true)) .'",
                            input: document.getElementById("lookup_input' . $custom_fld['fielddef_id'] .'"),
                            btn: document.getElementById("lookup_save' . $custom_fld['fielddef_id'] . '"),
                            lon: document.getElementById("lookup_lon' . $custom_fld['fielddef_id'] . '"),
                            lat: document.getElementById("lookup_lat' . $custom_fld['fielddef_id'] . '"),
                        });
                        </script>';
                    }

                    $obj->field = $lu_field . $lu_extra . $lu_script;
                    break;

                case 'json':
                    if (empty($custom_fld['headers'])) {
                        $obj->field = $mod->Lang('json_empty_headers');
                        break;
                    }

                    $admintheme = cmsms()->get_variable('admintheme');
                    $themeImgPath = "themes/{$admintheme->themeName}/images/icons/";

                    $selector = 'js-jttable' . $custom_fld['fielddef_id'];

                    // Generate Header
                    $tableHeadContent = array();
                    foreach ($custom_fld['headers'] as $key => $value) {
                        $tableHeadContent[] = '<th>' . $value . '</th>';
                    }
                    $tableHeadContent[] = '<th style="width: 75px;"></th>';

                    $tableHead = str_replace(
                        '{{content}}',
                        implode('', $tableHeadContent),
                        '<thead><tr>{{content}}</tr></thead>'
                    );

                    // Generate body

                    $emptyRow = str_replace(
                        array('{{colspan}}', '{{text}}'),
                        array(count($tableHeadContent), $mod->Lang('json_empty_row')),
                        '<tr class="jt-empty-row"><td colspan="{{colspan}}">{{text}}</td></tr>'
                    );

                    $tableBody = str_replace(
                        '{{content}}',
                        $emptyRow,
                        '<tbody>{{content}}</tbody>'
                    );

                    // Generate table
                    $table = str_replace(
                        array(
                            '{{id}}',
                            '{{thead}}',
                            '{{tbody}}'
                        ),
                        array(
                            'jttable' . $custom_fld['fielddef_id'],
                            $tableHead,
                            $tableBody
                        ),
                        '<table cellspacing="0" class="pagetable jt-table" id="{{id}}">{{thead}}{{tbody}}</table>'
                    );
                    $addBtn = '<button class="ui-button ui-state-default ui-corner-all ui-button-text-icon-primary jt-add"><span class="ui-button-icon-primary ui-icon ui-icon-disk"></span><span class="ui-button-text">' . $mod->Lang("json_add") . '</span></button>';
                    $orderBtn = '<button class="ui-button ui-state-default ui-corner-all ui-button-text-icon-primary jt-order"><span class="ui-button-icon-primary ui-icon ui-icon-disk"></span><span class="ui-button-text">' . $mod->Lang("save_order") . '</span></button>';

                    $hiddenTextarea = '<textarea name="' . $id . $name . '" class="jt-textarea hidden" style="display: none">' . $obj->value . '</textarea>';

                    /*$jsHeaders = array_map(function ($mItem) {
                        return "'" . $mItem . "'";
                    }, array_keys($custom_fld['headers']));*/
                    $headers = array_keys($custom_fld['headers']);
                    $jsHeaders = '';
                    foreach ($headers as $head) {
                        if (!empty($jsHeaders)) {
                            $jsHeaders .= ',';
                        }
                        $jsHeaders .= "'" . $head . "'";
                    }

                    $js = str_replace(
                        array('{{selector}}', '{{iconEdit}}', '{{iconRemove}}', '{{iconCancel}}', '{{iconSave}}', '{{headers}}'),
                        array(
                            '.' . $selector,
                            $themeImgPath . 'system/edit.gif',
                            $themeImgPath . 'system/delete.gif',
                            $themeImgPath . 'extra/red.gif',
                            $themeImgPath . 'extra/green.gif',
                            $jsHeaders
                        ),
                        '<script>new jsTable({selector: "{{selector}}",icons: {edit: "{{iconEdit}}",remove: "{{iconRemove}}",cancel: "{{iconCancel}}",save: "{{iconSave}}"}, headers: [{{headers}}], oddClass: "row1", evenClass: "row2"});</script>'
                    );

                    $obj->field = str_replace(
                        array('{{selector}}', '{{addBtn}}', '{{table}}', '{{orderBtn}}', '{{textarea}}', '{{js}}'),
                        array($selector, $addBtn, $table, $orderBtn, $hiddenTextarea, $js),
                        '<div class="{{selector}}">{{addBtn}}{{table}}{{orderBtn}}{{textarea}}{{js}}</div>'
                    );
                    break;
                case 'video':

                    if (!empty($value)) {
                        $value = json_decode($value, true);
                    } else {
                        $value = array('id' => '', 'type' => -1);
                    }

                    // Input Text
                    $size = !empty($custom_fld['size']) ? $custom_fld['size'] : 50;
                    $maxLength = !empty($custom_fld['max_length']) ? $custom_fld['max_length'] : 255;
                    $input = $mod->CreateInputText($id, $name . '[id]', $value['id'], $size, $maxLength);

                    // Select
                    $options = array(
                        'Youtube' => 'youtube',
                        'Vimeo' => 'vimeo'
                    );
                    $select = $mod->CreateInputDropdown($id, $name . '[type]', $options, -1, $value['type']);

                    $obj->field = str_replace(
                        array('{{labelId}}', '{{helpId}}', '{{input}}', '{{labelType}}', '{{select}}'),
                        array(
                            $mod->Lang('video_label_id'),
                            $mod->Lang('video_help_id'),
                            $input,
                            $mod->Lang('video_label_type'),
                            $select
                        ),
                        '<div class="row"><div class="grid_6"><label>{{labelId}}<span class="label__helper">{{helpId}}</span></label><br>{{input}}</div><div class="grid_6"><label>{{labelType}}</label><br>{{select}}</div></div>'
                    );
                    break;
            }

            $custom_flds_obj[$obj->alias] = $obj;
        }

        return $custom_flds_obj;
    }

    public function process(array &$countjoins, array &$joins, array &$where, array &$paramarray) {

    }

}

?>
