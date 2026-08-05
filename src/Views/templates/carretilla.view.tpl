<h1>Mi Carrito</h1>

{{if carritoVacio}}
<p>Tu carrito está vacío.</p>
{{endif carritoVacio}}

{{ifnot carritoVacio}}
<table class="tbl">
  <thead>
    <tr><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Subtotal</th><th></th></tr>
  </thead>
  <tbody>
  {{foreach items}}
    <tr>
      <td>{{productName}}</td>
      <td>
        <form method="post" action="index.php?page=Checkout_Carretilla" class="inline-form">
          <input type="hidden" name="action" value="actualizar" />
          <input type="hidden" name="productId" value="{{productId}}" />
          <input type="number" name="cantidad" value="{{cantidad}}" min="0" />
          <button type="submit">Actualizar</button>
        </form>
      </td>
      <td>Q{{precio}}</td>
      <td>Q{{subtotal}}</td>
      <td>
        <form method="post" action="index.php?page=Checkout_Carretilla" class="inline-form">
          <input type="hidden" name="action" value="eliminar" />
          <input type="hidden" name="productId" value="{{productId}}" />
          <button type="submit">Eliminar</button>
        </form>
      </td>
    </tr>
  {{endfor items}}
  </tbody>
</table>

<h3>Total: Q{{total}}</h3>

<form method="post" action="index.php?page=Checkout_Carretilla">
  <input type="hidden" name="action" value="vaciar" />
  <button type="submit">Vaciar Carrito</button>
</form>

<a href="index.php?page=Checkout_Checkout">Proceder al Pago</a>
{{endifnot carritoVacio}}