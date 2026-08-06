<h2>Histórico de Transacciones</h2>

{{if transacciones}}
<table border="1" cellpadding="8">
  <tr>
    <th>Fecha</th>
    <th>Orden PayPal</th>
    <th>Monto</th>
    <th>Estado</th>
  </tr>
  {{foreach transacciones}}
  <tr>
    <td>{{fecha_transaccion}}</td>
    <td>{{paypal_order_id}}</td>
    <td>{{monto}} {{moneda}}</td>
    <td>{{estado}}</td>
  </tr>
  {{endfor transacciones}}
</table>
{{endif transacciones}}
{{ifnot transacciones}}
<p>No tenés transacciones registradas todavía.</p>
{{endifnot transacciones}}