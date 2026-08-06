<?php

namespace Utilities\Cart;

use Dao\Catalogo\Productos as ProductosDao;

class CarritoManager
{
  const SESSION_KEY = "carrito";
  const TASA_IMPUESTO = 0.15; // ISV 15%

  private static function getCarritoCrudo(): array
  {
    if (!isset($_SESSION[self::SESSION_KEY]) || !is_array($_SESSION[self::SESSION_KEY])) {
      $_SESSION[self::SESSION_KEY] = [];
    }
    return $_SESSION[self::SESSION_KEY];
  }

  private static function guardar(array $carrito): void
  {
    $_SESSION[self::SESSION_KEY] = $carrito;
  }

  /**
   * Agrega una cantidad de un producto al carrito, validando disponibilidad y stock
   * @throws \Exception si el producto no existe, no está disponible o no hay stock suficiente
   */
  public static function addItem(int $idProducto, int $cantidad): void
  {
    if ($cantidad <= 0) {
      throw new \Exception("La cantidad debe ser mayor a cero");
    }
    $producto = ProductosDao::getProductoById($idProducto);
    if (!$producto || $producto["disponible"] !== "ACT") {
      throw new \Exception("El producto no está disponible");
    }

    $carrito = self::getCarritoCrudo();
    $cantidadActual = $carrito[$idProducto] ?? 0;
    $cantidadNueva = $cantidadActual + $cantidad;

    if ($cantidadNueva > $producto["stock"]) {
      throw new \Exception("Stock insuficiente para \"" . $producto["nombre"] . "\". Disponible: " . $producto["stock"]);
    }

    $carrito[$idProducto] = $cantidadNueva;
    self::guardar($carrito);
  }

  /**
   * Actualiza la cantidad absoluta de un producto en el carrito.
   * Si la cantidad es 0 o menor, el producto se elimina del carrito.
   * @throws \Exception si no hay stock suficiente
   */
  public static function updateItem(int $idProducto, int $cantidad): void
  {
    $carrito = self::getCarritoCrudo();

    if ($cantidad <= 0) {
      unset($carrito[$idProducto]);
      self::guardar($carrito);
      return;
    }

    $producto = ProductosDao::getProductoById($idProducto);
    if (!$producto || $producto["disponible"] !== "ACT") {
      throw new \Exception("El producto no está disponible");
    }
    if ($cantidad > $producto["stock"]) {
      throw new \Exception("Stock insuficiente para \"" . $producto["nombre"] . "\". Disponible: " . $producto["stock"]);
    }

    $carrito[$idProducto] = $cantidad;
    self::guardar($carrito);
  }

  public static function removeItem(int $idProducto): void
  {
    $carrito = self::getCarritoCrudo();
    unset($carrito[$idProducto]);
    self::guardar($carrito);
  }

  public static function clear(): void
  {
    self::guardar([]);
  }

  public static function getCantidadItems(): int
  {
    return array_sum(self::getCarritoCrudo());
  }

  /**
   * Devuelve el detalle del carrito con los datos actuales de cada producto
   * (nombre, precio, stock disponible) y el subtotal de línea.
   * Si un producto ya no existe o se desactivó, se marca con "noDisponible" = true.
   */
  public static function getDetalle(): array
  {
    $carrito = self::getCarritoCrudo();
    $detalle = [];
    foreach ($carrito as $idProducto => $cantidad) {
      $producto = ProductosDao::getProductoById((int)$idProducto);
      if (!$producto || $producto["disponible"] !== "ACT") {
        $detalle[] = [
          "idProducto" => $idProducto,
          "nombre" => $producto["nombre"] ?? "Producto no disponible",
          "precio" => 0,
          "cantidad" => $cantidad,
          "subtotal" => 0,
          "stock" => 0,
          "noDisponible" => true,
        ];
        continue;
      }
      $stockInsuficiente = $cantidad > $producto["stock"];
      $subtotal = $producto["precio"] * $cantidad;
      $detalle[] = [
        "idProducto" => $idProducto,
        "nombre" => $producto["nombre"],
        "precio" => $producto["precio"],
        "cantidad" => $cantidad,
        "subtotal" => $subtotal,
        "stock" => $producto["stock"],
        "noDisponible" => false,
        "stockInsuficiente" => $stockInsuficiente,
      ];
    }
    return $detalle;
  }

  /**
   * Calcula subtotal, impuesto y total del carrito completo
   */
  public static function getResumen(): array
  {
    $detalle = self::getDetalle();
    $subtotal = 0;
    foreach ($detalle as $linea) {
      $subtotal += $linea["subtotal"];
    }
    $impuesto = round($subtotal * self::TASA_IMPUESTO, 2);
    $total = round($subtotal + $impuesto, 2);
    return [
      "subtotal" => round($subtotal, 2),
      "impuesto" => $impuesto,
      "total" => $total,
      "tasaImpuesto" => self::TASA_IMPUESTO * 100,
      "cantidadItems" => self::getCantidadItems(),
    ];
  }

  /**
   * Finaliza la compra: valida y descuenta stock transaccionalmente.
   * Si tiene éxito, vacía el carrito.
   * @return array ["ok" => bool, "nombre" => string] nombre del producto que falló si ok=false
   */
  public static function finalizarCompra(): array
  {
    $carrito = self::getCarritoCrudo();
    if (count($carrito) === 0) {
      throw new \Exception("El carrito está vacío");
    }
    $resultado = ProductosDao::procesarCompra($carrito);
    if ($resultado["ok"]) {
      self::clear();
    }
    return $resultado;
  }
}
?>