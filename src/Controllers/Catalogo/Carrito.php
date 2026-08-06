<?php

namespace Controllers\Catalogo;

use Controllers\PublicController;
use Views\Renderer;
use Utilities\Cart\CarritoManager;

class Carrito extends PublicController
{
  private $viewData = [];
  private $mensaje = "";
  private $mensajeError = "";

  public function run(): void
  {
    if ($this->isPostBack()) {
      $this->handleAction();
    }

    $resumen = CarritoManager::getResumen();
    $this->viewData["detalle"] = CarritoManager::getDetalle();
    $this->viewData["subtotal"] = number_format($resumen["subtotal"], 2);
    $this->viewData["impuesto"] = number_format($resumen["impuesto"], 2);
    $this->viewData["total"] = number_format($resumen["total"], 2);
    $this->viewData["tasaImpuesto"] = $resumen["tasaImpuesto"];
    $this->viewData["cantidadItems"] = $resumen["cantidadItems"];
    $this->viewData["carritoVacio"] = $resumen["cantidadItems"] === 0;
    $this->viewData["mensaje"] = $this->mensaje;
    $this->viewData["mensajeError"] = $this->mensajeError;

    Renderer::render("Catalogo/carrito", $this->viewData);
  }

  private function handleAction(): void
  {
    $accion = $_POST["accion"] ?? "";
    try {
      switch ($accion) {
        case "actualizar":
          $cantidades = $_POST["cantidad"] ?? [];
          foreach ($cantidades as $idProducto => $cantidad) {
            CarritoManager::updateItem(intval($idProducto), intval($cantidad));
          }
          $this->mensaje = "Carrito actualizado";
          break;

        case "eliminar":
          $idProducto = intval($_POST["idProducto"] ?? 0);
          CarritoManager::removeItem($idProducto);
          $this->mensaje = "Producto eliminado del carrito";
          break;

        case "vaciar":
          CarritoManager::clear();
          $this->mensaje = "Carrito vaciado";
          break;

        case "finalizar":
          $resultado = CarritoManager::finalizarCompra();
          if ($resultado["ok"]) {
            $this->mensaje = "Compra finalizada exitosamente";
          } else {
            $this->mensajeError = "Stock insuficiente para \"" . $resultado["nombre"] . "\". Ajusta la cantidad e intenta de nuevo.";
          }
          break;

        default:
          break;
      }
    } catch (\Exception $ex) {
      $this->mensajeError = $ex->getMessage();
    }
  }
}
