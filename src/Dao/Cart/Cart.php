<?php

namespace Dao\Cart;

class Cart extends \Dao\Table
{
    public static function getProductosDisponibles()
    {
        $sqlAllProductosActivos = "SELECT * from products where productStatus in ('ACT');";
        $productosDisponibles = self::obtenerRegistros($sqlAllProductosActivos, array());

        //Sacar el stock de productos con carretilla autorizada
        $deltaAutorizada = \Utilities\Cart\CartFns::getAuthTimeDelta();
        $sqlCarretillaAutorizada = "select productId, sum(crrctd) as reserved
            from carretilla where TIME_TO_SEC(TIMEDIFF(now(), crrfching)) <= :delta
            group by productId;";
        $prodsCarretillaAutorizada = self::obtenerRegistros(
            $sqlCarretillaAutorizada,
            array("delta" => $deltaAutorizada)
        );
        //Sacar el stock de productos con carretilla no autorizada
        $deltaNAutorizada = \Utilities\Cart\CartFns::getUnAuthTimeDelta();
        $sqlCarretillaNAutorizada = "select productId, sum(crrctd) as reserved
            from carretillaanom where TIME_TO_SEC(TIMEDIFF(now(), crrfching)) <= :delta
            group by productId;";
        $prodsCarretillaNAutorizada = self::obtenerRegistros(
            $sqlCarretillaNAutorizada,
            array("delta" => $deltaNAutorizada)
        );
        $productosCurados = array();
        foreach ($productosDisponibles as $producto) {
            if (!isset($productosCurados[$producto["productId"]])) {
                $productosCurados[$producto["productId"]] = $producto;
            }
        }
        foreach ($prodsCarretillaAutorizada as $producto) {
            if (isset($productosCurados[$producto["productId"]])) {
                $productosCurados[$producto["productId"]]["productStock"] -= $producto["reserved"];
            }
        }
        foreach ($prodsCarretillaNAutorizada as $producto) {
            if (isset($productosCurados[$producto["productId"]])) {
                $productosCurados[$producto["productId"]]["productStock"] -= $producto["reserved"];
            }
        }
        $productosDisponibles = null;
        $prodsCarretillaAutorizada = null;
        $prodsCarretillaNAutorizada = null;
        return $productosCurados;
    }

    public static function getProductoDisponible($productId)
    {
        $sqlAllProductosActivos = "SELECT * from products where productStatus in ('ACT') and productId=:productId;";
        $productosDisponibles = self::obtenerRegistros($sqlAllProductosActivos, array("productId" => $productId));

        //Sacar el stock de productos con carretilla autorizada
        $deltaAutorizada = \Utilities\Cart\CartFns::getAuthTimeDelta();
        $sqlCarretillaAutorizada = "select productId, sum(crrctd) as reserved
            from carretilla where productId=:productId and TIME_TO_SEC(TIMEDIFF(now(), crrfching)) <= :delta
            group by productId;";
        $prodsCarretillaAutorizada = self::obtenerRegistros(
            $sqlCarretillaAutorizada,
            array("productId" => $productId, "delta" => $deltaAutorizada)
        );
        //Sacar el stock de productos con carretilla no autorizada
        $deltaNAutorizada = \Utilities\Cart\CartFns::getUnAuthTimeDelta();
        $sqlCarretillaNAutorizada = "select productId, sum(crrctd) as reserved
            from carretillaanom where productId = :productId and TIME_TO_SEC(TIMEDIFF(now(), crrfching)) <= :delta
            group by productId;";
        $prodsCarretillaNAutorizada = self::obtenerRegistros(
            $sqlCarretillaNAutorizada,
            array("productId" => $productId, "delta" => $deltaNAutorizada)
        );
        $productosCurados = array();
        foreach ($productosDisponibles as $producto) {
            if (!isset($productosCurados[$producto["productId"]])) {
                $productosCurados[$producto["productId"]] = $producto;
            }
        }
        foreach ($prodsCarretillaAutorizada as $producto) {
            if (isset($productosCurados[$producto["productId"]])) {
                $productosCurados[$producto["productId"]]["productStock"] -= $producto["reserved"];
            }
        }
        foreach ($prodsCarretillaNAutorizada as $producto) {
            if (isset($productosCurados[$producto["productId"]])) {
                $productosCurados[$producto["productId"]]["productStock"] -= $producto["reserved"];
            }
        }
        $productosDisponibles = null;
        $prodsCarretillaAutorizada = null;
        $prodsCarretillaNAutorizada = null;
        return $productosCurados;
    }

    public static function getProducto($productId)
    {
        $sqlAllProductosActivos = "SELECT * from products where productId=:productId;";
        $productosDisponibles = self::obtenerRegistros($sqlAllProductosActivos, array("productId" => $productId));
        return $productosDisponibles;
    }

    public static function addToCart($userId, $productId, $cantidad, $precio)
{
    $sql = "INSERT INTO carretilla (usercod, productId, crrctd, crrprc, crrfching)
            VALUES (:usercod, :productId, :cantidad, :precio, NOW())
            ON DUPLICATE KEY UPDATE crrctd = crrctd + :cantidad2, crrfching = NOW()";
    return self::executeNonQuery($sql, array(
        "usercod" => $userId,
        "productId" => $productId,
        "cantidad" => $cantidad,
        "precio" => $precio,
        "cantidad2" => $cantidad
    ));
}

public static function getAll($userId)
{
    $sql = "SELECT c.productId, p.productName, p.productImgUrl, c.crrctd as cantidad,
                   c.crrprc as precio, (c.crrctd * c.crrprc) as subtotal
            FROM carretilla c
            INNER JOIN products p ON p.productId = c.productId
            WHERE c.usercod = :usercod";
    return self::obtenerRegistros($sql, array("usercod" => $userId));
}

public static function updateCantidad($userId, $productId, $cantidad)
{
    if ($cantidad <= 0) {
        return self::removeFromCart($userId, $productId);
    }
    $sql = "UPDATE carretilla SET crrctd = :cantidad, crrfching = NOW()
            WHERE usercod = :usercod AND productId = :productId";
    return self::executeNonQuery($sql, array(
        "cantidad" => $cantidad,
        "usercod" => $userId,
        "productId" => $productId
    ));
}

public static function removeFromCart($userId, $productId)
{
    $sql = "DELETE FROM carretilla WHERE usercod = :usercod AND productId = :productId";
    return self::executeNonQuery($sql, array("usercod" => $userId, "productId" => $productId));
}

public static function clearCart($userId)
{
    $sql = "DELETE FROM carretilla WHERE usercod = :usercod";
    return self::executeNonQuery($sql, array("usercod" => $userId));
}

public static function getTotal($userId)
{
    $sql = "SELECT SUM(crrctd * crrprc) as total FROM carretilla WHERE usercod = :usercod";
    $row = self::obtenerUnRegistro($sql, array("usercod" => $userId));
    return $row ? (float)$row["total"] : 0.0;
}
}
