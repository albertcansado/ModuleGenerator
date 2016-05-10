<?php

#BEGIN_LICENSE
#-------------------------------------------------------------------------
# Module:   @kuzmany (kuzmany.biz)
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
# However, as a special exception to the GPL, this software is distributed
# as an addon module to CMS Made Simple.  You may not use this software
# in any Non GPL version of CMS Made simple, or in any version of CMS
# Made simple that does not indicate clearly and obviously in its admin 
# section that the site was built with CMS Made simple.
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
#END_LICENSE

$fielddefs = '';
$tmp = generator_tools::get_fields($this, false);

if (is_array($tmp)) {
    $fielddefs = array();
    for ($i = 0; $i < count($tmp); $i++) {
        $obj = cge_array::to_object($tmp[$i]);
        $fielddefs[$obj->alias] = $obj;
    }
}



$query = '';
$query2 = '';
$count = '';
$page = '';
$startelement = '';
$limit = 99999;
$itemlist = '';
$sorttype = '';
$countjoins = array();
$joins = array();
$sortfield = 1;
unset($params['assign']);
$inline = $this->GetPreference('display_inline', false);
$default_detailpage = $this->GetPreference('item_detail_returnid', '');
$detailpage = $default_detailpage;
if (!isset($params["detailpage"]) && isset($params["returnid"]))
    $params["detailpage"] = $params["returnid"];
$detailpage = extended_tools_opts::detailpage($params, $detailpage);
if ($detailpage == $default_detailpage)
    unset($params["detailpage"]);

$detailtemplate = '';
$filters = array();
$fields = array();
$fields2 = array();
$date_to = '';
$date_from = '';
$category_id = -1;
$paramarraytmp = array();
$groupby = '';


// template
$thetemplate = 'summary_template' . $this->GetPreference('current_summary_template');
if (isset($params['summarytemplate'])) {
    $thetemplate = 'summary_template' . $params['summarytemplate'];
}

// template
if (isset($params['detailtemplate']))
    $detailtemplate = $params['detailtemplate'];

$cache_id = 's' . $this->GetName() . md5(serialize($params));
$compile_id = '';

