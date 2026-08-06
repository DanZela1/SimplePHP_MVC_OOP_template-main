<?php

namespace Dao\Ventas;

use Dao\Table;

class Orden extends Table
{
    public static function crearOrden(array $ordenData, array $detalle): int
    {
        $conn = self::getConn();

        try {
            $conn->beginTransaction();

            $sqlOrden = "INSERT INTO ordenes
                (paypalOrderId, paypalCaptureId, estado, moneda, subtotal, impuesto, total, payerEmail, payerNombre, fechaCreacion)
                VALUES
                (:paypalOrderId, :paypalCaptureId, :estado, :moneda, :subtotal, :impuesto, :total, :payerEmail, :payerNombre, NOW())";
            self::executeNonQuery($sqlOrden, $ordenData, $conn);

            $idOrden = (int) $conn->lastInsertId();

            $sqlDetalle = "INSERT INTO orden_detalle
                (idOrden, idProducto, nombreProducto, precioUnitario, cantidad, subtotalLinea)
                VALUES
                (:idOrden, :idProducto, :nombreProducto, :precioUnitario, :cantidad, :subtotalLinea)";

            foreach ($detalle as $linea) {
                $linea["idOrden"] = $idOrden;
                self::executeNonQuery($sqlDetalle, $linea, $conn);
            }

            $conn->commit();
            return $idOrden;
        } catch (\Exception $ex) {
            $conn->rollBack();
            throw $ex;
        }
    }

    public static function getOrdenPorPayPalId(string $paypalOrderId)
    {
        $sql = "SELECT * FROM ordenes WHERE paypalOrderId = :paypalOrderId";
        return self::obtenerUnRegistro($sql, ["paypalOrderId" => $paypalOrderId]);
    }
}
