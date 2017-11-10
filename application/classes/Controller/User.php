<?php

defined('SYSPATH') or die('No direct script access.');

class Controller_User extends Controller_Admin {

    public function action_view() {
        $this->sub_navi->activate(__('Users'));
        $id = $this->request->param('id');
        $view = View::factory(I18n::$lang . '/admin/user/view');


        $user = ORM::factory('User', $id);
        $view->user = $user;

        $this->content = $view->render();
    }

    public function action_lock() {
        $id = $this->request->param('id');
        if ($id) {
            $user = ORM::factory('User', $id);

            $user->locked = 1;
            $user->save();
            if ($user->save()) {
                $this->redirect('admin/users/locked#' . $id);
            } else {
                $this->redirect('admin/users/lockedfail#' . $id);
            }
        }
    }

    public function action_unlock() {
        $id = $this->request->param('id');
        if ($id) {
            $user = ORM::factory('User', $id);
            $user->locked = 0;
            if ($user->save()) {
                $this->redirect('admin/users/unlocked#' . $id);
            } else {
                $this->redirect('admin/users/unlockedfail#' . $id);
            }
        }
    }

    public function action_resend_password() {
        $id = $this->request->param('id');
        if ($id) {
            $user = ORM::factory('User', $id);


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

                  $mailBody = View::factory(I18n::$lang . '/mails/passwordchanged');
                $mailBody->username = $user->username;
                $mailBody->password = $password;
                $mailBody->name = $user->name;
                $mailBody->surname = $user->surname;
                
                
                  $textBody = View::factory(I18n::$lang . '/mails/text_passwordchanged');
                $textBody->username = $user->username;
                $textBody->password = $password;
                $textBody->name = $user->name;
                $textBody->surname = $user->surname;
                $email = Email::factory('Ihre Registrierung bei histat.gesis.org')
                        ->to($user->email)
                        ->from($this->config->get('from'))
                        ->message($textBody->render())
                        ->message($mailBody->render(), 'text/html')
                        ->send();
                $this->redirect('admin/users/pwsend#' . $id);
            } catch (ORM_Validation_Exception $e) {
               
               $this->redirect('admin/users/pwsendfail#' . $id);
            }
        }
    }

}