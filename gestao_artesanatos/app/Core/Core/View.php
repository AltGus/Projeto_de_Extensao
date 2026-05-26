<?php
// app/Core/View.php
class View
{
    public static function render(string $page, array $data = [], string $layout = 'app'): void
    {
        extract($data, EXTR_SKIP);

        ob_start();
        require view_path('pages/' . $page);
        $content = ob_get_clean();

        require view_path('layouts/' . $layout);
    }
}
