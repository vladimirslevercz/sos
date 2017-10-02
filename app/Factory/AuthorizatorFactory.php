<?php

namespace App\Factory;

use Nette\Security\IAuthorizator;
use Nette\Security\Permission;

class AuthorizatorFactory
{
    /**
     * @return IAuthorizator
     */
    public static function create()
    {
        $acl = new Permission();

        $acl->addRole('guest');
        $acl->addRole('admin', 'guest');
        $acl->addRole('dev', 'admin');

        $acl->addResource('article');
        $acl->addResource('event');
        $acl->addResource('menu');
        $acl->addResource('newsletter');
        $acl->addResource('registration');
        $acl->addResource('ticket');
        $acl->addResource('user');
        $acl->addResource('document');


        $acl->allow('guest', 'article', ['readSecret', 'read']);
        $acl->allow('guest', 'document', ['read', 'show', 'list', 'create']);

        $acl->allow('admin');

        // Holy access for devstars!
        $acl->allow('dev');

        return $acl;
    }
};