if (!$smarty->isCached($this->GetDatabaseResource($thetemplate), $cache_id, $compile_id)) {
    if (isset($params['inline'])) {
        $inline = $params['inline'];
    }


// date from
    if (isset($params['datefrom'])) {
        $date_from = $params['datefrom'];
    }
    if (isset($params['datefromMonth'])) {
        $date_from = mktime(0, 0, 0, $params['datefromMonth'], $params['datefromDay'], $params['datefromYear']);
    }
    if (isset($params['dateto'])) {
        $date_to = $params['dateto'];
    }
    if (isset($params['datetoMonth'])) {
        $date_to = mktime(23, 59, 59, $params['datetoMonth'], $params['datetoDay'], $params['datetoYear']);
    }

    if (isset($params['item_date_from_to'])) {
        $date_from_to = explode('|', $params['item_date_from_to']);
        if (count($date_from_to) == 2) {
            $date_from = generator_tools::process_filter_date($date_from_to[0]);
            $date_to = generator_tools::process_filter_date($date_from_to[1]);
        }
    }

//category id
    if (isset($params["category_id"])) {
        $category_id = intval($params["category_id"]);
    }


    if (isset($params['itemlist'])) {
        $itemlist = $params['itemlist'];
        unset($params['itemlist']);
        if (!is_array($itemlist)) {
            $itemlist = explode(',', $itemlist);
        }
    }
    if (!(is_array($itemlist) && count($itemlist) > 0)) {
        // we don't have an explicit product list, so gotta build a query
        // from other parameters
        $sortorder = $this->GetPreference('sortorder_' . $this->GetPreference('mode'), 'desc');
        if (isset($params['sortorder'])) {
            switch (strtolower($params['sortorder'])) {
                case 'asc':
                case 'desc':
                    $sortorder = $params['sortorder'];
            }
        }

        $sortby = $this->GetPreference('sortby_' . $this->GetPreference('mode'), 'position');
        if (isset($params['sortby'])) {
            $tmp = strtolower(trim($params['sortby']));
        } else {
            $tmp = $sortby;
        }
        switch ($tmp) {
            case 'id':
                $sortby = 'id';
                break;
            case 'item_date':
                $sortby = 'item_date';
                break;
            case 'position':
                $sortby = 'position';
                break;
            case 'title':
                $sortby = 'title';
                break;
            case 'category':
                $sortby = 'category_id';
                break;
            case 'created':
                $sortby = 'create_date';
                break;
            case 'modified':
                $sortby = 'modified_date';
                break;
            case 'random':
                $sortby = 'RAND()';
                $sortorder = '';
                break;
            default:
                if (startswith($tmp, 'f:')) {
                    $fieldname = substr($tmp, strlen('f:'));
                    if (isset($fielddefs[$fieldname])) {
                        $fieldid = $fielddefs[$fieldname]->fielddef_id;
                        $as = 'FV' . $sortfield++;
                        $joins[] = cms_db_prefix() . "module_" . $this->_GetModuleAlias() . "_fieldval {$as} ON c.item_id = {$as}.item_id AND $as.fielddef_id = '{$fieldid}'";
                        $sortby = "{$as}.value";
                    }
                }
                break;
        }

        if ($sortby == 'random') {
            $sortby = 'RAND()';
            $sortorder = '';
        }

        if (isset($params['sorttype'])) {
            $tmp = trim($params['sorttype']);
            $tmp = strtoupper($tmp);
            switch ($tmp) {
                case 'STRING':
                    $sorttype = '';
                    break;
                case 'SIGNED':
                case 'UNSIGNED':
                    $sorttype = $tmp;
            }
        }
        $limit = $this->GetPreference('summary_pagelimit_' . $this->GetPreference('mode'), 10000);
        if (isset($params['pagelimit']) && $params['pagelimit'] > 0) {
            $limit = (int) $params['pagelimit'];
        }

        $limit = max($limit, 1);
        $limit = min($limit, 10000);


        $page = 1;
        if (isset($params['page'])) {
            $page = (int) $params['page'];
            if ($page < 1)
                $page = 1;
        }
        $startelement = ($page - 1) * $limit;

        $category = '';
        if (isset($params['category'])) {
            $category = trim($params['category']);
        }
        $categoryid = -100;
        if (isset($params['categoryid'])) {
            $categoryid = (int) $params['categoryid'];
        }

// category_alias
        $category_alias = '';
        if (isset($params['category_alias'])) {
            $categoryalias = trim($params['category_alias']);
        }

        $fieldid = -100;
        if (isset($params['fieldid'])) {
            $fieldid = (int) $params['fieldid'];
        }
        $fieldval = '';
        if (isset($params['fieldval'])) {
            $fieldval = trim($params['fieldval']);
        }

        //Get Category fields
        /*$categoryFields = false;
        if (isset($params['category_fields'])) {
            $categoryFields = (bool)$params['category_fields'];
        }*/

//
// Build the queries
//
        $itemsarray = array();
        $entryarray = array();
        $paramarray = array();
        $paramarray2 = array();
        $where = array();
        $where2 = array();
        $query = "SELECT c.*,<FIELDS> FROM " . cms_db_prefix() . "module_" . $this->_GetModuleAlias() . "_item c";
        $query2 = "SELECT count(*) as count,<FIELDS> FROM " . cms_db_prefix() . "module_" . $this->_GetModuleAlias() . "_item c";
        $where[] = 'c.active = ?';
        $paramarray[] = 1;

        $countjoins[] = cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_categories ca ON ca.category_id = c.category_id';
        $joins[] = cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_categories ca ON ca.category_id = c.category_id';
        $fields[] = 'ca.category_name, ca.category_alias';


        if ($category_id > 0) {
            $where[] = 'ca.category_id = ?';
            $paramarray[] = $category_id;
        } else if (empty($category) == false) {
            $category = cms_html_entity_decode($category);
            $categories = explode(',', $category);



            $querytmp = " (";
            $count = 0;
            foreach ($categories as $onecat) {
                if ($count > 0) {
                    $querytmp .= ' OR ';
                }
                if (strpos($onecat, '|') !== FALSE || strpos($onecat, '*') !== FALSE) {
                    $tmp = $db->qstr(trim(str_replace('*', '%', str_replace("'", '_', $onecat))));
                    $querytmp .= "upper(ca.long_name) like upper({$tmp})";
                } else {
                    $tmp = $db->qstr(trim(str_replace("'", '_', $onecat)));
                    $querytmp .= "ca.category_name = {$tmp}";
                }
                $count++;
            }
            $querytmp .= ")";

            $where[] = $querytmp;
        } else if (!empty($categoryalias)) {
            $category = cms_html_entity_decode($categoryalias);
            $where[] = 'ca.category_alias = \'' . $category .'\'';
        }




// filters
    $paramsfilter_ids = array();
    $paramsfilter_values = array();
        foreach ($params as $key => $param) {
            if (startswith($key, 'filter_')) {
                $filter_keys = explode('_', $key);
                if (count($filter_keys) == 3) {
                    $fieldid = $filter_keys[1];
                    $filter = $filter_keys[2];
                    $fieldval = $param;
                    if (isset($fieldid) && isset($fieldval) && $fieldval != "") {
                        if (intval($fieldid) > 0) {
                            // handle gathering '.$this->_GetModuleAlias().' that have a certain field id.
                            if ($filter == 'null') {
                                // handle a case when a field is not set for a product.
                                if (!in_array($fieldid, $paramsfilter_ids)) {
                                    $countjoins[] = cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval FVA' . $fieldid . ' ON c.item_id = FVA' . $fieldid . '.item_id AND FVA' . $fieldid . '.fielddef_id = ?';
                                    $joins[] = cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval FVA' . $fieldid . ' ON c.item_id = FVA' . $fieldid . '.item_id AND FVA' . $fieldid . '.fielddef_id = ?';
                                    $paramsfilter_ids[] = $fieldid;
                                }
                                $where[] = '(FVA' . $fieldid . '.value IS NULL)';
                                //array_unshift($paramarray, $fieldid);
                                //$paramarray[] = $fieldid;
                                //$paramsfilter_ids[] = $fieldid;
                            } else if ($filter == 'notnull') {
                                // handle a case when a field is not set for a product.
                                if (!in_array($fieldid, $paramsfilter_ids)) {
                                    $countjoins[] = cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval FVA' . $fieldid . ' ON c.item_id = FVA' . $fieldid . '.item_id AND FVA' . $fieldid . '.fielddef_id = ?';
                                    $joins[] = cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval FVA' . $fieldid . ' ON c.item_id = FVA' . $fieldid . '.item_id AND FVA' . $fieldid . '.fielddef_id = ?';
                                    $paramsfilter_ids[] = $fieldid;
                                }
                                $where[] = '(FVA' . $fieldid . '.value != \'\')';
                                //array_unshift($paramarray, $fieldid);
                                //$paramsfilter_ids[] = $fieldid;
                            } else if ($filter == 'in') {
                                // handle a case when a field is not set for a product.
                                if (!in_array($fieldid, $paramsfilter_ids)) {
                                    $countjoins[] = cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval FVA' . $fieldid . ' ON c.item_id = FVA' . $fieldid . '.item_id AND FVA' . $fieldid . '.fielddef_id = ?';
                                    $joins[] = cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval FVA' . $fieldid . ' ON c.item_id = FVA' . $fieldid . '.item_id AND FVA' . $fieldid . '.fielddef_id = ?';
                                    $paramsfilter_ids[] = $fieldid;
                                }
                                $where[] = 'FVA' . $fieldid . '.value IN (' . $db->qstr($fieldval) . ')';
                                //$paramarray[] = $fieldid;
                                //$paramsfilter_ids[] = $fieldid;
                            } else if ($filter == 'inset') {
                                // handle a case when a field is not set for a product.
                                if (!in_array($fieldid, $paramsfilter_ids)) {
                                    $countjoins[] = cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval FVA' . $fieldid . ' ON c.item_id = FVA' . $fieldid . '.item_id AND FVA' . $fieldid . '.fielddef_id = ?';
                                    $joins[] = cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval FVA' . $fieldid . ' ON c.item_id = FVA' . $fieldid . '.item_id AND FVA' . $fieldid . '.fielddef_id = ?';
                                    $paramsfilter_ids[] = $fieldid;
                                }
                                $where[] = ' FIND_IN_SET(' . $db->qstr($fieldval) . ',  FVA' . $fieldid . '.value) > 0';
                                //array_unshift($paramarray, $fieldid);
                                //$paramsfilter_ids[] = $fieldid;
                            } else if ($filter == 'less') {
                                if (is_numeric($fieldid)) {
                                    // limit results to all of the items that have this field value.
                                    if (!in_array($fieldid, $paramsfilter_ids)) {
                                        $countjoins[] = cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval FVA' . $fieldid . ' ON c.item_id = FVA' . $fieldid . '.item_id AND FVA' . $fieldid . '.fielddef_id = ?';
                                        $joins[] = cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval FVA' . $fieldid . ' ON c.item_id = FVA' . $fieldid . '.item_id AND FVA' . $fieldid . '.fielddef_id = ?';
                                        $paramsfilter_ids[] = $fieldid;
                                    }
                                    $where[] = 'FVA' . $fieldid . '.value < CAST(? AS DECIMAL)';
                                    //array_unshift($paramarray, $fieldid);
                                    //$paramsfilter_ids[] = $fieldid;
                                } else {
                                    $where[] = ' c.' . $fieldid . '  < CAST(? AS DECIMAL)';
                                }
                                //$paramarray[] = $fieldid;
                                //$paramarray[] = $fieldval;
                                $paramsfilter_values[] = $fieldval;
                            } else if ($filter == 'lessequal') {
                                // limit results to all of the items that have this field value.
                                if (!in_array($fieldid, $paramsfilter_ids)) {
                                    $countjoins[] = cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval FVA' . $fieldid . ' ON c.item_id = FVA' . $fieldid . '.item_id AND FVA' . $fieldid . '.fielddef_id = ?';
                                    $joins[] = cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval FVA' . $fieldid . ' ON c.item_id = FVA' . $fieldid . '.item_id AND FVA' . $fieldid . '.fielddef_id = ?';
                                    $paramsfilter_ids[] = $fieldid;
                                }
                                $where[] = 'FVA' . $fieldid . '.value <= CAST(? AS DECIMAL)';
                                //array_unshift($paramarray, $fieldid);
                                //$paramsfilter_ids[] = $fieldid;
                                //$paramarray[] = $fieldid;
                                //$paramarray[] = $fieldval;
                                $paramsfilter_values[] = $fieldval;
                            } else if ($filter == 'greater') {
                                if (is_numeric($fieldid)) {
                                    // limit results to all of the items that have this field value.
                                    if (!in_array($fieldid, $paramsfilter_ids)) {
                                        $countjoins[] = cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval FVA' . $fieldid . ' ON c.item_id = FVA' . $fieldid . '.item_id AND FVA' . $fieldid . '.fielddef_id = ?';
                                        $joins[] = cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval FVA' . $fieldid . ' ON c.item_id = FVA' . $fieldid . '.item_id AND FVA' . $fieldid . '.fielddef_id = ?';
                                        $paramsfilter_ids[] = $fieldid;
                                    }
                                    $where[] = 'FVA' . $fieldid . '.value > CAST(? AS DECIMAL)';
                                    //array_unshift($paramarray, $fieldid);
                                    //$paramsfilter_ids[] = $fieldid;
                                } else {
                                    $where[] = ' c.' . $fieldid . '  > CAST(? AS DECIMAL)';
                                }
                                //$paramarray[] = $fieldid;
                                //$paramarray[] = $fieldval;
                                $paramsfilter_values[] = $fieldval;
                            } else if ($filter == 'greaterequal') {
                                // limit results to all of the items that have this field value.
                                if (!in_array($fieldid, $paramsfilter_ids)) {
                                    $countjoins[] = cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval FVA' . $fieldid . ' ON c.item_id = FVA' . $fieldid . '.item_id AND FVA' . $fieldid . '.fielddef_id = ?';
                                    $joins[] = cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval FVA' . $fieldid . ' ON c.item_id = FVA' . $fieldid . '.item_id AND FVA' . $fieldid . '.fielddef_id = ?';
                                    $paramsfilter_ids[] = $fieldid;
                                }
                                $where[] = 'FVA' . $fieldid . '.value >= CAST(? AS DECIMAL)';
                                //array_unshift($paramarray, $fieldid);
                                //$paramsfilter_ids[] = $fieldid;
                                //$paramarray[] = $fieldid;
                                //$paramarray[] = $fieldval;
                                $paramsfilter_values[] = $fieldval;
                            } else if ($filter == 'like') {
                                // limit results to all of the items that have this field value.
                                if (!in_array($fieldid, $paramsfilter_ids)) {
                                    $countjoins[] = cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval FVA' . $fieldid . ' ON c.item_id = FVA' . $fieldid . '.item_id AND FVA' . $fieldid . '.fielddef_id = ?';
                                    $joins[] = cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval FVA' . $fieldid . ' ON c.item_id = FVA' . $fieldid . '.item_id AND FVA' . $fieldid . '.fielddef_id = ?';
                                    $paramsfilter_ids[] = $fieldid;
                                }
                                $where[] = 'FVA' . $fieldid . '.value LIKE ?';
                                //array_unshift($paramarray, $fieldid);
                                //$paramsfilter_ids[] = $fieldid;
                                //$paramarray[] = '%' . $fieldval . '%';
                                $paramsfilter_values[] = '%' . $fieldval . '%';
                            } else if ($filter == 'notequal') {
                                // limit results to all of the items that have this field value.
                                if (!in_array($fieldid, $paramsfilter_ids)) {
                                    $countjoins[] = cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval FVA' . $fieldid . ' ON c.item_id = FVA' . $fieldid . '.item_id AND FVA' . $fieldid . '.fielddef_id = ?';
                                    $joins[] = cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval FVA' . $fieldid . ' ON c.item_id = FVA' . $fieldid . '.item_id AND FVA' . $fieldid . '.fielddef_id = ?';
                                    $paramsfilter_ids[] = $fieldid;
                                }
                                $where[] = 'FVA' . $fieldid . '.value != ?';
                                //array_unshift($paramarray, $fieldid);
                                //$paramarray[] = $fieldid;
                                //$paramarray[] = $fieldval;
                                $paramsfilter_values[] = $fieldval;

                            } else if ($filter == 'equal') {
                                // limit results to all of the items that have this field value.
                                if (!in_array($fieldid, $paramsfilter_ids)) {
                                    array_unshift($countjoins, cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval FVA' . $fieldid . ' ON c.item_id = FVA' . $fieldid . '.item_id AND FVA' . $fieldid . '.fielddef_id = ?');
                                    array_unshift($joins, cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_fieldval FVA' . $fieldid . ' ON c.item_id = FVA' . $fieldid . '.item_id AND FVA' . $fieldid . '.fielddef_id = ?');
                                    array_unshift($paramsfilter_ids, $fieldid);
                                }
                                $where[] = 'FVA' . $fieldid . '.value = ?';
                                //array_unshift($paramarray, $fieldid);
                                //$paramsfilter_ids[] = $fieldid;
                                //$paramarray[] = $fieldval;
                                $paramsfilter_values[] = $fieldval;
                            }
                        } elseif (is_string($fieldid)) {
                            $fieldid = str_ireplace(array('Id', 'DateEnd', 'Date'), array('_id', '_date_end', '_date'), $fieldid);
                            if ($filter == 'null') {
                                $where[] = 'c.' . $fieldid . ' IS NULL)';
                            } else if ($filter == 'notnull') {
                                $where[] = 'c.' . $fieldid . ' IS NULL)';
                            } else if ($filter == 'in') {
                                if (startswith($fieldval, ','))
                                    $fieldval = substr($fieldval, 1);
                                $where[] = 'c.' . $fieldid . ' IN (' . $fieldval . ')';
                            } else if ($filter == 'inset') {
                                if (startswith($fieldval, ','))
                                    $fieldval = substr($fieldval, 1);
                                $where[] = ' FIND_IN_SET(' . $db->qstr($fieldval) . ', c.' . $fieldid . ') > 0';
                            } else if ($filter == 'like') {
                                $where[] = ' c.' . $fieldid . '  LIKE "%' . $fieldid . '%"';
                            } else if ($filter == 'less') {
                                $where[] = 'c.' . $fieldid . ' < ?';
                                //$paramarray[] = $fieldval;
                                $paramsfilter_values[] = $fieldval;
                            } else if ($filter == 'lessequal') {
                                $where[] = 'c.' . $fieldid . ' <=  ?';
                                //$paramarray[] = $fieldval;
                                $paramsfilter_values[] = $fieldval;
                            } else if ($filter == 'greater') {
                                $where[] = 'c.' . $fieldid . ' >  ?';
                                //$paramarray[] = $fieldval;
                                $paramsfilter_values[] = $fieldval;
                            } else if ($filter == 'greaterequal') {
                                $where[] = 'c.' . $fieldid . ' >= ?';
                                //$paramarray[] = $fieldval;
                                $paramsfilter_values[] = $fieldval;
                            } else if ($filter == 'equal') {
                                $where[] = 'c.' . $fieldid . ' = ?';
                                //$paramarray[] = $fieldval;
                                $paramsfilter_values[] = $fieldval;
                            } else if ($filter == 'notequal') {
                                $where[] = 'c.' . $fieldid . ' != ?';
                                //$paramarray[] = $fieldval;
                                $paramsfilter_values[] = $fieldval;
                            }
                        }
                    }
                }
            }
        }
        
        $paramarray = array_merge($paramsfilter_ids, $paramarray, $paramsfilter_values);

// recur
        if (isset($params['item_date_from_to'])) {

            if ($date_from && $date_to) {

                $days = round(($date_to - $date_from) / (3600 * 24));

                if ($days > 6) {
                    $where[] = "((c.item_date  > ? AND c.item_date  < ? AND (c.recursive = '' OR c.recursive IS NULL))
OR (c.recursive = 'daily' ))";
                    $paramarray[] = trim($db->DBTimeStamp($date_from), "'");
                    $paramarray[] = trim($db->DBTimeStamp($date_to), "'");
                    $fields[] = "e.value";
                } else {
                    $where[] = "((c.item_date  > ? AND c.item_date  < ? AND (c.recursive = '' OR c.recursive IS NULL))
                  OR (c.recursive = 'daily'  
AND ((

IF(?>?,
IF(e.value >= ? OR e .value <= ?,1,0)
,IF(e.value >= ? AND e .value <= ?,1,0)
) 

AND IF(e.value = ?, IF(DATE_FORMAT(c.item_date,'%H:%i') > ?,1,0),1) 
AND IF(e.value = ?, IF(DATE_FORMAT(c.item_date,'%H:%i') < ?,1,0),1))
)                  
))";
                    $paramarray[] = trim($db->DBTimeStamp($date_from), "'");
                    $paramarray[] = trim($db->DBTimeStamp($date_to), "'");

                    $paramarray[] = date('w', $date_from);
                    $paramarray[] = date('w', $date_to);
                    $paramarray[] = date('w', $date_from);
                    $paramarray[] = date('w', $date_to);
                    $paramarray[] = date('w', $date_from);
                    $paramarray[] = date('w', $date_to);

                    $paramarray[] = date('w', $date_from);
                    $paramarray[] = date('H:i', $date_from);
                    $paramarray[] = date('w', $date_to);
                    $paramarray[] = date('H:i', $date_to);
                }


                $joins[] = cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_item_extra e ON e.item_id = c.item_id';
                $countjoins[] = cms_db_prefix() . 'module_' . $this->_GetModuleAlias() . '_item_extra e ON e.item_id = c.item_id';

                //$fields[] = "IF(c.recursive = '' OR c.recursive IS NULL, date_format(c.item_date, '%Y-%m-%d %H:%i'), CONCAT(date_format(NOW() + INTERVAL (e.value-?) DAY, '%Y-%m-%d'),' ',date_format(c.item_date,'%H:%i'))) AS date_out, e.value";
                //array_unshift($paramarray, date('w', time()));
                $fields[] = "e.value,CONCAT(date_format(? + INTERVAL IF(e.value < ?, (7-?) + e.value, e.value-?) DAY,'%Y-%m-%d'),' ',date_format(c.item_date,'%H:%i')) as date_out";

                array_unshift($paramarray, date('w', $date_from));
                array_unshift($paramarray, date('w', $date_from));
                array_unshift($paramarray, date('w', $date_from));
                array_unshift($paramarray, trim($db->DBTimeStamp($date_from), "'"));

                /* $where2[] = 'date_out  > ? AND date_out < ?';
                  $paramarray2[] = trim($db->DBTimeStamp($date_from), "'");
                  $paramarray2[] = trim($db->DBTimeStamp($date_to), "'"); */

                $sortby = 'date_out';
            }
            $groupby = ' GROUP BY c.active ';
        } else {

            if ($date_from) {
                $where[] = " c.item_date  > ?";
                $paramarray[] = trim($db->DBTimeStamp($date_from), "'");
            }

            if ($date_to) {
                $where[] = " c.item_date  < ?";
                $paramarray[] = trim($db->DBTimeStamp($date_to), "'");
            }
        }


        if (isset($params["date_start"])) {

            $where[] = " c.item_date < NOW()";
        }

        if (isset($params["date_end"])) {
            $where[] = " c.item_date_end > NOW()";
        }


        $query = str_replace('<FIELDS>', implode(',', $fields), $query);
        $query2 = str_replace('<FIELDS>', implode(',', $fields), $query2);

        if (count($joins)) {
            $query .= ' LEFT JOIN ' . implode(' LEFT JOIN ', $joins);
        }
        if (count($countjoins)) {
            $query2 .= ' LEFT JOIN ' . implode(' LEFT JOIN ', $countjoins);
        }
        $query = $query . ' WHERE ' . implode(' AND ', $where);
        $query2 = $query2 . ' WHERE ' . implode(' AND ', $where);

        if (!empty($where2)) {
            $query = ' SELECT * FROM (' . $query . ') t';
            $query2 = ' SELECT * FROM (' . $query . ') t';
            $query = $query . ' WHERE ' . implode(' AND ', $where2);
            $query2 = $query2 . ' WHERE ' . implode(' AND ', $where2);
        }

        $paramarray = array_merge($paramarray, $paramarray2);



        if ($sorttype == '') {
            $query .= " ORDER BY " . $sortby . " " . $sortorder;
        } else {
            $query .= ' ORDER BY CAST(' . $sortby . ' AS ' . $sorttype . ') ' . $sortorder;
        }


// Execute the Queries
        $tmp_id = 'c' . md5($this->GetName() . $query2 . serialize($params) . serialize($paramarray) . $returnid);
        $count = cms_utils::get_app_data($tmp_id);
        if (!$count) {
            if ($groupby)
                $query2 .= $groupby;
            $count = $db->GetOne($query2 . ' GROUP BY ca.create_date', $paramarray);

            //echo $db->sql;
            // return first row
            if (isset($params["onecount"])) {
                $smarty->assign($params["onecount"], $count);
                return;
            }

            cms_utils::set_app_data($tmp_id, $count);
        }

//$tmp_id = md5($this->GetName() . md5($query) . implode('_', $paramarray) . implode('_', $params) . $limit . $startelement . 'results');
        $tmp_id = 'm' . md5($this->GetName() . $query . serialize($params) . serialize($paramarray) . $returnid);
        $dbresult = cms_utils::get_app_data($tmp_id);
        if (!$dbresult) {
            $paramarray = array_merge($paramarraytmp, $paramarray);
            $query .= ' LIMIT ' . $startelement . ',' . $limit;
            $dbresult = $db->GetAll($query, $paramarray);
            cms_utils::set_app_data($tmp_id, $dbresult);
        }
    }

// Determine the number of pages
    $npages = intval($count / $limit);
    if ($count % $limit != 0)
        $npages++;

    $config = cmsms()->GetConfig();

    if ($dbresult) {
        foreach ($dbresult as $row) {
            $onerow = cge_array::to_object($row);

            $dtpage = ($default_detailpage == -1 && !isset($params["detailpage"]) ? $detailpage : (isset($params["detailpage"]) ? $detailpage : $default_detailpage ));
            if (empty($row["url"]) == false) {
                $prettyurl = $row["url"];
            } else {
                $prettyurl = generator_tools::get_pretty_url($this, generator_tools::get_prefix($this), $row['item_id'], $row['alias'], $dtpage, $detailtemplate);
            }

            $onerow->url = $onerow->link = $this->CreateFrontendLink($id, $dtpage, 'detail', '', array('item_id' => $row['item_id'], 'detailtemplate' => $detailtemplate), '', true, $inline, '', false, $prettyurl);
            $onerow->file_location = generator_tools::file_location($this, $row);
// category file location
            $onerow->category_file_location = generator_tools::file_location($this, $row['category_id'], false);

            $images = '';
            if ($this->GetPreference('has_gallery') && $this->GetPreference('gallery_in_defaulttemplate')) {
                $item_gallery = new generator_item_gallery($this, $row["item_id"]);
                $images = $item_gallery->load_files($this->GetPreference('gallery_defaulttemplate_limit'));
            }
            $onerow->images = $images;


            // add custom fields
            $fielddefs = generator_fields::get_processed_fields_values($this, $row['item_id']);
            generator_fields::alias_to_object($fielddefs, $onerow);
            $onerow->fields = $fielddefs;

            // category fields
            $fields_cat = array();
            $fielddefs = generator_fields::get_processed_fields_values($this, $row['category_id'], 'categories');
            generator_fields::alias_to_object($fielddefs, $fields_cat);
            $onerow->categoryfields = $fields_cat;
            #$onerow->categoryfields = $fielddefs;

            $entryarray[] = $onerow;
            $itemsarray[] = $row;
        }
    }


// return first row
    if (isset($params["onerow"])) {
        $smarty->assign($params["onerow"], (isset($entryarray[0]) ? $entryarray[0] : ''));
        return;
    }
    //return  all results
    if (isset($params["allrow"])) {
        $smarty->assign($params["allrow"], $entryarray);
        return;
    }

// return items for module
    if (isset($params["items"]) && $params["items"] == true) {
        generator_tools::$items = $entryarray;
        return;
    }

//
// Give everything to smarty
//

    foreach ($params as $key => $value) {
        if ($key == 'mact' || $key == 'action')
            continue;
        $smarty->assign('param_' . $key, $value);
    }

    $smarty->assign('moduleurl', $this->CreateLink($id, 'default', $returnid, 'test', $params, '', true));
    $smarty->assign('items', $entryarray);
    $smarty->assign('totalcount', $count);
    $smarty->assign('itemcount', count($entryarray));
    $smarty->assign('itemonpage', (($page - 1) * $limit) + count($entryarray));
    $smarty->assign('pagetext', $this->Lang('page'));
    $smarty->assign('oftext', $this->Lang('of'));
    $smarty->assign('pagecount', $npages);
    $smarty->assign('curpage', $page);
    $smarty->assign('pagenumber', $page);
    if ($page == 1) {
        $smarty->assign('firstlink', $this->Lang('firstpage'));
        $smarty->assign('prevlink', $this->Lang('prevpage'));
    } else {
        $parms = $params;
        $parms['page'] = 1;
        $smarty->assign('firstlink', $this->CreateLink($id, 'default', $returnid, $this->Lang('firstpage'), $parms, '', false, $inline));
        $smarty->assign('firsturl', $this->CreateLink($id, 'default', $returnid, $this->Lang('firstpage'), $parms, '', true, $inline));
        $parms['page'] = $page - 1;
        $smarty->assign('prevlink', $this->CreateLink($id, 'default', $returnid, $this->Lang('prevpage'), $parms, '', false, $inline));
        $smarty->assign('prevurl', $this->CreateLink($id, 'default', $returnid, $this->Lang('prevpage'), $parms, '', true, $inline));
    }
    if ($page == $npages) {
        $smarty->assign('lastlink', $this->Lang('lastpage'));
        $smarty->assign('nextlink', $this->Lang('nextpage'));
    } else {
        $parms = $params;
        $parms['page'] = $npages;
        $smarty->assign('lastlink', $this->CreateLink($id, 'default', $returnid, $this->Lang('lastpage'), $parms, '', false, $inline));
        $smarty->assign('lasturl', $this->CreateLink($id, 'default', $returnid, $this->Lang('lastpage'), $parms, '', true, $inline));
        $parms['page'] = $page + 1;
        $smarty->assign('nextlink', $this->CreateLink($id, 'default', $returnid, $this->Lang('nextpage'), $parms, '', false, $inline));
        $smarty->assign('nexturl', $this->CreateLink($id, 'default', $returnid, $this->Lang('nextpage'), $parms, '', true, $inline));
    }
}
//
// Process the template
//

echo $smarty->fetch($this->GetDatabaseResource($thetemplate), $cache_id, $compile_id);

#
# EOF
#
?>
