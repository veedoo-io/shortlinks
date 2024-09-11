<?php declare(strict_types=1);

namespace App\Http\Requests\Url;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property-read string $url
 * @property-read string|null $author_name
 */
class UrlCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'url' => ['required', 'string', 'url'],
            'author_name' => ['nullable', 'string', 'min:1', 'max:255'],
        ];
    }
}
