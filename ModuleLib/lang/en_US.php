<?php

$config = cmsms()->GetConfig();
$dir = str_replace('\\', '/', dirname(__FILE__));
$default_lang = str_replace('ModuleLib' . DIRECTORY_SEPARATOR, '', __FILE__);
$curr_lang = str_replace('en_US.php', 'ext' . DIRECTORY_SEPARATOR . cms_current_language() . '.php', $default_lang);
include($default_lang);
if (file_exists($curr_lang)) {
    include($curr_lang );
}

$lang['postinstall'] = $module_name . ' has successfully been installed';
$lang['postuninstall'] = $module_name . ' has successfully been uninstalled';
$lang[$module_alias . '_modify_item'] = $module_name . ': Modify Items';
$lang[$module_alias . '_modify_categories'] = $module_name . ': Modify Categories';
$lang[$module_alias . '_modify_option'] = $module_name . ': Modify Options';
$lang[$module_alias . '_all_category_visbile'] = $module_name . ': all category visible';
$lang['changelog'] = ' ';

## perms
$lang['modify_item'] = $module_name . ': modify item';
$lang['modify_option'] = $module_name . ': modify option';
$lang['modify_categories'] = $module_name . ': modify categories';
$lang['all_category_visbile'] = $module_name . ': all category visible';


// module help
$lang['help'] = <<<EOT
<div id="page_tabs">     
  <div id="general">
    General Info   
  </div>     
  <div id="usage">
    Usage   
  </div>     
  <div id="permissions">
    Permissions   
  </div>     
  <div id="field">
    Field Definitions   
  </div>     
  <div id="categories">
    Categories   
  </div>     
  <div id="examples">
    Examples   
  </div>     
</div>   
<div class="clearb">  
</div>
<div id="page_content">  
  <div id="general_c">  

<h3>General Info</h3>   
    <p>Simply put, Module  allows you to create lists that you can display throughout your website. You could make a simple FAQ or Testimonials feature with this module. The web developer defines fields to constrain what data the client can enter. A number of field types can be specified - text input, checkbox, text area, select date, upload file, select file, dropdown - and additional instructions can be set for each type, for example, input field size, max length, WYSIWYG editor, possible drop down values, possible file extensions, directory paths for file selections, date formats, etc.
    </p>


<h3>About Author</h3>
<p>Zdeno Kuzmany<br /> <br />
    <strong>Personal:</strong><br>
                            <a href="http://kuzmany.biz/?utm_source=cmsms%2Bmodule&utm_medium=link&utm_campaign=urlwatchdog">kuzmany.biz</a><br>
                            <a href="http://madesimple.sk/?utm_source=cmsms%2Bmodule&utm_medium=link&utm_campaign=urlwatchdog">madesimple.sk</a><br>
                            <a href="http://twitter.com/kuzmany">twitter</a><br>
                            <a href="http://about.me/kuzmany">about.me</a><br><br />
