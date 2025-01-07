<?php
class RegistroController
{
    private $registerModel;
    private $presenter;
    private $pedidoModel;

    public function __construct($registerModel, $pedidoModel, $presenter)
    {
        $this->registerModel = $registerModel;
        $this->pedidoModel = $pedidoModel;
        $this->presenter = $presenter;
    }

    public function get() {
        $data = [];
        $this->presenter->render("registro", $data);
    }

    public function registrarUsuario() {
        $data = ["showMessage" => false];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset(
                $_POST["nombre"],
                $_POST["username"],
                $_POST["password"],
            )) {

                $formData = $_POST;
                $data = $formData;


                    $userData = [
                        'nombre' => $formData['nombre'],
                        'username' => $formData['username'],
                        'password' => $formData['password'],
                    ];

                $registrationSuccess = $this->registerModel->registrarUsuario($userData);
                    if ($registrationSuccess) {
                        $this->presenter->render("login");
                    } else {
                        $this->mostrarErrorDeRegistro("Error al registrar el usuario.", $data);
                    }
                }
            } else {
                $data["message"] = "Faltó completar uno o más campos. Por favor, intente nuevamente.";
                $data["showMessage"] = true;
                $this->presenter->render("registro", $data);
            }

    }

    public function registrarCliente()
    {
        $data = ["showMessage" => false];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset(
                $_POST["nombre"],
                $_POST["dni"],
                $_POST["celular"],
                $_POST["provincia"],
                $_POST["localidad"],
                $_POST["codigo_postal"],
                $_POST["direccion"]

            )) {
                $formData = $_POST;
                $data = $formData;
                $dni = $formData['dni'];

                // Verificar si el cliente con el DNI ya existe
                $clienteExistente = $this->registerModel->obtenerClientePorDni($dni);

                if ($clienteExistente) {
                    // Si el cliente ya existe, mostrar un mensaje de error
                    $data["message"] = "Ya existe un cliente con el DNI: $dni.";
                    $data["showMessage"] = true;
                    $this->presenter->render("registroCliente", $data);
                } else {
                    // Si no existe, continuar con el registro
                    $userData = [
                        'nombre' => $formData['nombre'],
                        'dni' => $formData['dni'],
                        'celular' => $formData['celular'],
                        'provincia' => $formData['provincia'],
                        'localidad' => $formData['localidad'],
                        'codigo_postal' => $formData['codigo_postal'],
                        'direccion' => $formData['direccion']
                    ];

                    $registrationSuccess = $this->registerModel->registrarCliente($userData);
                    if ($registrationSuccess) {
                        $this->mostrarRegistroClienteCorrectamente("El cliente ha sido agregado correctamente", $data);
                    } else {
                        $this->mostrarErrorDeRegistroCliente("Error al registrar el cliente.", $data);
                    }
                }
            } else {
                $clientes = $this->pedidoModel->obtenerClientes();
                $productos = $this->pedidoModel->obtenerProductos();
                $data = [
                    'clientes' => $clientes,
                    'productos' => $productos
                ];
                $data["message"] = "Faltó completar uno o más campos. Por favor, intente nuevamente.";
                $data["showMessage"] = true;
                $this->presenter->render("registroCliente", $data);
            }
        } else {
            $clientes = $this->pedidoModel->obtenerClientes();
            $productos = $this->pedidoModel->obtenerProductos();
            $data = [
                'clientes' => $clientes,
                'productos' => $productos
            ];
            $data["message"] = "Ingrese el nombre del cliente";
            $data["showMessage"] = true;
            $this->presenter->render("registroCliente", $data);
        }
    }





    private function mostrarErrorDeRegistro($message, $data) {
        $data["message"] = $message;
        $data['showMessage'] = true;
        $this->presenter->render("registro", $data);
    }

    private function mostrarRegistroClienteCorrectamente($message, $data) {
        $data["message"] = $message;
        $data['showMessage'] = true;
        $clientes = $this->pedidoModel->obtenerClientes();
        $productos = $this->pedidoModel->obtenerProductos();
        $data = [
            'clientes' => $clientes,
            'productos' => $productos
        ];
        $this->presenter->render("registroPedido", $data);
    }

    private function mostrarErrorDeRegistroCliente($message, $data) {
        $data["message"] = $message;
        $data['showMessage'] = true;
        $clientes = $this->pedidoModel->obtenerClientes();
        $productos = $this->pedidoModel->obtenerProductos();
        $data = [
            'clientes' => $clientes,
            'productos' => $productos
        ];
        $this->presenter->render("registroPedido", $data);
    }
 


}