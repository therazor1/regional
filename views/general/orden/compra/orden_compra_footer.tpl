<table class="b-t">
    <tr>
        <td style="background: url('assets/img/logo_formato_orden_transparente.png');background-repeat:no-repeat;
                   background-size: 100% auto; background-position:center;">
            <div style="position:relative">
                <div>
                    <div>No se cancelara la factura que no cumpla con los siguientes requisitos :</div>
                    <div>1.- Recepción del 100% de los ítems estipulados en esta O/C.</div>
                    <div>2.- Remitir a la oficina central u obra, según corresponda, copia de la presente O/C con la
                        factura. Al momento del pago se exigirá la copia cedible.
                    </div>
                    <div>3.- Adjuntar guía de despacho con timbre y firma de recepción de la empresa.</div>
                    <div>4.- Lo solicitado en la presente O/C, se entiende aceptado por el proveedor, en todos sus
                        términos y condiciones expresadas aqui.
                    </div>

                </div>
            </div>
        </td>
        <td style="width:20%" class="bold ctr">
            {if $orden_aprobacion}
                <div style="height:56px">
                    <img src="{$orden_aprobacion->us_pic_firma|pic}" style="height:56px">
                </div>
            {/if}
            <br><br>
            V*B* {if $orden_aprobacion} {$orden_aprobacion->us_name} {$orden_aprobacion->us_surname} {/if}
        </td>
        <td rowspan="2" style="width:30%">
            <table class="table-styled">
                <tr>
                    <td class="bold">Neto no afecto a IVA</td>
                    <td class="text-right">{$monto_base_no_afecto|coin_format:$tipo_moneda->decimales}</td>
                </tr>
                <tr>
                    <td class="b-t bold">Neto afecto a IVA</td>
                    <td class="b-t text-right">{$monto_base_afecto|coin_format:$tipo_moneda->decimales}</td>
                </tr>
                <tr>
                    <td class="b-t bold">+IVA</td>
                    <td class="b-t text-right">{$orden->monto_impuesto|coin_format:$tipo_moneda->decimales}</td>
                </tr>
                <tr>
                    <td class="b-t b-b bold">Total</td>
                    <td class="b-t b-b text-right">{$orden->monto_total|coin_format:$tipo_moneda->decimales}</td>
                </tr>
                <tr>
                    <td colspan="2" class="b-all ctr" style="padding:80px 0 8px 0">
                        <div class="bold">Firma Aceptación Proveedor</div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="2" class="pt-24">
            <div>Fecha de Pago:</div>
            {if $plazo_pago->min_dias > 0}
                <div>
                    {$plazo_pago->max_dias} días, contados desde la recepción conforme de la factura. OHLA podrá
                    adelantar el pago una vez que hayan transcurrido {$plazo_pago->min_dias} días desde la recepción
                    conforme de la factura, pagando en todo caso al prestador los días 10 o 25 (o hábil posterior).
                </div>
            {else}
                <div>Pago al Contado.</div>
            {/if}
            <div>El presente acuerdo estará vigente hasta que se hayan cumplido todas las
                obligaciones de las partes que tengan su origen en los servicios o suministros pactados.
            </div>
        </td>
    </tr>
</table>

<div class="div b-t ctr" style="margin-top:8px">
    El Grupo OHLA espera que sus proveedores y contratistas observen al menos Pautas Básicas de Comportamiento Ético y
    respeto de los Derechos
    Humanos y Laborales, condiciones de Seguridad y Salud Laboral para sus empleados y una gestión que considere el
    respeto al Medio Ambiente.
</div>