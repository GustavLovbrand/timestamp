<?php

class ApiDocsController extends Controller
{
    private string $apiControllerDir;

    public function __construct()
    {
        $this->apiControllerDir = __DIR__ . "/../api";
    }

    /**
     * @desc Auto-generates API documentation by scanning all API controllers.
     */
    public function index()
    {
        $endpoints = $this->scanApiControllers();
        $title = "API Dokumentation";

        $this->view("api_docs", compact("title", "endpoints"));
    }

    /**
     * Scan all controller classes under /app/api/
     */
    private function scanApiControllers(): array
    {
        $results = [];

        foreach (glob($this->apiControllerDir . "/*Controller.php") as $file) {

            require_once $file;

            $className = basename($file, ".php");

            if (!class_exists($className)) {
                continue;
            }

            $ref = new ReflectionClass($className);

            foreach ($ref->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {

                if ($method->class !== $className) continue;
                if ($method->isConstructor()) continue;

                $doc = $method->getDocComment();

                if (!$doc) continue; // Method has no PHPDoc

                $parsed = $this->parsePhpDoc($doc);

                // Add class + method information
                $parsed['controller'] = $className;
                $parsed['method']     = $method->name;

                $results[] = $parsed;
            }
        }

        return $results;
    }

    /**
     * Parse a PHPDoc block into structured data
     */
    private function parsePhpDoc(string $doc): array
    {
        $lines = explode("\n", $doc);

        $data = [
            "route"   => null,
            "desc"    => null,
            "auth"    => null,
            "params"  => [],
            "returns" => null,
            "errors"  => [],
        ];

        $mode = null;         // active mode: params | errors | returns
        $buffer = [];         // collects multiline data

        foreach ($lines as $lineRaw) {

            // Normalize line
            $line = trim($lineRaw, "/* \t");

            // START NEW TAGS
            if (str_starts_with($line, '@route')) {
                $data['route'] = trim(substr($line, 6));
                $mode = null;
                continue;
            }

            if (str_starts_with($line, '@desc')) {
                $data['desc'] = trim(substr($line, 5));
                $mode = null;
                continue;
            }

            if (str_starts_with($line, '@auth')) {
                $data['auth'] = trim(substr($line, 5));
                $mode = null;
                continue;
            }

            if (str_starts_with($line, '@params')) {
                $mode = 'params';
                continue;
            }

            if (str_starts_with($line, '@errors')) {
                $mode = 'errors';
                continue;
            }

            if (str_starts_with($line, '@returns')) {
                $mode = 'returns';
                $buffer = [];             // start collecting lines
                continue;
            }

            // MULTILINE RETURNS BLOCK
            if ($mode === 'returns') {

                // stop if we hit a new @tag
                if (str_starts_with($line, '@')) {
                    $mode = null;
                    continue;
                }

                // ignore empty lines at start
                if ($line !== '') {
                    $buffer[] = $line;
                }

                continue;
            }

            // PARAMS / ERRORS (single-line items starting with "- ")
            if ($mode === 'params' || $mode === 'errors') {

                if (preg_match('/^- (.*)$/', $line, $m)) {
                    $value = trim($m[1]);

                    if ($value !== '' && $value !== 'none') {
                        $data[$mode][] = $value;
                    }
                }

                continue;
            }
        }

        // Commit returns block
        if (!empty($buffer)) {
            $data['returns'] = implode("\n", $buffer);
        }

        return $data;
    }


}