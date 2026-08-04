<?php

it('redirects the root path to the login page', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});
