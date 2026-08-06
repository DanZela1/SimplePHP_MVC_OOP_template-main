<?php

namespace Controllers\Checkout;

use Controllers\PublicController;

class Accept extends PublicController
{
    public function run(): void
    {
        $dataview = array();
        $token = $_GET["token"] ?: "";
        $session_token = $_SESSION["orderid"] ?: "";
        if ($token !== "" && $token == $session_token) {
            $PayPalRestApi = new \Utilities\PayPal\PayPalRestApi(
                \Utilities\Context::getContextByKey("PAYPAL_CLIENT_ID"),
                \Utilities\Context::getContextByKey("PAYPAL_CLIENT_SECRET")
            );
            $result = $PayPalRestApi->captureOrder($session_token);
            $dataview["orderjson"] = json_encode($result, JSON_PRETTY_PRINT);

            if (isset($result->status) && $result->status === "COMPLETED") {
                $capture = $result->purchase_units[0]->payments->captures[0] ?? null;

                \Dao\TransaccionDao::insertar(array(
                    "paypal_order_id"   => $result->id ?? "",
                    "paypal_capture_id" => $capture->id ?? "",
                    "monto"             => $capture->amount->value ?? 0,
                    "moneda"            => $capture->amount->currency_code ?? "USD",
                    "estado"            => $result->status ?? "",
                    "payer_email"       => $result->payer->email_address ?? "",
                    "fecha_transaccion" => date("Y-m-d H:i:s"),
                ));
            }
        } else {
            $dataview["orderjson"] = "No Order Available!!!";
        }
        \Views\Renderer::render("paypal/accept", $dataview);
    }
}