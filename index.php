<?php

require_once 'connec.php';

function validateForm (array $form): array 
{
    $indexNames = ['lastname', 'firstname'];
    $errors = [];
    $validate = [];

    foreach ($form as $key => $value) {

        if (empty($form[$key])) {
            $errors[$key] = 'The form field ' . ucfirst($key) . ' must be filled';
        }elseif (!in_array($key, $indexNames)) {
            $errors[$key] = 'well tried but ' . $key . ' it is not in the form';
        } elseif (strlen($value) >= 45) {
            $errors[$key] = 'The ' . $key . ' must be 45 characters long' ;
        } else {
            $form[$key] = htmlentities($value);   
        }

        $validate = [
            'errors' => $errors,
            'form' => $form
        ];
    }   
    return $validate;
}

function insertFriends (PDO $pdo, string $firstname, string $lastname): void
{
    $query = 'INSERT INTO friend (firstname, lastname) VALUES (:firstname, :lastname)'; 
    $statement = $pdo->prepare($query);
    $statement->bindValue(':firstname', $firstname, \PDO::PARAM_STR);
    $statement->bindValue(':lastname', $lastname, \PDO::PARAM_STR);
    $statement->execute();
}

function searchId (PDO $pdo, string $firstname, string $lastname): array
{
    $query = 'SELECT id FROM friend WHERE firstname=:firstname AND lastname=:lastname';
    $statement = $pdo->prepare($query);
    $statement->bindValue(':firstname', $firstname, \PDO::PARAM_STR);
    $statement->bindValue(':lastname', $lastname, \PDO::PARAM_STR);
    $statement->execute();
    return $statement->fetchAll();
}

$pdo = new \PDO(DSN, USER, PASS);
$firstname = '';
$lastname ='';

if (!empty($_POST)) {
    $formData = validateForm(array_map('trim', $_POST));
    $firstname = $formData['form']['firstname'];
    $lastname =  $formData['form']['lastname'];


    if (empty($formData['errors'])) {
        if (empty(searchId($pdo, $firstname, $lastname))) {
            insertFriends($pdo, $firstname, $lastname);
            header('location: /');
        } else {
            $formData['errors']['id'] = 'Sorry a person is already registered under this username';
        }
    }
}

$query = "SELECT * FROM friend";
$statement = $pdo->query($query);
$friends = $statement->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Friends list</title>
</head>
<body>
    <?php if (!empty($formData['errors'])): ?>
        <div class="message">
            <?php if (isset($formData['errors']['id'])): ?>
                <?= $formData['errors']['id'] ?>
            <?php else: ?>
                <ul>
                    <?php foreach ($formData['errors'] as $message): ?>
                        <li>
                            <?= $message ?>
                        </li>
                    <?php endforeach ?>
                </ul>
            <?php endif ?>
        </div>
    <?php endif ?>
    <h1>Add yourself</h1>
    <form action="" method="POST">
        <div class="body-form">
            <label for="firstname">Firstname</label>
            <input type="text" name="firstname" id="firstname" value="<?= $firstname ?>" >
            <label for="lastname">Lastname</label>
            <input type="text" name="lastname" id="lastname" value="<?= $lastname ?>"">
            <input type="submit" value="Envoyer">
        </div>
    </form>

    <section class="list">
        <h2>The list of friends</h2>
        <ul>
            <div class="test">
            <?php foreach($friends as $friend): ?>
            <li><?= ucfirst($friend['firstname']) . ' ' . ucfirst($friend['lastname']) ?></li>
            </div>
        <?php endforeach ?>
        </ul>
    </section>
</body>
</html>