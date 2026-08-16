<?php

declare(strict_types=1);

namespace App\Http\Requests;

/**
 * Editing a post accepts exactly the same input as creating one.
 * Authorization happens in the controller via the PostPolicy.
 */
class UpdatePostRequest extends StorePostRequest {}
