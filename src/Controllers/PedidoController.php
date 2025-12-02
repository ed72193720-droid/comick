<?php

namespace App\Controllers;

use App\Models\Pedido;

class PedidoController extends BaseController {
    private $pedidoModel;
    
    public function __construct() {
        $this->pedidoModel = new Pedido();
    }
    
    public function metodoPago() {
        require_once __DIR__ . '/../../includes/session.php';
        
        requireLogin();
        
        $carrito = $_SESSION['carrito'] ?? [];
        
        if (empty($carrito)) {
            $this->redirect('galeria.php');
            return;
        }
        
        $total = getCarritoTotal();
        
        $this->render('cliente/metodo_pago', [
            'carrito' => $carrito,
            'total' => $total
        ]);
    }
    
    public function procesarPago() {
        require_once __DIR__ . '/../../includes/helpers.php';
        require_once __DIR__ . '/../../includes/session.php';
        
        requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('metodo_pago.php');
            return;
        }

        $metodo_pago = sanitizeInput($_POST['metodo_pago'] ?? '', 20);
        $carrito = $_SESSION['carrito'] ?? [];

        if (empty($carrito) || !in_array($metodo_pago, ['efectivo', 'tarjeta', 'yape'])) {
            $this->redirect('metodo_pago.php');
            return;
        }

        $total = getCarritoTotal();
        $id_cliente = $_SESSION['id_cliente'];

        $id_pedido = $this->pedidoModel->create([
            'id_cliente' => $id_cliente,
            'total' => $total,
            'metodo_pago' => $metodo_pago
        ]);

        if ($id_pedido) {
            foreach ($carrito as $id_producto => $item) {
                $this->pedidoModel->createDetalle([
                    'id_pedido' => $id_pedido,
                    'id_producto' => $id_producto,
                    'cantidad' => $item['cantidad'],
                    'precio' => $item['precio']
                ]);
            }

            $_SESSION['carrito'] = [];
            $this->redirect("pago_exito.php?pedido={$id_pedido}");
        } else {
            $this->redirect('metodo_pago.php');
        }
    }
    
    public function pagoExito() {
        require_once __DIR__ . '/../../includes/helpers.php';
        require_once __DIR__ . '/../../includes/session.php';
        
        requireLogin();
        
        $id_pedido = validateNumber($_GET['pedido'] ?? 0);
        
        if (!$id_pedido) {
            $this->redirect('mis_pedidos.php');
            return;
        }

        $id_cliente = $_SESSION['id_cliente'];
        $pedido = $this->pedidoModel->findByIdAndCliente($id_pedido, $id_cliente);

        if (!$pedido) {
            $this->redirect('mis_pedidos.php');
            return;
        }

        $this->render('cliente/pago_exito', ['pedido' => $pedido]);
    }
    
    public function misPedidos() {
        require_once __DIR__ . '/../../includes/session.php';
        
        requireLogin();
        
        $id_cliente = $_SESSION['id_cliente'];
        $pedidos = $this->pedidoModel->findByCliente($id_cliente);
        
        $this->render('cliente/mis_pedidos', ['pedidos' => $pedidos]);
    }
    
    public function verPedido() {
        require_once __DIR__ . '/../../includes/helpers.php';
        require_once __DIR__ . '/../../includes/session.php';
        
        requireLogin();
        
        $id_pedido = validateNumber($_GET['id'] ?? 0);
        
        if (!$id_pedido) {
            $this->redirect('mis_pedidos.php');
            return;
        }

        $id_cliente = $_SESSION['id_cliente'];
        $pedido = $this->pedidoModel->findByIdAndCliente($id_pedido, $id_cliente);

        if (!$pedido) {
            $this->redirect('mis_pedidos.php');
            return;
        }

        $detalle = $this->pedidoModel->getDetalle($id_pedido);

        $this->render('cliente/ver_pedido', [
            'pedido' => $pedido,
            'detalle' => $detalle
        ]);
    }
    
    public function cancelar() {
        require_once __DIR__ . '/../../includes/helpers.php';
        require_once __DIR__ . '/../../includes/session.php';
        
        requireLogin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('mis_pedidos.php');
            return;
        }

        $id_pedido = validateNumber($_POST['id_pedido'] ?? 0);
        
        if (!$id_pedido) {
            $this->redirect('mis_pedidos.php');
            return;
        }

        $id_cliente = $_SESSION['id_cliente'];
        $pedido = $this->pedidoModel->findByIdAndCliente($id_pedido, $id_cliente);

        if (!$pedido) {
            $this->redirect('mis_pedidos.php');
            return;
        }

        if ($pedido['estado'] !== 'Pendiente') {
            $this->redirect("ver_pedido.php?id={$id_pedido}");
            return;
        }

        if ($this->pedidoModel->cancelar($id_pedido)) {
            $this->redirect("ver_pedido.php?id={$id_pedido}");
        } else {
            $this->redirect('mis_pedidos.php');
        }
    }
}
