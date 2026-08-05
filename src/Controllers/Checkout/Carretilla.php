<?php
namespace Controllers\Checkout;

use Controllers\PrivateController;

class Carretilla extends PrivateController
{
    public function run(): void
    {
        $userId = \Utilities\Security::getUserId();

        if ($this->isPostBack()) {
            $action = $_POST["action"] ?? "";
            $productId = (int)($_POST["productId"] ?? 0);

            switch ($action) {
                case "actualizar":
                    \Dao\Cart\Cart::updateCantidad($userId, $productId, (int)($_POST["cantidad"] ?? 0));
                    break;
                case "eliminar":
                    \Dao\Cart\Cart::removeFromCart($userId, $productId);
                    break;
                case "vaciar":
                    \Dao\Cart\Cart::clearCart($userId);
                    break;
            }

            \Utilities\Site::redirectTo("index.php?page=Checkout_Carretilla");
        }

        $items = \Dao\Cart\Cart::getAll($userId);
        $total = \Dao\Cart\Cart::getTotal($userId);

        \Views\Renderer::render("carretilla", array(
            "items" => $items,
            "total" => number_format($total, 2),
            "carritoVacio" => count($items) === 0
        ));
    }
}