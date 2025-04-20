<?php

namespace App\Policies;

use App\Models\PagosModelo;
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
    public function view(User $user, PagosModelo $pagosModelo): bool
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
    public function update(User $user, PagosModelo $pagosModelo): bool
    {
        return $user->can('editar pagos');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PagosModelo $pagosModelo): bool
    {
        return $user->can('eliminar pagos');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PagosModelo $pagosModelo): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PagosModelo $pagosModelo): bool
    {
        return false;
    }
}
