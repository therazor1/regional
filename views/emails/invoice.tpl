<div>
    Estimados <span style="font-weight:bold">{$denomination}</span>,
</div>
<div style="margin-top:24px">
    Su factura de <span style="font-weight:bold">{$smonth}</span> de <span style="font-weight:bold">{$syear}</span> ya
    está disponible. En la parte inferior de este correo electrónico, encontrará adjunto el documento PDF.
</div>
<div style="margin-top:24px">
    <div>• <span style="font-weight:bold">FACTURA ELECTRÓNICA: {$serie}-{$correlative|doc}</span></div>
    <div>• Fecha de emisión: <span style="font-weight:bold">{$date_created->verboseDate()}</span></div>
    <div>• Total: <span style="font-weight:bold">{$total|coin}</span></div>
</div>