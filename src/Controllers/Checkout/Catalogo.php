<?php
namespace Controllers\Checkout;

use Controllers\PrivateController;

class Catalogo extends PrivateController
{
    public function run(): void
    {
        if ($this->isPostBack()) {
            $productId = (int)($_POST["productId"] ?? 0);
            $cantidad = max(1, (int)($_POST["cantidad"] ?? 1));

            $disponibles = \Dao\Cart\Cart::getProductoDisponible($productId);
            $stockDisponible = isset($disponibles[$productId])
                ? $disponibles[$productId]["productStock"] : 0;

            if ($cantidad <= $stockDisponible) {
                \Dao\Cart\Cart::addToCart(
                    \Utilities\Security::getUserId(),
                    $productId,
                    $cantidad,
                    $disponibles[$productId]["productPrice"]
                );
                $_SESSION["mensaje"] = "Producto agregado al carrito.";
            } else {
                $_SESSION["mensaje"] = "No hay suficiente stock disponible.";
            }

            \Utilities\Site::redirectTo("index.php?page=Checkout_Catalogo");
        }

        $productos = \Dao\Cart\Cart::getProductosDisponibles();

        \Views\Renderer::render("catalogo", array(
            "productos" => array_values($productos)
        ));
        unset($_SESSION["mensaje"]);
    }
}

?>
