<?php

defined('SYSPATH') or die('No direct script access.');

/**
 *
 */
class Controller_Search extends Controller_Data {

    private $layout;
    private $results = array();
    private $show = false;
    public function before() {
        parent::before();
        $this->layout = View::factory(I18n::$lang . '/search/layout');
        $orm = ORM::factory('theme');

        $themes = array(
            "-1" => __('All')
        );
        foreach ($orm->order_by("Thema")->find_all() as $theme) {
            $themes[$theme->ID_Thema] = $theme->Thema;
        }
        $this->layout->checked = true;
        $this->layout->themes = $themes;
        $results = array();
        $data = array();
        if (HTTP_Request::POST == $this->request->method()) {
            $this->show = true;
            $orm = ORM::factory('project');
            $results = $orm->search($this->request->post());
           
            if(count($results) > 0){
              
               $data = $orm->where('ID_Projekt','IN',  array_keys($results))->find_all();
             
            }
             
            $this->results =array("results"=>$results,"data"=>$data);
        }
    }

    public function action_index() {
        
    }

    public function action_extended() {
        if (HTTP_Request::POST == $this->request->method()) {
            $this->layout->checked = false;
        }
    }

    public function after() {
        $view = View::factory(I18n::$lang . '/search/result');
        $view->results = $this->results;
        $view->show = $this->show;
        $this->layout->results = $view->render();
        $this->content = $this->layout->render();
        parent::after();
    }

}