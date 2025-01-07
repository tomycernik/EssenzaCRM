<?php

class AdminController
{
    private $adminModel;
    private $presenter;

    public function __construct($adminModel, $presenter)
    {
        $this->adminModel = $adminModel;
        $this->presenter = $presenter;
    }

    public function get()
    {
        $anio = $_GET['anio'] ?? '2025'; // Si no hay valor para 'anio', se usa '2025' por defecto

        if ($anio === 'todos') {
            $anio = null; // O cualquier otro valor que indique que no se aplique el filtro de año
        }

        $ventas = $this->adminModel->obtenerVentasPorMes($anio);

        $total_venta_total_dinero = 0;
        $total_venta_total_productos = 0;
        $total_venta_difusores = 0;
        $total_venta_concentrados = 0;
        $total_venta_textiles = 0;
        $total_venta_repuesto_textil = 0;
        $total_cantidad_pedidos = 0;

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

        foreach ($ventas as &$venta) {
            $mes = $venta['mes_nombre'];
            list($nombre_mes, $anio) = explode('-', $mes);
            $mes_en_espanol = isset($meses_espanol[$nombre_mes]) ? $meses_espanol[$nombre_mes] : $nombre_mes;
            $venta['mes_nombre'] = $mes_en_espanol . '-' . $anio;

            $total_venta_total_dinero += $venta['venta_total_dinero'];
            $total_venta_total_productos += $venta['venta_total_productos'];
            $total_venta_difusores += $venta['venta_difusores'];
            $total_venta_concentrados += $venta['venta_concentrados'];
            $total_venta_textiles += $venta['venta_textiles'];
            $total_venta_repuesto_textil += $venta['venta_repuesto_textil'];
            $total_cantidad_pedidos += $venta['cantidad_pedidos'];
        }

        $ventas = array_reverse($ventas);

        $data = [
            'ventas' => $ventas,
            'total_venta_total_dinero' => $total_venta_total_dinero,
            'total_venta_total_productos' => $total_venta_total_productos,
            'total_venta_difusores' => $total_venta_difusores,
            'total_venta_concentrados' => $total_venta_concentrados,
            'total_venta_textiles' => $total_venta_textiles,
            'total_venta_repuesto_textil' => $total_venta_repuesto_textil,
            'total_cantidad_pedidos' => $total_cantidad_pedidos,
            'filtro_anio' => $anio // Pasar el año seleccionado para mostrar en la vista
        ];

        $this->presenter->render("admin", $data);
    }

    public function homeAdmin()
    {
        $anio = $_GET['anio'] ?? '2025'; // Si no hay valor para 'anio', se usa '2025' por defecto


        if ($anio === 'todos') {
            $anio = null; // No aplicar filtro
        }
        // Llamar al modelo con el parámetro de año, si está presente
        $ventas = $this->adminModel->obtenerVentasPorMes($anio);

        $total_venta_total_dinero = 0;
        $total_venta_total_productos = 0;
        $total_venta_difusores = 0;
        $total_venta_concentrados = 0;
        $total_venta_textiles = 0;
        $total_venta_repuesto_textil = 0;
        $total_cantidad_pedidos = 0;

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

        foreach ($ventas as &$venta) {
            // Convertir el mes a español
            $mes = $venta['mes_nombre'];  // Asegúrate de que `mes_nombre` esté en el formato 'Month-Year'
            list($nombre_mes, $anio) = explode('-', $mes);
            $mes_en_espanol = isset($meses_espanol[$nombre_mes]) ? $meses_espanol[$nombre_mes] : $nombre_mes;
            $venta['mes_nombre'] = $mes_en_espanol . '-' . $anio;

            $total_venta_total_dinero += $venta['venta_total_dinero'];
            $total_venta_total_productos += $venta['venta_total_productos'];
            $total_venta_difusores += $venta['venta_difusores'];
            $total_venta_concentrados += $venta['venta_concentrados'];
            $total_venta_textiles += $venta['venta_textiles'];
            $total_venta_repuesto_textil += $venta['venta_repuesto_textil'];
            $total_cantidad_pedidos += $venta['cantidad_pedidos'];
        }

        // Invertir el orden de los meses
        $ventas = array_reverse($ventas);

        $botones = [
            'verPedidos' => '/ed/EssenzaDivinaCRM-master/pedido/mostrarPedidos',
            'verClientes' => '/ed/EssenzaDivinaCRM-master/cliente',
            'verVendedores' => '/ed/EssenzaDivinaCRM-master/admin/mostrarVentasVendedores',
        ];

        // Preparar los datos para la vista
        $data = [
            'ventas' => $ventas,
            'total_venta_total_dinero' => $total_venta_total_dinero,
            'total_venta_total_productos' => $total_venta_total_productos,
            'total_venta_difusores' => $total_venta_difusores,
            'total_venta_concentrados' => $total_venta_concentrados,
            'total_venta_textiles' => $total_venta_textiles,
            'total_venta_repuesto_textil' => $total_venta_repuesto_textil,
            'total_cantidad_pedidos' => $total_cantidad_pedidos,
            'botones' => $botones,
            'anio' => $anio  // Pasar el año actual a la vista para mostrarlo
        ];

        $this->presenter->render("admin", $data, false);
    }


    public function mostrarVentasVendedores()
    {
        $anio = $_GET['anio'] ?? '2025'; // Si no hay valor para 'anio', se usa '2025' por defecto

        if ($anio === 'todos') {
            $anio = null; // No aplicar filtro
        }
        $resultado = $this->adminModel->obtenerVentasVendedoresPorMes($anio);

        $ventas = $resultado['data'];
        $alertas = $resultado['alertas'];

        // Inicializar variables generales
        $total_general_pedidos = 0;
        $total_general_dinero = 0;
        $total_general_productos = 0;

        // Aquí agruparemos las ventas por vendedor
        $ventas_agrupadas = [];

        foreach ($ventas as $venta) {
            $nombre_vendedor = $venta['nombre_vendedor'];

            // Si el vendedor no está aún en el array, lo inicializamos
            if (!isset($ventas_agrupadas[$nombre_vendedor])) {
                $ventas_agrupadas[$nombre_vendedor] = [
                    'nombre_vendedor' => $nombre_vendedor,
                    'meses' => []
                ];
            }

            // Añadir el mes y sus estadísticas a la lista del vendedor
            $ventas_agrupadas[$nombre_vendedor]['meses'][] = [
                'mes_nombre' => $venta['mes_nombre'],
                'cantidad_pedidos' => $venta['cantidad_pedidos'],
                'total_productos_vendidos' => $venta['total_productos_vendidos']
            ];

            // Sumar las estadísticas a los totales generales
            $total_general_pedidos += $venta['cantidad_pedidos'];
            $total_general_dinero += $venta['total_dinero_productos'];
            $total_general_productos += $venta['total_productos_vendidos'];
        }

        // Ahora formateamos los datos para enviarlos a la vista
        $data = [
            'ventas' => array_values($ventas_agrupadas), // array_values para resetear las claves
            'total_general_pedidos' => $total_general_pedidos,
            'total_general_dinero' => $total_general_dinero,
            'total_general_productos' => $total_general_productos,
            'alertas' => $alertas,
            'anio' => $anio  // Pasar el año actual a la vista para mostrarlo
        ];

        // Renderizamos la vista con los datos procesados
        $this->presenter->render("vendedores", $data);
    }

}