<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Aquí puedes registrar los canales de transmisión que tu aplicación
| utiliza para event broadcasting.
|
*/

Broadcast::channel('channel-name', function ($user) {
    return true; // O tu lógica de autorización
});
