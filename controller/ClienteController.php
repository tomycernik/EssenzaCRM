<?php

class ClienteController
{
    private $clienteModel;
    private $pedidoModel;
    private $presenter;

    public function __construct($clienteModel, $pedidoModel, $presenter)
    {
        $this->clienteModel = $clienteModel;
        $this->pedidoModel = $pedidoModel;
        $this->presenter = $presenter;
    }


    public function get() {
        $clientes = $this->clienteModel->obtenerClientesConTotalGastado();

        $data = [
            'clientes' => $clientes
        ];

        $this->presenter->render("clientes", $data);
    }

    public function mostrarPerfilCliente() {
        $userId = $_GET['id'];
        $isAdmin = $_SESSION['isAdmin'];
        $isVendedor = $_SESSION['isVendedor'];
        $vendedorId = $_SESSION['userID'];

        if ($isAdmin) {
            // Obtener el perfil del cliente
            $perfilCliente = $this->clienteModel->obtenerPerfilCliente($userId);
        } elseif ($isVendedor) {
            // Obtener los pedidos del cliente por el vendedor
            $perfilCliente = $this->clienteModel->obtenerPedidosPerfilClientePorVendedor($userId, $vendedorId);
        } else {
            // Si el usuario no es admin ni vendedor
            $perfilCliente = [
                'cliente' => null,
                'pedidos' => [],
                'cantidadPedidos' => 0,
                'dineroGastado' => 0,
                'promedioDiasEntrePedidos' => null,
                'fechaUltimoPedido' => null // Agregar el campo de fecha del último pedido
            ];
        }

        $this->presenter->render('perfilCliente', $perfilCliente);
    }

    public function editarCliente() {
        $clienteId = isset($_GET['id']) ? (int)$_GET['id'] : 0; // Convierte a entero

        if ($clienteId <= 0) {
            // Manejo de error, el ID no es válido
            $this->presenter->render("error", ["message" => "ID de cliente no válido."]);
            return;
        }

        $cliente = $this->clienteModel->obtenerClientePorId($clienteId);

        // Lista de provincias
        $provincias = [
            "Buenos Aires", "Catamarca", "Chaco", "Chubut", "Córdoba",
            "Corrientes", "Entre Ríos", "Formosa", "Jujuy", "La Pampa",
            "La Rioja", "Mendoza", "Misiones", "Neuquén", "Río Negro",
            "Salta", "San Juan", "San Luis", "Santa Cruz", "Santa Fe",
            "Santiago del Estero", "Tierra del Fuego", "Tucumán"
        ];

        // Preparar datos para la vista
        $data = [
            'cliente_id' => $cliente['id'],
            'cliente_nombre' => $cliente['nombre'],
            'cliente_dni' => $cliente['dni'],
            'cliente_celular' => $cliente['celular'],
            'cliente_provincia' => $cliente['provincia'],
            'cliente_localidad' => $cliente['localidad'],
            'cliente_codigo_postal' => $cliente['codigo_postal'],
            'cliente_direccion' => $cliente['direccion'],
            'provincias' => array_map(function($provincia) use ($cliente) {
                return [
                    'nombre' => $provincia,
                    'selected' => ($provincia === $cliente['provincia']) ? 'selected' : ''
                ];
            }, $provincias),
            'showMessage' => false,
            'message' => ''
        ];

        $this->presenter->render("editarCliente", $data);
    }



    public function actualizarCliente()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $formData = $_POST;

            $updatedData = [
                'id' => $formData['id'],
                'nombre' => $formData['nombre'],
                'dni' => $formData['dni'],
                'celular' => $formData['celular'],
                'provincia' => $formData['provincia'],
                'localidad' => $formData['localidad'],
                'codigo_postal' => $formData['codigo_postal'],
                'direccion' => $formData['direccion'],
            ];

            $success = $this->clienteModel->actualizarCliente($updatedData);

            if ($success) {
                $this->get();
            } else {
                $this->editarCliente();
            }
        }
    }

    public function obtenerDatosClienteResumido()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cliente_id'])) {
            $clienteId = $_POST['cliente_id'];
            $cliente = $this->clienteModel->obtenerDatosCliente($clienteId);
            header('Content-Type: application/json');
            echo json_encode($cliente);
            exit();
        }
    }
    public function buscarPorDni()
    {
        if (isset($_POST['dni'])) {
            $dni = $_POST['dni'];
            $clientes = $this->clienteModel->buscarPorDni($dni);

            header('Content-Type: application/json');
            echo json_encode($clientes);
            exit();
        }
    }
    public function obtenerTodos()
    {
        $clientes = $this->clienteModel->obtenerTodos(); // Método para obtener todos los clientes
        header('Content-Type: application/json');
        echo json_encode($clientes);
        exit();
    }

}