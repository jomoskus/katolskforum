<?php

test('the home page returns a successful response', function (): void {
    $this->get(route('home'))->assertOk();
});
