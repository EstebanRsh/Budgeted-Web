<?php
declare(strict_types=1);

class ApiController
{
    /**
     * Verifica si un email está disponible.
     * GET /api/check-email?email=test@example.com&except_id=5
     */
    public function checkEmail(): void
    {
        header('Content-Type: application/json');

        $email = $_GET['email'] ?? '';
        $exceptId = isset($_GET['except_id']) ? (int)$_GET['except_id'] : null;

        if (empty($email)) {
            echo json_encode(['available' => false, 'message' => 'Email requerido']);
            return;
        }

        if (!validar_email($email)) {
            echo json_encode(['available' => false, 'message' => 'Email inválido']);
            return;
        }

        $available = email_unico($email, $exceptId);

        echo json_encode([
            'available' => $available,
            'message' => $available ? 'Email disponible' : 'Email ya está en uso'
        ]);
    }
}
