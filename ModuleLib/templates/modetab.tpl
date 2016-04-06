{assign var="tmpmod" value=$mod}
{assign var="tmpactionid" value=$actionid}


{literal}
    <script tyle="text/javascript">
    $(document).ready(function(){
    $("#advanced,#simple").hide();
    $("#"+$("#mode select").val()).fadeIn();;

    $("#mode select").change(function(){
    $("#advanced,#simple").hide();
    $("#"+$(this).val()).fadeIn();;
    })
    })
    </script>
{/literal}

{$startform}

<fieldset>
    <h3>{$prompt_mode}</h3>
    <div class="pageoverflow">
        <p class="pageinput" id="mode">{$input_mode}</p>
    </div>
    <div id="advanced">
        <p>{$mod->Lang('advanced_mode_info')}</p>
        <div class="pageoverflow">
            <p class="pagetext">{$prompt_summary_sorting}:</p>
            <p class="pageinput">{$input_summary_sorting_advanced}</p>
        </div>
        <div class="pageoverflow">
            <p class="pagetext">{$prompt_summary_sortorder}:</p>
            <p class="pageinput">{$input_summary_sortorder_advanced}</p>
        </div>
        <div class="pageoverflow">
            <p class="pagetext">{$prompt_summary_pagelimit}:</p>
            <p class="pageinput">{$input_summary_pagelimit_advanced}</p>
        </div>
    </div>
    <div id="simple">
        <p>{$mod->Lang('simple_mode_info')}</p>
        <div class="pageoverflow">
            <p class="pagetext">{$prompt_summary_sorting}:</p>
            <p class="pageinput">{$input_summary_sorting_simple}</p>
        </div>
        <div class="pageoverflow">
            <p class="pagetext">{$prompt_summary_sortorder}:</p>
            <p class="pageinput">{$input_summary_sortorder_simple}</p>
        </div>
        <div class="pageoverflow">
            <p class="pagetext">{$prompt_summary_pagelimit}:</p>
            <p class="pageinput">{$input_summary_pagelimit_simple}</p>
        </div>
    </div>
        <input type="hidden" name="{$tmpactionid}tab" value="mode" />
</fieldset>	
<div class="pageoverflow">
    <p class="pagetext">&nbsp;</p>
    <p class="pageinput">{$submit}</p>
</div>    
{$endform}
