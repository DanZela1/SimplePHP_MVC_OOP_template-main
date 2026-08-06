<h1>Catálogo de Productos</h1>
<section class="grid">
  <div class="row">
    <form class="col-12" action="index.php" method="get">
      <div class="flex align-center">
        <div class="col-9 row">
          <input type="hidden" name="page" value="Catalogo_Productos">
          <label class="col-3" for="partialNombre">Nombre</label>
          <input class="col-9" type="text" name="partialNombre" id="partialNombre" value="{{partialNombre}}" />
          <label class="col-3" for="idCategoria">Categoría</label>
          <select class="col-9" name="idCategoria" id="idCategoria">
            <option value="0">Todas</option>
            {{foreach categorias}}
            <option value="{{idCategoria}}" {{selected}}>{{nombre}}</option>
            {{endfor categorias}}
          </select>
          <label class="col-3" for="disponible">Disponibilidad</label>
          <select class="col-9" name="disponible" id="disponible">
            <option value="TOD" {{disponible_TOD}}>Todos</option>
            <option value="ACT" {{disponible_ACT}}>Disponible</option>
            <option value="INA" {{disponible_INA}}>No disponible</option>
          </select>
        </div>
        <div class="col-3 align-end">
          <button type="submit">Filtrar</button>
        </div>
      </div>
    </form>
  </div>
</section>
<section class="WWList">
  <table>
    <thead>
      <tr>
        <th>
          {{ifnot OrderByIdProducto}}
          <a href="index.php?page=Catalogo_Productos&orderBy=idProducto&orderDescending=0">Id <i class="fas fa-sort"></i></a>
          {{endifnot OrderByIdProducto}}
          {{if OrderIdProductoDesc}}
          <a href="index.php?page=Catalogo_Productos&orderBy=clear&orderDescending=0">Id <i class="fas fa-sort-down"></i></a>
          {{endif OrderIdProductoDesc}}
          {{if OrderIdProducto}}
          <a href="index.php?page=Catalogo_Productos&orderBy=idProducto&orderDescending=1">Id <i class="fas fa-sort-up"></i></a>
          {{endif OrderIdProducto}}
        </th>
        <th class="left">
          {{ifnot OrderByNombre}}
          <a href="index.php?page=Catalogo_Productos&orderBy=nombre&orderDescending=0">Nombre <i class="fas fa-sort"></i></a>
          {{endifnot OrderByNombre}}
          {{if OrderNombreDesc}}
          <a href="index.php?page=Catalogo_Productos&orderBy=clear&orderDescending=0">Nombre <i class="fas fa-sort-down"></i></a>
          {{endif OrderNombreDesc}}
          {{if OrderNombre}}
          <a href="index.php?page=Catalogo_Productos&orderBy=nombre&orderDescending=1">Nombre <i class="fas fa-sort-up"></i></a>
          {{endif OrderNombre}}
        </th>
        <th>Categoría</th>
        <th>
          {{ifnot OrderByPrecio}}
          <a href="index.php?page=Catalogo_Productos&orderBy=precio&orderDescending=0">Precio <i class="fas fa-sort"></i></a>
          {{endifnot OrderByPrecio}}
          {{if OrderPrecioDesc}}
          <a href="index.php?page=Catalogo_Productos&orderBy=clear&orderDescending=0">Precio <i class="fas fa-sort-down"></i></a>
          {{endif OrderPrecioDesc}}
          {{if OrderPrecio}}
          <a href="index.php?page=Catalogo_Productos&orderBy=precio&orderDescending=1">Precio <i class="fas fa-sort-up"></i></a>
          {{endif OrderPrecio}}
        </th>
        <th>
          {{ifnot OrderByStock}}
          <a href="index.php?page=Catalogo_Productos&orderBy=stock&orderDescending=0">Stock <i class="fas fa-sort"></i></a>
          {{endifnot OrderByStock}}
          {{if OrderStockDesc}}
          <a href="index.php?page=Catalogo_Productos&orderBy=clear&orderDescending=0">Stock <i class="fas fa-sort-down"></i></a>
          {{endif OrderStockDesc}}
          {{if OrderStock}}
          <a href="index.php?page=Catalogo_Productos&orderBy=stock&orderDescending=1">Stock <i class="fas fa-sort-up"></i></a>
          {{endif OrderStock}}
        </th>
        <th>Disponible</th>
        <th><a href="index.php?page=Catalogo_Producto&mode=INS">Nuevo</a></th>
      </tr>
    </thead>
    <tbody>
      {{foreach productos}}
      <tr>
        <td>{{idProducto}}</td>
        <td><a class="link" href="index.php?page=Catalogo_Producto&mode=DSP&idProducto={{idProducto}}">{{nombre}}</a></td>
        <td>{{categoriaNombre}}</td>
        <td class="right">L. {{precio}}</td>
        <td class="right">{{stock}}</td>
        <td class="center">{{disponible}}</td>
        <td class="center">
          <a href="index.php?page=Catalogo_Producto&mode=UPD&idProducto={{idProducto}}">Editar</a>
          &nbsp;
          <a href="index.php?page=Catalogo_Producto&mode=DEL&idProducto={{idProducto}}">Eliminar</a>
        </td>
      </tr>
      {{endfor productos}}
    </tbody>
  </table>
  {{pagination}}
</section>