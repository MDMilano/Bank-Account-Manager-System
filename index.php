<?php
    include_once 'config/settings-configuration.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=, initial-scale=1.0">
    <title>Banknginamo</title>
    <link rel="icon" type="image/png" href="src/images/icon.png">
</head>
<body>
    <h1>Sign In</h1>
    <form action="dashboard/authentication/bank-account.php" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token?>">
        <label for="account_number">Account Number: </label>
        <input type="number" name="account_number" placeholder="Enter your account number" required><br>
        <label for="pin">Pin: </label>
        <input type="password" name="pin" placeholder="Enter your 4-digit pin" required><br>
        <input type="submit" name="btn-sign-in" value="Sign In">
    </form>

    <h1>Create New Account</h1>
    <form action="dashboard/authentication/bank-account.php" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token?>">
        <label for="account_number">Account Number: </label>
        <input type="number" name="account_number" placeholder="Enter your account number" required><br>
        <label for="owner_name">Owner Name: </label>
        <input type="text" name="owner_name" placeholder="Enter your name" required><br>
        <label for="pin">Pin: </label>
        <input type="password" name="pin" placeholder="Enter your 4-digit pin" required><br>
        <input type="submit" name="btn-sign-up" value="Sign Up">
    </form>
</body>
</html>