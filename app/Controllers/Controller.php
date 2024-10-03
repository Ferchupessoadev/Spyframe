<?php

namespace App\Controllers;

abstract class Controller
{
    /**
     * Request data
     * @var array
     */
    protected $request;

    /**
     * Constructor
     * @param array $request
     */
    public function __construct(array $request = [])
    {
        $this->request = $request;
    }

    /**
     * Render a view
     * @param string $view
     * @param array $data
     * @return string
     */
    public function view(string $view, array $data = []): string
    {
        $view = str_replace('.', '/', $view);
        foreach ($data as $key => $value) {
            $data[$key] = htmlspecialchars($value);
        }

        extract($data);

        $viewPath = '../resources/views/' . $view . '.php';
        if (!file_exists($viewPath)) {
            throw new \Exception('View not found: ' . $view);
        }
        ob_start();
        include $viewPath;
        $content = ob_get_clean();
        return $content;
    }

    /**
     * Redirect to another page
     * @param string $url
     * @param int $statusCode
     * @return void
     */
    public function redirect(string $url, $statusCode = 303): void
    {
        header('Location: ' . $url, true, $statusCode);
        exit;
    }
}