<strong>Do nice thing with me</strong><br />
                            <a href="http://cmsmadesimple.sk/outsourcing/?utm_source=cmsms%2Bmodule&utm_medium=link&utm_campaign=urlwatchdog">CMS Made Simple outsourcing</a><br><br>
                        </p>
  </div>  
  <div id="usage_c">    
    <h3>Usage</h3>      
      <p>You can configure {$module_name} here: Content > {$module_name}
      </p>      
    <p>Place this tag in your page:<br /> 
    {{$module_name}}
    </p>    
    <div>     
      <div style="float:left;">
        After installing the module the next thing to do is set the options.          
        <ol>          
          <li>To change the name of the module in the menu change the 'Module Friendly Name'.
          </li>         
          <li>To change the name of the item tab change the 'Item Plural'.
          </li>       
        </ol>   
      </div>
      <br style="clear:both;" />    
    </div>    
    <div>
      <div style="float:left;">
        Next - set the Field Definitions. 
        <ol>
          <li>Choose from 'Text Input', 'Checkbox', 'Text Area', 'Select Date', 'Upload File', 'Select File' & 'Dropdown'.
          </li>
          <li>For each field definition, you can specify additional instructions in the 'extra' field. See the 'Field Definitions' tab for a list.
          </li>
          <li>Each item in each list has three default fields. All Field Definitions set here are additional to them.
          </li>
        </ol>
      </div><br style="clear:both;" />
    </div>
        <div>
      
      <div style="float:left;">
        Now we move on to the Item list itself. In this example it says 'Add Box', this was renamed in the 'Options' tab. 
        <ol>
          <li>The first field is the default 'Title' field.
          </li>
          <li>The 'Category' dropdown is also a default field, and if unchanged, will be set to 'General'.
          </li>
          <li>The third default field is the checkbox called 'Active'. This allows you to toggle a list entry without deleting it.
          </li>
        </ol>
      </div><br style="clear:both;" />
    </div>
    <div style="clear:both;" />
    </div>
  </div>
  <div id="permissions_c"><h3>Permissions</h3>
    <p>You can specify the following permissions under Users & Groups > Group Permissions
    </p>
    <ul>
      <li>{$module_name}: modify Items
      </li>
      <li>{$module_name}: modify Categories
      </li>
      <li>{$module_name}: modify Options
      </li>
      <li>{$module_name}: all category visible
      </li>
    </ul>
  </div>
  <div id="field_c"><h3>Field Definitions</h3>
    <p>The first thing you should configure are your field definitions.
    </p>
    <p>For each field definition, you can specify additional instructions in the "extra" field.
    </p>
    <ul>    
      <li>        
      <p>Instruction: 
        <code>size[20]
        </code><br />
        Possible value: integer<br />
        Applicable to field type: Text Input, Select Date
      </p>    
      </li>    
      <li>        
      <p>Instruction: 
        <code>max_length[20]
        </code><br />
        Possible value: integer<br />
        Applicable to field type: Text Input, Text Area, Select Date
      </p>    
      </li>  
      <li>        
      <p>Instruction: 
        <code>wysiwyg[1]
        </code><br />
        Possible value: 1|0|true|false<br />
        Applicable to field type: Text Area
      </p>    
      </li>    
      <li>        
      <p>Instruction: 
        <code>udt[udt_name]
        </code><br />
        Possible value: string<br />
        Applicable to field type: dropdown_from_udt
      </p>    
      </li>    
      <li>        
      <p>Instruction: 
        <code>options[apple=Apple,banana=Banana]
        </code><br />
        Possible value: key=value,... <br />
        Applicable to field type: Dropdown
      </p>    
      </li>    
      <li>        
      <p>Instruction: 
        <code>Modulename[action=default,sortby=title,sortorder=desc]
        </code><br />
        Possible value: everything you used in module
        <br />
        Applicable to field type: Module
      </p>    
      </li>    
      <li>        
      <p>Instruction: 
        <code>multiple[5]
        </code><br />
        Possible value: integer<br />
        Applicable to field type: Module
      </p>    
      </li>  
      <li>        
      <p>Instruction: 
        <code>GBFilePicker[mode=browser,dir=images,allow=jpg,gif]
        </code><br />        
        Applicable to field type: filepicker (need GBFilePicker module installed)
      </p>    
      </li>  
      <li>        
      <p>Instruction: 
        <code>allow[pdf,gif,png,jpeg,jpg]
        </code><br />
        Possible value: extension,... (keep lowercase)<br />
        Applicable to field type: Upload File, Select File
      </p>    
      </li>    
      <li>        
      <p>Instruction: 
        <code>dir[/path/to/dir]
        </code><br />
        Possible value: Directory path that will be appended to 
        <code>\$config['uploads_path']
        </code>. No slash at the end.<br />
        Applicable to field type: Select File
      </p>    
      </li>    
      <li>        
      <p>Instruction: 
        <code>exclude_prefix[thumb_,foo_]
        </code><br />
        Possible value: prefix,...<br />
        Applicable to field type: Select File
      </p>    
      </li>    
      <li>        
      <p>Instruction: 
        <code>dateformat[dd/mm/yy]
        </code><br />
        Possible value: Date format used by the jquery datepicker<br />
        Applicable to field type: Text Area
      </p>    
      </li>
    </ul>
    <p>You can specify multiple instructions separated by a semicolon ( 
      <code>;
      </code> ), for example:
    </p>
    <p>
      <code>allow[pdf];dir[/docs/pdf]
      </code>
    </p>
  </div>
  <div id="categories_c">
    <h3>Categories</h3>
    <p>You can use hierarchy categories with each item.
    </p>
    <p>Example: {ModuleName category="Cats*"} - view each item from category Cats and his children</p>
    <br />
    <p>You can use depth param to extract all categories in specific level.</p>
    <p>Example: {ModuleName action="categories" depth="2"} - Only obtains the second-level categories <br /> Cat 1<br />Cat 1 | Cat 2<br />Cat 3 | Cat 4 <br />Return <strong>Cat 2</strong> and <strong>Cat 4</strong></p>
  </div>
  <div id="examples_c">
    <h3>Examples</h3>
    <p>{ModuleName filter_1_equal=1 date_start=1 date_end=1}</p>
    <ul>
    <li>value from custom fieldid with id 1 = 1</li>
    <li>date start  < NOW</li>
    <li>end date  > NOW</li>
    </ul>
    <br />
    <p>{ModuleName filter_itemId_inset='1,2,3'}</p>
    <ul>
    <li>item_id is in set (1,2,3)</li>
    </ul>
    <br />
    <p>{ModuleName  allrow="items"}</p>
    <ul>
    <li>set all result to smarty var \$items</li>    
    </ul>
    <br />
    <p>{ModuleName action="detail" item_id=1  onerow="item"}</p>
    <ul>
    <li>detail from item with id 1</li>    
    <li>set result to smarty var \$item</li>    
    </ul>
    <p>{ModuleName item_date_from_to="now|6" date_start=1}</p>
    <ul>
    <li>view for recursive items like events, calendars etc.</li>
    <li>need allowed start_date and recursive from editing option tab</li>    
    <li>item_date_from_to contains 2 parameters with | separator <br />
    first is  date_start <br />
    second is  date_start <br />
    both parameter can has values: now, integer (now + x days) or 2 values with : separator (now + 3 days + 4 hour) 
