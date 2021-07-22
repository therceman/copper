<?php


namespace Copper\Component\Auth;


interface AuthServiceInterface
{
    public static function validate($login, $password);

    public static function authorize($id);
}