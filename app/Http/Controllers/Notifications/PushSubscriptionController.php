<?php

declare(strict_types=1);

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notifications\StorePushSubscriptionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Gestiona las suscripciones de web push del usuario autenticado.
 *
 * No necesita branch scope: una suscripción es por dispositivo del usuario,
 * no por sucursal. El usuario recibe notificaciones de todas sus sucursales
 * en el mismo dispositivo.
 *
 * No necesita auditoría (Auditable): no es un dato operativo de pherce_intel.
 */
final class PushSubscriptionController extends Controller
{
    /**
     * Guarda o actualiza la suscripción push del dispositivo actual.
     * El browser llama a este endpoint tras obtener permiso del usuario.
     */
    public function store(StorePushSubscriptionRequest $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $user->updatePushSubscription(
            endpoint: $request->string('endpoint')->toString(),
            key: $request->string('key')->toString() ?: null,
            token: $request->string('token')->toString() ?: null,
            contentEncoding: $request->string('contentEncoding')->toString() ?: null,
        );

        return response()->json(['subscribed' => true], 201);
    }

    /**
     * Elimina la suscripción push del dispositivo actual.
     * Se llama cuando el usuario desactiva las notificaciones.
     */
    public function destroy(Request $request): Response
    {
        $endpoint = $request->string('endpoint')->toString();

        if ($endpoint !== '') {
            /** @var \App\Models\User $user */
            $user = $request->user();
            $user->deletePushSubscription($endpoint);
        }

        return response()->noContent();
    }
}
