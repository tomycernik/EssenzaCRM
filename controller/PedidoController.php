<?php

class PedidoController
{
    private $pedidoModel;
    private $presenter;
    private $sessionManager;

    public function __construct($pedidoModel, $presenter, $sessionManager)
    {
        $this->pedidoModel = $pedidoModel;
        $this->presenter = $presenter;
        $this->sessionManager = $sessionManager;
    }

    public function get()
    {
        $clientes = $this->pedidoModel->obtenerClientes();
        $productos = $this->pedidoModel->obtenerProductos();
        $data = [
            'clientes' => $clientes,
            'productos' => $productos
        ];
        $this->presenter->render("registroPedido", $data);
    }

    public function registrarPedido()
    {
        $userId = $_SESSION['userID'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['cliente'], $_POST['fecha'], $_POST['productos'], $_POST['costo_productos'], $_POST['costo_envio'], $_POST['costo_total'], $_POST['comentario'], $_POST['medio_de_pago']) && !empty($_POST['productos'])) {
                $clienteId = $_POST['cliente'];
                $fecha = $_POST['fecha'];
                $productos = $_POST['productos'];
                $costoProductos = $_POST['costo_productos'];
                $costoEnvio = $_POST['costo_envio'];
                $costoTotal = $_POST['costo_total'];
                $comentario = $_POST['comentario'];
                $factura = $_POST['factura'] ?? '';
                $medioDePago = $_POST['medio_de_pago'];
                $vendedorId = $userId;

                // Consolidar las cantidades de productos repetidos
                $productosConsolidados = [];
                foreach ($productos as $producto) {
                    if (isset($producto['id'], $producto['cantidad']) && $producto['cantidad'] > 0) {
                        $productoId = $producto['id'];
                        $cantidad = $producto['cantidad'];

                        if (isset($productosConsolidados[$productoId])) {
                            $productosConsolidados[$productoId] += $cantidad;
                        } else {
                            $productosConsolidados[$productoId] = $cantidad;
                        }
                    }
                }

                // Verifica que haya al menos un producto válido
                if (empty($productosConsolidados)) {
                    $data["message"] = "Debe agregar al menos un producto con una cantidad válida.";
                    $data["showMessage"] = true;
                    $clientes = $this->pedidoModel->obtenerClientes();
                    $productos = $this->pedidoModel->obtenerProductos();
                    $data["clientes"] = $clientes;
                    $data["productos"] = $productos;
                    $this->presenter->render("registroPedido", $data);
                    return;
                }

                $pedidoId = $this->pedidoModel->crearPedido($clienteId, $fecha, $costoProductos, $costoEnvio, $costoTotal, $comentario, $factura, $medioDePago, $vendedorId);

                foreach ($productosConsolidados as $productoId => $cantidad) {
                    $this->pedidoModel->agregarProductoAPedido($pedidoId, $productoId, $cantidad);
                }
                $this->mostrarPedidos();
            } else {
                $data["message"] = "Faltó completar uno o más campos. Por favor, intente nuevamente.";
                $data["showMessage"] = true;
                $clientes = $this->pedidoModel->obtenerClientes();
                $productos = $this->pedidoModel->obtenerProductos();
                $data["clientes"] = $clientes;
                $data["productos"] = $productos;
                $this->presenter->render("registroPedido", $data);
            }
        }
    }


    public function mostrarPedidos() {
        $usuarioId = $_SESSION['userID'];
        $isAdmin = $_SESSION['isAdmin'];
        $isVendedor = $_SESSION['isVendedor'];
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        $registros_por_pagina = 100;

        $mes = isset($_GET['mes']) ? $_GET['mes'] : '';
        // Si el parámetro 'anio' no está presente o es una cadena vacía, asignamos el valor por defecto
        $anio = isset($_GET['anio']) && $_GET['anio'] !== '' ? $_GET['anio'] : 2025;

        $provincia = isset($_GET['provincia']) ? $_GET['provincia'] : '';
        $localidad = isset($_GET['localidad']) ? $_GET['localidad'] : '';
        $metodoPago = isset($_GET['metodoPago']) ? $_GET['metodoPago'] : '';
        $fechaInicio = isset($_GET['fechaInicio']) ? $_GET['fechaInicio'] : '';
        $fechaFin = isset($_GET['fechaFin']) ? $_GET['fechaFin'] : '';
        $diaSemana = isset($_GET['diaSemana']) ? $_GET['diaSemana'] : '';

        // Obtener los totales, incluidos los tipos de producto
        $totales = $this->pedidoModel->obtenerTotales($mes, $anio, $provincia, $localidad, $metodoPago, $fechaInicio, $fechaFin, $diaSemana);
        $total_registros = $totales['total_pedidos'];
        $total_productos = $totales['total_productos'];
        $total_costo_productos = $totales['total_costo_productos'];
        $total_concentrados = $totales['total_concentrados'];
        $total_difusores = $totales['total_difusores'];
        $total_textiles = $totales['total_textiles'];

        $total_paginas = ceil($total_registros / $registros_por_pagina);
        $paginaAnteriorDeshabilitada = $pagina <= 1;
        $paginaSiguienteDeshabilitada = $pagina >= $total_paginas;

        if ($isAdmin) {
            $pedidos = $this->pedidoModel->obtenerPedidosConDetalles($pagina, $registros_por_pagina, $mes, $anio, $provincia, $localidad, $metodoPago, $fechaInicio, $fechaFin, $diaSemana);
        } elseif ($isVendedor) {
            $pedidos = $this->pedidoModel->obtenerPedidosDelVendedor($usuarioId, $pagina, $registros_por_pagina, $mes, $anio, $provincia, $localidad, $metodoPago, $fechaInicio, $fechaFin, $diaSemana);
        } else {
            $pedidos = [];
        }
 
        $data = [
            'pedidos' => $pedidos,
            'pagina' => $pagina,
            'total_paginas' => $total_paginas,
            'paginaAnteriorDeshabilitada' => $paginaAnteriorDeshabilitada,
            'paginaSiguienteDeshabilitada' => $paginaSiguienteDeshabilitada,
            'paginaAnterior' => $pagina - 1,
            'paginaSiguiente' => $pagina + 1,
            'mes' => $mes,
            'anio' => $anio, // Pasar el año seleccionado a la vista
            'anio2025' => $anio === '2025',
            'anio2024' => $anio === '2024',
            'provincia' => $provincia,
            'localidad' => $localidad,
            'metodoPago' => $metodoPago,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'diaSemana' => $diaSemana,
            'total_pedidos' => $total_registros,
            'total_productos' => $total_productos,
            'total_costo_productos' => $total_costo_productos,
            'total_concentrados' => $total_concentrados,
            'total_difusores' => $total_difusores,
            'total_textiles' => $total_textiles,
        ];

        $this->presenter->render("pedidos", $data);
    }


    public function borrarPedido()
    {
        if (isset($_GET['id'])) {
            $pedido_id = intval($_GET['id']);

            $this->pedidoModel->eliminarPedido($pedido_id);

            $this->mostrarPedidos();
        }

    }
}