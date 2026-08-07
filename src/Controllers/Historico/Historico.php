<?php

namespace Controllers\Historico;

use Controllers\PublicController;

class Historico extends PublicController
{
    public function run(): void
    {
        $viewData = array();

        $viewData["transacciones"] = \Dao\TransaccionDao::obtenerTodas();

        \Views\Renderer::render("Historico/Historico", $viewData);
    }
}