<?php

namespace Controllers\Checkout;

use Controllers\PublicController;
use Utilities\Cart\CarritoManager;

class Accept extends PublicController
{
    public function run(): void
    {
        $dataview = array();
        $token = $_GET["token"] ?? "";
        $session_token = $_SESSION["orderid"] ?? "";

        if ($token !== "" && $token == $session_token) {
            $PayPalRestApi = new \Utilities\PayPal\PayPalRestApi(
                \Utilities\Context::getContextByKey("PAYPAL_CLIENT_ID"),
                \Utilities\Context::getContextByKey("PAYPAL_CLIENT_SECRET")
            );
            $result = $PayPalRestApi->captureOrder($session_token);

            if (isset($result->status) && $result->status === "COMPLETED") {
                try {
                    CarritoManager::finalizarCompra();
                    $dataview["mensaje"] = "¡Pago confirmado! Tu compra fue procesada correctamente.";
                    unset($_SESSION["orderid"]);
                } catch (\Exception $ex) {
                    $dataview["mensaje"] = "El pago se procesó, pero hubo un problema con el inventario: " . $ex->getMessage();
                }
            } else {
                $dataview["mensaje"] = "No se pudo confirmar el pago con PayPal.";
            }

            $dataview["orderjson"] = json_encode($result, JSON_PRETTY_PRINT);
        } else {
            $dataview["mensaje"] = "No hay una orden válida para procesar.";
        }

        \Views\Renderer::render("paypal/accept", $dataview);
    }
}
