<?php
use Servit\Restsrv\RestServer\ThemeController;

class RootThemeController extends ThemeController
{

    protected $theme = '';
    protected $themeurl = "page";
    protected $themepath = '';
    protected $productsrv = '';
    protected $categories = [];
    protected $products = [];
    protected $product = '';

    protected function getthemepath()
    {
        return $this->themepath;
    }

    protected function get_themeurl()
    {
        return $this->themeurl;
    }

    public function __construct()
    {
        parent::__construct();
        $this->themepath = __DIR__ . '/../../views/'; //vue1
    }

    public function handle404()
    {
        $this->server->setStatus(404);
        return "NOT FOUND";
    }

    public function handle401()
    {
        $this->server->setStatus(401);
        echo '401:Unauthorized';
    }

    public function get_header()
    {
        require_once $this->themepath . 'head.php';
    }

    public function get_footer()
    {
        require_once $this->themepath . 'foot.php';
    }
}
