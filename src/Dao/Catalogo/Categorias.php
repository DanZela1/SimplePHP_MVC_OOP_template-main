<?php

namespace Dao\Catalogo;

use Dao\Table;

class Categorias extends Table
{
  public static function getCategorias(bool $soloActivas = true)
  {
    $sqlstr = "SELECT idCategoria, nombre, estado FROM categorias";
    $params = [];
    if ($soloActivas) {
      $sqlstr .= " WHERE estado = :estado";
      $params["estado"] = "ACT";
    }
    $sqlstr .= " ORDER BY nombre ASC";
    return self::obtenerRegistros($sqlstr, $params);
  }

  public static function getCategoriaById(int $idCategoria)
  {
    $sqlstr = "SELECT idCategoria, nombre, estado FROM categorias WHERE idCategoria = :idCategoria";
    $params = ["idCategoria" => $idCategoria];
    return self::obtenerUnRegistro($sqlstr, $params);
  }
}
?>