<section class="grid">
  <div class="row flex align-center">
    <h1 class="col-8">Carrito de Compras</h1>
    <div class="col-4 align-end">
      <a href="index.php?page=Catalogo_CatalogoPublico">&larr; Seguir comprando</a>
    </div>
  </div>

  {{if mensaje}}
  <div class="col-12 success">{{mensaje}}</div>
  {{endif mensaje}}
  {{if mensajeError}}
  <div class="col-12 error">{{mensajeError}}</div>
  {{endif mensajeError}}

  {{if carritoVacio}}
  <p>Tu carrito está vacío.</p>
  {{endif carritoVacio}}

  {{ifnot carritoVacio}}
  <form action="index.php?page=Catalogo_Carrito" method="POST">
    <input type="hidden" name="accion" value="actualizar" />
    <table>
      <thead>
        <tr>
          <th class="left">Producto</th>
          <th class="right">Precio</th>
          <th class="center">Cantidad</th>
          <th class="right">Subtotal</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        {{foreach detalle}}
        <tr>
          <td>
            {{nombre}}
            {{if noDisponible}}
            <span class="error">(ya no disponible)</span>
            {{endif noDisponible}}
            {{if stockInsuficiente}}
            <span class="error">(stock insuficiente, disponible: {{stock}})</span>
            {{endif stockInsuficiente}}
          </td>
          <td class="right">L. {{precio}}</td>
          <td class="center">
            <input type="number" name="cantidad[{{idProducto}}]" value="{{cantidad}}" min="0" max="{{stock}}" class="qty-input" />
          </td>
          <td class="right">L. {{subtotal}}</td>
          <td class="center">
            <button type="submit" form="frmEliminar_{{idProducto}}">Eliminar</button>
          </td>
        </tr>
        {{endfor detalle}}
      </tbody>
    </table>
    <div class="row my-2 flex-end">
      <button type="submit">Actualizar cantidades</button>
    </div>
  </form>

  {{foreach detalle}}
  <form id="frmEliminar_{{idProducto}}" action="index.php?page=Catalogo_Carrito" method="POST">
    <input type="hidden" name="accion" value="eliminar" />
    <input type="hidden" name="idProducto" value="{{idProducto}}" />
  </form>
  {{endfor detalle}}

  <section class="row my-4">
    <div class="col-12 col-m-6 offset-m-6">
      <div class="row">
        <span class="col-6">Subtotal</span>
        <span class="col-6 right">L. {{subtotal}}</span>
      </div>
      <div class="row">
        <span class="col-6">Impuesto ({{tasaImpuesto}}%)</span>
        <span class="col-6 right">L. {{impuesto}}</span>
      </div>
      <div class="row">
        <strong class="col-6">Total</strong>
        <strong class="col-6 right">L. {{total}}</strong>
      </div>
    </div>
  </section>

  <div class="row my-2 flex-end">
    <form action="index.php?page=Catalogo_Carrito" method="POST">
      <input type="hidden" name="accion" value="vaciar" />
      <button type="submit">Vaciar carrito</button>
    </form>
    &nbsp;
    <form action="index.php?page=Catalogo_Carrito" method="POST">
      <input type="hidden" name="accion" value="finalizar" />
      <button type="submit" class="primary">Finalizar Compra</button>
    </form>
  </div>
  {{endifnot carritoVacio}}
</section>