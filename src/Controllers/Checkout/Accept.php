<?php

namespace Controllers\Checkout;

use Controllers\PublicController;
use Utilities\Cart\CarritoManager;
use Dao\Ventas\Orden;

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
                $detalleCarrito = CarritoManager::getDetalle();
                $resumen = CarritoManager::getResumen();

                try {
                    CarritoManager::finalizarCompra();
                    $this->registrarOrden($result, $detalleCarrito, $resumen);
                    $this->registrarTransaccion($result);
                    $dataview["pagoExitoso"] = true;
                    $dataview["mensaje"] = "¡Pago confirmado! Tu compra fue procesada correctamente.";
                } catch (\Exception $ex) {
                    $dataview["pagoExitoso"] = false;
                    $dataview["mensaje"] = "El pago se procesó, pero hubo un problema al registrar tu compra: " . $ex->getMessage();
                }

                unset($_SESSION["orderid"]);
            } else {
                $dataview["pagoExitoso"] = false;
                $dataview["mensaje"] = $this->mensajeDeError($result);
                unset($_SESSION["orderid"]);
            }

            $dataview["orderjson"] = json_encode($result, JSON_PRETTY_PRINT);
        } else {
            $dataview["pagoExitoso"] = false;
            $dataview["mensaje"] = "No hay una orden válida para procesar.";
        }

        \Views\Renderer::render("paypal/accept", $dataview);
    }

    private function mensajeDeError($result): string
    {
        $issue = $result->details[0]->issue ?? null;

        switch ($issue) {
            case "INSTRUMENT_DECLINED":
                return "Tu método de pago fue rechazado (posibles fondos insuficientes). Tu carrito sigue intacto, podés intentar de nuevo con otro método de pago.";
            case "PAYER_CANNOT_PAY":
                return "PayPal no permitió completar este pago con la cuenta usada. Tu carrito sigue intacto.";
            default:
                return "No se pudo confirmar el pago con PayPal. Tu carrito sigue intacto, podés intentar de nuevo.";
        }
    }

    private function registrarOrden($result, array $detalleCarrito, array $resumen): void
    {
        $capture = $result->purchase_units[0]->payments->captures[0] ?? null;

        $payerEmail = $result->payment_source->paypal->email_address
            ?? $result->payer->email_address
            ?? "";
        $payerNombre = trim(
            ($result->payer->name->given_name ?? "") . " " . ($result->payer->name->surname ?? "")
        );

        $ordenData = array(
            "paypalOrderId"   => $result->id,
            "paypalCaptureId" => $capture->id ?? "",
            "estado"          => $result->status,
            "moneda"          => $capture->amount->currency_code ?? "USD",
            "subtotal"        => $resumen["subtotal"],
            "impuesto"        => $resumen["impuesto"],
            "total"           => $resumen["total"],
            "payerEmail"      => $payerEmail,
            "payerNombre"     => $payerNombre,
        );

        $detalle = array();
        foreach ($detalleCarrito as $linea) {
            $detalle[] = array(
                "idProducto"     => $linea["idProducto"],
                "nombreProducto" => $linea["nombre"],
                "precioUnitario" => $linea["precio"],
                "cantidad"       => $linea["cantidad"],
                "subtotalLinea"  => $linea["subtotal"],
            );
        }

        Orden::crearOrden($ordenData, $detalle);
    }

    private function registrarTransaccion($result): void
    {
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
}