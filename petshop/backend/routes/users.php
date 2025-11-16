<?php

$userService = new UserService();

/**
 * @OA\Get(
 *     path="/api/users",
 *     tags={"users"},
 *     summary="Dohvati sve korisnike",
 *     description="Vraća listu svih korisnika u sistemu",
 *     @OA\Response(
 *         response=200,
 *         description="Uspješno dohvaćeni korisnici",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="Marko Marković"),
 *                 @OA\Property(property="email", type="string", example="marko@example.com"),
 *                 @OA\Property(property="phone", type="string", example="+38761234567"),
 *                 @OA\Property(property="address", type="string", example="Sarajevo, BiH"),
 *                 @OA\Property(property="role", type="string", example="customer"),
 *                 @OA\Property(property="created_at", type="string", example="2025-11-16 10:00:00")
 *             )
 *         )
 *     )
 * )
 */
Flight::route('GET /api/users', function() use ($userService) {
    $users = $userService->getAllUsers();
    Flight::json($users);
});

/**
 * @OA\Get(
 *     path="/api/users/{id}",
 *     tags={"users"},
 *     summary="Dohvati korisnika po ID-u",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID korisnika",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Uspješno dohvaćen korisnik",
 *         @OA\JsonContent(
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="name", type="string", example="Marko Marković"),
 *             @OA\Property(property="email", type="string", example="marko@example.com"),
 *             @OA\Property(property="phone", type="string", example="+38761234567"),
 *             @OA\Property(property="address", type="string", example="Sarajevo, BiH"),
 *             @OA\Property(property="role", type="string", example="customer")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Korisnik nije pronađen"
 *     )
 * )
 */
Flight::route('GET /api/users/@id', function($id) use ($userService) {
    $user = $userService->getUserById($id);
    Flight::json($user);
});

/**
 * @OA\Post(
 *     path="/api/users",
 *     tags={"users"},
 *     summary="Kreiraj novog korisnika",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "email", "password", "role"},
 *             @OA\Property(property="name", type="string", example="Marko Marković"),
 *             @OA\Property(property="email", type="string", format="email", example="marko@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="password123"),
 *             @OA\Property(property="phone", type="string", example="+38761234567"),
 *             @OA\Property(property="address", type="string", example="Sarajevo, BiH"),
 *             @OA\Property(property="role", type="string", enum={"admin", "customer"}, example="customer")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Korisnik uspješno kreiran"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Greška u validaciji"
 *     )
 * )
 */
Flight::route('POST /api/users', function() use ($userService) {
    $data = Flight::request()->data->getData();
    $result = $userService->createUser($data);
    
    if (isset($result['error'])) {
        Flight::json($result, 400);
    } else {
        Flight::json($result, 201);
    }
});

/**
 * @OA\Put(
 *     path="/api/users/{id}",
 *     tags={"users"},
 *     summary="Ažuriraj korisnika",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID korisnika",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="name", type="string", example="Marko Marković"),
 *             @OA\Property(property="email", type="string", example="marko@example.com"),
 *             @OA\Property(property="password", type="string", example="newpassword123"),
 *             @OA\Property(property="phone", type="string", example="+38761234567"),
 *             @OA\Property(property="address", type="string", example="Banja Luka, BiH"),
 *             @OA\Property(property="role", type="string", example="customer")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Korisnik uspješno ažuriran"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Korisnik nije pronađen"
 *     )
 * )
 */
Flight::route('PUT /api/users/@id', function($id) use ($userService) {
    $data = Flight::request()->data->getData();
    $result = $userService->updateUser($id, $data);
    
    if (isset($result['error'])) {
        Flight::json($result, 400);
    } else {
        Flight::json($result);
    }
});

/**
 * @OA\Delete(
 *     path="/api/users/{id}",
 *     tags={"users"},
 *     summary="Obriši korisnika",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID korisnika",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Korisnik uspješno obrisan"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Korisnik nije pronađen"
 *     )
 * )
 */
Flight::route('DELETE /api/users/@id', function($id) use ($userService) {
    $result = $userService->deleteUser($id);
    
    if (isset($result['error'])) {
        Flight::json($result, 404);
    } else {
        Flight::json($result);
    }
});
?>