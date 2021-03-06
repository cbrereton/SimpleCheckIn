<?php
/**
 * Step 1: Require the Slim PHP 5 Framework
 *
 * If using the default file layout, the `Slim/` directory
 * will already be on your include path. If you move the `Slim/`
 * directory elsewhere, ensure that it is added to your include path
 * or update this file path as needed.
 */
require 'Slim/Slim.php';

/**
 * Step 2: Instantiate the Slim application
 *
 * Here we instantiate the Slim application with its default settings.
 * However, we could also pass a key-value array of settings.
 * Refer to the online documentation for available settings.
 */
$app = new Slim();
$app->add(new Slim_Middleware_ContentTypes());

/**
 * Step 3: Define the Slim application routes
 *
 * Here we define several Slim application routes that respond
 * to appropriate HTTP request methods. In this example, the second
 * argument for `Slim::get`, `Slim::post`, `Slim::put`, and `Slim::delete`
 * is an anonymous function. If you are using PHP < 5.3, the
 * second argument should be any variable that returns `true` for
 * `is_callable()`. An example GET route for PHP < 5.3 is:
 *
 * $app = new Slim();
 * $app->get('/hello/:name', 'myFunction');
 * function myFunction($name) { echo "Hello, $name"; }
 *
 * The routes below work with PHP >= 5.3.
 */

//GET all the children when page loads
$app->get('/children', function () {
    //Get all children from DB
    $sql = "SELECT * FROM child";
    try {
        $db = getConnection();
        $stmt = $db->query($sql);  
        $children = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        //echo json_encode($people);
        echo json_encode($children);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}'; 
    }
});


//GET all the guardians when page loads
$app->get('/guardian', function () {
    //Get all children from DB
    $sql = "SELECT * FROM guardian";
    try {
        $db = getConnection();
        $stmt = $db->query($sql);  
        $guardians = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        //echo json_encode($people);
        echo json_encode($guardians);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}'; 
    }
});

//GET attendance when page loads
$app->get('/attendance', function () {
    //Set current date and time
    $today = date("F j, Y");

    //Get all children from DB
    $sql = "SELECT * FROM attendance WHERE date_in='$today' AND date_out=''";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);  
        $stmt->bindParam("today", $today);
        $stmt->execute();
//        $stmt = $db->query($sql);  
        $attendance = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        //echo json_encode($people);
        echo json_encode($attendance);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}'; 
    }
});

//Check in a Child
$app->post('/checkin', function() use ($app) {
    //Set current date and time
    $today = date("F j, Y");
    $time = date("g:i a");
        
    //Get the form data
    $body = json_decode($app->request()->getBody());
    
    //Set child and guardian
    $child = $body->child;
    $child_id = $body->child_id;
    $guardian = $body->guardian;

    //Set up the sql statements and injection
    
    $sql = "INSERT INTO attendance (child, child_id, guardian_in, date_in, time_in) 
            VALUES (:child, :child_id, :guardian_in, :date_in, :time_in)";
        try {
            $db = getConnection();
            $stmt = $db->prepare($sql);  
            $stmt->bindParam("child", $child);
            $stmt->bindParam("child_id", $child_id);
            $stmt->bindParam("guardian_in", $guardian);
            $stmt->bindParam("date_in", $today);
            $stmt->bindParam("time_in", $time);
            $stmt->execute();
            
        } catch(PDOException $e) {
            error_log($e->getMessage(), 3, '/var/tmp/php.log');
            echo '{"error":{"text":'. $e->getMessage() .'}}'; 
        }    
    
    echo $child;
});

//Checkout a Child
$app->put('/checkout/', function() use ($app){
    
    $body = json_decode($app->request()->getBody());

    $child = $body->child;
    $child_id = $body->child_id;
    $guardian = $body->guardian;
    $today = date("F j, Y");
    $time = date("g:i a");
    
    $sql = "UPDATE attendance 
            SET guardian_out='" . $guardian . "', time_out='" . $time . "', date_out='".$today."'
            WHERE child='".$child."'
            AND child_id='".$child_id."'
            AND date_in='".$today."'";
        try {
            $db = getConnection();
            $stmt = $db->prepare($sql);  
            $stmt->execute();
            
        } catch(PDOException $e) {
            error_log($e->getMessage(), 3, '/var/tmp/php.log');
            echo '{"error":{"text":'. $e->getMessage() .'}}'; 
        }    
    
    echo $child;

});


