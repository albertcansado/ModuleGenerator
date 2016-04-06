{*<div>
{$formstart}
<fieldset style="float:left; width:49%;">
<legend>{$mod->Lang('filters')}:&nbsp;</legend>
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('hierarchy')}:</p>
  <p class="pageinput">{$input_hierarchy}&nbsp;{$mod->Lang('include_children')} {$input_children}</p>
</div>
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('prompt_summary_sorting')}:</p>
  <p class="pageinput">{$input_sortby}</p>
</div>
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('prompt_summary_sortorder')}:</p>
  <p class="pageinput">{$input_sortorder}</p>
</div>
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('page_limit')}:</p>
  <p class="pageinput">{$input_pagelimit}</p>
</div>
{if $mod->GetPreference('item_date_edit')}
<div class="pageoverflow">
  <p class="pagetext">{$mod->Lang('date')}</p>
  <p class="pageinput">  	
  {$mod->Lang('from')}
  {capture assign="idfromdate"}{$mod->GetActionId()}datefrom{/capture}
  {html_select_date field_order=DMY prefix=$idfromdate time=$date_from start_year="-10" end_year="+1"}
  &nbsp;
  {$mod->Lang('to')}
  {capture assign="idtodate"}{$mod->GetActionId()}dateto{/capture}
  {html_select_date field_order=DMY prefix=$idtodate time=$date_to start_year="-10" end_year="+1"}
  </p>
</div>
{/if}

<div class="pageoverflow">
  <p class="pagetext">&nbsp;</p>
  <p class="pageinput"><input type="submit" name="{$mod->GetActionId()}submit" value="{$mod->Lang('submit')}"></p>
</div>

</fieldset>

{$formend}
</div>*}




<div class="content destinations">
                
                    {$formstart}
                        <fieldset>
                            <legend>Filter</legend>
                            <div class="dest_column"><select><option>Krajina</option><option>1</option><option>2</option></select></div>
                            <div class="dest_column"><select><option>Destinácia</option><option>1</option><option>2</option></select></div>
                            <div class="dest_column"><select><option>Charakteristika</option><option>1</option><option>2</option></select></div>
                            <div class="clear"></div>
                        </fieldset>
                    </form>
                    <div class="hr_full"></div>
                    <h3>Zoznam destinácií</h3>
                    <div class="dest_column">
                        <h4>EURÓPA</h4>
                        <ul class="list">
                            <li><a href="">Alila Villas Soori, Bali</a></li>
                            <li><a href="">Alila Villas Taiwan</a></li>
                            <li><a href="">Alila Uluwatu, Catamaran</a></li>
                            <li><a href="">Mangis, Taiwan</a></li>
                        </ul>
                        <h4>AFRIKA</h4>
                        <ul class="list">
                            <li><a href="">Alila Villas, Taiwan</a></li>
                            <li><a href="">Alila Uluwatu, Catamaran</a></li>
                        </ul>
                    </div>

                    <div class="dest_column">
                        <h4>AMERIKA</h4>
                        <ul class="list">
                            <li><a href="">Alila Villas Soori, Bali</a></li>
                            <li><a href="">Alila Villas Taiwan</a></li>
                            <li><a href="">Alila Uluwatu, Catamaran</a></li>
                            <li><a href="">Mangis, Taiwan</a></li>
                            <li><a href="">Alila Villas Taiwan</a></li>
                            <li><a href="">Alila Uluwatu, Catamaran</a></li>
                            <li><a href="">Mangis, Taiwan</a></li>
                            <li><a href="">Aliali</a></li>
                        </ul>
                    </div>

                    <div class="dest_column">
                        <h4>ÁZIA</h4>
                        <ul class="list">
                            <li><a href="">Alila Villas Soori, Bali</a></li>
                            <li><a href="">Alila Villas Taiwan</a></li>
                            <li><a href="">Alila Uluwatu, Catamaran</a></li>
                        </ul>
                        <h4>AUSTRÁLIA</h4>
                        <ul class="list">
                            <li><a href="">Alila Villas Soori, Bali</a></li>
                            <li><a href="">Alila Villas Taiwan</a></li>
                        </ul>
                    </div>
                    <div class="clear"></div>
                    {$formend}
                </div>

