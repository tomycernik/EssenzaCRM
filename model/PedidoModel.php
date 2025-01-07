<?php

class PedidoModel
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }


    public function obtenerClientes() {
        $query = "SELECT id, nombre, direccion, codigo_postal, provincia, localidad, celular, rol, dni FROM usuario";
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

    public function obtenerProductos() {
        $query = "SELECT p.id, t.nombre AS tipo, a.nombre AS aroma
          FROM producto p
          JOIN tipo_producto t ON p.tipo_id = t.id
          LEFT JOIN aroma a ON p.aroma_id = a.id";

        $stmt = $this->database->prepare($query);

        if ($stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($this->database->error));
        }

        $stmt->execute();
        $result = $stmt->get_result();

        if ($result === false) {
            die('Query failed: ' . htmlspecialchars($this->database->error));
        }

        $productos = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();
        return $productos;
    }

    public function crearPedido($clienteId, $fecha, $costoProductos, $costoEnvio, $costoTotal, $comentario, $factura, $medioDePago, $registradoPor) {
        // Asegúrate de que los campos de texto no sean nulos
        $comentario = !empty($comentario) ? $comentario : ''; // Si está vacío, usa una cadena vacía
        $factura = !empty($factura) ? $factura : ''; // Si está vacío, usa una cadena vacía

        // Configura la zona horaria y ajusta la fecha
        date_default_timezone_set('America/Argentina/Buenos_Aires');
        $fecha = date('Y-m-d H:i:s', strtotime($fecha)); // Ajusta la fecha a la zona horaria de Argentina

        // Prepara la consulta SQL
        $sql = "INSERT INTO pedido (cliente_id, fecha, costo_productos, costo_envio, costo_total, comentario, factura, medio_de_pago, id_vendedor) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->database->prepare($sql);

        if ($stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($this->database->error));
        }

        $stmt->bind_param("isdddsssi",
            $clienteId,         // entero
            $fecha,             // cadena
            $costoProductos,    // decimal
            $costoEnvio,        // decimal
            $costoTotal,        // decimal
            $comentario,        // cadena
            $factura,           // cadena
            $medioDePago,       // cadena
            $registradoPor      // entero
        );

        $stmt->execute();

        $pedidoId = $stmt->insert_id;
        $stmt->close();

        return $pedidoId;
    }



    public function agregarProductoAPedido($pedidoId, $productoId, $cantidad) {
        $sql = "INSERT INTO pedido_producto (pedido_id, producto_id, cantidad) VALUES (?, ?, ?)";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("iii", $pedidoId, $productoId, $cantidad);
        $stmt->execute();
        $stmt->close();
        $this->actualizarTotalProductos($pedidoId);
    }

    private function actualizarTotalProductos($pedidoId) {
        $sql = "SELECT SUM(cantidad) AS total_productos FROM pedido_producto WHERE pedido_id = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("i", $pedidoId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $totalProductos = $row['total_productos'];
        $stmt->close();

        $sql = "UPDATE pedido SET cantidad_productos = ? WHERE id = ?";
        $stmt = $this->database->prepare($sql);
        $stmt->bind_param("ii", $totalProductos, $pedidoId);
        $stmt->execute();
        $stmt->close();
    }

    public function obtenerPedidosConDetalles($pagina = 1, $limite = 100, $mes = '', $anio = '', $provincia = '', $localidad = '', $metodoPago = '', $fechaInicio = '', $fechaFin = '', $diaSemana = '') {
        $offset = ($pagina - 1) * $limite;

        $query = "SELECT p.id AS pedido_id, 
              p.cliente_id, 
              u1.nombre AS cliente, 
              u1.dni, 
              u1.provincia, 
              u1.localidad, 
              u2.nombre AS vendedor, 
              p.costo_productos, 
              p.costo_envio, 
              p.costo_total, 
              p.comentario, 
              p.factura, 
              p.medio_de_pago, 
              p.cantidad_productos, 
              CASE 
                  WHEN DAYOFWEEK(p.fecha) = 1 THEN 'Domingo' 
                  WHEN DAYOFWEEK(p.fecha) = 2 THEN 'Lunes' 
                  WHEN DAYOFWEEK(p.fecha) = 3 THEN 'Martes' 
                  WHEN DAYOFWEEK(p.fecha) = 4 THEN 'Miércoles' 
                  WHEN DAYOFWEEK(p.fecha) = 5 THEN 'Jueves' 
                  WHEN DAYOFWEEK(p.fecha) = 6 THEN 'Viernes' 
                  WHEN DAYOFWEEK(p.fecha) = 7 THEN 'Sábado' 
              END AS dia_semana, 
              DATE_FORMAT(p.fecha, '%d/%m/%Y') AS fecha 
       FROM pedido p 
       JOIN usuario u1 ON p.cliente_id = u1.id 
       JOIN usuario u2 ON p.id_vendedor = u2.id 
       WHERE 1=1 ";

        $params = [];
        if ($anio !== null) { // Filtrar solo si $anio no es null
            $query .= " AND YEAR(p.fecha) = ?";
            $params[] = $anio;
        }
        if ($mes) {
            $query .= " AND DATE_FORMAT(p.fecha, '%m') = ?";
            $params[] = $mes;
        }
        if ($provincia) {
            $query .= " AND u1.provincia = ?";
            $params[] = $provincia;
        }
        if ($localidad) {
            $query .= " AND u1.localidad LIKE ?";
            $params[] = "%" . $localidad . "%";
        }
        if ($metodoPago) {
            $query .= " AND p.medio_de_pago = ?";
            $params[] = $metodoPago;
        }
        if ($fechaInicio) {
            $query .= " AND p.fecha >= ?";
            $params[] = $fechaInicio;
        }
        if ($fechaFin) {
            $query .= " AND p.fecha <= ?";
            $params[] = $fechaFin;
        }
        if ($diaSemana) {
            $query .= " AND DAYOFWEEK(p.fecha) = ?";
            $diasSemanaMap = [
                'domingo' => 1,
                'lunes' => 2,
                'martes' => 3,
                'miércoles' => 4,
                'jueves' => 5,
                'viernes' => 6,
                'sábado' => 7,
            ];
            $params[] = $diasSemanaMap[strtolower($diaSemana)] ?? null;
        }

        // Paginado
        $query .= " ORDER BY p.id DESC LIMIT ?, ?";
        $params[] = $offset;
        $params[] = $limite;

        // Preparar la consulta
        $stmt = $this->database->prepare($query);
        if (count($params) > 0) {
            $types = str_repeat('s', count($params) - 2) . 'ii';
            $stmt->bind_param($types, ...$params);
        } else {
            $stmt->execute();
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $rawData = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if (empty($rawData)) {
            return [];
        }

        // Procesar los datos y devolver los resultados
        $pedidos = [];
        foreach ($rawData as $row) {
            $pedidoId = $row['pedido_id'];
            $pedidos[$pedidoId] = [
                'pedido_id' => $pedidoId,
                'cliente_id' => $row['cliente_id'],
                'cliente' => $row['cliente'],
                'dni' => $row['dni'],
                'provincia' => $row['provincia'],
                'localidad' => $row['localidad'],
                'vendedor' => $row['vendedor'],
                'costo_productos' => $row['costo_productos'],
                'costo_envio' => $row['costo_envio'],
                'costo_total' => $row['costo_total'],
                'comentario' => $row['comentario'],
                'factura' => $row['factura'],
                'medio_de_pago' => $row['medio_de_pago'],
                'cantidad_productos' => $row['cantidad_productos'],
                'dia_semana' => $row['dia_semana'],
                'fecha' => $row['fecha'],
                'productos' => []
            ];
        }

        // Obtener productos para los pedidos
        $pedidoIds = array_keys($pedidos);
        if (empty($pedidoIds)) {
            return array_values($pedidos);
        }

        $pedidoIdsPlaceholders = implode(',', array_fill(0, count($pedidoIds), '?'));
        $queryProductos = "SELECT pp.pedido_id, pp.producto_id, t.nombre AS tipo, a.nombre AS aroma, pp.cantidad 
           FROM pedido_producto pp 
           JOIN producto prod ON pp.producto_id = prod.id 
           JOIN tipo_producto t ON prod.tipo_id = t.id 
           LEFT JOIN aroma a ON prod.aroma_id = a.id 
           WHERE pp.pedido_id IN ($pedidoIdsPlaceholders)";

        $stmt = $this->database->prepare($queryProductos);
        $stmt->bind_param(str_repeat('i', count($pedidoIds)), ...$pedidoIds);
        $stmt->execute();
        $resultProductos = $stmt->get_result();
        $rawDataProductos = $resultProductos->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Asociar productos a los pedidos correspondientes
        foreach ($rawDataProductos as $row) {
            $pedidos[$row['pedido_id']]['productos'][] = [
                'producto_id' => $row['producto_id'],
                'tipo' => $row['tipo'],
                'aroma' => $row['aroma'],
                'cantidad' => $row['cantidad']
            ];
        }

        return array_values($pedidos);
    }



    public function obtenerTotales($mes = '', $anio = '', $provincia = '', $localidad = '', $metodoPago = '', $fechaInicio = '', $fechaFin = '', $diaSemana = '') {
        $query = "SELECT 
        COUNT(DISTINCT p.id) AS total_pedidos, 
        SUM(pp.cantidad) AS total_productos,
        SUM(p.costo_productos) AS total_costo_productos,
        SUM(CASE WHEN tp.nombre = 'Concentrado' THEN pp.cantidad ELSE 0 END) AS total_concentrados,
        SUM(CASE WHEN tp.nombre = 'Difusor' THEN pp.cantidad ELSE 0 END) AS total_difusores,
        SUM(CASE WHEN tp.nombre = 'Textil' THEN pp.cantidad ELSE 0 END) AS total_textiles
      FROM pedido p
      LEFT JOIN pedido_producto pp ON p.id = pp.pedido_id
      LEFT JOIN producto prod ON pp.producto_id = prod.id
      LEFT JOIN tipo_producto tp ON prod.tipo_id = tp.id
      JOIN usuario u1 ON p.cliente_id = u1.id
      WHERE 1=1";

        // Agregar filtro por año
        if ($anio) {
            $query .= " AND YEAR(p.fecha) = ?";
        }

        // Agregar filtros existentes
        if ($mes) {
            $query .= " AND DATE_FORMAT(p.fecha, '%m') = ?";
        }
        if ($provincia) {
            $query .= " AND u1.provincia = ?";
        }
        if ($localidad) {
            $query .= " AND u1.localidad LIKE ?";
        }
        if ($metodoPago) {
            $query .= " AND p.medio_de_pago = ?";
        }
        if ($fechaInicio) {
            $query .= " AND p.fecha >= ?";
        }
        if ($fechaFin) {
            $query .= " AND p.fecha <= ?";
        }
        if ($diaSemana) {
            $query .= " AND DAYOFWEEK(p.fecha) = ?";
        }

        // Preparar la consulta
        $stmt = $this->database->prepare($query);
        $params = [];

        // Agregar los parámetros correspondientes
        if ($anio) $params[] = $anio;
        if ($mes) $params[] = $mes;
        if ($provincia) $params[] = $provincia;
        if ($localidad) $params[] = "%" . $localidad . "%";
        if ($metodoPago) $params[] = $metodoPago;
        if ($fechaInicio) $params[] = $fechaInicio;
        if ($fechaFin) $params[] = $fechaFin;
        if ($diaSemana) {
            // Mapear el día de la semana al valor de DAYOFWEEK (Domingo = 1, Lunes = 2, ..., Sábado = 7)
            $diasSemanaMap = [
                'Domingo' => 1,
                'Lunes' => 2,
                'Martes' => 3,
                'Miércoles' => 4,
                'Jueves' => 5,
                'Viernes' => 6,
                'Sábado' => 7,
            ];
            $params[] = $diasSemanaMap[$diaSemana] ?? null;
        }

        // Si hay parámetros, vincularlos
        if (count($params) > 0) {
            $types = str_repeat('s', count($params) - ($diaSemana ? 1 : 0)) . ($diaSemana ? 'i' : '');
            $stmt->bind_param($types, ...$params);
        }

        // Ejecutar la consulta
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();

        // Devolver los resultados
        return [
            'total_pedidos' => $data['total_pedidos'],
            'total_productos' => $data['total_productos'],
            'total_costo_productos' => $data['total_costo_productos'],
            'total_concentrados' => $data['total_concentrados'],
            'total_difusores' => $data['total_difusores'],
            'total_textiles' => $data['total_textiles'],
        ];
    }


    public function obtenerPedidosDelVendedor($vendedorId, $pagina = 1, $limite = 100, $mes = '', $anio = '', $provincia = '', $localidad = '', $metodoPago = '', $fechaInicio = '', $fechaFin = '') {
        $offset = ($pagina - 1) * $limite;

        // Consulta para obtener los pedidos con filtros
        $query = "SELECT p.id AS pedido_id, 
                     p.cliente_id, 
                     u1.dni, 
                     u1.nombre AS cliente, 
                     u1.provincia AS provincia, 
                     u1.localidad AS localidad, 
                     u2.nombre AS vendedor, 
                     p.costo_productos, 
                     p.costo_envio, 
                     p.costo_total, 
                     p.comentario, 
                     p.factura, 
                     p.medio_de_pago, 
                     p.cantidad_productos, 
                     CASE 
                         WHEN DAYOFWEEK(p.fecha) = 1 THEN 'Domingo' 
                         WHEN DAYOFWEEK(p.fecha) = 2 THEN 'Lunes' 
                         WHEN DAYOFWEEK(p.fecha) = 3 THEN 'Martes' 
                         WHEN DAYOFWEEK(p.fecha) = 4 THEN 'Miércoles' 
                         WHEN DAYOFWEEK(p.fecha) = 5 THEN 'Jueves' 
                         WHEN DAYOFWEEK(p.fecha) = 6 THEN 'Viernes' 
                         WHEN DAYOFWEEK(p.fecha) = 7 THEN 'Sábado' 
                     END AS dia_semana, 
                     DATE_FORMAT(p.fecha, '%d/%m/%Y') AS fecha 
              FROM pedido p 
              JOIN usuario u1 ON p.cliente_id = u1.id 
              JOIN usuario u2 ON p.id_vendedor = u2.id 
              WHERE p.id_vendedor = ?";

        // Inicializar los parámetros
        $params = [$vendedorId];

        // Añadir filtro de año
        if ($anio !== null) { // Filtrar solo si $anio no es null
            $query .= " AND YEAR(p.fecha) = ?";
            $params[] = $anio;
        }

        // Añadir filtro de mes
        if ($mes) {
            $query .= " AND DATE_FORMAT(p.fecha, '%m') = ?";
            $params[] = $mes;
        }

        // Añadir filtros adicionales si se proporcionan
        if ($provincia) {
            $query .= " AND u1.provincia = ?";
            $params[] = $provincia;
        }
        if ($localidad) {
            $query .= " AND u1.localidad LIKE ?";
            $params[] = "%" . $localidad . "%";
        }
        if ($metodoPago) {
            $query .= " AND p.medio_de_pago = ?";
            $params[] = $metodoPago;
        }
        if ($fechaInicio) {
            $query .= " AND p.fecha >= ?";
            $params[] = $fechaInicio;
        }
        if ($fechaFin) {
            $query .= " AND p.fecha <= ?";
            $params[] = $fechaFin;
        }

        // Añadir paginación
        $query .= " ORDER BY p.id DESC LIMIT ?, ?";
        $params[] = $offset;
        $params[] = $limite;

        // Preparar la consulta
        $stmt = $this->database->prepare($query);

        // Asignar los tipos de parámetros
        $types = str_repeat('s', count($params) - 2) . 'ii';  // Para cadenas y enteros
        $stmt->bind_param($types, ...$params);

        // Ejecutar la consulta
        $stmt->execute();
        $resultPedidos = $stmt->get_result();
        $rawDataPedidos = $resultPedidos->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $pedidos = [];
        $pedidoIds = [];

        // Procesar los pedidos obtenidos
        foreach ($rawDataPedidos as $row) {
            $pedidoId = $row['pedido_id'];
            $pedidoIds[] = $pedidoId;

            $pedidos[$pedidoId] = [
                'pedido_id' => $pedidoId,
                'cliente_id' => $row['cliente_id'],
                'cliente' => $row['cliente'],
                'dni' => $row['dni'],
                'provincia' => $row['provincia'],
                'localidad' => $row['localidad'],
                'vendedor' => $row['vendedor'],
                'costo_productos' => $row['costo_productos'],
                'costo_envio' => $row['costo_envio'],
                'costo_total' => $row['costo_total'],
                'comentario' => $row['comentario'],
                'factura' => $row['factura'],
                'medio_de_pago' => $row['medio_de_pago'],
                'cantidad_productos' => $row['cantidad_productos'],
                'dia_semana' => $row['dia_semana'],
                'fecha' => $row['fecha'],
                'productos' => []
            ];
        }

        // Obtener productos para los pedidos
        if (count($pedidoIds) > 0) {
            $placeholders = implode(',', array_fill(0, count($pedidoIds), '?'));
            $queryProductos = "SELECT pp.pedido_id, pp.producto_id, t.nombre AS tipo, a.nombre AS aroma, pp.cantidad 
                           FROM pedido_producto pp 
                           JOIN producto prod ON pp.producto_id = prod.id 
                           JOIN tipo_producto t ON prod.tipo_id = t.id 
                           LEFT JOIN aroma a ON prod.aroma_id = a.id 
                           WHERE pp.pedido_id IN ($placeholders)";

            $stmt = $this->database->prepare($queryProductos);
            $stmt->bind_param(str_repeat('i', count($pedidoIds)), ...$pedidoIds);
            $stmt->execute();
            $resultProductos = $stmt->get_result();
            $rawDataProductos = $resultProductos->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            // Asociar productos a los pedidos
            foreach ($rawDataProductos as $row) {
                $pedidos[$row['pedido_id']]['productos'][] = [
                    'producto_id' => $row['producto_id'],
                    'tipo' => $row['tipo'],
                    'aroma' => $row['aroma'],
                    'cantidad' => $row['cantidad']
                ];
            }
        }

        return array_values($pedidos);
    }



    public function eliminarPedido($pedido_id) {
        $pedido_id = intval($pedido_id);

        try {
            // Eliminar los productos asociados con el pedido
            $query = "DELETE FROM pedido_producto WHERE pedido_id = ?";
            $stmt = $this->database->prepare($query);
            $stmt->bind_param("i", $pedido_id);
            $stmt->execute();
            $stmt->close();

            // Eliminar el pedido
            $query = "DELETE FROM pedido WHERE id = ?";
            $stmt = $this->database->prepare($query);
            $stmt->bind_param("i", $pedido_id);
            $stmt->execute();
            $stmt->close();

            return true;
        } catch (Exception $e) {
            // Deshacer la transacción en caso de error
            $this->database->rollback();
            error_log("Error al eliminar el pedido: " . $e->getMessage());
            return false;
        }
    }
}