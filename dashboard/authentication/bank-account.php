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
            echo "<script>alert('All fields are required to create an account.'); window.location.href='../../';</script>";
            exit;
        }

        if(strlen($pin) !== 4 || !ctype_digit($pin)) {
            echo "<script>alert('The PIN must be a 4-digit number.'); window.location.href='../../';</script>";
            exit;
        }
        
        $stmt = $this->runQuery("SELECT * FROM account_info WHERE account_number = :account_number");
        $stmt->execute(array(":account_number" => $account_number));

        if($stmt->rowCount() > 0){
            echo "<script>alert('The account number already exists. Please use a different account number.'); window.location.href='../../';</script>";
            exit;
        }

        if(!isset($csrf_token) || !hash_equals($_SESSION['csrf_token'], $csrf_token)){
            echo "<script>alert('Invalid CSRF token. Please try again.'); window.location.href='../../';</script>";
            exit;
        }

        unset($_SESSION['csrf_token']);

        $hashPin = password_hash($pin, PASSWORD_DEFAULT);

        $stmt = $this->runQuery("INSERT INTO account_info (account_number, owner_name, pin, balance) VALUES (:account_number, :owner_name, :pin, :balance)");
        $exec = $stmt->execute(array(
            ":account_number" => $account_number,
            ":owner_name" => $owner_name,
            ":pin" => $hashPin,
            ":balance" => 0
        ));

        if($exec){
            echo "<script>alert('Account created successfully!'); window.location.href='../../';</script>";
        }else{
            echo "<script>alert('An error occurred while creating the account. Please try again.'); window.location.href='../../';</script>";
        }

        unset($_SESSION['auto_generated_account_number']);
    }

    public function userSignIn($csrf_token, $account_number, $pin)
    {
        if(empty($account_number) || empty($pin)){
            echo "<script>alert('Both account number and PIN are required to sign in.'); window.location.href='../../';</script>";
            exit;
        }

        if(!isset($csrf_token) || !hash_equals($_SESSION['csrf_token'], $csrf_token)){
            echo "<script>alert('Invalid CSRF token. Please try again.'); window.location.href='../../';</script>";
            exit;
        }

        unset($_SESSION['csrf_token']);

        $stmt = $this->runQuery("SELECT * FROM account_info WHERE account_number = :account_number");
        $stmt->execute(array(":account_number" => $account_number));
        $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if($stmt->rowCount() == 1){
            if(password_verify($pin, $userRow['pin'])){
                $_SESSION['account_number'] = $userRow['account_number'];

                echo "<script>alert('Welcome back, {$userRow['owner_name']}!'); window.location.href='../';</script>";
                exit;
            }else{
                echo "<script>alert('Incorrect PIN. Please try again.'); window.location.href='../../';</script>";
                exit;
            }
        }else{
            echo "<script>alert('No account found with the provided account number.'); window.location.href='../../';</script>";
            exit;
        }
    }

    public function deposit($csrf_token, $deposit_amount){
        if(empty($deposit_amount)){
            echo "<script>alert('Please enter an amount to deposit.'); window.location.href='../';</script>";
            exit;
        }

        if(!isset($csrf_token) || !hash_equals($_SESSION['csrf_token'], $csrf_token)){
            echo "<script>alert('Invalid CSRF token. Please try again.'); window.location.href='../';</script>";
            exit;
        }

        unset($_SESSION['csrf_token']);

        if($deposit_amount > 0){
            $account_number = $_SESSION['account_number'];
            $stmt = $this->runQuery("SELECT * FROM account_info WHERE account_number = :account_number");
            $stmt->execute(array(":account_number" => $account_number));
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);

            $updatedAmount = $userData['balance'] + $deposit_amount;

            $stmt = $this->runQuery("UPDATE account_info SET balance = :balance WHERE account_number = :account_number");
            $exec = $stmt->execute(array(
                ":balance" => $updatedAmount,
                ":account_number" => $account_number
            ));

            if($exec){
                echo "<script>alert('Deposit completed successfully!'); window.location.href='../';</script>";
            }else{
                echo "<script>alert('An error occurred while processing the deposit. Please try again.'); window.location.href='../';</script>";
            }
        }else{
            echo "<script>alert('The deposit amount must be greater than 0.'); window.location.href='../';</script>";
            exit;
        }
    }

    public function withdraw($csrf_token, $withdraw_amount){
        if(empty($withdraw_amount)){
            echo "<script>alert('Please enter an amount to withdraw.'); window.location.href='../';</script>";
            exit;
        }

        if(!isset($csrf_token) || !hash_equals($_SESSION['csrf_token'], $csrf_token)){
            echo "<script>alert('Invalid CSRF token. Please try again.'); window.location.href='../';</script>";
            exit;
        }

        unset($_SESSION['csrf_token']);

        if($withdraw_amount > 0){
            $account_number = $_SESSION['account_number'];
            $stmt = $this->runQuery("SELECT * FROM account_info WHERE account_number = :account_number");
            $stmt->execute(array(":account_number" => $account_number));
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);

            if($userData['balance'] >= $withdraw_amount){
                $updatedAmount = $userData['balance'] - $withdraw_amount;

                $stmt = $this->runQuery("UPDATE account_info SET balance = :balance WHERE account_number = :account_number");
                $exec = $stmt->execute(array(
                    ":balance" => $updatedAmount,
                    ":account_number" => $account_number
                ));

                if($exec){
                    echo "<script>alert('Withdrawal completed successfully!'); window.location.href='../';</script>";
                }else{
                    echo "<script>alert('An error occurred while processing the withdrawal. Please try again.'); window.location.href='../';</script>";
                }
            }else{
                echo "<script>alert('Insufficient balance for the requested withdrawal amount.'); window.location.href='../';</script>";
                exit;
            }
        }else{
            echo "<script>alert('The withdrawal amount must be greater than 0.'); window.location.href='../';</script>";
            exit;
        }
    }

    public function transfer($csrf_token, $receiver_account_number, $transfer_amount){
        if(empty($receiver_account_number) || empty($transfer_amount)){
            echo "<script>alert('Please enter account number and amount to transfer.'); window.location.href='../';</script>";
            exit;
        }

        if($_SESSION['account_number'] == $receiver_account_number){
            echo "<script>alert('You cannot transfer money to your own account.'); window.location.href='../';</script>";
            exit;
        }

        if(!isset($csrf_token) || !hash_equals($_SESSION['csrf_token'], $csrf_token)){
            echo "<script>alert('Invalid CSRF token. Please try again.'); window.location.href='../';</script>";
            exit;
        }

        unset($_SESSION['csrf_token']);

        if($transfer_amount > 0){
            $stmt = $this->runQuery("SELECT * FROM account_info WHERE account_number = :account_number");
            $stmt->execute(array(":account_number" => $_SESSION['account_number']));
            $senderData = $stmt->fetch(PDO::FETCH_ASSOC);

            if($senderData['balance'] >= $transfer_amount){
                $senderBalance = $senderData['balance'] - $transfer_amount;

                $stmt = $this->runQuery("UPDATE account_info SET balance = :balance WHERE account_number = :account_number");
                $exec = $stmt->execute(array(
                    ":balance" => $senderBalance,
                    ":account_number" => $_SESSION['account_number']
                ));
            }else{
                echo "<script>alert('Insufficient balance for the requested transfer amount.'); window.location.href='../';</script>";
                exit;
            }

            $stmt = $this->runQuery("SELECT * FROM account_info WHERE account_number = :account_number");
            $stmt->execute(array(":account_number" => $receiver_account_number));
            $receiverData = $stmt->fetch(PDO::FETCH_ASSOC);

            $receiverBalance = $receiverData['balance'] + $transfer_amount;

            $stmt = $this->runQuery("UPDATE account_info SET balance = :balance WHERE account_number = :account_number");
            $exec1 = $stmt->execute(array(
                ":balance" => $receiverBalance,
                ":account_number" => $receiver_account_number
            ));

            if($exec && $exec1){
                echo "<script>alert('Transfer completed successfully!'); window.location.href='../';</script>";
            }else{
                echo "<script>alert('An error occurred while processing the transfer. Please try again.'); window.location.href='../';</script>";
            }
        }else{
            echo "<script>alert('The transfer amount must be greater than 0.'); window.location.href='../';</script>";
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

        echo "<script>alert('You have successfully signed out.'); window.location.href='../../';</script>";
        exit;
    }

    public function redirect($message, $url){
        echo "<script>alert('$message'); window.location.href='$url';</script>";
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

if(isset($_POST['btn-deposit'])){
    $csrf_token = trim($_POST['csrf_token']);
    $deposit_amount =  trim($_POST['depositAmount']);

    $deposit = new bankAccount();
    $deposit->deposit($csrf_token, $deposit_amount);
}

if(isset($_POST['btn-withdraw'])){
    $csrf_token = trim($_POST['csrf_token']);
    $withdraw_amount =  trim($_POST['withdrawAmount']);

    $withdraw = new bankAccount();
    $withdraw->withdraw($csrf_token, $withdraw_amount);
}

if(isset($_POST['btn-transfer'])){
    $csrf_token = trim($_POST['csrf_token']);
    $receiver_account_number =  trim($_POST['accountNumber']);
    $transfer_amount =  trim($_POST['transferAmount']);


    $transfer = new bankAccount();
    $transfer->transfer($csrf_token, $receiver_account_number, $transfer_amount);
}    
?>