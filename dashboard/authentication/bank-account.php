<?php

    require_once __DIR__.'/../../database/database-connection.php';
    include_once __DIR__.'/../../config/settings-configuration.php';

class bankAccount{

    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->dbConnection();
    }

    public function createAccount($csrf_token, $account_number, $owner_name, $pin)
    {
        if(empty($account_number) || empty($owner_name) || empty($pin)){
            echo "<script>alert('Please fill in all fields!'); window.location.href='../../';</script>";
            exit;
        }

        if(strlen($pin) !== 4 || !ctype_digit($pin)) {
            echo "<script>alert('Enter a 4-digit PIN!'); window.location.href='../../';</script>";
            exit;
        }
        

        $stmt = $this->runQuery("SELECT * FROM account_info WHERE account_number = :account_number");
        $stmt->execute(array(":account_number" => $account_number));

        if($stmt->rowCount() > 0){
            echo "<script>alert('Account Number is already existed!'); window.location.href='../../';</script>";
            exit;
        }

        if(!isset($csrf_token) || !hash_equals($_SESSION['csrf_token'], $csrf_token)){
            echo "<script>alert('Invalid CSRF Token!'); window.location.href='../../';</script>";
            exit;
        }

        unset($_SESSION['csrf_token']);

        $hashPin = password_hash($pin, PASSWORD_DEFAULT);

        $stmt = $this->runQuery("INSERT INTO account_info (account_number, owner_name, pin) VALUES (:account_number, :owner_name, :pin)");
        $exec = $stmt->execute(array(
            ":account_number" => $account_number,
            ":owner_name" => $owner_name,
            ":pin" => $hashPin,
        ));

        if($exec){
            echo "<script>alert('Account added successfully!'); window.location.href='../../';</script>";
        }else{
            echo "<script>alert('Error adding account!'); window.location.href='../../';</script>";
        }
    }

    public function userSignIn($csrf_token, $account_number, $pin)
    {
        if(empty($account_number) || empty($pin)){
            echo "<script>alert('Please fill in all fields!'); window.location.href='../../';</script>";
            exit;
        }

        if(!isset($csrf_token) || !hash_equals($_SESSION['csrf_token'], $csrf_token)){
            echo "<script>alert('Invalid CSRF Token!'); window.location.href='../../';</script>";
            exit;
        }

        unset($_SESSION['csrf_token']);

        $stmt = $this->runQuery("SELECT * FROM account_info WHERE account_number = :account_number");
        $stmt->execute(array(":account_number" => $account_number));
        $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if($stmt->rowCount() == 1){
            if(password_verify($pin, $userRow['pin'])){
                $_SESSION['account_number'] = $userRow['account_number'];

                echo "<script>alert('Welcome to Banknginamo {$userRow['owner_name']}!'); window.location.href='../';</script>";
                exit;
            }else{
                echo "<script>alert('Incorrect password!'); window.location.href='../../';</script>";
                exit;
            }
        }else{
            echo "<script>alert('No user found!'); window.location.href='../../';</script>";
            exit;
        }
    }

    public function isSignedIn(){
        if(isset($_SESSION['account_number'])){
            return true;
        }
    }

    public function userSignOut(){
        unset($_SESSION['account_number']);

        echo "<script>alert('Sign out successfully!'); window.location.href='../../';</script>";
        exit;
    }

    public function redirect(){
        echo "<script>alert('User must sign in first!'); window.location.href='../';</script>";
        exit;
    }

    public function runQuery($sql){
        $stmt = $this->conn->prepare($sql);
        return $stmt;
    }
}

if(isset($_POST['btn-sign-up']))
{
    $csrf_token = trim($_POST['csrf_token']);
    $account_number =  trim($_POST['account_number']);
    $owner_name =  trim($_POST['owner_name']);
    $pin =  trim($_POST['pin']);

    $signUp = new bankAccount();
    $signUp->createAccount($csrf_token, $account_number, $owner_name, $pin);
}

if(isset($_POST['btn-sign-in']))
{
    $csrf_token = trim($_POST['csrf_token']);
    $account_number =  trim($_POST['account_number']);
    $pin =  trim($_POST['pin']);

    $signUp = new bankAccount();
    $signUp->userSignIn($csrf_token, $account_number, $pin);
}

if(isset($_GET['sign-out'])){
    $signOut = new bankAccount();
    $signOut->userSignOut();
}
?>