</li>    
    <li>set result to smarty var \$item</li>    
    </ul>
  </div>
</div>
EOT;
$lang['help_param_action'] = <<<EOT
'Override the default action. Possible values are:
<ul>
<li>&quot;default&quot; - to display the summary view.</li>
<li>&quot;detail&quot; - to display a specified entry in detail mode.</li>
<li>&quot;categories&quot; - to display the categories</li>
<li>&quot;filter&quot; - filter for items</li>
</ul>
EOT;
$lang['help_param_category_id'] = 'Specify the category id to display items only from this category.';
$lang['help_param_category'] = 'Specify the category name to display items only from this category.';
$lang['help_param_detailtemplate'] = 'The detail template you wish to use.';
$lang['help_param_summarytemplate'] = 'The summary template you wish to use';
$lang['help_param_categorytemplate'] = 'The category template you wish to use';
$lang['help_param_filtertemplate'] = 'The filter template you wish to use';
$lang['help_param_sortby'] = 'You can order by any of the following columns: item_id, title, category, item_date, modified, random, f:fieldalias.';
$lang['help_param_pagelimit'] = 'Page limit for pagination';
$lang['help_param_sortorder'] = 'Order of default action';
$lang['help_param_depth'] = 'Specify nth-level categories to want to obtain. First level is 1. For categories action';
$lang['help_param_inline'] = 'Inline';
$lang['help_param_onerow'] = 'Set result array to smarty. For default and detail action';
$lang['help_param_allrow'] = 'Set results array to smarty. For default action';
$lang['help_param_onecount'] = 'Set count result to smarty. For default action';
$lang['help_param_date_start'] = 'Allow using date_end for public items';
$lang['help_param_date_end'] = 'Items expiration';
$lang['help_param_filter'] = 'Filter for results. You can use filter_FIELDID_CONDITION for fields or filter_PREF_CONDITION for item cols.<br />
Condition for FIELDS (etc: filter_1_equal=\'apple\'):<br  />
<ul>
<li>null</li>
<li>notnull</li>
<li>in</li>
<li>inset</li>
<li>inset</li>
<li>less</li>
<li>lessequal</li>
<li>greater</li>
<li>greaterequal</li>
<li>like</li>
<li>notequal</li>
<li>equal</li>
</ul>
Condition for item cols - itemId, itemDateEnd, itemDate, alias, url, featured   (etc: filter_itemDateEnd_equal=\'2011-01-01 00:00:00\'):<br  />
<ul>
<li>null</li>
<li>notnull</li>
<li>in</li>
<li>inset</li>
<li>like</li>
<li>less</li>
<li>lessequal</li>
<li>greater</li>
<li>greaterequal</li>
<li>notequal</li>
<li>equal</li>
</ul>
';

$lang['help_param_detailpage'] = 'Page to display item details in. Must be a page alias. Used to allow details to be displayed in a different page than summary.';
$lang['help_param_item'] = 'This parameter is only applicable to the detail view. It allows specifying which item to display in detail mode. Must be an item alias.';
?>
