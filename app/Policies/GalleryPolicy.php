<?php

namespace App\Policies;

use App\User;
use App\Gallery;
use Illuminate\Auth\Access\HandlesAuthorization;

class GalleryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can delete the gallery.
     *
     * @param  \App\User  $user
     * @param  \App\Gallery  $gallery
     * @return mixed
     */
    public function destroy(User $user, Gallery $gallery)
    {
        return $user->id == $gallery->user_id;
    }
}
