<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_la_raiz_redirige_al_panel(): void
    {
        $this->get('/')->assertRedirect(route('dashboard'));
    }

    public function test_el_panel_requiere_iniciar_sesion(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }
}