<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use Cake\Utility\Security;
use Firebase\JWT\JWT;

/**
 * Api Controller
 *
 */
class ApiController extends AppController
{
    public function loginUser()
    {

        // Login User form data with jwt token value
        $result = $this->Authentication->getResult();
        // regardless of POST or GET, redirect if user is logged in
        if ($result && $result->isValid()) {

            $key = Security::getSalt();
            // Token = xxx.yyy.zzz
            $payload = [
                "sub" => $result->getData()->id, // Subject of the token
                "exp" => time() + 604800, // Expiration time
                "iat" => time(), // Time when JWT was issued.
            ];
            // Set the JWT token in the response
            $token = JWt::encode($payload, $key, "HS256");

            // $this->Auth->setUser($user);
            $status = true;
            $message = "Login successfully";
        }
        // display error if user submitted and authentication failed
        if ($this->request->is('post') && !$result->isValid()) {
            $status = false;
            $message = "Invalid username or password";
            $token = null;
        }


        $this->set([
            "status" => $status,
            "message" => $message,
            "token" => $token
        ]);
        $this->viewBuilder()->setOption("serialize", ["status", "message", "token"]);

    }

    public function registerUser()
    {
        $this->request->allowMethod(["post"]);
        // Register User form data
        $formData = $this->request->getData();
        // email address check rules
        $emailCheck = $this->Users->find()->where([
            "email" => $formData["email"]
        ])->first();
        if(!empty($emailCheck)){
            // Already exists
            $status = false;
            $message = "User already exists";
        }else{
            // Insert user data
            $userObject = $this->Users->newEmptyEntity();
            $userObject = $this->Users->patchEntity($userObject, $formData);
            if ($this->Users->save($userObject)) {
                // Success response
                $status = true;
                $message = "User added successfully";
            } else {
                // Failed response
                $status = false;
                $message = "Failed to create user";
            }
        }

        $this->set([
            "status" => $status,
            "message" => $message
        ]);
        $this->viewBuilder()->setOption("serialize", ["status","message"]);

    }

    public function updateUser($id =null)
    {
        // Edit User form data
        $this->request->allowMethod(["put","post"]);
        $user_id = $this->request->getParam("user_id");

        $userInfo = $this->request->getData();
        //check user address
        $userData = $this->Users->get($user_id);
        if (!empty($userData)){
            // user exist
            $userObject = $this->Users->patchEntity($userData, $userInfo);
            if ($this->Users->save($userObject)) {
                // Success response
                $status = true;
                $message = "User updated successfully";
            } else {
                // Failed response
                $status = false;
                $message = "Failed to update user";
            }
        }else{
            // user not exist
            $status = false;
            $message = "User not found";
        }
        $this->set([
            "status" => $status,
            "message" => $message
        ]);
        $this->viewBuilder()->setOption("serialize", ["status","message"]);

    }

    public function listUsers()
    {
        // List Users
        $this->request->allowMethod(["get"]);
        $users = $this->Users->find()->toList();
        $this->set([
            "status" => true,
            "message" => "User list",
            "data" => $users
        ]);
        $this->viewBuilder()->setOption("serialize", ["status", "message", "data"]);
    }

    public function deleteUser()
    {
        $this->request->allowMethod(["delete"]);
        // List Users
        $user_id = $this->request->getParam("user_id");
        $userData = $this->Users->get($user_id);
        if (!empty($userData)) {
            // user exist
            if ($this->Users->delete($userData)) {
                // Success response
                $status = true;
                $message = "User deleted successfully";
            } else {
                // Failed response
                $status = false;
                $message = "Failed to delete user";
            }
        } else {
            // user not exist
            $status = false;
            $message = "User not found";
        }
        $this->set([
            "status" => $status,
            "message" => $message
        ]);
        $this->viewBuilder()->setOption("serialize", ["status", "message"]);
    }
}
