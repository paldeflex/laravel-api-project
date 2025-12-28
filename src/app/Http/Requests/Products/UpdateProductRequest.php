<?php

declare(strict_types=1);

namespace App\Http\Requests\Products;

use App\Enums\Products\ProductStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'quantity' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'price' => ['sometimes', 'integer', 'min:0'],
            'status' => ['sometimes', Rule::enum(ProductStatus::class)],
            'images' => ['sometimes', 'array'],
            'images.*' => ['file', 'image', 'max:5120'],
        ];
    }
}
