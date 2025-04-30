<?php
    require_once 'authentication/bank-account.php';
    $bank = new bankAccount();

    if(!$bank->isSignedIn()){
        $bank->redirect();
    }

    $stmt = $bank->runQuery("SELECT * FROM account_info WHERE account_number = :account_number");
    $stmt->execute(array(":account_number" => $_SESSION['account_number']));
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Account Manager System</title>
</head>
<body>
    <h1>WELCOME TO BANKNGINAMO <?php echo $userData['owner_name']?></h1>
    <button><a href="authentication/bank-account.php?sign-out">Sign Out</a></button>
</body>
</html>