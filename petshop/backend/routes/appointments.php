<?php

$appointmentService = new AppointmentService();

/**
 * @OA\Get(
 *     path="/api/appointments",
 *     tags={"appointments"},
 *     summary="Dohvati sve termine",
 *     @OA\Response(
 *         response=200,
 *         description="Lista svih termina",
 *         @OA\JsonContent(
 *             type="array",
 *             @OA\Items(
 *                 @OA\Property(property="id", type="integer", example=1),
 *                 @OA\Property(property="user_id", type="integer", example=2),
 *                 @OA\Property(property="customer_name", type="string", example="Marko Marković"),
 *                 @OA\Property(property="pet_id", type="integer", example=1),
 *                 @OA\Property(property="pet_name", type="string", example="Max"),
 *                 @OA\Property(property="appointment_date", type="string", format="date", example="2025-11-20"),
 *                 @OA\Property(property="appointment_time", type="string", format="time", example="14:30:00"),
 *                 @OA\Property(property="service_type", type="string", example="Veterinarski pregled"),
 *                 @OA\Property(property="status", type="string", example="scheduled"),
 *                 @OA\Property(property="created_at", type="string", example="2025-11-16 10:00:00")
 *             )
 *         )
 *     )
 * )
 */
Flight::route('GET /api/appointments', function() use ($appointmentService) {
    $appointments = $appointmentService->getAllAppointments();
    Flight::json($appointments);
});

/**
 * @OA\Get(
 *     path="/api/appointments/{id}",
 *     tags={"appointments"},
 *     summary="Dohvati termin po ID-u",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID termina",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Detalji termina",
 *         @OA\JsonContent(
 *             @OA\Property(property="id", type="integer", example=1),
 *             @OA\Property(property="user_id", type="integer", example=2),
 *             @OA\Property(property="customer_name", type="string", example="Marko Marković"),
 *             @OA\Property(property="customer_email", type="string", example="marko@example.com"),
 *             @OA\Property(property="customer_phone", type="string", example="+38761234567"),
 *             @OA\Property(property="pet_id", type="integer", example=1),
 *             @OA\Property(property="pet_name", type="string", example="Max"),
 *             @OA\Property(property="pet_species", type="string", example="Pas"),
 *             @OA\Property(property="appointment_date", type="string", example="2025-11-20"),
 *             @OA\Property(property="appointment_time", type="string", example="14:30:00"),
 *             @OA\Property(property="service_type", type="string", example="Pregled"),
 *             @OA\Property(property="notes", type="string", example="Rutinski pregled"),
 *             @OA\Property(property="status", type="string", example="scheduled")
 *         )
 *     ),
 *     @OA\Response(response=404, description="Termin nije pronađen")
 * )
 */
Flight::route('GET /api/appointments/@id', function($id) use ($appointmentService) {
    $appointment = $appointmentService->getAppointmentById($id);
    Flight::json($appointment);
});

/**
 * @OA\Get(
 *     path="/api/users/{userId}/appointments",
 *     tags={"appointments"},
 *     summary="Dohvati termine korisnika",
 *     @OA\Parameter(
 *         name="userId",
 *         in="path",
 *         required=true,
 *         description="ID korisnika",
 *         @OA\Schema(type="integer", example=2)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Lista termina korisnika"
 *     )
 * )
 */
Flight::route('GET /api/users/@userId/appointments', function($userId) use ($appointmentService) {
    $appointments = $appointmentService->getAppointmentsByUser($userId);
    Flight::json($appointments);
});

/**
 * @OA\Get(
 *     path="/api/pets/{petId}/appointments",
 *     tags={"appointments"},
 *     summary="Dohvati termine ljubimca",
 *     @OA\Parameter(
 *         name="petId",
 *         in="path",
 *         required=true,
 *         description="ID ljubimca",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Lista termina ljubimca"
 *     )
 * )
 */
Flight::route('GET /api/pets/@petId/appointments', function($petId) use ($appointmentService) {
    $appointments = $appointmentService->getAppointmentsByPet($petId);
    Flight::json($appointments);
});

/**
 * @OA\Post(
 *     path="/api/appointments",
 *     tags={"appointments"},
 *     summary="Kreiraj novi termin",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"user_id", "pet_id", "appointment_date", "appointment_time", "service_type"},
 *             @OA\Property(property="user_id", type="integer", example=2),
 *             @OA\Property(property="pet_id", type="integer", example=1),
 *             @OA\Property(property="appointment_date", type="string", format="date", example="2025-11-20"),
 *             @OA\Property(property="appointment_time", type="string", format="time", example="14:30:00"),
 *             @OA\Property(property="service_type", type="string", example="Veterinarski pregled"),
 *             @OA\Property(property="notes", type="string", example="Rutinski pregled")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Termin uspješno kreiran"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Greška u validaciji ili termin već zauzet"
 *     )
 * )
 */
Flight::route('POST /api/appointments', function() use ($appointmentService) {
    $data = Flight::request()->data->getData();
    $result = $appointmentService->createAppointment($data);
    
    if (isset($result['error'])) {
        Flight::json($result, 400);
    } else {
        Flight::json($result, 201);
    }
});

/**
 * @OA\Put(
 *     path="/api/appointments/{id}",
 *     tags={"appointments"},
 *     summary="Ažuriraj termin",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID termina",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="appointment_date", type="string", format="date", example="2025-11-21"),
 *             @OA\Property(property="appointment_time", type="string", format="time", example="15:00:00"),
 *             @OA\Property(property="service_type", type="string", example="Vakcinacija"),
 *             @OA\Property(property="notes", type="string", example="Ažurirana napomena"),
 *             @OA\Property(
 *                 property="status",
 *                 type="string",
 *                 enum={"scheduled", "completed", "cancelled"},
 *                 example="scheduled"
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Termin uspješno ažuriran"
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Greška u validaciji"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Termin nije pronađen"
 *     )
 * )
 */
Flight::route('PUT /api/appointments/@id', function($id) use ($appointmentService) {
    $data = Flight::request()->data->getData();
    $result = $appointmentService->updateAppointment($id, $data);
    
    if (isset($result['error'])) {
        Flight::json($result, 400);
    } else {
        Flight::json($result);
    }
});

/**
 * @OA\Put(
 *     path="/api/appointments/{id}/cancel",
 *     tags={"appointments"},
 *     summary="Otkaži termin",
 *     description="Postavlja status termina na 'cancelled'",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID termina",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Termin uspješno otkazan"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Termin nije pronađen"
 *     )
 * )
 */
Flight::route('PUT /api/appointments/@id/cancel', function($id) use ($appointmentService) {
    $result = $appointmentService->cancelAppointment($id);
    
    if (isset($result['error'])) {
        Flight::json($result, 400);
    } else {
        Flight::json($result);
    }
});

/**
 * @OA\Delete(
 *     path="/api/appointments/{id}",
 *     tags={"appointments"},
 *     summary="Obriši termin",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID termina",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Termin uspješno obrisan"
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Termin nije pronađen"
 *     )
 * )
 */
Flight::route('DELETE /api/appointments/@id', function($id) use ($appointmentService) {
    $result = $appointmentService->deleteAppointment($id);
    
    if (isset($result['error'])) {
        Flight::json($result, 404);
    } else {
        Flight::json($result);
    }
});
?>