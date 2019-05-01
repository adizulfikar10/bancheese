<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

return function (App $app) {
    date_default_timezone_set("Asia/Jakarta");

    $container = $app->getContainer();

    $app->get('/[{name}]', function (Request $request, Response $response, array $args) use ($container) {
        // Sample log message
        $container->get('logger')->info("Slim-Skeleton '/' route");

        // Render index view
        return $container->get('renderer')->render($response, 'index.phtml', $args);
    });

    $app->group('/api/v1', function () use ($app) {

        $app->post("/login", function (Request $request, Response $response, $args){
            $user = $request->getParsedBody();
            $username = $user['username'];
            $password = sha1($user['password']);

            $sql = "SELECT username FROM tbl_user WHERE username =:username AND password=:password";
            $stmt = $this->db->prepare($sql);

            $data = [
                ":username" => $username,
                ":password" => $password
            ];


            if($stmt->execute($data)){
                if ($stmt->rowCount() > 0) {
                    $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>null);
                }else{
                    $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'FAILED','CODE'=>500,'DATA'=>null);
                }
            }
            $newResponse = $response->withJson($result);
            return $newResponse;

        });

        //BAGIAN USER

        $app->get("/users", function (Request $request, Response $response){
            $sql = "SELECT * FROM tbl_user";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetchAll();

            if ($stmt->rowCount() > 0) {
                $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>$data);
            }else{
                $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'FAILED','CODE'=>500,'DATA'=>null);
            }
            
            $newResponse = $response->withJson($result);
            return $newResponse;
            // return $response->withJson(["status" => "success", "data" => $result], 200);
        });

        $app->get("/users/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $sql = "SELECT * FROM tbl_user WHERE id_user=:id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([":id" => $id]);
            $data = $stmt->fetch();
            if ($stmt->rowCount() > 0) {
                $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>$data);
            }else{
                $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'FAILED','CODE'=>500,'DATA'=>null);
            }
            $newResponse = $response->withJson($result);
            return $newResponse;
            // return $response->withJson(["status" => "success", "data" => $result], 200);
        });

        $app->post("/users", function (Request $request, Response $response){

            $new_users = $request->getParsedBody();
            
            $sql = "INSERT INTO tbl_user (id_cabang, username, password, nama_user, no_hp, alamat, role) VALUES (:id_cabang, :username, :password,:nama_user,:no_hp,:alamat,:role)";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id_cabang" => $new_users["id_cabang"],
                ":username" => $new_users["username"],
                ":password" => $new_users["password"],
                ":nama_user" => $new_users["nama_user"],
                ":no_hp" => $new_users["no_hp"],
                ":alamat" => $new_users["alamat"],
                ":role" => $new_users["role"]
            ];
            
            if($stmt->execute($data)){
                if ($stmt->rowCount() > 0) {
                    $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>$data);
                }else{
                    $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'FAILED','CODE'=>500,'DATA'=>null);
                }
            }

            $newResponse = $response->withJson($result);
            return $newResponse;
        });
        
        $app->post("/users/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $new_users = $request->getParsedBody();
            $sql = "UPDATE tbl_user SET id_cabang=:id_cabang, username=:username, password=:password, nama_user=:nama_user, no_hp=:no_hp, alamat=:alamat, role=:role,dtm_upd=:dtm_upd WHERE id_user=:id";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id" => $id,
                ":id_cabang" => $new_users["id_cabang"],
                ":username" => $new_users["username"],
                ":password" => $new_users["password"],
                ":nama_user" => $new_users["nama_user"],
                ":no_hp" => $new_users["no_hp"],
                ":alamat" => $new_users["alamat"],
                ":role" => $new_users["role"],
                ":dtm_upd" => date("Y-m-d H:i:s")
            ];

            
            if($stmt->execute($data)){
                if ($stmt->rowCount() > 0) {
                    $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>$data);
                }else{
                    $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'FAILED','CODE'=>500,'DATA'=>null);
                }
            }
            $newResponse = $response->withJson($result);
            return $newResponse;
        });

        $app->delete("/users/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $sql = "DELETE FROM tbl_user WHERE id_user=:id";
            $stmt = $this->db->prepare($sql);

            $data = [
                ":id" => $id
            ];

            if($stmt->execute($data)){
                if ($stmt->rowCount() > 0) {
                    $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>$data);
                }else{
                    $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'FAILED','CODE'=>500,'DATA'=>null);
                }
            }
            $newResponse = $response->withJson($result);
            return $newResponse;
        });

        // BAGIAN MASTER MENU
        $app->get("/mastermenu", function (Request $request, Response $response){
            $sql = "SELECT * FROM tbl_master_menu";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetchAll();
            if ($stmt->rowCount() > 0) {
                $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>$data);
            }else{
                $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'FAILED','CODE'=>500,'DATA'=>null);
            }
            
            $newResponse = $response->withJson($result);
            return $newResponse;
        });

        $app->get("/mastermenu/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $sql = "SELECT * FROM tbl_master_menu WHERE id_menu=:id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([":id" => $id]);
            $data = $stmt->fetch();
            if ($stmt->rowCount() > 0) {
                $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>$data);
            }else{
                $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'FAILED','CODE'=>500,'DATA'=>null);
            }
            
            $newResponse = $response->withJson($result);
            return $newResponse;
        });

        $app->post("/mastermenu", function (Request $request, Response $response){

            $new_mastermenu = $request->getParsedBody();
            
            $sql = "INSERT INTO tbl_master_menu (nama_menu, harga, status) VALUES (:nama_menu, :harga, :status)";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":nama_menu" => $new_mastermenu["nama_menu"],
                ":harga" => $new_mastermenu["harga"],
                ":status" => $new_mastermenu["status"]
            ];
            
            if($stmt->execute($data)){
                if ($stmt->rowCount() > 0) {
                    $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>$data);
                }else{
                    $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'FAILED','CODE'=>500,'DATA'=>null);
                }
            }

            $newResponse = $response->withJson($result);
            return $newResponse;
        });

        $app->post("/mastermenu/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $new_mastermenu = $request->getParsedBody();
            $sql = "UPDATE tbl_master_menu SET nama_menu=:nama_menu, harga=:harga, status=:status, dtm_upd=:dtm_upd WHERE id_menu=:id";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id" => $id,
                ":nama_menu" => $new_mastermenu["nama_menu"],
                ":harga" => $new_mastermenu["harga"],
                ":status" => $new_mastermenu["status"],
                ":dtm_upd" => date("Y-m-d H:i:s")
            ];
            
            if($stmt->execute($data)){
                if ($stmt->rowCount() > 0) {
                    $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>$data);
                }else{
                    $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'FAILED','CODE'=>500,'DATA'=>null);
                }
            }

            $newResponse = $response->withJson($result);
            return $newResponse;
        });

        $app->delete("/mastermenu/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $sql = "DELETE FROM tbl_master_menu WHERE id_menu=:id";
            $stmt = $this->db->prepare($sql);

            $data = [
                ":id" => $id
            ];

            if($stmt->execute($data)){
                if ($stmt->rowCount() > 0) {
                    $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>$data);
                }else{
                    $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'FAILED','CODE'=>500,'DATA'=>null);
                }
            }
            $newResponse = $response->withJson($result);
            return $newResponse;
        });

        // BAGIAN MENU DETAIL
        $app->get("/menudetail", function (Request $request, Response $response){
            $sql = "SELECT * FROM tbl_menu_detail";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetchAll();
            if ($stmt->rowCount() > 0) {
                $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>$data);
            }else{
                $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'FAILED','CODE'=>500,'DATA'=>null);
            }
            
            $newResponse = $response->withJson($result);
            return $newResponse;
        });

        $app->get("/menudetail/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $sql = "SELECT * FROM tbl_menu_detail WHERE id_menu_detail=:id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([":id" => $id]);
            $data = $stmt->fetch();
            if ($stmt->rowCount() > 0) {
                $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>$data);
            }else{
                $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'FAILED','CODE'=>500,'DATA'=>null);
            }
            
            $newResponse = $response->withJson($result);
            return $newResponse;
        });

        $app->post("/menudetail", function (Request $request, Response $response){

            $new_mastermenu = $request->getParsedBody();
            
            $sql = "INSERT INTO tbl_menu_detail (id_menu, id_cabang, harga, nama_menu, status) VALUES (:id_menu, :id_cabang, :harga, :nama_menu, :status)";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id_menu" => $new_mastermenu["id_menu"],
                ":id_cabang" => $new_mastermenu["id_cabang"],
                ":harga" => $new_mastermenu["harga"],
                ":nama_menu" => $new_mastermenu["nama_menu"],
                ":status" => $new_mastermenu["status"]
            ];
            
            if($stmt->execute($data)){
                if ($stmt->rowCount() > 0) {
                    $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>$data);
                }else{
                    $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'FAILED','CODE'=>500,'DATA'=>null);
                }
            }

            $newResponse = $response->withJson($result);
            return $newResponse;
        });

        $app->post("/menudetail/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $new_mastermenu = $request->getParsedBody();
            $sql = "UPDATE tbl_menu_detail SET id_menu=:id_menu, id_cabang=:id_cabang, harga=:harga, nama_menu=:nama_menu, status=:status, dtm_upd=:dtm_upd WHERE id_menu_detail=:id";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id" => $id,
                ":id_menu" => $new_mastermenu["id_menu"],
                ":id_cabang" => $new_mastermenu["id_cabang"],
                ":harga" => $new_mastermenu["harga"],
                ":nama_menu" => $new_mastermenu["nama_menu"],
                ":status" => $new_mastermenu["status"],
                ":dtm_upd" => date("Y-m-d H:i:s")
            ];
            
            if($stmt->execute($data)){
                if ($stmt->rowCount() > 0) {
                    $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>$data);
                }else{
                    $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'FAILED','CODE'=>500,'DATA'=>null);
                }
            }

            $newResponse = $response->withJson($result);
            return $newResponse;
        });

        $app->delete("/menudetail/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $sql = "DELETE FROM tbl_menu_detail WHERE id_menu_detail=:id";
            $stmt = $this->db->prepare($sql);

            $data = [
                ":id" => $id
            ];

            if($stmt->execute($data)){
                if ($stmt->rowCount() > 0) {
                    $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>$data);
                }else{
                    $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'FAILED','CODE'=>500,'DATA'=>null);
                }
            }
            $newResponse = $response->withJson($result);
            return $newResponse;
        });
    });


};
