<?php

declare(strict_types=1);

namespace App\Domain\Notifications\Services;

use App\Domain\Notifications\DTOs\WebPushData;
use App\Domain\Notifications\Notifications\GenericWebPushNotification;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

/**
 * Punto central para enviar notificaciones web push desde cualquier módulo.
 *
 * Uso desde otros Services:
 *
 *   $this->pushService->send(
 *       users: $bodegueros,
 *       data: new WebPushData(
 *           title: 'Stock bajo',
 *           body: 'Cemento Selvalegre está por debajo del mínimo.',
 *           url: '/inventory',
 *           severity: 'warning',
 *       )
 *   );
 */
final class PushNotificationService
{
    /**
     * Enviar push a uno o varios usuarios.
     *
     * @param  User|Collection<int, User>|array<int, User>  $users
     */
    public function send(User|Collection|array $users, WebPushData $data): void
    {
        $notifiables = match (true) {
            $users instanceof User => collect([$users]),
            is_array($users) => collect($users),
            default => $users,
        };

        // Solo enviamos a usuarios que tienen al menos una suscripción activa
        $notifiables = $notifiables->filter(
            fn (User $user) => $user->pushSubscriptions()->exists()
        );

        if ($notifiables->isEmpty()) {
            return;
        }

        Notification::send($notifiables, new GenericWebPushNotification($data));
    }

    /**
     * Enviar push a todos los usuarios de una sucursal con un rol específico.
     *
     * @param  array<string>  $roles
     */
    public function sendToRolesInBranch(int $branchId, array $roles, WebPushData $data): void
    {
        $users = User::query()
            ->whereHas('branches', fn ($q) => $q->where('branches.id', $branchId))
            ->role($roles)
            ->where('is_active', true)
            ->get();

        $this->send($users, $data);
    }
}
