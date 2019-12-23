


<?php




// CREATE TABLE users (
//     id int NOT NULL AUTO_INCREMENT,
//     username varchar(200) NOT NULL,
//     email varchar(200) NOT NULL,
//     password text NOT NULL,
//     admin int NOT NULL,
//     CONSTRAINT users_pk PRIMARY KEY (id)
// );


include "config.php";
global $response;
$response = array();
function verifyToken($token){
	global $response;
	global $conn;
	$token = explode("~", $token);
	$user = $token[0];
	$pass = $token[1];
	if($token[0] && $token[1]){
		$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ? AND password = ? AND admin = 1");
		$stmt->bind_param("sss", $user,$user,$pass);
		$stmt->execute();
		$stmt->store_result();
						
		//if the user already exist in the database 
		if($stmt->num_rows > 0){
			return true;
			
		}else{
			
			$response['error'] = true;
			$response['message'] = 'Invalid Admin Token';
			$stmt->close();
			return false;
	
		}
	}else{
		
		$response['error'] = true;
		$response['message'] = 'Invalid Admin Token';
		$stmt->close();
		return false;

	}

}


	
	//if it is an api call 
	//that means a get parameter named api call is set in the URL 
	//and with this parameter we are concluding that it is an api call 
	if(isset($_GET['task'])){
		
		switch($_GET['task']){



			case 'adduser':

					//getting the values 
					$token = $_POST['token'];
					$username = $_POST['username']; 
					$email = $_POST['email']; 
					$password = md5($_POST['password']);
					$admin = $_POST['admin']; 

					if(verifyToken($token)){
						//checking if the user is already exist with this username or email
						//as the email and username should be unique for every user 
						$stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
						$stmt->bind_param("ss", $username, $email);
						$stmt->execute();
						$stmt->store_result();
						
						//if the user already exist in the database 
						if($stmt->num_rows > 0){
							$response['error'] = true;
							$response['message'] = 'User already registered';
							$stmt->close();
						}else{
							
							//if user is new creating an insert query 
							$stmt = $conn->prepare("INSERT INTO users (username, email, password, admin) VALUES (?, ?, ?, ?)");
							$stmt->bind_param("sssi", $username, $email, $password, $admin);
							
							//if the user is successfully added to the database 
							if($stmt->execute()){

								try{
									$q ="CREATE TABLE ".$username." (ATT INT, Date DATETIME,Branch VARCHAR(100))";
									$conn->query($q);

								}catch(exception $e){

								}
								
								//fetching the user back 
								$stmt = $conn->prepare("SELECT id, id, username, email, admin FROM users WHERE username = ?"); 
								$stmt->bind_param("s",$username);
								$stmt->execute();
								$stmt->bind_result($userid, $id, $username, $email, $admin);
								$stmt->fetch();
								
								$user = array(
									'id'=>$id, 
									'username'=>$username, 
									'email'=>$email,
									'admin'=>$admin
								);
								
								$stmt->close();
								
								//adding the user data in response 
								$response['error'] = false; 
								$response['message'] = 'User registered successfully'; 
								$response['user'] = $user; 
							}
						}
				}


			break;	
			
			case 'login':
				
					//getting values 
					$username = $_POST['username'];
					$password = md5($_POST['password']); 
					
					//creating the query 
					$stmt = $conn->prepare("SELECT id, username, email, admin FROM users WHERE username = ? or email =? AND password = ?");
					$stmt->bind_param("sss",$username,$username, $password);
					
					$stmt->execute();
					
					$stmt->store_result();
					
					//if the user exist with given credentials 
					if($stmt->num_rows > 0){
						
						$stmt->bind_result($id, $username, $email, $admin);
						$stmt->fetch();
						
						$user = array(
							'id'=>$id, 
							'username'=>$username, 
							'email'=>$email,
							'admin'=>$admin
						);
						
						$response['error'] = false; 
						$response['message'] = 'Login successfull'; 
						$response['user'] = $user; 
					}else{
						//if the user not found 
						$response['error'] = false; 
						$response['message'] = 'Invalid username or password';
					}
			
			break; 

			case 'listuser':
				$token = $_POST['token'];
				if(verifyToken($token)){
					
					$sql = "SELECT id, username, email,admin FROM users";
					$result = $conn->query($sql);
					$users = array();

					if ($result->num_rows > 0) {
						$count =0;
    					while($row = $result->fetch_assoc()) {
							$user[$count] = array(
								'id'=>$row['id'], 
								'username'=>$row['username'], 
								'email'=>$row['email'],
								'admin'=>$row['admin']
							);
							$count++;
						}
						$response['error'] = false; 
						$response['message'] = 'User fetched'; 
						$response['user'] = $user; 
					} else {
						$response['error'] = false; 
						$response['message'] = 'No users found.';
					}
				$conn->close();

				}
	
			
			
			
			break;

			case 'deleteuser':
				$username =$_POST['username'];
				$token =$_POST['token'];
				if(verifyToken($token)){

					$stmt = $conn->prepare("DELETE FROM users WHERE username = ?");
					$stmt->bind_param("s", $username);

					if($stmt->execute()){
						try{
							$qu = "DROP TABLE IF EXISTS ".$username;
							$conn->query($qu);
						
						}catch(exception $e){

						}
						$response['error'] = false; 
						$response['message'] = 'User deleted successfully.';
					}else{
						$response['error'] = true; 
						$response['message'] = 'Unable to delete user.';

					}
				}
				
			break;
			
			default: 
				$response['error'] = true; 
				$response['message'] = 'Invalid Operation Called';
		}
		
	}else{
		//if it is not api call 
		//pushing appropriate values to response array 
		$response['error'] = true; 
		$response['message'] = 'Invalid API Call';
	}

	echo json_encode($response);
	
?>