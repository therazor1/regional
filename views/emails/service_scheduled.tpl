<div>
    Hola <span style="font-weight:bold">{$user_name}</span>,
</div>
<div style="font-size:20px;line-height:20px;margin-top:24px">
    Gracias por programar tu viaje
</div>
<div style="margin-top:24px">
    <span style="color:gray">Fecha:</span> {$date_scheduled|verbose_date}
</div>
<div style="margin-top:8px">
    <span style="color:gray">Hora:</span> {$date_scheduled|human_time}
</div>
<div style="margin-top:24px;padding-top:16px;border-top:1px solid #CCCCCC;">
    {foreach $stops as $i => $stop}
        <div style="padding:10px 0 10px 20px;position:relative">
            <div style="display:inline-block;width: 8px;
                    height: 8px;border-radius:4px;margin-right: 10px;
                    position:absolute;top: 14px; left: 0;
                    background-color: {if $i==0}green{else}red{/if}"></div>
            {$stop->address}
        </div>
    {/foreach}
</div>
<div style="border-top:1px solid #CCCCCC;margin-top:16px"></div>