//Register a Child
$app->post('/children', function() use ($app) {
    $body = json_decode($app->request()->getBody());
    
    $first_name = $body->first_name;
    $last_name = $body->last_name;
    $active = $body->active;
    $category = $body->category;
    $birthday = $body->birthday;
    $allergies = $body->allergies;
    $notes = $body->notes;
    $photo = $body->photo;
    $guardian1 = $body->guardian1;
    $guardian2 = $body->guardian2;
    $guardian3 = $body->guardian3;
    $guardian4 = $body->guardian4;

    $sql = "INSERT INTO child (first_name, last_name, active, category, birthday, notes, allergies, guardian1, guardian2, guardian3, guardian4)
            VALUES (:first_name, :last_name, :active, :category, :birthday, :notes, :allergies, :guardian1, :guardian2, :guardian3, :guardian4)";
        try {
            $db = getConnection();
            $stmt = $db->prepare($sql);  
            $stmt->bindParam("first_name", $first_name);
            $stmt->bindParam("last_name", $last_name);
            $stmt->bindParam("active", $active);
            $stmt->bindParam("category", $category);
            $stmt->bindParam("birthday", $birthday);
            $stmt->bindParam("allergies", $allergies);
            $stmt->bindParam("notes", $notes);
//            $stmt->bindParam("photo", $photo);
            $stmt->bindParam("guardian1", $guardian1);
            $stmt->bindParam("guardian2", $guardian2);
            $stmt->bindParam("guardian3", $guardian3);
            $stmt->bindParam("guardian4", $guardian4);
        
            $stmt->execute();
            
        } catch(PDOException $e) {
            error_log($e->getMessage(), 3, '/var/tmp/php.log');
            echo '{"error":{"text":'. $e->getMessage() .'}}'; 
        }    
        echo $first_name . ' ' . $last_name;
});


//Register a Guardian
$app->post('/guardian', function() use ($app) {
    $body = json_decode($app->request()->getBody());
    
    $first_name = $body->first_name;
    $last_name = $body->last_name;
    $active = $body->active;
    $phone = $body->phone;
    $sms = $body->sms;
    $email = $body->email;
    $carrier = $body->carrier;
    $child1 = $body->child1;
    $child2 = $body->child2;
    $child3 = $body->child3;
    $child4 = $body->child4;

    $sql = "INSERT INTO guardian (first_name, last_name, active, phone, sms, email, carrier, child1, child2, child3, child4)
            VALUES (:first_name, :last_name, :active, :phone, :sms, :email, :carrier, :child1, :child2, :child3, :child4)";
        try {
            $db = getConnection();
            $stmt = $db->prepare($sql);  
            $stmt->bindParam("first_name", $first_name);
            $stmt->bindParam("last_name", $last_name);
            $stmt->bindParam("active", $active);
            $stmt->bindParam("phone", $phone);
            $stmt->bindParam("sms", $sms);
            $stmt->bindParam("email", $email);
            $stmt->bindParam("carrier", $carrier);
            $stmt->bindParam("child1", $child1);
            $stmt->bindParam("child2", $child2);
            $stmt->bindParam("child3", $child3);
            $stmt->bindParam("child4", $child4);
        
            $stmt->execute();
            
        } catch(PDOException $e) {
            error_log($e->getMessage(), 3, '/var/tmp/php.log');
            echo '{"error":{"text":'. $e->getMessage() .'}}'; 
        }    
        echo $first_name . ' ' . $last_name;
});





//NEED TO ADD THE FOLLOWING
//-------------------------
// Update child info and status
// Register a Guardian
// Update a Guardian
// Get Attendance record


//PUT route
$app->put('/put', function () {
    echo 'This is a PUT route';
});

//DELETE route
$app->delete('/delete', function () {
    echo 'This is a DELETE route';
});

/**
 * Step 4: Run the Slim application
 *
 * This method should be called last. This is responsible for executing
 * the Slim application using the settings and routes defined above.
 */
$app->run();


function getConnection() {
    $dbhost="localhost";
    $dbuser="root";
    $dbpass="";
    $dbname="checkin";
    $dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);  
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbh;
}