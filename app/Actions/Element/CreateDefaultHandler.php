<?php

namespace App\Actions\Element;

use Exception;

class CreateDefaultHandler extends AbstractCreateElementHandler implements CreateElementHandlerInterface
{
    /**
     * @throws Exception
     */
    public function handle($data)
    {
        $validator = function (int $hashId) {
            if ($this->model::where('hash_id', $hashId)->first()) {
                return false;
            }

            return true;
        };

        $data['hash_id'] = $this->generateHashId(
            $validator,
            config('app.hash_generator.default.min', 0),
            config('app.hash_generator.default.max', 999999),
            config('app.hash_generator.default.tries', 2)
        );

        return $this->createElement($data);
    }
}
