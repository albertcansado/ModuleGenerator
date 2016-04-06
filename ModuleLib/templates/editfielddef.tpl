<h3>{$title}</h3>
{$startform}
<div class="pageoverflow">
    <p class="pagetext">*{$nametext}:</p>
    <p class="pageinput">{$inputname}</p>
</div>
<div class="pageoverflow">
    <p class="pagetext">{$alias}:</p>
    <p class="pageinput">{$input_alias}</p>
</div>
<div class="pageoverflow">
    <p class="pagetext">{$helptext}:</p>
    <p class="pageinput">{$inputhelp}</p>
</div>
<div class="pageoverflow">
    <p class="pagetext">{$typetext}:</p>
    <p class="pageinput">{$inputtype}</p>
</div>
<div class="pageoverflow">
    <p class="pagetext">{$userviewtext}:</p>
    <p class="pageinput">{$input_userview}</p>
</div>
<div class="pageoverflow">
    <p class="pagetext">{$editview_text}:</p>
    <p class="pageinput">{$input_editview}</p>
</div>
<div class="pageoverflow">
    <p class="pagetext">{$hidename_text}:</p>
    <p class="pageinput">{$input_hidename}</p>
</div>
<div class="pageoverflow">
    <p class="pagetext">{$extra}:</p>
    <p class="pageinput">{$input_extra}</p>
</div>
<div class="pageoverflow">
    <p class="pagetext">{$mod->Lang('section')}:</p>
    <p class="pageinput">{$input_section}</p>
</div>
<div class="pageoverflow">
    <p class="pagetext">&nbsp;</p>
<p class="pageinput">{if isset($fielddef_id)}{$fielddef_id}{/if}{$submit}{$cancel}{$apply}</p>
</div>
{$endform}
