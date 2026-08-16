<?php

declare(strict_types=1);

namespace App\Http\Requests;

/**
 * Editing a thread accepts exactly the same input as creating one.
 * Authorization happens in the controller via the ThreadPolicy.
 */
class UpdateThreadRequest extends StoreThreadRequest {}
