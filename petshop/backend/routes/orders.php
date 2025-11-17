<?php

$orderService = new OrderService();

/**
 * @OA\Get(
 *     path="/api/orders",
 *     tags={"orders"},
 *     summary="Dohvati sve narudžbe",
 *     @OA\Response(
 *         response=200,
 *         description="Lista svih narudžbi",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="user_id", type="integer", example=2),
 *                 @OA\Property(property="customer_name", type="string", example="Marko Marković"),
 *                 @OA\Property(property="total_amount", type="number", format="float", example=51.98),
 *                 @OA\Property(property="status", type="string", example="pending"),
 *                 @OA\Property(property="created_at", type="string", example="2025-11-16 10:00:00")
 *             )
 *         )
 *     )
 * )
 */
Flight::route('GET /api/orders', function() use ($orderService) {
    $orders = $orderService->getAllOrders();
    Flight::json($orders);
});

/**
 * @OA\Get(
 *     path="/api/orders/{id}",
 *     tags={"orders"},
 *     summary="Dohvati narudžbu po ID-u",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID narudžbe",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Detalji narudžbe sa stavkama",
 *         @OA\JsonContent(
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="user_id", type="integer", example=2),
 *             @OA\Property(property="customer_name", type="string", example="Marko Marković"),
 *             @OA\Property(property="customer_email", type="string", example="marko@example.com"),
 *             @OA\Property(property="total_amount", type="number", example=51.98),
 *             @OA\Property(property="status", type="string", example="pending"),
 *             @OA\Property(
 *                 property="items",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="product_id", type="integer"),
 *                     @OA\Property(property="product_name", type="string"),
 *                     @OA\Property(property="quantity", type="integer"),
 *                     @OA\Property(property="price", type="number")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(response=404, description="Narudžba nije pronađena")
 * )
 */
Flight::route('GET /api/orders/@id', function($id) use ($orderService) {
    $order = $orderService->getOrderById($id);
    Flight::json($order);
});

/**
 * @OA\Get(
 *     path="/api/users/{userId}/orders",
 *     tags={"orders"},
 *     summary="Dohvati narudžbe korisnika",
 *     @OA\Parameter(
 *         name="userId",
 *         in="path",
 *         required=true,
 *         description="ID korisnika",
 *         @OA\Schema(type="integer", example=2)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Lista narudžbi korisnika"
 *     )
 * )
 */
Flight::route('GET /api/users/@userId/orders', function($userId) use ($orderService) {
    $orders = $orderService->getOrdersByUser($userId);
    Flight::json($orders);
});

/**
 * @OA\Post(
 *     path="/api/orders",
 *     tags={"orders"},
 *     summary="Kreiraj novu narudžbu",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"user_id", "items"},
 *             @OA\Property(property="user_id", type="integer", example=2),
 *             @OA\Property(
 *                 property="items",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="product_id", type="integer", example=1),
 *                     @OA\Property(property="quantity", type="integer", example=2)
 *                 ),
 *                 example={
 *                     {"product_id": 1, "quantity": 2},
 *                     {"product_id": 2, "quantity": 1}
 *                 }
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Narudžba uspješno kreirana"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Greška u validaciji ili nedovoljne zalihe"
 *     )
 * )
 */
Flight::route('POST /api/orders', function() use ($orderService) {
    $data = Flight::request()->data->getData();
    $result = $orderService->createOrder($data);
    
    if (isset($result['error'])) {
        Flight::json($result, 400);
    } else {
        Flight::json($result, 201);
    }
});

/**
 * @OA\Put(
 *     path="/api/orders/{id}/status",
 *     tags={"orders"},
 *     summary="Ažuriraj status narudžbe",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID narudžbe",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"status"},
 *             @OA\Property(
 *                 property="status",
 *                 type="string",
 *                 enum={"pending", "processing", "shipped", "delivered", "cancelled"},
 *                 example="processing"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Status narudžbe ažuriran"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Nevalidan status"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Narudžba nije pronađena"
 *     )
 * )
 */
Flight::route('PUT /api/orders/@id/status', function($id) use ($orderService) {
    $data = Flight::request()->data->getData();
    
    if (!isset($data['status'])) {
        Flight::json(['error' => 'Status je obavezan parametar'], 400);
        return;
    }
    
    $result = $orderService->updateOrderStatus($id, $data['status']);
    
    if (isset($result['error'])) {
        Flight::json($result, 400);
    } else {
        Flight::json($result);
    }
});

/**
 * @OA\Delete(
 *     path="/api/orders/{id}",
 *     tags={"orders"},
 *     summary="Obriši narudžbu",
 *     description="Briše narudžbu i vraća proizvode na lager",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID narudžbe",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Narudžba uspješno obrisana"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Narudžba nije pronađena"
 *     )
 * )
 */
Flight::route('DELETE /api/orders/@id', function($id) use ($orderService) {
    $result = $orderService->deleteOrder($id);
    
    if (isset($result['error'])) {
        Flight::json($result, 404);
    } else {
        Flight::json($result);
    }
});
?>