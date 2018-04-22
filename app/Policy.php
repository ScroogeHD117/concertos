<?php

namespace App;
use App\User;
use App\Torrent;

class Policy
{
    static function isModerator(User $user)
    {
        return $user->group->is_modo;
    }

    static function canEditTorrent(User $user, Torrent $torrent)
    {
        return self::isModerator($user) || $torrent->user->id == $user->id;
    }
}