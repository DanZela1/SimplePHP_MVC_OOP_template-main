<?php

namespace Controllers\Catalogo;

use Controllers\PublicController;
use Views\Renderer;
use Dao\Catalogo\Productos as ProductosDao;
use Dao\Catalogo\Categorias as CategoriasDao;
use Utilities\Site;
use Utilities\Validators;

class Producto extends PublicController
{
  private $viewData = [];
  private $mode = "DSP";
  private $modeDescriptions = [
    "DSP" => "Detalle de %s %s",
    "INS" => "Nuevo Producto",
    "UPD" => "Editar %s %s",
    "DEL" => "Eliminar %s %s"
  ];
  private $readonly = "";
  private $showCommitBtn = true;
  private $producto = [
    "idProducto" => 0,
    "idCategoria" => 0,
    "nombre" => "",
    "descripcion" => "",
    "precio" => 0,
    "stock" => 0,
    "disponible" => "ACT",
    "imagenUrl" => ""
  ];
  private $producto_xss_token = "";

  public function run(): void
  {
    try {
      $this->getData();
      if ($this->isPostBack()) {
        if ($this->validateData()) {
          $this->handlePostAction();
        }
      }
      $this->setViewData();
      Renderer::render("Catalogo/producto", $this->viewData);
    } catch (\Exception $ex) {
      Site::redirectToWithMsg(
        "index.php?page=Catalogo_Productos",
        $ex->getMessage()
      );
    }
  }

  private function getData()
  {
    $this->mode = $_GET["mode"] ?? "NOF";
    if (isset($this->modeDescriptions[$this->mode])) {
      $this->readonly = $this->mode === "DEL" ? "readonly" : "";
      $this->showCommitBtn = $this->mode !== "DSP";
      if ($this->mode !== "INS") {
        $this->producto = ProductosDao::getProductoById(intval($_GET["idProducto"]));
        if (!$this->producto) {
          throw new \Exception("No se encontró el Producto", 1);
        }
      }
    } else {
      throw new \Exception("Formulario cargado en modalidad invalida", 1);
    }
  }

  private function validateData()
  {
    $errors = [];
    $this->producto_xss_token = $_POST["producto_xss_token"] ?? "";
    $this->producto["idProducto"] = intval($_POST["idProducto"] ?? "");
    $this->producto["idCategoria"] = intval($_POST["idCategoria"] ?? "");
    $this->producto["nombre"] = strval($_POST["nombre"] ?? "");
    $this->producto["descripcion"] = strval($_POST["descripcion"] ?? "");
    $this->producto["precio"] = floatval($_POST["precio"] ?? "");
    $this->producto["stock"] = intval($_POST["stock"] ?? "");
    $this->producto["disponible"] = strval($_POST["disponible"] ?? "");
    $this->producto["imagenUrl"] = strval($_POST["imagenUrl"] ?? "");

    if (Validators::IsEmpty($this->producto["nombre"])) {
      $errors["nombre_error"] = "El nombre del producto es requerido";
    }

    if ($this->producto["idCategoria"] <= 0) {
      $errors["idCategoria_error"] = "La categoría es requerida";
    }

    if ($this->producto["precio"] <= 0) {
      $errors["precio_error"] = "El precio es requerido y debe ser mayor a cero";
    }

    if ($this->producto["stock"] < 0) {
      $errors["stock_error"] = "El stock no puede ser negativo";
    }

    if (!in_array($this->producto["disponible"], ["ACT", "INA"])) {
      $errors["disponible_error"] = "La disponibilidad del producto es invalida";
    }

    if (count($errors) > 0) {
      foreach ($errors as $key => $value) {
        $this->producto[$key] = $value;
      }
      return false;
    }
    return true;
  }

  private function handlePostAction()
  {
    switch ($this->mode) {
      case "INS":
        $this->handleInsert();
        break;
      case "UPD":
        $this->handleUpdate();
        break;
      case "DEL":
        $this->handleDelete();
        break;
      default:
        throw new \Exception("Modo invalido", 1);
        break;
    }
  }

  private function handleInsert()
  {
    $result = ProductosDao::insertProducto(
      $this->producto["idCategoria"],
      $this->producto["nombre"],
      $this->producto["descripcion"],
      $this->producto["precio"],
      $this->producto["stock"],
      $this->producto["disponible"],
      $this->producto["imagenUrl"]
    );
    if ($result > 0) {
      Site::redirectToWithMsg(
        "index.php?page=Catalogo_Productos",
        "Producto creado exitosamente"
      );
    }
  }

  private function handleUpdate()
  {
    $result = ProductosDao::updateProducto(
      $this->producto["idProducto"],
      $this->producto["idCategoria"],
      $this->producto["nombre"],
      $this->producto["descripcion"],
      $this->producto["precio"],
      $this->producto["stock"],
      $this->producto["disponible"],
      $this->producto["imagenUrl"]
    );
    if ($result > 0) {
      Site::redirectToWithMsg(
        "index.php?page=Catalogo_Productos",
        "Producto actualizado exitosamente"
      );
    }
  }

  private function handleDelete()
  {
    $result = ProductosDao::deleteProducto($this->producto["idProducto"]);
    if ($result > 0) {
      Site::redirectToWithMsg(
        "index.php?page=Catalogo_Productos",
        "Producto Eliminado exitosamente"
      );
    }
  }

  private function setViewData(): void
  {
    $this->viewData["mode"] = $this->mode;
    $this->viewData["producto_xss_token"] = $this->producto_xss_token;
    $this->viewData["FormTitle"] = sprintf(
      $this->modeDescriptions[$this->mode],
      $this->producto["idProducto"],
      $this->producto["nombre"]
    );
    $this->viewData["showCommitBtn"] = $this->showCommitBtn;
    $this->viewData["readonly"] = $this->readonly;

    $disponibleKey = "disponible_" . strtolower($this->producto["disponible"]);
    $this->producto[$disponibleKey] = "selected";

    // Categorías para el select del formulario
    $categorias = CategoriasDao::getCategorias(true);
    foreach ($categorias as &$cat) {
      $cat["selected"] = $cat["idCategoria"] == $this->producto["idCategoria"] ? "selected" : "";
    }
    $this->viewData["categorias"] = $categorias;

    $this->viewData["producto"] = $this->producto;
  }
}
?>