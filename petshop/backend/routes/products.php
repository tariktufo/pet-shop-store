<?php

$productService = new ProductService();

/**
 * @OA\Get(
 *     path="/api/products",
 *     tags={"products"},
 *     summary="Dohvati sve proizvode",
 *     @OA\Response(
 *         response=200,
 *         description="Lista svih proizvoda"
 *     )
 * )
 */
Flight::route('GET /api/products', function() use ($productService) {
    $products = $productService->getAllProducts();
    Flight::json($products);
});

/**
 * @OA\Get(
 *     path="/api/products/{id}",
 *     tags={"products"},
 *     summary="Dohvati proizvod po ID-u",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(response=200, description="Detalji proizvoda"),
 *     @OA\Response(response=404, description="Proizvod nije pronađen")
 * )
 */
Flight::route('GET /api/products/@id', function($id) use ($productService) {
    $product = $productService->getProductById($id);
    Flight::json($product);
});

/**
 * @OA\Get(
 *     path="/api/products/category/{category}",
 *     tags={"products"},
 *     summary="Dohvati proizvode po kategoriji",
 *     @OA\Parameter(
 *         name="category",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="string", example="Hrana")
 *     ),
 *     @OA\Response(response=200, description="Lista proizvoda iz kategorije")
 * )
 */
Flight::route('GET /api/products/category/@category', function($category) use ($productService) {
    $products = $productService->getProductsByCategory($category);
    Flight::json($products);
});

/**
 * @OA\Post(
 *     path="/api/products",
 *     tags={"products"},
 *     summary="Kreiraj novi proizvod",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "category", "price", "stock_quantity"},
 *             @OA\Property(property="name", type="string", example="Hrana za pse"),
 *             @OA\Property(property="description", type="string", example="Premium hrana"),
 *             @OA\Property(property="category", type="string", example="Hrana"),
 *             @OA\Property(property="price", type="number", format="float", example=45.99),
 *             @OA\Property(property="stock_quantity", type="integer", example=100),
 *             @OA\Property(property="image_url", type="string", example="https://example.com/image.jpg")
 *         )
 *     ),
 *     @OA\Response(response=201, description="Proizvod kreiran"),
 *     @OA\Response(response=400, description="Greška u validaciji")
 * )
 */
Flight::route('POST /api/products', function() use ($productService) {
    $data = Flight::request()->data->getData();
    $result = $productService->createProduct($data);
    
    if (isset($result['error'])) {
        Flight::json($result, 400);
    } else {
        Flight::json($result, 201);
    }
});

/**
 * @OA\Put(
 *     path="/api/products/{id}",
 *     tags={"products"},
 *     summary="Ažuriraj proizvod",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         @OA\JsonContent(
 *             @OA\Property(property="name", type="string"),
 *             @OA\Property(property="description", type="string"),
 *             @OA\Property(property="category", type="string"),
 *             @OA\Property(property="price", type="number"),
 *             @OA\Property(property="stock_quantity", type="integer"),
 *             @OA\Property(property="image_url", type="string")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Proizvod ažuriran")
 * )
 */
Flight::route('PUT /api/products/@id', function($id) use ($productService) {
    $data = Flight::request()->data->getData();
    $result = $productService->updateProduct($id, $data);
    
    if (isset($result['error'])) {
        Flight::json($result, 400);
    } else {
        Flight::json($result);
    }
});

/**
 * @OA\Put(
 *     path="/api/products/{id}/stock",
 *     tags={"products"},
 *     summary="Ažuriraj zalihe proizvoda",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"quantity"},
 *             @OA\Property(property="quantity", type="integer", example=-5, description="Broj za dodati/oduzeti")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Zalihe ažurirane")
 * )
 */
Flight::route('PUT /api/products/@id/stock', function($id) use ($productService) {
    $data = Flight::request()->data->getData();
    
    if (!isset($data['quantity'])) {
        Flight::json(['error' => 'Quantity je obavezan parametar'], 400);
        return;
    }
    
    $result = $productService->updateStock($id, $data['quantity']);
    
    if (isset($result['error'])) {
        Flight::json($result, 400);
    } else {
        Flight::json($result);
    }
});

/**
 * @OA\Delete(
 *     path="/api/products/{id}",
 *     tags={"products"},
 *     summary="Obriši proizvod",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(response=200, description="Proizvod obrisan")
 * )
 */
Flight::route('DELETE /api/products/@id', function($id) use ($productService) {
    $result = $productService->deleteProduct($id);
    
    if (isset($result['error'])) {
        Flight::json($result, 404);
    } else {
        Flight::json($result);
    }
});
?>