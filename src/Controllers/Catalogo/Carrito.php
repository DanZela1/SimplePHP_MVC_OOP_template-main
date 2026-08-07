<?php

namespace Controllers\Catalogo;

use Controllers\PublicController;
use Views\Renderer;
use Utilities\Cart\CarritoManager;
use Utilities\Paypal\PayPalOrder;
use Utilities\PayPal\PayPalRestApi;

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
          $this->irAPayPal();
          break;

        default:
          break;
      }
    } catch (\Exception $ex) {
      $this->mensajeError = $ex->getMessage();
    }
  }



  private function irAPayPal(): void
  {
    $detalle = CarritoManager::getDetalle();

    if (count($detalle) === 0) {
      $this->mensajeError = "El carrito está vacío";
      return;
    }

    foreach ($detalle as $linea) {
      if ($linea["noDisponible"] || !empty($linea["stockInsuficiente"])) {
        $this->mensajeError = "Hay productos en tu carrito que ya no están disponibles o no tienen stock suficiente";
        return;
      }
    }

    $PayPalOrder = new PayPalOrder(
      "order" . time() . rand(1000, 9999),
      "http://localhost/negocios_web/SimplePHP_MVC_OOP_template-main/index.php?page=Checkout_Error",
      "http://localhost/negocios_web/SimplePHP_MVC_OOP_template-main/index.php?page=Checkout_Accept"
    );

    foreach ($detalle as $linea) {
      $impuestoUnitario = round($linea["precio"] * CarritoManager::getTasaImpuesto(), 2);
      $PayPalOrder->addItem(
        $linea["nombre"],
        $linea["nombre"],
        (string) $linea["idProducto"],
        $linea["precio"],
        $impuestoUnitario,
        $linea["cantidad"],
        "PHYSICAL_GOODS"
      );
    }

    $PayPalRestApi = new PayPalRestApi(
      \Utilities\Context::getContextByKey("PAYPAL_CLIENT_ID"),
      \Utilities\Context::getContextByKey("PAYPAL_CLIENT_SECRET")
    );
    $PayPalRestApi->getAccessToken();
    $response = $PayPalRestApi->createOrder($PayPalOrder);

    if (!isset($response->id)) {
      $this->mensajeError = "No se pudo conectar con PayPal. Intenta de nuevo.";
      return;
    }

    $_SESSION["orderid"] = $response->id;

    foreach ($response->links as $link) {
      if ($link->rel == "approve") {
        \Utilities\Site::redirectTo($link->href);
      }
    }

    $this->mensajeError = "PayPal no devolvió un link de aprobación. Intenta de nuevo.";
  }
}
