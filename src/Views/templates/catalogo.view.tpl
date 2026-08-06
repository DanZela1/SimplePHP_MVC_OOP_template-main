<h1>Catálogo de Productos</h1>

{{if mensaje}}
<p class="alert">{{mensaje}}</p>
{{endif mensaje}}

<div class="grid">
{{foreach productos}}
  <div class="col-4 producto-card">
    <img src="{{productImgUrl}}" alt="{{productName}}" />
    <h3>{{productName}}</h3>
    <p>{{productDescription}}</p>
    <p>Q{{productPrice}}</p>
    <p>Stock disponible: {{productStock}}</p>
    <form method="post" action="index.php?page=Checkout_Catalogo">
      <input type="hidden" name="productId" value="{{productId}}" />
      <input type="number" name="cantidad" value="1" min="1" max="{{productStock}}" />
      <button type="submit">Agregar al carrito</button>
    </form>
  </div>
{{endfor productos}}
</div>