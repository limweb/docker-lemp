<?php
require_once __DIR__.'/RootThemeController.php';

class RootController extends RootThemeController
{

    protected $theme = '';

    public function init()
    {
    }

    public function __construct()
    {
        parent::__construct();
    }

    public function authorize()
    {
        return true;
    }

    public function __destruct()
    {
        $this->get_footer();
    }

    /**
     *@noAuth
     *@url GET /
     *@url GET /index
     */
    public function index()
    {
        $this->get_header();
        require_once $this->themepath . '/index.php';
    }

}
