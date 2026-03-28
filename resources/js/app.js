import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

import Echo from "laravel-echo";

window.Echo = new Echo({
    broadcaster: "reverb",
    host: window.location.hostname + ":6001",
});