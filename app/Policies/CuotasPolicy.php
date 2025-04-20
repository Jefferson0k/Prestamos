<?php

namespace App\Policies;

use App\Models\CuotasModelo;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CuotasPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('ver cuotas');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CuotasModelo $cuotasModelo): bool
    {
        return $user->can('ver cuotas');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('crear cuotas');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CuotasModelo $cuotasModelo): bool
    {
        return $user->can('editar cuotas');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CuotasModelo $cuotasModelo): bool
    {
        return $user->can('eliminar cuotas');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CuotasModelo $cuotasModelo): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CuotasModelo $cuotasModelo): bool
    {
        return false;
    }
}
