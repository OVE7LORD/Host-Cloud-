<?php
namespace HostCloud\Validation;

class Validator {
    public static function validateQuestion(array $data): array {
        $errors = [];
        
        if (empty(trim($data['question'] ?? ''))) {
            $errors['question'] = 'Question is required';
        } elseif (strlen(trim($data['question'])) < 10) {
            $errors['question'] = 'Question must be at least 10 characters';
        } elseif (strlen(trim($data['question'])) > 1000) {
            $errors['question'] = 'Question must not exceed 1000 characters';
        }

        if (empty(trim($data['answer'] ?? ''))) {
            $errors['answer'] = 'Answer is required';
        } elseif (strlen(trim($data['answer'])) > 2000) {
            $errors['answer'] = 'Answer must not exceed 2000 characters';
        }

        return $errors;
    }

    public static function validateUser(array $data): array {
        $errors = [];
        
        if (empty(trim($data['email'] ?? ''))) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var(trim($data['email']), FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        } elseif (strlen($data['email']) > 255) {
            $errors['email'] = 'Email is too long';
        }
        
        if (empty($data['password'] ?? '')) {
            $errors['password'] = 'Password is required';
        } elseif (strlen($data['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        } elseif (strlen($data['password']) > 72) { // bcrypt max length
            $errors['password'] = 'Password is too long';
        }
        
        return $errors;
    }

    public static function sanitizeInput(string $input): string {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}
