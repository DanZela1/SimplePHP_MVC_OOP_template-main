<?php

namespace Controllers\Checkout;

use Controllers\PublicController;

class Error extends PublicController
{
    public function run(): void
    {
        $viewData = array(
            "mensaje" => "Hubo un problema al procesar tu pago."
        );
        \Views\Renderer::render("paypal/error", $viewData);
    }
}