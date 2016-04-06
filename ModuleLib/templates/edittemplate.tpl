<h3>{$title}</h3>
{$startform}
<div class="pageoverflow">
    <p class="pagetext">* {$prompt_name}:</p>
    <p class="pageinput">{$input_name}</p>
</div>
<div class="pageoverflow">
    <p class="pagetext">{$prompt_template}:</p>
    <p class="pageinput">{$input_template}</p>
</div>
<div class="pageoverflow">
    <p class="pagetext">&nbsp;</p>
    <p class="pageinput">{if isset($idfield)}{$idfield}{/if}{$submit}{$cancel}{$apply}</p>
</div>
{$input_type}
{$endform}
