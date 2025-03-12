<?php

namespace App\Trait;

trait ValidationMessages
{
    public function messages(): array
    {
        return $this->getMessages();
    }

    public function getMessages(): array
    {
        return [
            'solution_type_id.unique' => 'This solution type is already assigned to this tenant.',
        ];
    }
}
