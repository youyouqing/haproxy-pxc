<?php
namespace app\index\controller;

use DbManager\Factory;
use DbManager\mysql;
use DbManager\mysqlFactory;
use DbManager\sqliteFactory;
use duotai\Cat;
use duotai\MTiger;
use duotai\XTiger;
use factory\Client;
use think\Db;

class Index
{
    public function index()
    {
        return 'i love you baby';
    }

    public function factory()
    {
        $client = new Client();
    }

    //region 测试各种设计模式

    /**
     * 多态
     */
    public function duotai()
    {
        \duotai\Client::call(new XTiger());
        \duotai\Client::call(new MTiger());
//        \duotai\Client::call(new Cat());//会报错  Cat没有继承与Tiger
    }

    //面向接口编程

    public function db()
    {
        //简单工厂扩展性很低   扩展需要修改源码
//        $db = Factory::createDB("mysql");
//        $db->conn();
//
//        $db = Factory::createDB("sqlite");
//        $db->conn();

//        $db = Factory::createDB("xxx");
//        $db->conn();


        //抽象工厂
        $mysqlFactory = new mysqlFactory();
        $db = $mysqlFactory->createDB();
        $db->conn();

        $sqliteFactory = new sqliteFactory();
        $db = $sqliteFactory->createDB();
        $db->conn();
    }


    //endregion


}
