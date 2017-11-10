<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_Auth extends Controller_Index {

    private $layout;

    public function before() {
        parent::before();
        $this->main_navi->activate(__('Login'));
        $this->sub_navi->add('auth/login', __('Login'));
        $this->sub_navi->add('auth/create', __('Create'));
  
        $this->layout = View::factory('auth/layout');
        $this->layout->content = '';
    }

    public function action_index() {
        //if user is logged in display Error page
        if ($this->user->has_roles(array('admin', 'login'))) {
            throw new HTTP_Exception_404();
        }

        $this->action_login();
    }

    public function action_login() {
        //if user is logged in display Error page
        if ($this->user->has_roles(array('admin', 'login'))) {
            throw new HTTP_Exception_404();
        }
        $this->sub_navi->activate(__('Login'));
        $view = View::factory(I18n::$lang . '/auth/login');


        if (HTTP_Request::POST == $this->request->method()) {
            Auth::instance()->logout(); //Logout guest
            // Attempt to login user
            $remember = array_key_exists('remember', $this->request->post()) ? (bool) $this->request->post('remember') : FALSE;

            $user = Auth::instance()->login($this->request->post('username'), $this->request->post('password'), $remember);


            // If successful...
            if ($user) {
                $user = Auth::instance()->get_user();
                if (!(bool) $user->locked) {
                    $this->redirect($this->session->get('referrer', I18n::$lang . '/index'));
                } else {
                    $view->incorrect = TRUE;
                    Auth::instance()->force_login('guest');
                }

              
            } else {
                $view->incorrect = TRUE;
                Auth::instance()->force_login('guest');
            }
        }
        $this->layout->content = $view->render();
    }

    public function action_logout() {
        //if user is logged in display Error page
        if ($this->user->has_roles(array('guest'))) {
            throw new HTTP_Exception_404();
        }
        Auth::instance()->logout(TRUE);
        Auth::instance()->force_login('guest');
        $this->redirect(I18n::$lang . '/index');
    }

    public function action_create() {
        //if user is logged in display Error page
        if ($this->user->has_roles(array('admin', 'user'))) {
            throw new HTTP_Exception_404();
        }

        $view = View::factory(I18n::$lang . '/auth/create');
        $view->errors = array();
        $this->sub_navi->activate(__('Create'));
        if (HTTP_Request::POST == $this->request->method()) {
            try {
                $password = Text::random('alnum');
                //Add additional values which dont comes from Form
                $additional = array(
                    'ip' => $_SERVER['REMOTE_ADDR'],
                    'chdate' => time(),
                    'mkdate' => time(),
                    'password' => $password
                );

                $post = Arr::merge($this->request->post(), $additional);
                // Create the user using form values
                $user = ORM::factory('User');
                $user->create_user($post, array(
                    'username',
                    'password',
                    'email',
                    'mkdate',
                    'chdate',
                    'title',
                    'name',
                    'surname',
                    'institution',
                    'department',
                    'street',
                    'zip',
                    'location',
                    'country',
                    'phone',
                    'ip'
                ));

                // Grant user login role
                $user->add('roles', ORM::factory('Role', array('name' => 'login')));

                // Reset values so form is not sticky
                $_POST = array();

                //Change the View
                $view = View::factory(I18n::$lang . '/auth/create_success');
                $view->email = $post['email'];

                $mailBody = View::factory(I18n::$lang . '/mails/registration');
                $mailBody->username = $post['username'];
                $mailBody->password = $password;
                $mailBody->name = $post['name'];
                $mailBody->surname = $post['surname'];
                
                $textBody = View::factory(I18n::$lang . '/mails/text_registration');
                $textBody->username = $post['username'];
                $textBody->password = $password;
                $textBody->name = $post['name'];
                $textBody->surname = $post['surname'];
                $email = Email::factory('Ihre Registrierung bei histat.gesis.org')
                        ->to($post['email'])
                        ->from($this->config->get('from'))
                        ->message($textBody->render())
                        ->message($mailBody->render(), 'text/html')
                        ->send();
            } catch (ORM_Validation_Exception $e) {
                $view->errors = $e->errors(I18n::$lang);
            }
        }



        $this->layout->content = $view->render();
    }
    public function action_password_lost(){
         //if user is logged in display Error page
        if ($this->user->has_roles(array('admin', 'login'))) {
            throw new HTTP_Exception_404();
        }
        $this->sub_navi->activate(__('Login'));
        $view = View::factory(I18n::$lang . '/auth/password_lost');


        if (HTTP_Request::POST == $this->request->method()) {
           $email = $this->request->post('email');
       
            $user = ORM::factory('User', array('email'=>$email));
           
            if($user->loaded()){

            try {
                //Add additional values which dont comes from Form
                $password = Text::random('alnum');
                $post = array(
                    'chdate' => time(),
                    'password' => $password
                );



                // Edit the user using form values
                $user->change_password($post, array(
                    'password',
                    'chdate'
                ));

                  $mailBody = View::factory(I18n::$lang . '/mails/passwordchangedcustom');
                $mailBody->username = $user->username;
                $mailBody->password = $password;
                $mailBody->name = $user->name;
                $mailBody->surname = $user->surname;
                
                
                  $textBody = View::factory(I18n::$lang . '/mails/text_passwordchangedcustom');
                $textBody->username = $user->username;
                $textBody->password = $password;
                $textBody->name = $user->name;
                $textBody->surname = $user->surname;
                $email = Email::factory(__('Your new Password for histat.gesis.org'))
                        ->to($user->email)
                        ->from($this->config->get('from'))
                        ->message($textBody->render())
                        ->message($mailBody->render(), 'text/html')
                        ->send();
               $view->message = __('New password was send to the E-Mail address');
            } catch (ORM_Validation_Exception $e) {
               
               $view->message = __('Password could not be changed');
            }
            }else{
                   $view->message = __('E-Mail not found');
            }
        }
        $this->layout->content = $view->render();
    }
    public function after() {
        $this->content = $this->layout->render();
        parent::after();
    }

}