<?php

namespace Dao\Catalogo;

use Dao\Table;

class Productos extends Table
{
  /**
   * Listado de productos con búsqueda, filtros por categoría/disponibilidad,
   * orden y paginación (mismo patrón usado por Dao\Recetas\Recetas::getRecetas)
   */
  public static function getProductos(
    string $partialNombre = "",
    int $idCategoria = 0,
    string $disponible = "",
    string $orderBy = "",
    bool $orderDescending = false,
    int $page = 0,
    int $itemsPerPage = 10
  ) {
    $sqlstr = "SELECT p.idProducto, p.idCategoria, p.nombre, p.descripcion, p.precio, p.stock, p.disponible, p.imagenUrl, c.nombre as categoriaNombre
                FROM productos p INNER JOIN categorias c ON p.idCategoria = c.idCategoria";
    $sqlstrCount = "SELECT COUNT(*) as count FROM productos p INNER JOIN categorias c ON p.idCategoria = c.idCategoria";
    $conditions = [];
    $params = [];

    if ($partialNombre != "") {
      $conditions[] = "p.nombre LIKE :partialNombre";
      $params["partialNombre"] = "%" . $partialNombre . "%";
    }

    if ($idCategoria > 0) {
      $conditions[] = "p.idCategoria = :idCategoria";
      $params["idCategoria"] = $idCategoria;
    }

    if (!in_array($disponible, ["ACT", "INA", ""])) {
      throw new \Exception("Error Processing Request disponible has invalid value");
    }
    if ($disponible != "") {
      $conditions[] = "p.disponible = :disponible";
      $params["disponible"] = $disponible;
    }

    if (count($conditions) > 0) {
      $sqlstr .= " WHERE " . implode(" AND ", $conditions);
      $sqlstrCount .= " WHERE " . implode(" AND ", $conditions);
    }

    if (!in_array($orderBy, ["idProducto", "nombre", "precio", "stock", ""])) {
      throw new \Exception("Error Processing Request OrderBy has invalid value");
    }
    if ($orderBy != "") {
      $sqlstr .= " ORDER BY p." . $orderBy;
      if ($orderDescending) {
        $sqlstr .= " DESC";
      }
    }

    $numeroDeRegistros = self::obtenerUnRegistro($sqlstrCount, $params)["count"];
    $pagesCount = $itemsPerPage > 0 ? ceil($numeroDeRegistros / $itemsPerPage) : 1;

    if ($pagesCount > 0 && $page > $pagesCount - 1) {
      $page = $pagesCount - 1;
    }
    if ($page < 0) {
      $page = 0;
    }

    $offset = $page * $itemsPerPage;
    $sqlstr .= " LIMIT " . $offset . ", " . $itemsPerPage;

    $registros = self::obtenerRegistros($sqlstr, $params);

    return [
      "productos" => $registros,
      "total" => $numeroDeRegistros,
      "page" => $page,
      "itemsPerPage" => $itemsPerPage
    ];
  }

  /**
   * Catálogo público: solo productos disponibles (independiente de si hay o no stock,
   * el stock se valida al agregar al carrito)
   */
  public static function getCatalogoPublico(string $partialNombre = "", int $idCategoria = 0)
  {
    $sqlstr = "SELECT p.idProducto, p.idCategoria, p.nombre, p.descripcion, p.precio, p.stock, p.disponible, p.imagenUrl, c.nombre as categoriaNombre
                FROM productos p INNER JOIN categorias c ON p.idCategoria = c.idCategoria
                WHERE p.disponible = 'ACT'";
    $params = [];

    if ($partialNombre != "") {
      $sqlstr .= " AND p.nombre LIKE :partialNombre";
      $params["partialNombre"] = "%" . $partialNombre . "%";
    }
    if ($idCategoria > 0) {
      $sqlstr .= " AND p.idCategoria = :idCategoria";
      $params["idCategoria"] = $idCategoria;
    }
    $sqlstr .= " ORDER BY p.nombre ASC";

    return self::obtenerRegistros($sqlstr, $params);
  }

