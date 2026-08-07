<?php

namespace Controllers\Catalogo;

use Controllers\PrivateController;
use Views\Renderer;
use Dao\Catalogo\Productos as ProductosDao;
use Dao\Catalogo\Categorias as CategoriasDao;
use Utilities\Cart\CarritoManager;

class CatalogoPublico extends PrivateController
{
  private $viewData = [];
  private $partialNombre = "";
  private $idCategoria = 0;
  private $mensaje = "";
  private $mensajeError = "";

  public function run(): void
  {
    $this->getParams();

    if ($this->isPostBack() && isset($_POST["agregarCarrito"])) {
      $this->handleAgregarCarrito();
    }

    $productos = ProductosDao::getCatalogoPublico($this->partialNombre, $this->idCategoria);
    $this->viewData["productos"] = $productos;
    $this->viewData["partialNombre"] = $this->partialNombre;
    $this->viewData["idCategoria"] = $this->idCategoria;
    $this->viewData["mensaje"] = $this->mensaje;
    $this->viewData["mensajeError"] = $this->mensajeError;
    $this->viewData["cantidadCarrito"] = CarritoManager::getCantidadItems();

    $categorias = CategoriasDao::getCategorias(true);
    foreach ($categorias as &$cat) {
      $cat["selected"] = $cat["idCategoria"] == $this->idCategoria ? "selected" : "";
    }
    $this->viewData["categorias"] = $categorias;

    Renderer::render("Catalogo/catalogoPublico", $this->viewData);
  }

  private function getParams(): void
  {
    $this->partialNombre = isset($_GET["partialNombre"]) ? strval($_GET["partialNombre"]) : "";
    $this->idCategoria = isset($_GET["idCategoria"]) ? intval($_GET["idCategoria"]) : 0;
  }

  private function handleAgregarCarrito(): void
  {
    try {
      $idProducto = intval($_POST["idProducto"] ?? 0);
      $cantidad = intval($_POST["cantidad"] ?? 1);
      CarritoManager::addItem($idProducto, $cantidad);
      $this->mensaje = "Producto agregado al carrito";
    } catch (\Exception $ex) {
      $this->mensajeError = $ex->getMessage();
    }
  }
}
?>