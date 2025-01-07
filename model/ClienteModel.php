<?php

class ClienteModel {
    private $database;

    public function __construct($database) {
        $this->database = $database;
    }

    public function obtenerPerfilCliente($clienteId)
    {
        // Obtener información del cliente
        $queryCliente = "SELECT nombre, provincia, localidad, celular, dni FROM usuario WHERE id = ?";
        $stmtCliente = $this->database->prepare($queryCliente);
        $stmtCliente->bind_param("i", $clienteId);
        $stmtCliente->execute();
        $resultCliente = $stmtCliente->get_result();
        $cliente = $resultCliente->fetch_assoc();
        $stmtCliente->close();

        // Verificar si se obtuvo la información del cliente
        if (!$cliente) {
            return ['error' => 'Cliente no encontrado'];
        }

        // Obtener pedidos del cliente
        $queryPedidos = "SELECT p.id AS pedido_id, 
                   p.fecha, 
                    p.costo_total, 
                    p.factura, 
                    p.medio_de_pago, 
                    pp.producto_id, 
                    t.nombre AS tipo, 
                    a.nombre AS aroma, 
                    pp.cantidad, 
                    p.cantidad_productos
             FROM pedido p
             JOIN pedido_producto pp ON p.id = pp.pedido_id
             JOIN producto prod ON pp.producto_id = prod.id
             JOIN tipo_producto t ON prod.tipo_id = t.id
             JOIN aroma a ON prod.aroma_id = a.id
             WHERE p.cliente_id = ?";
        $stmtPedidos = $this->database->prepare($queryPedidos);
        $stmtPedidos->bind_param("i", $clienteId);
        $stmtPedidos->execute();
        $resultPedidos = $stmtPedidos->get_result();
        $rawData = $resultPedidos->fetch_all(MYSQLI_ASSOC);
        $stmtPedidos->close();

        // Verificar si se obtuvieron pedidos del cliente
        if (empty($rawData)) {
            return [
                'cliente' => $cliente,
                'pedidos' => [],
                'cantidadPedidos' => 0,
                'dineroGastado' => 0,
                'promedioDiasEntrePedidos' => null,
                'fechaUltimoPedido' => null
            ];
        }

        $pedidos = [];
        $totalCostos = 0;
        $fechasPedidos = [];
        $fechaUltimoPedido = null;

        foreach ($rawData as $row) {
            $pedidoId = $row['pedido_id'];
            if (!isset($pedidos[$pedidoId])) {
                $pedidos[$pedidoId] = [
                    'pedido_id' => $pedidoId,
                    'fecha' => $row['fecha'],
                    'costo_total' => $row['costo_total'],
                    'factura' => $row['factura'],
                    'medio_de_pago' => $row['medio_de_pago'],
                    'cantidad_productos' => $row['cantidad_productos'],
                    'productos' => []
                ];
                // Guardamos el costo total del pedido solo una vez
                $totalCostos += $row['costo_total'];
                // Guardamos la fecha del pedido
                $fechasPedidos[] = $row['fecha'];
                $fechaUltimoPedido = $row['fecha']; // Actualiza la fecha del último pedido
            }

            $pedidos[$pedidoId]['productos'][] = [
                'producto_id' => $row['producto_id'],
                'tipo' => $row['tipo'],
                'aroma' => $row['aroma'],
                'cantidad' => $row['cantidad']
            ];
        }

        // Calcular cantidad de pedidos y dinero gastado
        $cantidadPedidos = count($pedidos);

        // Calcular el promedio de días entre pedidos
        if ($cantidadPedidos > 1) {
            sort($fechasPedidos);
            $totalDias = 0;
            for ($i = 1; $i < $cantidadPedidos; $i++) {
                $date1 = new DateTime($fechasPedidos[$i - 1]);
                $date2 = new DateTime($fechasPedidos[$i]);
                $interval = $date1->diff($date2);
                $totalDias += $interval->days;
            }
            $promedioDiasEntrePedidos = $totalDias / ($cantidadPedidos - 1);
        } else {
            $promedioDiasEntrePedidos = null; // No se puede calcular el promedio con un solo pedido
        }



        return [
            'cliente' => $cliente,
            'pedidos' => array_values($pedidos),
            'cantidadPedidos' => $cantidadPedidos,
            'dineroGastado' => $totalCostos,
            'promedioDiasEntrePedidos' => $promedioDiasEntrePedidos,
            'fechaUltimoPedido' => $fechaUltimoPedido
        ];
    }


