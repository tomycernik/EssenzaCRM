<?php

class AdminModel
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function obtenerVentasPorMes($anio = null) {
        $query = "SELECT 
                 DATE_FORMAT(pedidos_agrupados.fecha, '%Y-%m') AS mes_numero,
                 DATE_FORMAT(pedidos_agrupados.fecha, '%M-%Y') AS mes_nombre,
                 SUM(pedidos_agrupados.costo_productos) AS venta_total_dinero,
                 SUM(pedidos_agrupados.venta_total_productos) AS venta_total_productos,
                 SUM(pedidos_agrupados.venta_difusores) AS venta_difusores,
                 SUM(pedidos_agrupados.venta_concentrados) AS venta_concentrados,
                 SUM(pedidos_agrupados.venta_textiles) AS venta_textiles,
                 SUM(pedidos_agrupados.venta_repuesto_textil) as venta_repuesto_textil,
                 COUNT(DISTINCT pedidos_agrupados.pedido_id) AS cantidad_pedidos
              FROM (
                   SELECT p.id AS pedido_id, 
                          p.costo_productos,
                          p.fecha, 
                          SUM(pp.cantidad) AS venta_total_productos,
                          SUM(CASE WHEN t.nombre = 'Difusor' THEN pp.cantidad ELSE 0 END) AS venta_difusores,
                          SUM(CASE WHEN t.nombre = 'Concentrado' THEN pp.cantidad ELSE 0 END) AS venta_concentrados,
                          SUM(CASE WHEN t.nombre = 'Textil' THEN pp.cantidad ELSE 0 END) AS venta_textiles,
                          SUM(CASE WHEN t.nombre = 'Repuesto_Textil' THEN pp.cantidad ELSE 0 END) AS venta_repuesto_textil
                   FROM pedido p
                   JOIN pedido_producto pp ON p.id = pp.pedido_id
                   JOIN producto prod ON pp.producto_id = prod.id
                   JOIN tipo_producto t ON prod.tipo_id = t.id
                   GROUP BY p.id
              ) AS pedidos_agrupados
              WHERE 1=1";

        // Agregar condición de filtro por año si se proporciona
        if ($anio !== null) {
            $query .= " AND YEAR(pedidos_agrupados.fecha) = ?";
        }

        $query .= " GROUP BY mes_numero, mes_nombre
                 ORDER BY mes_numero DESC";

        $stmt = $this->database->prepare($query);
        if ($stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($this->database->error));
        }

        // Enlazar el parámetro del año si está presente
        if ($anio !== null) {
            $stmt->bind_param("i", $anio);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        if ($result === false) {
            die('Query failed: ' . htmlspecialchars($this->database->error));
        }

        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $data;
    }


    public function obtenerVentasVendedoresPorMes($anio = null) {
        // Definir el mes actual
        $mes_actual = date('Y-m'); // Esto define el mes actual en formato 'YYYY-MM'

        // Query ajustada con el filtro de año
        $query = "
    SELECT 
        DATE_FORMAT(p.fecha, '%Y-%m') AS mes_numero,
        DATE_FORMAT(p.fecha, '%M-%Y') AS mes_nombre,
        u.nombre AS nombre_vendedor,
        COUNT(DISTINCT p.id) AS cantidad_pedidos,
        SUM(p.costo_productos) AS total_dinero_productos,
        SUM(pp.cantidad) AS total_productos_vendidos
    FROM pedido p
    JOIN usuario u ON p.id_vendedor = u.id
    JOIN pedido_producto pp ON p.id = pp.pedido_id
    WHERE u.rol IN ('admin', 'vendedor')
    ";

        // Si se proporciona un año, agregamos la condición WHERE para filtrar por el año
        if ($anio) {
            $query .= " AND YEAR(p.fecha) = ? ";
        }

        $query .= "
    GROUP BY mes_numero, mes_nombre, u.id
    ORDER BY u.nombre, mes_numero DESC;
    ";

        $stmt = $this->database->prepare($query);
        if ($stmt === false) {
            die('Prepare failed: ' . htmlspecialchars($this->database->error));
        }

        // Si se proporciona un año, vinculamos el parámetro para la consulta
        if ($anio) {
            $stmt->bind_param('i', $anio); // 'i' para integer
        }

        $stmt->execute();
        $result = $stmt->get_result();
        if ($result === false) {
            die('Query failed: ' . htmlspecialchars($this->database->error));
        }

        $data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Array para convertir nombres de meses a español
        $meses_espanol = [
            'January' => 'Enero',
            'February' => 'Febrero',
            'March' => 'Marzo',
            'April' => 'Abril',
            'May' => 'Mayo',
            'June' => 'Junio',
            'July' => 'Julio',
            'August' => 'Agosto',
            'September' => 'Septiembre',
            'October' => 'Octubre',
            'November' => 'Noviembre',
            'December' => 'Diciembre'
        ];

        // Inicializa la lista de alertas
        $alertas = [];

        // Procesa los datos
        foreach ($data as &$row) {
            // Convertir el mes de inglés a español
            $mes_ingles = date('F', strtotime($row['mes_numero'] . '-01')); // Obtiene el nombre del mes en inglés
            $mes_en_espanol = $meses_espanol[$mes_ingles]; // Convierte al mes en español
            $row['mes_nombre'] = $mes_en_espanol . " " . date('Y', strtotime($row['mes_numero'] . '-01')); // Formato "Mes Año"

            // Verificar si el mes es el actual para generar alertas
            if ($row['mes_numero'] === $mes_actual) {
                $totalProductos = $row['total_productos_vendidos'];

                // Generar alertas en función de los productos vendidos
                if ($totalProductos >= 5000 && $totalProductos < 8000) {
                    $alertas[] = "Alerta: El vendedor {$row['nombre_vendedor']} ha alcanzado 5 mil productos vendidos en {$row['mes_nombre']}.";
                } elseif ($totalProductos >= 8000 && $totalProductos < 10000) {
                    $alertas[] = "Alerta: El vendedor {$row['nombre_vendedor']} ha alcanzado 8 mil productos vendidos en {$row['mes_nombre']}.";
                } elseif ($totalProductos >= 10000 && $totalProductos < 12000) {
                    $alertas[] = "Alerta: El vendedor {$row['nombre_vendedor']} ha alcanzado 10 mil productos vendidos en {$row['mes_nombre']}.";
                } elseif ($totalProductos >= 12000 && $totalProductos < 15000) {
                    $alertas[] = "Alerta: El vendedor {$row['nombre_vendedor']} ha alcanzado 12 mil productos vendidos en {$row['mes_nombre']}.";
                } elseif ($totalProductos >= 15000) {
                    $alertas[] = "Alerta: El vendedor {$row['nombre_vendedor']} ha alcanzado 15 mil productos vendidos en {$row['mes_nombre']}.";
                }
            }
        }

        // Devuelve los datos junto con las alertas
        return ['data' => $data, 'alertas' => $alertas];
    }


}