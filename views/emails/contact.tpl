<div style="font-weight:bold;font-size:20px">Formulario de contacto</div>
<div style="margin-top:8px">
    {foreach $pairs as $pair}
        <div style="margin-top:8px">
            <span style="font-weight:bold">{$pair->id}:</span> {$pair->name}
        </div>
    {/foreach}
</div>