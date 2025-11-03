<?php
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../helpers/response.php';

class ProductController {
    private $productModel;

    public function __construct($mysqli) {
        $this->productModel = new Product($mysqli);
    }

    /**
     * GET /products
     * GET /products/{id}
     */
    public function get($id = null) {
        if ($id) {
            $item = $this->productModel->getById($id);
            if (!$item) respond(['error' => 'Product not found'], 404);
            respond($item);
        } else {
            $list = $this->productModel->getAll();
            respond($list);
        }
    }

    /**
     * POST /products
     */
    public function create() {
        $data = getJsonBody();
        if (!$data) respond(['error' => 'Invalid JSON body'], 400);

        // Validasi field wajib
        $required = ['name', 'brand', 'category', 'type', 'price', 'image'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                respond(['error' => "Field '$field' is required"], 400);
            }
        }

        try {
            $created = $this->productModel->create($data);
            respond($created, 201);
        } catch (Exception $e) {
            respond(['error' => 'Insert failed', 'detail' => $e->getMessage()], 500);
        }
    }

    /**
     * PUT /products/{id}
     */
    public function update($id) {
        if (!$id) respond(['error' => 'Product ID required'], 400);
        $data = getJsonBody();
        if (!$data) respond(['error' => 'Invalid JSON body'], 400);

        try {
            $success = $this->productModel->update($id, $data);
            if ($success) {
                respond(['message' => 'Product updated']);
            } else {
                respond(['error' => 'Product not found'], 404);
            }
        } catch (Exception $e) {
            respond(['error' => 'Update failed', 'detail' => $e->getMessage()], 500);
        }
    }

    /**
     * DELETE /products/{id}
     */
    public function delete($id) {
        if (!$id) respond(['error' => 'Product ID required'], 400);

        try {
            $deleted = $this->productModel->delete($id);
            if ($deleted) {
                respond(['message' => 'Product deleted']);
            } else {
                respond(['error' => 'Product not found'], 404);
            }
        } catch (Exception $e) {
            respond(['error' => 'Delete failed', 'detail' => $e->getMessage()], 500);
        }
    }
}
