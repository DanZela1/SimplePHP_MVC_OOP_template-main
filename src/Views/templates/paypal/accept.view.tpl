<section class="grid">
  {{if pagoExitoso}}
  <div class="row flex align-center">
    <h1 class="col-12">¡Gracias por tu compra!</h1>
  </div>

  <div class="col-12 success">
    {{mensaje}}
  </div>

  <div class="row my-4">
    <p class="col-12">
      Tu pago fue procesado exitosamente. En breve recibirás la confirmación de tu pedido.
    </p>
  </div>
  {{endif pagoExitoso}}

  {{ifnot pagoExitoso}}
  <div class="row flex align-center">
    <h1 class="col-12">No se pudo completar tu compra</h1>
  </div>

  <div class="col-12 error">
    {{mensaje}}
  </div>
  {{endifnot pagoExitoso}}

  <div class="row my-2">
    <div class="col-12">
      {{if pagoExitoso}}
      <a href="index.php?page=Catalogo_CatalogoPublico" class="primary">Seguir comprando</a>
      {{endif pagoExitoso}}
      {{ifnot pagoExitoso}}
      <a href="index.php?page=Catalogo_Carrito" class="primary">Volver al carrito</a>
      &nbsp;
      <a href="index.php?page=Catalogo_CatalogoPublico">Seguir comprando</a>
      {{endifnot pagoExitoso}}
    </div>
  </div>

  <!-- <pre>{{orderjson}}</pre> -->
</section>