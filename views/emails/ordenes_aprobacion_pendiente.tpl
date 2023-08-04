{extends './layouts/base.tpl'}
{block content}
    <div>
        Estimado/a {$nombre},
    </div>

    <div style="margin-top:24px">
        Recordatorio, a la fecha tienes {$num_ordenes} Orden{if $num_ordenes > 1}es{/if} de compra
        pendiente{if $num_ordenes > 1}s{/if} esperando aprobación. ingresa al sistema para conocer más.
    </div>

    <div style="margin-top:24px">
        {foreach $ordenes as $i => $o}
            {if $i!=0}
                <div style="margin-top:8px;margin-bottom:8px;height:1px;background:#EEE"></div>
            {/if}
            <div>
                <div>
                    <strong>{$o->od_correlativo}</strong>
                </div>
                <div style="color:#555">
                    Empresa: {$o->em_nombre}
                </div>
                <div style="color:#555">
                    Obra: {$o->ob_nombre}
                </div>
            </div>
        {/foreach}
    </div>

    <div style="margin-top:24px">
        <a style="background:#039be5;padding:0 20px;color:#FFFFFF!important;border-radius:20px;text-decoration:none;
            display:inline-block; height:40px; font-size:14px; font-weight:bold; line-height:40px;"
           href="{$stg->url_web}">
            INGRESAR
        </a>
    </div>
{/block}