<?php
namespace Dao;

class TransaccionDao
{
    public static function insertar($datos)
    {
        $conn = Dao::getConn();
        $sql = "INSERT INTO transacciones 
                (paypal_order_id, paypal_capture_id, monto, moneda, estado, payer_email, fecha_transaccion)
                VALUES (:paypal_order_id, :paypal_capture_id, :monto, :moneda, :estado, :payer_email, :fecha_transaccion)";
        $stmt = $conn->prepare($sql);
        return $stmt->execute($datos);
    }

    public static function obtenerTodas()
    {
        $conn = Dao::getConn();
        $sql = "SELECT * FROM transacciones ORDER BY fecha_transaccion DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
?>