  public static function getProductoById(int $idProducto)
  {
    $sqlstr = "SELECT p.idProducto, p.idCategoria, p.nombre, p.descripcion, p.precio, p.stock, p.disponible, p.imagenUrl
                FROM productos p WHERE p.idProducto = :idProducto";
    $params = ["idProducto" => $idProducto];
    return self::obtenerUnRegistro($sqlstr, $params);
  }

  public static function insertProducto(
    int $idCategoria,
    string $nombre,
    string $descripcion,
    float $precio,
    int $stock,
    string $disponible,
    string $imagenUrl
  ) {
    $sqlstr = "INSERT INTO productos (idCategoria, nombre, descripcion, precio, stock, disponible, imagenUrl)
                VALUES (:idCategoria, :nombre, :descripcion, :precio, :stock, :disponible, :imagenUrl)";
    $params = [
      "idCategoria" => $idCategoria,
      "nombre" => $nombre,
      "descripcion" => $descripcion,
      "precio" => $precio,
      "stock" => $stock,
      "disponible" => $disponible,
      "imagenUrl" => $imagenUrl,
    ];
    return self::executeNonQuery($sqlstr, $params);
  }

  public static function updateProducto(
    int $idProducto,
    int $idCategoria,
    string $nombre,
    string $descripcion,
    float $precio,
    int $stock,
    string $disponible,
    string $imagenUrl
  ) {
    $sqlstr = "UPDATE productos SET idCategoria = :idCategoria, nombre = :nombre, descripcion = :descripcion,
                precio = :precio, stock = :stock, disponible = :disponible, imagenUrl = :imagenUrl
                WHERE idProducto = :idProducto";
    $params = [
      "idProducto" => $idProducto,
      "idCategoria" => $idCategoria,
      "nombre" => $nombre,
      "descripcion" => $descripcion,
      "precio" => $precio,
      "stock" => $stock,
      "disponible" => $disponible,
      "imagenUrl" => $imagenUrl,
    ];
    return self::executeNonQuery($sqlstr, $params);
  }

  public static function deleteProducto(int $idProducto)
  {
    $sqlstr = "DELETE FROM productos WHERE idProducto = :idProducto";
    $params = ["idProducto" => $idProducto];
    return self::executeNonQuery($sqlstr, $params);
  }

  /**
   * Procesa el checkout del carrito de forma transaccional:
   * valida y descuenta stock de cada línea. Si algún producto no tiene
   * stock suficiente, se revierte todo (no se descuenta nada).
   *
   * @param array $items [idProducto => cantidad]
   * @return array ["ok" => bool, "idProducto" => int, "nombre" => string]
   */
  public static function procesarCompra(array $items): array
  {
    $conn = self::getConn();
    $conn->beginTransaction();
    try {
      foreach ($items as $idProducto => $cantidad) {
        $sqlSel = "SELECT nombre, stock FROM productos WHERE idProducto = :idProducto AND disponible = 'ACT' FOR UPDATE";
        $producto = self::obtenerUnRegistro($sqlSel, ["idProducto" => $idProducto], $conn);

        if (!$producto) {
          $conn->rollBack();
          return ["ok" => false, "idProducto" => $idProducto, "nombre" => "(producto no encontrado)"];
        }
        if ($producto["stock"] < $cantidad) {
          $conn->rollBack();
          return ["ok" => false, "idProducto" => $idProducto, "nombre" => $producto["nombre"]];
        }

        $sqlUpd = "UPDATE productos SET stock = stock - :cantidad WHERE idProducto = :idProducto";
        self::executeNonQuery($sqlUpd, ["cantidad" => $cantidad, "idProducto" => $idProducto], $conn);
      }
      $conn->commit();
      return ["ok" => true, "idProducto" => 0, "nombre" => ""];
    } catch (\Exception $ex) {
      $conn->rollBack();
      throw $ex;
    }
  }
}
?>