<?php

$petService = new PetService();

/**
 * @OA\Get(
 *     path="/api/pets",
 *     tags={"pets"},
 *     summary="Dohvati sve ljubimce",
 *     @OA\Response(
 *         response=200,
 *         description="Lista svih ljubimaca",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="name", type="string", example="Max"),
 *                 @OA\Property(property="species", type="string", example="Pas"),
 *                 @OA\Property(property="breed", type="string", example="Njemački ovčar"),
 *                 @OA\Property(property="date_of_birth", type="string", format="date", example="2020-05-15"),
 *                 @OA\Property(property="owner_id", type="integer", example=2),
 *                 @OA\Property(property="owner_name", type="string", example="Marko Marković")
 *             )
 *         )
 *     )
 * )
 */
Flight::route('GET /api/pets', function() use ($petService) {
    $pets = $petService->getAllPets();
    Flight::json($pets);
});

/**
 * @OA\Get(
 *     path="/api/pets/{id}",
 *     tags={"pets"},
 *     summary="Dohvati ljubimca po ID-u",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID ljubimca",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Detalji ljubimca"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Ljubimac nije pronađen"
 *     )
 * )
 */
Flight::route('GET /api/pets/@id', function($id) use ($petService) {
    $pet = $petService->getPetById($id);
    Flight::json($pet);
});

/**
 * @OA\Get(
 *     path="/api/users/{userId}/pets",
 *     tags={"pets"},
 *     summary="Dohvati ljubimce korisnika",
 *     @OA\Parameter(
 *         name="userId",
 *         in="path",
 *         required=true,
 *         description="ID vlasnika",
 *         @OA\Schema(type="integer", example=2)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Lista ljubimaca korisnika"
 *     )
 * )
 */
Flight::route('GET /api/users/@userId/pets', function($userId) use ($petService) {
    $pets = $petService->getPetsByOwner($userId);
    Flight::json($pets);
});

/**
 * @OA\Post(
 *     path="/api/pets",
 *     tags={"pets"},
 *     summary="Kreiraj novog ljubimca",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "species", "owner_id"},
 *             @OA\Property(property="name", type="string", example="Max"),
 *             @OA\Property(property="species", type="string", example="Pas"),
 *             @OA\Property(property="breed", type="string", example="Labrador"),
 *             @OA\Property(property="date_of_birth", type="string", format="date", example="2022-03-15"),
 *             @OA\Property(property="owner_id", type="integer", example=2)
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Ljubimac uspješno kreiran"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Greška u validaciji"
 *     )
 * )
 */
Flight::route('POST /api/pets', function() use ($petService) {
    $data = Flight::request()->data->getData();
    $result = $petService->createPet($data);
    
    if (isset($result['error'])) {
        Flight::json($result, 400);
    } else {
        Flight::json($result, 201);
    }
});

/**
 * @OA\Put(
 *     path="/api/pets/{id}",
 *     tags={"pets"},
 *     summary="Ažuriraj ljubimca",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         @OA\JsonContent(
 *             @OA\Property(property="name", type="string"),
 *             @OA\Property(property="species", type="string"),
 *             @OA\Property(property="breed", type="string"),
 *             @OA\Property(property="date_of_birth", type="string", format="date"),
 *             @OA\Property(property="owner_id", type="integer")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Ljubimac ažuriran"),
 *     @OA\Response(response=404, description="Ljubimac nije pronađen")
 * )
 */
Flight::route('PUT /api/pets/@id', function($id) use ($petService) {
    $data = Flight::request()->data->getData();
    $result = $petService->updatePet($id, $data);
    
    if (isset($result['error'])) {
        Flight::json($result, 400);
    } else {
        Flight::json($result);
    }
});

/**
 * @OA\Delete(
 *     path="/api/pets/{id}",
 *     tags={"pets"},
 *     summary="Obriši ljubimca",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(response=200, description="Ljubimac obrisan"),
 *     @OA\Response(response=404, description="Ljubimac nije pronađen")
 * )
 */
Flight::route('DELETE /api/pets/@id', function($id) use ($petService) {
    $result = $petService->deletePet($id);
    
    if (isset($result['error'])) {
        Flight::json($result, 404);
    } else {
        Flight::json($result);
    }
});
?>