    public function obtenerPedidosPerfilClientePorVendedor($clienteId, $vendedorId) {
        // Obtener datos del cliente
        $queryCliente = "SELECT nombre, provincia, localidad, celular, dni 
                     FROM usuario 
                     WHERE id = ?";
        $stmtCliente = $this->database->prepare($queryCliente);
        $stmtCliente->bind_param("i", $clienteId);
        $stmtCliente->execute();
        $resultCliente = $stmtCliente->get_result();
        $cliente = $resultCliente->fetch_assoc();
        $stmtCliente->close();

        // Verificar si se obtuvo la información del cliente
        if (!$cliente) {
            return ['error' => 'Cliente no encontrado'];
        }

        // Obtener estadísticas generales de todos los pedidos del cliente
        // Obtener estadísticas generales de todos los pedidos del cliente
        $queryEstadisticas = "SELECT p.fecha, p.costo_total 
                      FROM pedido p 
                      WHERE cliente_id = ?";
        $stmtEstadisticas = $this->database->prepare($queryEstadisticas);
        $stmtEstadisticas->bind_param("i", $clienteId);
        $stmtEstadisticas->execute();
        $resultEstadisticas = $stmtEstadisticas->get_result();
        $datosEstadisticas = $resultEstadisticas->fetch_all(MYSQLI_ASSOC);
        $stmtEstadisticas->close();

// Calcular estadísticas
        $cantidadPedidos = count($datosEstadisticas);
        $totalCostos = array_sum(array_column($datosEstadisticas, 'costo_total'));
        $fechasPedidos = array_column($datosEstadisticas, 'fecha');
        $fechaUltimoPedido = $cantidadPedidos > 0 ? end($fechasPedidos) : null;

// Calcular el promedio de días entre pedidos
        if ($cantidadPedidos > 1) {
            sort($fechasPedidos); // Asegurar que estén ordenadas
            $totalDias = 0;
            for ($i = 1; $i < $cantidadPedidos; $i++) {
                $date1 = new DateTime($fechasPedidos[$i - 1]);
                $date2 = new DateTime($fechasPedidos[$i]);
                $interval = $date1->diff($date2);
                $totalDias += $interval->days;
            }
            $promedioDiasEntrePedidos = $totalDias / ($cantidadPedidos - 1);
        } else {
            $promedioDiasEntrePedidos = null; // No se puede calcular el promedio con un solo pedido
        }

        // Obtener pedidos específicos del vendedor
        $queryPedidos = "SELECT p.id AS pedido_id, 
                   p.fecha,
                    p.costo_total, 
                    p.factura, 
                    p.medio_de_pago, 
                    pp.producto_id, 
                    t.nombre AS tipo, 
                    a.nombre AS aroma, 
                    pp.cantidad, 
                    p.cantidad_productos
             FROM pedido p
             JOIN pedido_producto pp ON p.id = pp.pedido_id
             JOIN producto prod ON pp.producto_id = prod.id
             JOIN tipo_producto t ON prod.tipo_id = t.id
             JOIN aroma a ON prod.aroma_id = a.id
             WHERE p.cliente_id = ? AND p.id_vendedor = ?";

        $stmtPedidos = $this->database->prepare($queryPedidos);
        $stmtPedidos->bind_param("ii", $clienteId, $vendedorId);
        $stmtPedidos->execute();
        $resultPedidos = $stmtPedidos->get_result();
        $rawData = $resultPedidos->fetch_all(MYSQLI_ASSOC);
        $stmtPedidos->close();

        $pedidos = [];
        foreach ($rawData as $row) {
            $pedidoId = $row['pedido_id'];
            if (!isset($pedidos[$pedidoId])) {
                $pedidos[$pedidoId] = [
                    'pedido_id' => $pedidoId,
                    'fecha' => $row['fecha'],
                    'costo_total' => $row['costo_total'],
                    'factura' => $row['factura'],
                    'medio_de_pago' => $row['medio_de_pago'],
                    'cantidad_productos' => $row['cantidad_productos'],
                    'productos' => []
                ];
            }

            $pedidos[$pedidoId]['productos'][] = [
                'producto_id' => $row['producto_id'],
                'tipo' => $row['tipo'],
                'aroma' => $row['aroma'],
                'cantidad' => $row['cantidad']
            ];
        }


        return [
            'cliente_id' => $clienteId,
            'vendedor_id' => $vendedorId,
            'pedidos' => array_values($pedidos),
            'cantidadPedidos' => $cantidadPedidos,
            'dineroGastado' => $totalCostos,
            'promedioDiasEntrePedidos' => $promedioDiasEntrePedidos,
            'fechaUltimoPedido' => $fechaUltimoPedido,
            'cliente' => $cliente

        ];
    }

