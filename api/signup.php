<?php

require_once '../libs/DBaccess.php';
require_once "../libs/helper.php";

session_start();

$response = array();

$username = /*cleanInput(*/$_POST['username']/*)*/;
$name = cleanInput($_POST['name']);
$surname = cleanInput($_POST['surname']);
$mail = /*cleanInput(*/$_POST['mail']/*, true)*/;
$password = cleanInput($_POST['password']);
$rePassword = cleanInput($_POST['rePassword']);
$toRedirect = urldecode($_POST['redirect'] ?? urlencode('/?r=home'));

$validusername = validateCredentials($username);
$validemail = validateCredentials($mail);
$validnome = validateCredentials($name);
$validcognome = validateCredentials($surname);
$validpassword = validateCredentials($password);
$validrepassword = validateCredentials($rePassword);
$veryvalidemail = validateEmail($mail);


if($validusername && $validemail && $validnome && $validcognome && $validpassword && $validrepassword){

    $DBaccess = new DBaccess();

    $existingUsername = ($DBaccess-> getConnection() !== false) ? $DBaccess->getUsernameQuery($username) : false;
    $existingMail = ($DBaccess-> getConnection() !== false) ? $DBaccess->getMailQuery($mail) : false;

    if(!$existingUsername && !$existingMail && $password == $rePassword && $veryvalidemail){

        $insertResult = $DBaccess-> insertUser($username, $mail, $name, $surname, hash('sha512', $password));
        $DBaccess->closeConnection();
        $response['ok'] = $insertResult;

        if($insertResult){

            $_SESSION['username'] = $username;
            $SESSION['admin'] = false;
            $response['red'] = $toRedirect;
        }else{

            $response['red'] = "/?r=signup";
        }

    }else{
        
        $elements = array(
            'r' => 'signup'
        );

        if($existingUsername){

            $elements['euses'] = 'error';
        }
        if($existingMail){

            $elements['eemes'] = 'error';
        }
        if($password !== $rePassword){

            $elements['epanc'] = 'error';
        }
        if(!$veryvalidemail){

            $elements['eemnv'] = 'error';
        }

        $response['ok']= false;
        $redirect = '/?' . http_build_query($elements);
        $response['red'] = $redirect;
        $DBaccess->closeConnection();  
    }    
}else{

    $response['ok']= false;

    $elements = array(
        'r' => 'signup'
    );

    if(!$validusername){

        $elements['eusnv'] = 'error';
    }
    if(!$validemail){

        $elements['eemnv'] = 'error';
    }
    
    if(!$validnome){

        $elements['enonv'] = 'error';
    }
    if(!$validcognome){

        $elements['econv'] = 'error';
    }
    if(!$validpassword){

        $elements['epanv'] = 'error';
    }
    if(!$validrepassword){

        $elements['epanc'] = 'error';
    }

    $redirect = '/?' . http_build_query($elements);

    $response['red'] = $redirect;
}

$toRedirect = $response['red'];
header("location: $toRedirect");



/*if(!$existingUsername && !$existingMail && $password == $rePassword && $mail != "Fatal error"){

    $insertResult = $DBaccess-> insertUser($username, $mail, $name, $surname, hash('sha512', $password));
    $DBaccess->closeConnection();
    if($insertResult){
        $_SESSION['username'] = $username;
        $SESSION['admin'] = false;

    }else{

        header("Location: /?r=singup");
    }
    header("location: $toRedirect");

}else{

    $DBaccess->closeConnection();
    header("location: /?r=signup");
}*/


