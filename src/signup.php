<?php
require_once "db.php";
require_once "utils.php";

$db = new Db('enotio');

$username = $pwd = $confirm_pwd = "";
$username_err = $pwd_err = $confirm_pwd_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    switch (validateUsername($_POST["username"])) {
        case UserNameError::Empty:
            $username_err = "Please enter a username.";
            break;
        case UserNameError::Invalid:
            $username_err = "Username can only contain letters, numbers, and underscores.";
            break;
        case UserNameError::Taken:
            $username_err = "This username is already taken.";
            break;
        case UserNameError::Ok:
            $username = trim($_POST["username"]);
            break;
    }

    if (empty(trim($_POST["password"]))) {
        $pwd_err = "Please enter a password.";     
    } else if (strlen(trim($_POST["password"])) < 6) {
        $pwd_err = "Password must have atleast 6 characters.";
    } else {
        $pwd = trim($_POST["password"]);
    }

    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_pwd_err = "Please confirm password.";     
    } else {
        $confirm_pwd = trim($_POST["confirm_password"]);
        if (empty($pwd_err) && ($pwd != $confirm_pwd)) {
            $confirm_pwd_err = "Password did not match.";
        }
    }

    if (empty($username_err) && empty($pwd_err) && empty($confirm_pwd_err)) {
        $db->createUser($username, $pwd);
        header("location: signin.php");
    }
}

$db->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body{ font: 14px sans-serif; }
        .wrapper{ width: 360px; padding: 20px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Sign Up</h2>
        <p>Please fill this form to create an account.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control <?php echo (!empty($pwd_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $pwd; ?>">
                <span class="invalid-feedback"><?php echo $pwd_err; ?></span>
            </div>
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_pwd_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $confirm_pwd; ?>">
                <span class="invalid-feedback"><?php echo $confirm_pwd_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Submit">
                <input type="reset" class="btn btn-secondary ml-2" value="Reset">
            </div>
            <p>Already have an account? <a href="signin.php">Login here</a>.</p>
        </form>
    </div>    
</body>
</html>