<?php

namespace app\admin\controller;

class Index extends Common
{
    public function index()
    {
        return $this->fetch();
    }

    public function logout()
    {
        session('admin', null);
        cookie('admin', null);
        return $this->redirect('/');
    }
}