    public function obtenerDatosCliente($id)
    {
        $query = "SELECT * FROM usuario WHERE id = ?";
        $stmt = $this->database->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $cliente = $result->fetch_assoc();
        return $cliente;
    }


    public function obtenerClientesConTotalGastado() {
        $query = "SELECT c.id AS cliente_id, 
       c.nombre AS cliente_nombre, 
       c.dni AS cliente_dni, 
       COALESCE(SUM(p.costo_total), 0) AS total_gastado, 
       c.provincia AS cliente_provincia, 
       c.localidad AS cliente_localidad
          FROM usuario c  
          LEFT JOIN pedido p ON c.id = p.cliente_id
          WHERE c.rol = 'cliente'
          GROUP BY c.id, c.nombre, c.dni
          ORDER BY total_gastado DESC";

        $stmt = $this->database->prepare($query);
        if ($stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($this->database->error));
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            die('Query failed: ' . htmlspecialchars($this->database->error));
        }

        $clientes = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $clientes;
    }


    public function buscarPorDni($dni)
    {
        $query = "SELECT * FROM usuario WHERE dni = ?";
        $stmt = $this->database->prepare($query);
        $stmt->bind_param('s', $dni);
        $stmt->execute();
        $result = $stmt->get_result();

        $clientes = [];
        while ($row = $result->fetch_assoc()) {
            $clientes[] = $row;
        }

        $stmt->close();
        return $clientes;
    }
    public function obtenerTodos()
    {
        // Prepara la consulta SQL para obtener todos los clientes
        $query = "SELECT * FROM usuario";
        $stmt = $this->database->prepare($query);
        
        $stmt->execute();

        $result = $stmt->get_result();

        $clientes = [];
        while ($row = $result->fetch_assoc()) {
            $clientes[] = $row;
        }

        $stmt->close();

        return $clientes;
    }

    public function obtenerClientePorId($id)
    {
        $query = "SELECT * FROM usuario WHERE id = ?";
        $stmt = $this->database->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function actualizarCliente($data)
    {
        $query = "UPDATE usuario SET nombre = ?, dni = ?, celular = ?, provincia = ?, localidad = ?, codigo_postal = ?, direccion = ? WHERE id = ?";
        $stmt = $this->database->prepare($query);
        $stmt->bind_param(
            "sssssssi",
            $data['nombre'],
            $data['dni'],
            $data['celular'],
            $data['provincia'],
            $data['localidad'],
            $data['codigo_postal'],
            $data['direccion'],
            $data['id']
        );
        return $stmt->execute();
    }

}