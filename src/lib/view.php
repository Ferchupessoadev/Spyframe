<?php

namespace Spyframe\lib;

/**
 * View class for rendering views
 *
 * This class facilitates the rendering of views for your PHP application.
 * for default, it will render views in the `resources/views` folder.
 *
 * example:
 *
 * ```php
 * $view = new view();
 * return $view->view('home', ['title' => 'E.E.T N°2 Independencia', 'description' => 'Escuela Técnica N°2 Independencia']);
 * ```
 *
 * @author https://github.com/Ferchupessoadev
 * @license MIT
 * @version 1.0
 */
class view
{
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

        if (file_exists('../resources/views/' . $view . '.php')) {
            ob_start();
            include '../resources/views/' . $view . '.php';
            $content = ob_get_clean();
            return $content;
        } else {
            return 'View ' . $view . ' not found';
        }
    }
}
