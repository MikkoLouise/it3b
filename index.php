<?php
require_once 'config/config.php';

if(isset($_SESSION['user_id'])){
    header('Location ' . BASE_URL . '/app/' . $_SESSION['user_role'] . '/index.php');
    exit;
}

$error='';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    $error = 'Invalid login credentials';

    if($login==='' || $password===''){
        // Log incomplete login attempt
        logActivity($pdo,null,$login,'login','failed');
    } else {

        $result = loginUser($pdo,$login,$password);

        if($result===true){
        // Log complete login attempt
            logActivity(
            $pdo,$_SESSION['user_id'],
            $_SESSION['user_email'],
            'login',
            'success'
            );
            
            echo 'Location: ' . BASE_URL . '/app/' . $_SESSION['user_role'] . '/index.php';
            header('Location: ' . BASE_URL . '/app/' . $_SESSION['user_role'] . '/index.php');
            exit;

    }elseif($result=== 'active_session'){
        echo 'This account is already logged in on another device';

        $error = 'This account is already logged in on another device';
    }else{
        $error = 'Invalid Login Credentials';
    } 
    
    }

  }

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" 
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

    <style>

        body {
            font-family: Arial;
            background: #c4adad;
            margin: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        a {
            background: #f30101;
            color: white;
            padding: 8px 12px;
            text-decoration: none;
            border-radius: 5px;
        }

        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            background: white;
        }

        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #2c2828;
            color: white;
        }

        tr:hover {
            background: #f1f1f1;
        }

        
    </style>
</head>

<form method="POST">
    <label>Username or Email</label>
    <input  type="text"
            name="login"
            required>
    <br>
    <br>
    
    <label>Password</label>
    <input  type="password"
            name="password"
            required>
    <br>

    <button type="submit">Sign In</button>

</form>


<?php if(isset($_GET['timeout'])): ?>
    <p>Your Session Has Expired Due To Inactivity. Please Login Again</p>

<?php endif; ?>


</body>
</html>
