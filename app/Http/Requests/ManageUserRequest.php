<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ManageUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only admins can manage users
        return $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'action' => 'required|in:block,unblock,assign_role,remove_role',
            'role' => 'required_if:action,assign_role,remove_role|in:admin,seller,buyer',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'action.required' => 'Action is required',
            'action.in' => 'Action must be one of: block, unblock, assign_role, remove_role',
            'role.required_if' => 'Role is required when assigning or removing roles',
            'role.in' => 'Role must be one of: admin, seller, buyer',
        ];
    }
}
