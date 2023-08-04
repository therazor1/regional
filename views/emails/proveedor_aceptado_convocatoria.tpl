{extends './layouts/base.tpl'}
{block content}
    <div>
        Estimado/a: {$nombre}
    </div>
    <div style="margin-top:24px">
        Su empresa fue adjudicada para una orden de compra.
    </div>
    <div style="margin-top:24px">
        <a href="{$stg->url_proveedores}">Ver orden</a>
    </div>
    <div style="margin-top:24px">
        <div>Atentamente,</div>
        <div>OHL</div>
    </div>
{/block}