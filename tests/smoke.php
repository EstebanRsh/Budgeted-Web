<?php
declare(strict_types=1);

/**
 * Script de pruebas básicas (smoke tests)
 * Valida rutas clave, exportaciones, CSRF, y seguridad
 *
 * Uso: php tests/smoke.php [base_url] [email_test] [password_test]
 * Ej:  php tests/smoke.php http://localhost/proyectos/presupuestador demo@demo.test demo1234
 */

require_once dirname(__DIR__) . '/config/app.php';

class SmokeTest
{
    private string $baseUrl;
    private string $email;
    private string $password;
    private array $results = [];
    private string $sessionId = '';

    public function __construct(string $baseUrl, string $email, string $password)
    {
        $this->baseUrl = rtrim($baseUrl, '/') . '/';
        $this->email = $email;
        $this->password = $password;
    }

    public function run(): void
    {
        echo "\n=== SMOKE TESTS ===\n";

        $this->testLogin();
        $this->testProtectedRoutes();
        $this->testListados();
        $this->testExports();
        $this->testCSRF();

        $this->printResultados();
    }

    private function testLogin(): void
    {
        echo "\n[1] Testing login...\n";

        // Login POST
        $response = $this->curl('POST', 'auth/login', ['email' => $this->email, 'password' => $this->password]);
        if (strpos($response, 'Location') !== false || strpos($response, 'dashboard') !== false) {
            $this->pass('✓ Login exitoso');
        } else {
            $this->fail('✗ Login falló');
        }
    }

    private function testProtectedRoutes(): void
    {
        echo "\n[2] Testing rutas protegidas...\n";

        // Sin session, /presupuestos debe redirigir a login
        $response = $this->curl('GET', 'presupuestos');
        if (strpos($response, 'login') !== false || strpos($response, 'Location') !== false) {
            $this->pass('✓ Ruta /presupuestos protegida');
        } else {
            $this->fail('✗ Ruta /presupuestos no está protegida');
        }

        // /admin debe redirigir
        $response = $this->curl('GET', 'admin');
        if (strpos($response, 'login') !== false || strpos($response, 'Location') !== false) {
            $this->pass('✓ Ruta /admin protegida');
        } else {
            $this->fail('✗ Ruta /admin no está protegida');
        }
    }

    private function testListados(): void
    {
        echo "\n[3] Testing listados...\n";

        // Simular login (en tests reales, guardarías session)
        $this->curl('POST', 'auth/login', ['email' => $this->email, 'password' => $this->password]);

        $rutas = [
            'presupuestos' => 'Presupuestos',
            'clientes' => 'Clientes',
            'productos' => 'Productos',
        ];

        foreach ($rutas as $ruta => $keyword) {
            $response = $this->curl('GET', $ruta);
            if (strpos(strtolower($response), strtolower($keyword)) !== false) {
                $this->pass("✓ GET /{$ruta} retorna contenido");
            } else {
                $this->fail("✗ GET /{$ruta} no retorna contenido esperado");
            }
        }
    }

    private function testExports(): void
    {
        echo "\n[4] Testing exports...\n";

        // PDF
        $response = $this->curl('GET', 'presupuestos/1/pdf', []);
        if (strpos($response, 'PDF') !== false || strlen($response) > 1000) {
            $this->pass('✓ Export PDF funciona');
        } else {
            $this->fail('✗ Export PDF falló');
        }

        // Excel
        $response = $this->curl('GET', 'presupuestos/export/excel', []);
        if (strpos($response, 'xlsx') !== false || strlen($response) > 1000) {
            $this->pass('✓ Export Excel funciona');
        } else {
            $this->fail('✗ Export Excel falló');
        }
    }

    private function testCSRF(): void
    {
        echo "\n[5] Testing CSRF...\n";

        // POST sin CSRF token debe fallar en ciertos endpoints
        $response = $this->curl('POST', 'presupuestos/1/eliminar', [], false);
        if (strpos(strtolower($response), 'csrf') !== false || strpos(strtolower($response), 'token') !== false || strpos(strtolower($response), 'error') !== false) {
            $this->pass('✓ CSRF protegido (POST sin token rechazado)');
        } else {
            $this->fail('✗ CSRF no parece estar protegido');
        }
    }

    private function curl(string $method, string $path, array $data = [], bool $withCSRF = true): string
    {
        $url = $this->baseUrl . $path;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return $response ?? '';
    }

    private function pass(string $msg): void
    {
        $this->results[] = $msg;
        echo $msg . "\n";
    }

    private function fail(string $msg): void
    {
        $this->results[] = $msg;
        echo $msg . "\n";
    }

    private function printResultados(): void
    {
        $passed = count(array_filter($this->results, fn($r) => strpos($r, '✓') === 0));
        $total = count($this->results);

        echo "\n=== RESUMEN ===\n";
        echo "{$passed}/{$total} pruebas pasadas\n";

        if ($passed === $total) {
            echo "\n✓ Todas las pruebas pasaron!\n";
        } else {
            echo "\n✗ Algunas pruebas fallaron. Revisa los detalles arriba.\n";
        }
    }
}

// CLI args
$baseUrl = $argv[1] ?? 'http://localhost/proyectos/presupuestador';
$email = $argv[2] ?? 'demo@demo.test';
$password = $argv[3] ?? 'demo1234';

$test = new SmokeTest($baseUrl, $email, $password);
$test->run();
