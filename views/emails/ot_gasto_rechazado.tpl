{extends './layouts/base.tpl'}
{block content}
    <div>
        Estimado/a: {$user->name} {$user->surname}
    </div>
    <div style="margin-top:24px">
        Se rechazó el gasto registrado por el monto de {$ot_gasto->monto}.
    </div>
{/block}
