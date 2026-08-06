<?php

namespace Controllers\Catalogo;

use Controllers\PublicController;
use Utilities\Context;
use Utilities\Paging;
use Dao\Catalogo\Productos as DaoProductos;
use Dao\Catalogo\Categorias as DaoCategorias;
use Views\Renderer;

class Productos extends PublicController
{
  private $partialNombre = "";
  private $idCategoria = 0;
  private $disponible = "";
  private $orderBy = "";
  private $orderDescending = false;
  private $pageNumber = 1;
  private $itemsPerPage = 10;
  private $viewData = [];
  private $productos = [];
  private $productosCount = 0;
  private $pages = 0;

  public function run(): void
  {
    $this->getParamsFromContext();
    $this->getParams();
    $tmpProductos = DaoProductos::getProductos(
      $this->partialNombre,
      $this->idCategoria,
      $this->disponible,
      $this->orderBy,
      $this->orderDescending,
      $this->pageNumber - 1,
      $this->itemsPerPage
    );
    $this->productos = $tmpProductos["productos"];
    $this->productosCount = $tmpProductos["total"];
    $this->pages = $this->productosCount > 0 ? ceil($this->productosCount / $this->itemsPerPage) : 1;
    if ($this->pageNumber > $this->pages) {
      $this->pageNumber = $this->pages;
    }
    $this->setParamsToContext();
    $this->setParamsToDataView();
    Renderer::render("Catalogo/productos", $this->viewData);
  }

  private function getParams(): void
  {
    $this->partialNombre = isset($_GET["partialNombre"]) ? $_GET["partialNombre"] : $this->partialNombre;
    $this->idCategoria = isset($_GET["idCategoria"]) ? intval($_GET["idCategoria"]) : $this->idCategoria;
    $this->disponible = isset($_GET["disponible"]) && in_array($_GET["disponible"], ['ACT', 'INA', 'TOD']) ? $_GET["disponible"] : $this->disponible;
    if ($this->disponible === "TOD") {
      $this->disponible = "";
    }
    $this->orderBy = isset($_GET["orderBy"]) && in_array($_GET["orderBy"], ["idProducto", "nombre", "precio", "stock", "clear"]) ? $_GET["orderBy"] : $this->orderBy;
    if ($this->orderBy === "clear") {
      $this->orderBy = "";
    }
    $this->orderDescending = isset($_GET["orderDescending"]) ? boolval($_GET["orderDescending"]) : $this->orderDescending;
    $this->pageNumber = isset($_GET["pageNum"]) ? intval($_GET["pageNum"]) : $this->pageNumber;
    $this->itemsPerPage = isset($_GET["itemsPerPage"]) ? intval($_GET["itemsPerPage"]) : $this->itemsPerPage;
  }

  private function getParamsFromContext(): void
  {
    $this->partialNombre = Context::getContextByKey("productos_partialNombre");
    $this->idCategoria = intval(Context::getContextByKey("productos_idCategoria"));
    $this->disponible = Context::getContextByKey("productos_disponible");
    $this->orderBy = Context::getContextByKey("productos_orderBy");
    $this->orderDescending = boolval(Context::getContextByKey("productos_orderDescending"));
    $this->pageNumber = intval(Context::getContextByKey("productos_page"));
    $this->itemsPerPage = intval(Context::getContextByKey("productos_itemsPerPage"));
    if ($this->pageNumber < 1) $this->pageNumber = 1;
    if ($this->itemsPerPage < 1) $this->itemsPerPage = 10;
  }

  private function setParamsToContext(): void
  {
    Context::setContext("productos_partialNombre", $this->partialNombre, true);
    Context::setContext("productos_idCategoria", $this->idCategoria, true);
    Context::setContext("productos_disponible", $this->disponible, true);
    Context::setContext("productos_orderBy", $this->orderBy, true);
    Context::setContext("productos_orderDescending", $this->orderDescending, true);
    Context::setContext("productos_page", $this->pageNumber, true);
    Context::setContext("productos_itemsPerPage", $this->itemsPerPage, true);
  }

  private function setParamsToDataView(): void
  {
    $this->viewData["partialNombre"] = $this->partialNombre;
    $this->viewData["idCategoria"] = $this->idCategoria;
    $this->viewData["disponible"] = $this->disponible;
    $this->viewData["orderBy"] = $this->orderBy;
    $this->viewData["orderDescending"] = $this->orderDescending;
    $this->viewData["pageNum"] = $this->pageNumber;
    $this->viewData["itemsPerPage"] = $this->itemsPerPage;
    $this->viewData["productosCount"] = $this->productosCount;
    $this->viewData["pages"] = $this->pages;
    $this->viewData["productos"] = $this->productos;

    if ($this->orderBy !== "") {
      $orderByKey = "Order" . ucfirst($this->orderBy);
      $orderByKeyNoOrder = "OrderBy" . ucfirst($this->orderBy);
      $this->viewData[$orderByKeyNoOrder] = true;
      if ($this->orderDescending) {
        $orderByKey .= "Desc";
      }
      $this->viewData[$orderByKey] = true;
    }

    $disponibleKey = "disponible_" . ($this->disponible === "" ? "TOD" : $this->disponible);
    $this->viewData[$disponibleKey] = "selected";

    // Categorías para el filtro (select), marcando la seleccionada
    $categorias = DaoCategorias::getCategorias(false);
    foreach ($categorias as &$cat) {
      $cat["selected"] = $cat["idCategoria"] == $this->idCategoria ? "selected" : "";
    }
    $this->viewData["categorias"] = $categorias;

    $pagination = Paging::getPagination(
      $this->productosCount,
      $this->itemsPerPage,
      $this->pageNumber,
      "index.php?page=Catalogo_Productos",
      "Catalogo_Productos"
    );
    $this->viewData["pagination"] = $pagination;
  }
}
?>