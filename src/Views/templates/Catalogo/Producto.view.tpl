<section class="container-m row px-4 py-4">
  <h1>{{FormTitle}}</h1>
</section>
<section class="container-m row px-4 py-4">
  {{with producto}}
  <form action="index.php?page=Catalogo_Producto&mode={{~mode}}&idProducto={{idProducto}}" method="POST" class="col-12 col-m-8 offset-m-2">
    <div class="row my-2 align-center">
      <label class="col-12 col-m-3" for="idProductoD">Código</label>
      <input class="col-12 col-m-9" readonly disabled type="text" name="idProductoD" id="idProductoD" value="{{idProducto}}" />
      <input type="hidden" name="mode" value="{{~mode}}" />
      <input type="hidden" name="idProducto" value="{{idProducto}}" />
      <input type="hidden" name="producto_xss_token" value="{{~producto_xss_token}}" />
    </div>
    <div class="row my-2 align-center">
      <label class="col-12 col-m-3" for="nombre">Nombre</label>
      <input class="col-12 col-m-9" {{~readonly}} type="text" name="nombre" id="nombre" placeholder="Nombre del producto" value="{{nombre}}" />
      {{if nombre_error}}
      <div class="col-12 col-m-9 offset-m-3 error">{{nombre_error}}</div>
      {{endif nombre_error}}
    </div>
    <div class="row my-2 align-center">
      <label class="col-12 col-m-3" for="descripcion">Descripción</label>
      <textarea class="col-12 col-m-9" {{~readonly}} name="descripcion" id="descripcion" placeholder="Descripción del producto">{{descripcion}}</textarea>
    </div>
    <div class="row my-2 align-center">
      <label class="col-12 col-m-3" for="idCategoria">Categoría</label>
      <select class="col-12 col-m-9" name="idCategoria" id="idCategoria" {{if ~readonly}} readonly disabled {{endif ~readonly}}>
        {{foreach ~categorias}}
        <option value="{{idCategoria}}" {{selected}}>{{nombre}}</option>
        {{endfor ~categorias}}
      </select>
      {{if idCategoria_error}}
      <div class="col-12 col-m-9 offset-m-3 error">{{idCategoria_error}}</div>
      {{endif idCategoria_error}}
    </div>
    <div class="row my-2 align-center">
      <label class="col-12 col-m-3" for="precio">Precio (L.)</label>
      <input class="col-12 col-m-9" {{~readonly}} type="number" step="0.01" min="0.01" name="precio" id="precio" value="{{precio}}" />
      {{if precio_error}}
      <div class="col-12 col-m-9 offset-m-3 error">{{precio_error}}</div>
      {{endif precio_error}}
    </div>
    <div class="row my-2 align-center">
      <label class="col-12 col-m-3" for="stock">Stock</label>
      <input class="col-12 col-m-9" {{~readonly}} type="number" min="0" name="stock" id="stock" value="{{stock}}" />
      {{if stock_error}}
      <div class="col-12 col-m-9 offset-m-3 error">{{stock_error}}</div>
      {{endif stock_error}}
    </div>
    <div class="row my-2 align-center">
      <label class="col-12 col-m-3" for="disponible">Disponibilidad</label>
      <select class="col-12 col-m-9" name="disponible" id="disponible" {{if ~readonly}} readonly disabled {{endif ~readonly}}>
        <option value="ACT" {{disponible_act}}>Disponible</option>
        <option value="INA" {{disponible_ina}}>No disponible</option>
      </select>
      {{if disponible_error}}
      <div class="col-12 col-m-9 offset-m-3 error">{{disponible_error}}</div>
      {{endif disponible_error}}
    </div>
    <div class="row my-2 align-center">
      <label class="col-12 col-m-3" for="imagenUrl">URL de imagen</label>
      <input class="col-12 col-m-9" {{~readonly}} type="text" name="imagenUrl" id="imagenUrl" placeholder="https://..." value="{{imagenUrl}}" />
    </div>
    {{endwith producto}}
    <div class="row my-4 align-center flex-end">
      {{if showCommitBtn}}
      <button class="primary col-12 col-m-2" type="submit" name="btnConfirmar">Confirmar</button>
      &nbsp;
      {{endif showCommitBtn}}
      <button class="col-12 col-m-2" type="button" id="btnCancelar">
        {{if showCommitBtn}}
        Cancelar
        {{endif showCommitBtn}}
        {{ifnot showCommitBtn}}
        Regresar
        {{endifnot showCommitBtn}}
      </button>
    </div>
  </form>
</section>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const btnCancelar = document.getElementById("btnCancelar");
    btnCancelar.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      window.location.assign("index.php?page=Catalogo_Productos");
    });
  });
</script>
