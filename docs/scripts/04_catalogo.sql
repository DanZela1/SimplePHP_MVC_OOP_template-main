CREATE TABLE
    `categorias` (
        `idCategoria` INT NOT NULL AUTO_INCREMENT,
        `nombre` VARCHAR(80) NOT NULL,
        `estado` CHAR(3) NOT NULL DEFAULT 'ACT',
        PRIMARY KEY (`idCategoria`)
    ) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = utf8mb4;

CREATE TABLE
    `productos` (
        `idProducto` INT NOT NULL AUTO_INCREMENT,
        `idCategoria` INT NOT NULL,
        `nombre` VARCHAR(150) NOT NULL,
        `descripcion` TEXT,
        `precio` DECIMAL(10, 2) NOT NULL DEFAULT 0,
        `stock` INT NOT NULL DEFAULT 0,
        `disponible` CHAR(3) NOT NULL DEFAULT 'ACT',
        `imagenUrl` VARCHAR(255) DEFAULT NULL,
        PRIMARY KEY (`idProducto`),
        KEY `idCategoria_idx` (`idCategoria`),
        CONSTRAINT `producto_categoria_key` FOREIGN KEY (`idCategoria`) REFERENCES `categorias` (`idCategoria`) ON DELETE NO ACTION ON UPDATE NO ACTION
    ) ENGINE = InnoDB AUTO_INCREMENT = 1 DEFAULT CHARSET = utf8mb4;

-- Datos de ejemplo
INSERT INTO `categorias` (`nombre`, `estado`) VALUES
    ('Electrónica', 'ACT'),
    ('Hogar', 'ACT'),
    ('Ropa', 'ACT');

INSERT INTO `productos` (`idCategoria`, `nombre`, `descripcion`, `precio`, `stock`, `disponible`, `imagenUrl`) VALUES
    (1, 'Audífonos Bluetooth', 'Audífonos inalámbricos con cancelación de ruido', 899.00, 15, 'ACT', ''),
    (2, 'Set de Sartenes', 'Juego de 3 sartenes antiadherentes', 1250.50, 8, 'ACT', ''),
    (3, 'Camisa Casual', 'Camisa de algodón manga larga', 350.00, 0, 'ACT', '');