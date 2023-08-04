{extends './layouts/base.tpl'}
{block content}
    <div>
        Estimado/a: {$name} {$surname}
    </div>
    <div style="margin-top:24px">
        La creación del código de material ha sido rechazada porque este ya se encuentre dentro del catálogo con los
        siguientes datos:
    </div>
    <div style="margin-top:24px">
        OBSERVACIONES: {$motivo_rechazado}
    </div>
    <div style="margin-top:24px">
        <div>Atentamente,</div>
        <div>Administrador del Catálogo de materiales</div>
    </div>
{/block}