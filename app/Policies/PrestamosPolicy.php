<?php

namespace App\Policies;

use App\Models\PrestamosModelo;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PrestamosPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('ver prestamos');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PrestamosModelo $prestamosModelo): bool
    {
        return $user->can('ver prestamos');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('crear prestamos');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PrestamosModelo $prestamosModelo): bool
    {
        return $user->can('editar prestamos');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PrestamosModelo $prestamosModelo): bool
    {
        return $user->can('eliminar prestamos');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PrestamosModelo $prestamosModelo): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PrestamosModelo $prestamosModelo): bool
    {
        return false;
    }
}
