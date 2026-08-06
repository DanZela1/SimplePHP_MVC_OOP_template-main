CREATE TABLE ordenes (
    idOrden INT AUTO_INCREMENT PRIMARY KEY,
    paypalOrderId VARCHAR(50) NOT NULL,
    paypalCaptureId VARCHAR(50) NOT NULL,
    estado VARCHAR(20) NOT NULL,
    moneda VARCHAR(3) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    impuesto DECIMAL(10,2) NOT NULL,
    total DECIMAL(10,2) NOT NULL,
    payerEmail VARCHAR(150),
    payerNombre VARCHAR(150),
    fechaCreacion DATETIME NOT NULL
);

CREATE TABLE orden_detalle (
    idOrdenDetalle INT AUTO_INCREMENT PRIMARY KEY,
    idOrden INT NOT NULL,
    idProducto INT NOT NULL,
    nombreProducto VARCHAR(150) NOT NULL,
    precioUnitario DECIMAL(10,2) NOT NULL,
    cantidad INT NOT NULL,
    subtotalLinea DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (idOrden) REFERENCES ordenes(idOrden)
);