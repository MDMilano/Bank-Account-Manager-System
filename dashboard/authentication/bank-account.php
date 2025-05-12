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

    function showAlert($type, $message, $redirectUrl = null) {
        // Don't exit immediately - let the script finish and then show the alert
        echo "
        <script>
            // Wait for the document to be fully loaded
            document.addEventListener('DOMContentLoaded', function() {
                // Check if SweetAlert is loaded
                if (typeof Swal === 'undefined') {
                    // If SweetAlert is not loaded, load it dynamically
                    var script = document.createElement('script');
                    script.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
                    script.onload = function() {
                        // Once loaded, show the alert
                        showSweetAlert();
                    };
                    document.head.appendChild(script);
                } else {
                    // If SweetAlert is already loaded, show the alert
                    showSweetAlert();
                }
                
                // Function to show the SweetAlert
                function showSweetAlert() {
                    Swal.fire({
                        icon: '$type',
                        title: '$message',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed && '$redirectUrl') {
                            window.location.href = '$redirectUrl';
                        }
                    });
                }
            });
        </script>";
        
        // If this is the end of the script execution, exit
        if ($redirectUrl) {
            exit;
        }
    }

    public function createAccount($csrf_token, $account_number, $owner_name, $pin)
    {
        if (empty($account_number) || empty($owner_name) || empty($pin)) {
            $this->showAlert('error', 'All fields are required to create an account.', '../../');
        }

        if (strlen($pin) !== 4 || !ctype_digit($pin)) {
            $this->showAlert('error', 'The PIN must be a 4-digit number.', '../../');
        }

        $stmt = $this->runQuery("SELECT * FROM account_info WHERE account_number = :account_number");
        $stmt->execute(array(":account_number" => $account_number));

        if ($stmt->rowCount() > 0) {
            $this->showAlert('error', 'The account number already exists. Please use a different account number.', '../../');
        }

        if (!isset($csrf_token) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
            $this->showAlert('error', 'Invalid CSRF token. Please try again.', '../../');
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

        unset($_SESSION['auto_generated_account_number']);

        if ($exec) {
            $this->showAlert('success', 'Account created successfully!', '../../');
        } else {
            $this->showAlert('error', 'An error occurred while creating the account. Please try again.', '../../');
        }
    }

    public function userSignIn($csrf_token, $account_number, $pin)
    {
        if(empty($account_number) || empty($pin)){
            $this->showAlert('error', 'Both account number and PIN are required to sign in.', '../../');
        }

        if(!isset($csrf_token) || !hash_equals($_SESSION['csrf_token'], $csrf_token)){
            $this->showAlert('error', 'Invalid CSRF token. Please try again.', '../../');
        }

        unset($_SESSION['csrf_token']);

        $stmt = $this->runQuery("SELECT * FROM account_info WHERE account_number = :account_number");
        $stmt->execute(array(":account_number" => $account_number));
        $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if($stmt->rowCount() == 1){
            if(password_verify($pin, $userRow['pin'])){
                $_SESSION['account_number'] = $userRow['account_number'];

                $this->showAlert('success', "Welcome, {$userRow['owner_name']}!", '../');
            }else{
                $this->showAlert('error', 'Incorrect PIN. Please try again.', '../../');
            }
        }else{
            $this->showAlert('error', 'No account found with the provided account number.', '../../');
        }
    }

    public function deposit($csrf_token, $deposit_amount){
        if(empty($deposit_amount)){
            $this->showAlert('error', 'Please enter an amount to deposit.', '../');
        }

        if(!isset($csrf_token) || !hash_equals($_SESSION['csrf_token'], $csrf_token)){
            $this->showAlert('error', 'Invalid CSRF token. Please try again.', '../');
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

            $transaction = $this->transaction($account_number, "deposit", $deposit_amount);

            if($exec && $transaction){
                $this->showAlert('success', 'Deposit completed successfully!', '../');
            }else{
                $this->showAlert('error', 'An error occurred while processing the deposit. Please try again.', '../');
            }
        }else{
            $this->showAlert('error', 'The deposit amount must be greater than 0.', '../');
        }
    }

    public function withdraw($csrf_token, $withdraw_amount){
        if(empty($withdraw_amount)){
            $this->showAlert('error', 'Please enter an amount to withdraw.', '../');
        }

        if(!isset($csrf_token) || !hash_equals($_SESSION['csrf_token'], $csrf_token)){
            $this->showAlert('error', 'Invalid CSRF token. Please try again.', '../');
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

                $transaction = $this->transaction($account_number, "withdrawal", $withdraw_amount);

                if($exec & $transaction){
                    $this->showAlert('success', 'Withdrawal completed successfully!', '../');
                }else{
                    $this->showAlert('error', 'An error occurred while processing the withdrawal. Please try again.', '../');
                }
            }else{
                $this->showAlert('error', 'Insufficient balance for the requested withdrawal amount.', '../');
            }
        }else{
            $this->showAlert('error', 'The withdrawal amount must be greater than 0.', '../');
        }
    }

    public function transfer($csrf_token, $receiver_account_number, $transfer_amount){
        if(empty($receiver_account_number) || empty($transfer_amount)){
            $this->showAlert('error', 'Please enter account number and amount to transfer.', '../');
        }

        if($_SESSION['account_number'] == $receiver_account_number){
            $this->showAlert('error', 'You cannot transfer money to your own account.', '../');
        }

        if(!isset($csrf_token) || !hash_equals($_SESSION['csrf_token'], $csrf_token)){
            $this->showAlert('error', 'Invalid CSRF token. Please try again.', '../');
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
                $this->showAlert('error', 'Insufficient balance for the requested transfer amount.', '../');
            }

            $stmt = $this->runQuery("SELECT * FROM account_info WHERE account_number = :account_number");
            $stmt->execute(array(":account_number" => $receiver_account_number));
            
            if($stmt->rowCount() == 0) {
                $this->showAlert('error', 'Recipient account not found. Please check the account number.', '../');
            }
            
            $receiverData = $stmt->fetch(PDO::FETCH_ASSOC);

            $receiverBalance = $receiverData['balance'] + $transfer_amount;

            $stmt = $this->runQuery("UPDATE account_info SET balance = :balance WHERE account_number = :account_number");
            $exec1 = $stmt->execute(array(
                ":balance" => $receiverBalance,
                ":account_number" => $receiver_account_number
            ));

            $transaction = $this->transaction($_SESSION['account_number'], "transfer", $transfer_amount, $receiver_account_number);

            if($exec && $exec1 && $transaction){
                $this->showAlert('success', 'Transfer completed successfully!', '../');
            }else{
                $this->showAlert('error', 'An error occurred while processing the transfer. Please try again.', '../');
            }
        }else{
            $this->showAlert('error', 'The transfer amount must be greater than 0.', '../');
        }
    }

    public function editUsername($csrf_token, $username){
        if(empty($username)){
            $this->showAlert('error', 'Please enter username to edit.', '../');
        }

        if(!isset($csrf_token) || !hash_equals($_SESSION['csrf_token'], $csrf_token)){
            $this->showAlert('error', 'Invalid CSRF token. Please try again.', '../');
        }

        unset($_SESSION['csrf_token']);

        $stmt = $this->runQuery("SELECT * FROM account_info WHERE account_number = :account_number");
        $stmt->execute(array(":account_number" => $_SESSION['account_number']));
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if($stmt->rowCount() == 1){
            $stmt = $this->runQuery("UPDATE account_info SET owner_name = :username WHERE account_number = :account_number");
            $exec = $stmt->execute(array(
                ":username" => $username,
                ":account_number" => $_SESSION['account_number']
            ));

            if($exec){
                $this->showAlert('success', 'Username successfully updated!', '../');
            }else{
                $this->showAlert('error', 'An error occurred while updating the username. Please try again.', '../');
            }
        }else{
            $this->showAlert('error', 'No account found with the provided account number.', '../');
        }
    }

    private function transaction($account_number, $transaction_type, $amount, $recipient_account_number = NULL){
        $stmt = $this->runQuery("INSERT INTO transactions (account_number, transaction_type, amount, recipient_account_number) VALUES (:account_number, :transaction_type, :amount, :recipient_account_number)");
        $stmt->execute(array(
            ":account_number" => $account_number,
            ":transaction_type" => $transaction_type,
            ":amount" => $amount,
            ":recipient_account_number" => $recipient_account_number
        ));
        return true;
    }

    public function deleteAccount($pin){
        if(empty($pin)){
            $this->showAlert('error', 'Please enter your 4-digit PIN to confirm account deletion.', '../');
        }

        $stmt = $this->runQuery("SELECT * FROM account_info WHERE account_number = :account_number");
        $stmt->execute(array(":account_number" => $_SESSION['account_number']));
        $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if($stmt->rowCount() > 0){
            if(password_verify($pin, $userRow['pin'])){
                $stmt = $this->runQuery("DELETE FROM account_info WHERE account_number = :account_number");
                $deleteAccount = $stmt->execute(array(
                    ":account_number" => $_SESSION['account_number']
                ));

                if($deleteAccount){
                    unset($_SESSION['account_number']);
                    $this->showAlert('success', "Account already deleted!", '../../');
                }else{
                    $this->showAlert('error', 'An error occurred while deleting account. Please try again.', '../');
                }
            }else{
                $this->showAlert('error', 'Incorrect PIN. Please try again.', '../');
            }
        }else{
            $this->showAlert('error', 'No account found with the provided account number.', '../');
        }
    }

    public function hideAccountNumber($accountNumber, $start, $length) {
        $hiddenPart = str_repeat('*', $length);
        return substr($accountNumber, 0, $start) . $hiddenPart . substr($accountNumber, $start + $length);
    }

    public function isSignedIn(){
        if(isset($_SESSION['account_number'])){
            return true;
        }
    }

    public function userSignOut(){
        unset($_SESSION['account_number']);

        $this->showAlert('success', 'You have successfully signed out.', '../../');
    }

    public function redirect($message, $url){
        $this->showAlert('info', $message, $url);
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

if(isset($_POST['btn-edit-username'])){
    $csrf_token = trim($_POST['csrf_token']);
    $username =  trim($_POST['username']);

    $edit = new bankAccount();
    $edit->editUsername($csrf_token, $username);
}

if(isset($_POST['btn-delete-account'])){
    $pin = trim($_POST['deleteConfirmPin']);

    $delete = new bankAccount();
    $delete->deleteAccount($pin);
}
?>