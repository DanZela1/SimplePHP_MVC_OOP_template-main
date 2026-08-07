<section class="grid">
  <div class="row flex align-center">
    <h1 class="col-8">Catálogo de Productos</h1>
    <div class="col-4 align-end">
      <a href="index.php?page=Catalogo_Carrito">🛒 Carrito ({{cantidadCarrito}})</a>
    </div>
  </div>

  {{if mensaje}}
  <div class="col-12 success">{{mensaje}}</div>
  {{endif mensaje}}
  {{if mensajeError}}
  <div class="col-12 error">{{mensajeError}}</div>
  {{endif mensajeError}}

  <div class="row">
    <form class="col-12" action="index.php" method="get">
      <div class="flex align-center">
        <div class="col-9 row">
          <input type="hidden" name="page" value="Catalogo_CatalogoPublico">
          <label class="col-3" for="partialNombre">Buscar</label>
          <input class="col-9" type="text" name="partialNombre" id="partialNombre" value="{{partialNombre}}" />
          <label class="col-3" for="idCategoria">Categoría</label>
          <select class="col-9" name="idCategoria" id="idCategoria">
            <option value="0">Todas</option>
            {{foreach categorias}}
            <option value="{{idCategoria}}" {{selected}}>{{nombre}}</option>
            {{endfor categorias}}
          </select>
        </div>
        <div class="col-3 align-end">
          <button type="submit">Buscar</button>
        </div>
      </div>
    </form>
  </div>
</section>

<section class="grid">
  <div class="row">
    {{foreach productos}}
    <div class="col-12 col-m-4 my-2">
      <div class="card">
        <h3>{{nombre}}</h3>
        <p class="muted">{{categoriaNombre}}</p>
        <p>{{descripcion}}</p>
        <p><strong>L. {{precio}}</strong></p>
        <p>Stock disponible: {{stock}}</p>
        {{if stock}}
        <form action="index.php?page=Catalogo_CatalogoPublico" method="POST" class="flex align-center">
          <input type="hidden" name="agregarCarrito" value="1" />
          <input type="hidden" name="idProducto" value="{{idProducto}}" />
          <input type="number" name="cantidad" value="1" min="1" max="{{stock}}" class="col-4" />
          <button type="submit" class="col-8">Agregar al carrito</button>
        </form>
        {{endif stock}}
        {{ifnot stock}}
        <p class="error">Sin stock disponible</p>
        {{endifnot stock}}
      </div>
    </div>
    {{endfor productos}}
  </div>
</section>