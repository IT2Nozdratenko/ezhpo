<?php

namespace App\Actions\Element;

use App\Product;

class CreateProductHandler extends AbstractCreateElementHandler implements CreateElementHandlerInterface
{
    public function handle($data)
    {
        $validator = function (int $hashId) {
            if (Product::where('hash_id', $hashId)->first()) {
                return false;
            }

            return true;
        };

        $data['hash_id'] = $this->generateHashId(
            $validator,
            config('app.hash_generator.product.min', 0),
            config('app.hash_generator.product.max', 999999),
            config('app.hash_generator.product.tries', 2)
        );

        if ($data['type_product'] === 'Абонентская плата без реестров') {
            $data['type_anketa'] = null;
            $data['type_view'] = null;
        }

        return $this->createElement($data);
    }
}
