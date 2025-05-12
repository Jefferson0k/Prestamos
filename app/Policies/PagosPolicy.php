<?php

namespace App\Policies;

use App\Models\Pagos;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PagosPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('ver pagos');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Pagos $pagos): bool
    {
        return $user->can('ver pagos');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('crear pagos');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Pagos $pagos): bool
    {
        return $user->can('editar pagos');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Pagos $pagos): bool
    {
        return $user->can('eliminar pagos');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Pagos $pagos): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Pagos $pagos): bool
    {
        return false;
    }
}
