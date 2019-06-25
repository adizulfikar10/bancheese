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

            $sql = "SELECT username,role,nama_user,id_cabang,id_user FROM tbl_user WHERE username =:username AND password=:password";
            $stmt = $this->db->prepare($sql);

            $data = [
                ":username" => $username,
                ":password" => $password
            ];

            if($stmt->execute($data)){
                if ($stmt->rowCount() > 0) {
                    $data = $stmt->fetch();
                    $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>$data);
                }else{
                    $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'Username atau tidak ditemukan','CODE'=>400,'DATA'=>null);
                }
            }else{
                $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'Error executing query','CODE'=>500,'DATA'=>null);

            }
            $newResponse = $response->withJson($result);
            return $newResponse;

        });

        //BAGIAN USER

        $app->post("/changePassword", function (Request $request, Response $response){
            $user = $request->getParsedBody();
            $id_user = $user['id_user'];
            $password = sha1($user['password']);
            $password_baru = ($user['password_baru']);
            $ulang_password = ($user['ulang_password']);
            if($password_baru != $ulang_password){
                $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'Password baru tidak sesuai','CODE'=>500,'DATA'=>null);
                return $response->withJson($result);
            }
            $sql = "SELECT username FROM tbl_user WHERE id_user =:id_user AND password=:password";
            $stmt = $this->db->prepare($sql);

            $data = [
                ":id_user" => $id_user,
                ":password" => $password
            ];

            if($stmt->execute($data)){
                if ($stmt->rowCount() > 0) {
                    $sqlPass = "UPDATE tbl_user SET password=:password WHERE id_user=:id_user";
                    $stmtPass = $this->db->prepare($sqlPass);
                    
                    $dataPass = [
                        ":id_user" => $id_user,
                        ":password" => sha1($password_baru)
                    ];
                    
                    if($stmtPass->execute($dataPass)){
                        if ($stmtPass->rowCount() > 0) {
                            $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>null);
                        }else{
                            $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'Tidak ada data yang berubah','CODE'=>500,'DATA'=>null);
                        }
                    }
                }else{
                    $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'Password lama tidak sesuai','CODE'=>400,'DATA'=>null);
                }
            }else{
                $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'Error executing query','CODE'=>500,'DATA'=>null);

            }

            $newResponse = $response->withJson($result);
            return $newResponse;


        });

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
                ":password" => sha1($new_users["password"]),
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
            if($new_users["password"]!=''){
                $data = [
                    ":id" => $id,
                    ":id_cabang" => $new_users["id_cabang"],
                    ":username" => $new_users["username"],
                    ":password" => sha1($new_users["password"]),
                    ":nama_user" => $new_users["nama_user"],
                    ":no_hp" => $new_users["no_hp"],
                    ":alamat" => $new_users["alamat"],
                    ":role" => $new_users["role"],
                    ":dtm_upd" => date("Y-m-d H:i:s")
                ];
                $sql = "UPDATE tbl_user SET id_cabang=:id_cabang, username=:username, password=:password, nama_user=:nama_user, no_hp=:no_hp, alamat=:alamat, role=:role,dtm_upd=:dtm_upd WHERE id_user=:id";
            }else{
                $data = [
                    ":id" => $id,
                    ":id_cabang" => $new_users["id_cabang"],
                    ":username" => $new_users["username"],
                    ":nama_user" => $new_users["nama_user"],
                    ":no_hp" => $new_users["no_hp"],
                    ":alamat" => $new_users["alamat"],
                    ":role" => $new_users["role"],
                    ":dtm_upd" => date("Y-m-d H:i:s")
                ];
                $sql = "UPDATE tbl_user SET id_cabang=:id_cabang, username=:username, nama_user=:nama_user, no_hp=:no_hp, alamat=:alamat, role=:role,dtm_upd=:dtm_upd WHERE id_user=:id";
            }

            $stmt = $this->db->prepare($sql);
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

        //END USER

        //BAGIAN CABANG

        $app->get("/cabang", function (Request $request, Response $response){
            $sql = "SELECT * FROM tbl_cabang";
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

        $app->get("/cabang/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $sql = "SELECT * FROM tbl_cabang WHERE id_cabang=:id";
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

        $app->post("/cabang", function (Request $request, Response $response){

            $new_cabang = $request->getParsedBody();
            
            $sql = "INSERT INTO tbl_cabang (nama_cabang, alamat, no_hp, nama_pemilik, jam_buka, jam_tutup, printer) VALUES (:nama_cabang, :alamat, :no_hp, :nama_pemilik, :jam_buka, :jam_tutup, :printer)";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":nama_cabang" => $new_cabang["nama_cabang"],
                ":alamat" => $new_cabang["alamat"],
                ":no_hp" => $new_cabang["no_hp"],
                ":nama_pemilik" => $new_cabang["nama_pemilik"],
                ":jam_buka" => $new_cabang["jam_buka"],
                ":jam_tutup" => $new_cabang["jam_tutup"],
                ":printer" => $new_cabang["printer"]
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

        $app->post("/cabang/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $new_cabang = $request->getParsedBody();
            $sql = "UPDATE tbl_cabang SET nama_cabang=:nama_cabang, alamat=:alamat, no_hp=:no_hp, 
            nama_pemilik=:nama_pemilik, jam_buka=:jam_buka, jam_tutup=:jam_tutup, printer=:printer,
            dtm_upd=:dtm_upd
             WHERE id_cabang=:id";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id" => $id,
                ":nama_cabang" => $new_cabang["nama_cabang"],
                ":alamat" => $new_cabang["alamat"],
                ":no_hp" => $new_cabang["no_hp"],
                ":nama_pemilik" => $new_cabang["nama_pemilik"],
                ":jam_buka" => $new_cabang["jam_buka"],
                ":jam_tutup" => $new_cabang["jam_tutup"],
                ":printer" => $new_cabang["printer"],
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

        $app->delete("/cabang/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $sql = "DELETE FROM tbl_cabang WHERE id_cabang=:id";
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

        //END CABANG

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
        //END MENU

        // BAGIAN MENU DETAIL
        $app->get("/menudetail", function (Request $request, Response $response){
            $sql = "SELECT * FROM v_menu";
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
            $sql = "SELECT * FROM v_menu WHERE id_menu_detail=:id";
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

            $new_menudetail = $request->getParsedBody();
            
            $sql = "INSERT INTO tbl_menu_detail (id_menu, id_cabang, harga, nama_menu, status) VALUES (:id_menu, :id_cabang, :harga, :nama_menu, :status)";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id_menu" => $new_menudetail["id_menu"],
                ":id_cabang" => $new_menudetail["id_cabang"],
                ":harga" => $new_menudetail["harga"],
                ":nama_menu" => $new_menudetail["nama_menu"],
                ":status" => $new_menudetail["status"]
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
            $new_menudetail = $request->getParsedBody();
            $sql = "UPDATE tbl_menu_detail SET id_menu=:id_menu, id_cabang=:id_cabang, harga=:harga, nama_menu=:nama_menu, status=:status, dtm_upd=:dtm_upd WHERE id_menu_detail=:id";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id" => $id,
                ":id_menu" => $new_menudetail["id_menu"],
                ":id_cabang" => $new_menudetail["id_cabang"],
                ":harga" => $new_menudetail["harga"],
                ":nama_menu" => $new_menudetail["nama_menu"],
                ":status" => $new_menudetail["status"],
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
            echo $id;
            $sql = "DELETE FROM tbl_menu_detail WHERE id_menu_detail=:id";
            $stmt = $this->db->prepare($sql);
            echo $sql;

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

        // END MENU DETAIL

        // BAGIAN KATEGRI
        $app->get("/kategori", function (Request $request, Response $response){
            $sql = "SELECT * FROM tbl_kategori";
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

        $app->get("/kategori/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $sql = "SELECT * FROM tbl_kategori WHERE id_kategori=:id";
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

        $app->post("/kategori", function (Request $request, Response $response){

            $new_kategori = $request->getParsedBody();
            
            $sql = "INSERT INTO tbl_kategori (nama_kategori) VALUES (:nama_kategori)";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":nama_kategori" => $new_kategori["nama_kategori"],
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

        $app->post("/kategori/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $new_kategori = $request->getParsedBody();
            $sql = "UPDATE tbl_kategori SET nama_kategori=:nama_kategori, dtm_upd=:dtm_upd WHERE id_kategori=:id";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id" => $id,
                ":nama_kategori" => $new_kategori["nama_kategori"],
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

        $app->delete("/kategori/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $sql = "DELETE FROM tbl_kategori WHERE id_kategori=:id";
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
        // END KATEGORI

        //BAGIAN BAHAN_BAKU
        $app->get("/bahanbaku", function (Request $request, Response $response){
            $sql = "SELECT * FROM tbl_bahan_baku";
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

        $app->get("/bahanbaku/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $sql = "SELECT * FROM tbl_bahan_baku WHERE id_bahan=:id";
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

        $app->post("/bahanbaku", function (Request $request, Response $response){

            $new_bahan = $request->getParsedBody();
            
            $sql = "INSERT INTO tbl_bahan_baku (id_kategori,nama_bahan,satuan) VALUES (:id_kategori,:nama_bahan,:satuan)";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id_kategori" => $new_bahan["id_kategori"],
                ":nama_bahan" => $new_bahan["nama_bahan"],
                ":satuan" => $new_bahan["satuan"],
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

        $app->post("/bahanbaku/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $new_menudetail = $request->getParsedBody();
            $sql = "UPDATE tbl_bahan_baku SET id_kategori=:id_kategori,nama_bahan=:nama_bahan,satuan=:satuan, dtm_upd=:dtm_upd WHERE id_bahan=:id";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id" => $id,
                ":id_kategori" => $new_bahan["id_kategori"],
                ":nama_bahan" => $new_bahan["nama_bahan"],
                ":satuan" => $new_bahan["satuan"],
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

        $app->delete("/bahanbaku/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $sql = "DELETE FROM tbl_bahan_baku WHERE id_bahan=:id";
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
        //END BAHAN BAKU

        //BAGIAN DEBET
        $app->get("/debet", function (Request $request, Response $response){
            $sql = "SELECT * FROM tbl_debet";
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

        $app->get("/debet/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $sql = "SELECT * FROM tbl_debet WHERE id_debet=:id";
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

        $app->post("/debet", function (Request $request, Response $response){

            $new_debet = $request->getParsedBody();
            
            $sql = "INSERT INTO tbl_debet (id_bahan,id_cabang,id_user,qty,harga) VALUES (:id_bahan,:id_cabang,:id_user,:qty,:harga)";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id_bahan" => $new_debet["id_bahan"],
                ":id_cabang" => $new_debet["id_cabang"],
                ":id_user" => $new_debet["id_user"],
                ":qty" => $new_debet["qty"],
                ":harga" => $new_debet["harga"],
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

        $app->post("/debet/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $new_debet = $request->getParsedBody();
            $sql = "UPDATE tbl_debet SET id_bahan=:id_bahan,id_cabang=:id_cabang,id_user=:id_user,qty=:qty,harga=:harga, dtm_upd=:dtm_upd WHERE id_debet=:id";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id" => $id,
                ":id_bahan" => $new_debet["id_bahan"],
                ":id_cabang" => $new_debet["id_cabang"],
                ":id_user" => $new_debet["id_user"],
                ":qty" => $new_debet["qty"],
                ":harga" => $new_debet["harga"],
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

        $app->delete("/debet/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $sql = "DELETE FROM tbl_debet WHERE id_debet=:id";
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
        //END DEBET

        //BAGIAN KREDIT

        $app->get("/kredit", function (Request $request, Response $response){
            $sql = "SELECT * FROM tbl_kredit";
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

        $app->get("/kredit/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $sql = "SELECT * FROM tbl_kredit WHERE id_kredit=:id";
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

        $app->post("/kredit", function (Request $request, Response $response){

            $new_kredit = $request->getParsedBody();
            
            $sql = "INSERT INTO tbl_kredit (id_bahan,id_cabang,id_user,qty,harga) VALUES (:id_bahan,:id_cabang,:id_user,:qty,:harga)";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id_bahan" => $new_kredit["id_bahan"],
                ":id_cabang" => $new_kredit["id_cabang"],
                ":id_user" => $new_kredit["id_user"],
                ":qty" => $new_kredit["qty"],
                ":harga" => $new_kredit["harga"],
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

        $app->post("/kredit/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $new_kredit = $request->getParsedBody();
            $sql = "UPDATE tbl_kredit SET id_bahan=:id_bahan,id_cabang=:id_cabang,id_user=:id_user,qty=:qty,harga=:harga, dtm_upd=:dtm_upd WHERE id_kredit=:id";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id" => $id,
                ":id_bahan" => $new_kredit["id_bahan"],
                ":id_cabang" => $new_kredit["id_cabang"],
                ":id_user" => $new_kredit["id_user"],
                ":qty" => $new_kredit["qty"],
                ":harga" => $new_kredit["harga"],
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

        $app->delete("/kredit/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $sql = "DELETE FROM tbl_kredit WHERE id_kredit=:id";
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

        //END KREDIT

        //BAGIAN TRANSAKSI

        $app->get("/transaksi", function (Request $request, Response $response){
            $sql = "SELECT * FROM tbl_transaksi";
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

        $app->post("/transaksiList", function (Request $request, Response $response){
            $sql = "SELECT A.ID_TRANSAKSI,A.STATUS,A.TGL_TRANSAKSI, sum(B.QTY) as QTY,sum(B.HARGA*B.QTY) as NOMINAL FROM `tbl_transaksi` A join tbl_transaksi_detail B on A.ID_TRANSAKSI = B.ID_TRANSAKSI
                    where DATE_FORMAT(NOW(), '%Y-%m-%d') = DATE_FORMAT(A.TGL_TRANSAKSI, '%Y-%m-%d')
                    and A.id_user = :id_user
                    and A.id_cabang = :id_cabang
                    group by A.ID_TRANSAKSI
                    order by TGL_TRANSAKSI DESC";

            $stmt = $this->db->prepare($sql);
            $body = $request->getParsedBody();
            $data = [
                ":id_user" => $body["id_user"],
                ":id_cabang" => $body["id_cabang"],
            ];
            $stmt->execute($data);
            $data = $stmt->fetchAll();

            if ($stmt->rowCount() > 0) {
                $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>$data);
            }else{
                $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'Tidak ada data yang ditampilkan','CODE'=>404,'DATA'=>null);
            }
            
            $newResponse = $response->withJson($result);
            return $newResponse;
        });

        $app->get("/apptransaksiDetail/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            
            $sql = "SELECT A.ID_TRANSAKSI,B.ID_TRANSAKSI_DETAIL,B.ID_MENU_DETAIL, A.BAYAR, A.STATUS, B.HARGA, B.QTY,B.DISKON,B.NAMA_MENU,C.NAMA_USER, A.TGL_TRANSAKSI FROM `tbl_transaksi` A 
                    join tbl_transaksi_detail B on A.ID_TRANSAKSI = B.ID_TRANSAKSI 
                    join tbl_user C on A.id_user = C.ID_USER 
                    where A.ID_TRANSAKSI = :id_transaksi";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([":id_transaksi" => $id]);
            $data = $stmt->fetchAll();
            if ($stmt->rowCount() > 0) {
                $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>$data);
            }else{
                $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'Tidak ada data yang ditampilkan','CODE'=>404,'DATA'=>null);
            }
            
            $newResponse = $response->withJson($result);
            return $newResponse;
        });

        $app->get("/transaksi/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $sql = "SELECT * FROM tbl_transaksi WHERE id_transaksi=:id";
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

        $app->post("/transaksi", function (Request $request, Response $response){

            $new_transaksi = $request->getParsedBody();
            
            $sql = "INSERT INTO tbl_transaksi(id_user,id_cabang,status,nama_user) VALUES (:id_user,:id_cabang,:status,:nama_user);";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id_user" => $new_transaksi["id_user"],
                ":id_cabang" => $new_transaksi["id_cabang"],
                ":status" => $new_transaksi["status"],
                ":nama_user" => $new_transaksi["nama_user"],
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

        $app->post("/transaksi/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $new_transaksi = $request->getParsedBody();
            $sql = "UPDATE tbl_transaksi SET id_user=:id_user,id_cabang=:id_cabang,status=:status,nama_user=:nama_user, dtm_upd=:dtm_upd WHERE id_transaksi=:id";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id" => $id,
                ":id_user" => $new_transaksi["id_user"],
                ":id_cabang" => $new_transaksi["id_cabang"],
                ":status" => $new_transaksi["status"],
                ":nama_user" => $new_transaksi["nama_user"],
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

        $app->delete("/transaksi/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $sql = "DELETE FROM tbl_transaksi WHERE id_transaksi=:id";
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


        //END TRANSAKSI

        //BAGIAN TRANSAKSI DETAIL

        $app->get("/transaksidetail", function (Request $request, Response $response){
            $sql = "SELECT * FROM tbl_transaksi_detail";
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

        $app->get("/transaksidetail/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $sql = "SELECT * FROM tbl_transaksi_detail WHERE id_transaksi_detail=:id";
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

        $app->post("/transaksidetail", function (Request $request, Response $response){

            $new_transaksi_detail = $request->getParsedBody();
            
            $sql = "INSERT INTO tbl_transaksi_detail(id_transaksi,id_menu_detail,harga,qty,diskon) VALUES (:id_transaksi,:id_menu_detail,:harga,:qty,:diskon);";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id_transaksi" => $new_transaksi_detail["id_transaksi"],
                ":id_menu_detail" => $new_transaksi_detail["id_menu_detail"],
                ":harga" => $new_transaksi_detail["harga"],
                ":qty" => $new_transaksi_detail["qty"],
                ":diskon" => $new_transaksi_detail["diskon"],
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

        $app->post("/transaksidetail/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $new_transaksi_detail = $request->getParsedBody();
            $sql = "UPDATE tbl_transaksi_detail SET id_transaksi=:id_transaksi, id_menu_detail=:id_menu_detail, harga=:harga, qty=:qty, diskon=:diskon, dtm_upd=:dtm_upd WHERE id_transaksi_detail=:id";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id" => $id,
                ":id_transaksi" => $new_transaksi_detail["id_transaksi"],
                ":id_menu_detail" => $new_transaksi_detail["id_menu_detail"],
                ":harga" => $new_transaksi_detail["harga"],
                ":qty" => $new_transaksi_detail["qty"],
                ":diskon" => $new_transaksi_detail["diskon"],
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

        $app->delete("/transaksidetail/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $sql = "DELETE FROM tbl_transaksi_detail WHERE id_transaksi_detail=:id";
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

        $app->post("/addTransaksi", function (Request $request, Response $response, $args){
            $transaksi = $request->getParsedBody();

            $sql = "DELETE FROM tbl_transaksi_detail WHERE id_transaksi=:id";
            $stmt = $this->db->prepare($sql);
            $dataDeleteDetail = [
                ":id" => $transaksi['ID_TRANSAKSI']
            ];
            $stmt->execute($dataDeleteDetail);

            $sql = "DELETE FROM tbl_transaksi WHERE id_transaksi=:id";
            $stmt = $this->db->prepare($sql);
            $dataDelete = [
                ":id" => $transaksi['ID_TRANSAKSI']
            ];
            $stmt->execute($dataDelete);

          


            $sql = "INSERT INTO tbl_transaksi(id_transaksi,id_user,id_cabang,status,bayar) VALUES (:id_transaksi,:id_user,:id_cabang,:status,:bayar);";
            $stmt = $this->db->prepare($sql);
            $dataTransaksi = [
                ":id_transaksi" => $transaksi['ID_TRANSAKSI'],
                ":id_user" => $transaksi["ID_USER"],
                ":id_cabang" => $transaksi["ID_CABANG"],
                ":status" => $transaksi["STATUS"],
                ":bayar" => $transaksi["BAYAR"]
            ];
            $isSuccess = true;
            if($stmt->execute($dataTransaksi)){
                foreach($transaksi['listTransaksi'] as $detailTransaksi){

                    $sql = "INSERT INTO tbl_transaksi_detail(id_transaksi,id_menu_detail,harga,qty,nama_menu,diskon) 
                    VALUES (:id_transaksi,:id_menu_detail,:harga,:qty,:nama_menu,:diskon);";
                    $stmt = $this->db->prepare($sql);
                    
                    $data = [
                        ":id_transaksi" =>$detailTransaksi['transaksi']['ID_TRANSAKSI'],
                        ":id_menu_detail" => $detailTransaksi['ID_MENU_DETAIL'],
                        ":harga" =>$detailTransaksi['HARGA'],
                        ":qty" =>$detailTransaksi['transaksi']['QTY'],
                        ":nama_menu" => $detailTransaksi['NAMA_MENU'],
                        ":diskon" => $detailTransaksi['transaksi']['DISKON'],
                    ];
                    if($stmt->execute($data)){
                        
                    }else{
                        $isSuccess = false;
                    }
                }
            }

            if($isSuccess){
                $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>NULL);
            }else{
                $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'Ada data yang tidak dapat diunggah','CODE'=>400,'DATA'=>NULL);
            }
                
            $newResponse = $response->withJson($result);
            return $newResponse;
        });
        //END TRANSAKSI DETAIL

        //VIEW 
        // //-- V TRANSAKSI
        // //---ALL TRANSAKSI 
        // $app->get("/vtransaksi", function (Request $request, Response $response){
        //     $sql = "SELECT * FROM v_transaksi";
        //     $stmt = $this->db->prepare($sql);
        //     $stmt->execute();
        //     $data = $stmt->fetchAll();
        //     if ($stmt->rowCount() > 0) {
        //         $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>$data);
        //     }else{
        //         $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'FAILED','CODE'=>500,'DATA'=>null);
        //     }
            
        //     $newResponse = $response->withJson($result);
        //     return $newResponse;
        // });
   
        //---TRANSAKSI BY QUERY_STRING
        $app->get("/vtransaksidetail", function (Request $request, Response $response, $args){
            
            $cabang = $request->getQueryParam("cabang");
            $tanggal = $request->getQueryParam("tgl");
            $periode = $request->getQueryParam("periode");

            
            if($periode=='Daily'){
                $where= "DATE_FORMAT(TGL_TRANSAKSI,'%Y%m%d') = '$tanggal'";
            }else if($periode=='Monthly'){
                $where= "DATE_FORMAT(TGL_TRANSAKSI,'%Y%m') = '$tanggal'";    
            }else{
                $where= "DATE_FORMAT(TGL_TRANSAKSI,'%Y') = '$tanggal'";    
            }
            
            $where.=($cabang!='all')?"AND ID_CABANG=$cabang":"";

            $sql = "SELECT 
            ID_MENU,
            TGL_TRANSAKSI,
            STATUS,
            METODE_PEMBAYARAN,  
            NAMA_MENU,
            SUM(QTY) AS QTY,
            SUM(HARGA) AS HARGA,
            AVG(DISKON) AS DISKON,
            SUM(NET_HARGA) AS NET_HARGA 
            FROM v_transaksi
            WHERE $where 
            GROUP BY
            ID_MENU,
            STATUS,
            METODE_PEMBAYARAN,
            NAMA_MENU
            ";

            // echo $sql;

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
        $app->get("/vtransaksi", function (Request $request, Response $response, $args){
            $cabang = $request->getQueryParam("cabang");
            $kasir = $request->getQueryParam("kasir");
            $metode = $request->getQueryParam("metode");
            $tgl_tansaksi = $request->getQueryParam("tgl_transaksi");
            $status = $request->getQueryParam("status");
            $periode = $request->getQueryParam("periode");

            $where_cabang = ($cabang != "")?"=$cabang":"LIKE '%'";

            if ($periode == 'Daily'){
                $sql ="SELECT ID_CABANG,
                    DATE_FORMAT(TGL_TRANSAKSI,'%Y%m%d') AS PERIODE
                    ,DATE_FORMAT(TGL_TRANSAKSI,'%e %b %Y') AS TGL_TRANSAKSI
                    ,sum(NET_HARGA) AS NET_HARGA,sum(QTY) AS QTY 
                    FROM v_transaksi
                    WHERE id_cabang $where_cabang 
                    AND tgl_transaksi LIKE '$tgl_tansaksi%' 
                    GROUP BY DATE_FORMAT(TGL_TRANSAKSI,'%Y-%m-%d')
                    ORDER BY TGL_TRANSAKSI";
            }else if ($periode == 'Monthly'){
                $sql ="SELECT ID_CABANG
                    ,DATE_FORMAT(TGL_TRANSAKSI,'%Y%m') AS PERIODE
                    ,DATE_FORMAT(TGL_TRANSAKSI,'%b %Y') AS TGL_TRANSAKSI
                    ,sum(NET_HARGA) AS NET_HARGA,sum(QTY) AS QTY 
                    FROM v_transaksi
                    WHERE id_cabang $where_cabang 
                    AND tgl_transaksi LIKE '$tgl_tansaksi%' 
                    GROUP BY DATE_FORMAT(TGL_TRANSAKSI,'%Y-%m')
                    ORDER BY DATE_FORMAT(TGL_TRANSAKSI,'%Y%m')";
            }else{
                $sql ="SELECT ID_CABANG
                    ,DATE_FORMAT(TGL_TRANSAKSI,'%Y') AS PERIODE
                    ,DATE_FORMAT(TGL_TRANSAKSI,'%Y') AS TGL_TRANSAKSI
                    ,sum(NET_HARGA) AS NET_HARGA,sum(QTY) AS QTY 
                    FROM v_transaksi
                    WHERE id_cabang $where_cabang 
                    AND tgl_transaksi LIKE '$tgl_tansaksi%' 
                    GROUP BY DATE_FORMAT(TGL_TRANSAKSI,'%Y')
                    ORDER BY DATE_FORMAT(TGL_TRANSAKSI,'%Y%m')";
            }
                // $sql = "SELECT * FROM v_transaksi";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetchAll();
            if ($stmt->rowCount() > 0) {
                $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>$data);
            }else{
                $result = array('STATUS'     => 'FAILED', 'MESSAGE' => 'FAILED','CODE'=>500,'DATA'=>null);
            }
            
            $newResponse = $response->withJson($result);
            return $newResponse;
        });
        


        //master tahun
        $app->get("/vtransaksi/tahun", function (Request $request, Response $response, $args){
            $sql="SELECT date_format(TGL_TRANSAKSI,'%Y') AS VAL FROM v_transaksi group by date_format(TGL_TRANSAKSI,'%Y')";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetchAll();
            if ($stmt->rowCount() > 0) {
                $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>$data);
            }else{
                $result = array('STATUS'     => 'FAILED', 'MESSAGE' => 'FAILED','CODE'=>500,'DATA'=>null);
            }
            
            $newResponse = $response->withJson($result);
            return $newResponse;
        });
        //--END V TRANSAKSI
        //-- V MENU
        $app->get("/vmenu", function (Request $request, Response $response){
            $sql = "SELECT * FROM v_menu";
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

        $app->get("/vmenu/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $sql = "SELECT * FROM v_menu WHERE id_cabang=:id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([":id" => $id]);
            $data = $stmt->fetchAll();
            if ($stmt->rowCount() > 0) {
                $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>$data);
            }else{
                $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'FAILED','CODE'=>500,'DATA'=>null);
            }
            
            $newResponse = $response->withJson($result);
            return $newResponse;
        });
        //--END V MENU

        //V SALDO GUDANG BAHAN
        //-- V SALDO AKHIR 
        $app->get("/vsaldo/{id_cabang}", function (Request $request, Response $response, $args){
            $id = $args["id_cabang"];
            $periode = $request->getQueryParam("periode");
            $bahan = urldecode($request->getQueryParam("bahan"));
            $harga = urldecode($request->getQueryParam("harga"));

            
            if($periode != ''){
                //SALDO PER PERIODE TAHUN-BULAN
                if(strlen($periode)==4){
                    $sql = "SELECT * FROM v_saldo_periode WHERE id_cabang=:id_cabang 
                    AND periode LIKE '$periode%' 
                    AND nama_bahan LIKE '$bahan%'";
                }else if(strlen($periode)==7){
                    $sql = "SELECT 
                    DATE_FORMAT(TGL_TRANSAKSI,'%e %b %Y') AS TGL_TRANSAKSI,
                    NAMA_BAHAN,
                    SUM(DEBET) AS DEBET,
                    SUM(KREDIT) AS KREDIT,
                    HARGA 
                    FROM v_saldo 
                    WHERE id_cabang=:id_cabang 
                    AND tgl_transaksi LIKE '$periode%' 
                    AND nama_bahan LIKE '$bahan%'
                    and harga 
                    GROUP BY
                    DATE_FORMAT(TGL_TRANSAKSI,'%Y%m%d'),
                    NAMA_BAHAN";
                }
                else{
                    $sql = "SELECT 
                    DATE_FORMAT(TGL_TRANSAKSI,'%e %b %Y') AS TGL_TRANSAKSI,
                    SUM(DEBET) AS DEBET,
                    SUM(KREDIT) AS KREDIT
                    FROM v_saldo WHERE id_cabang=:id_cabang 
                    AND DATE_FORMAT(TGL_TRANSAKSI,'%Y-%m') = DATE_FORMAT('$periode','%Y-%m') 
                    AND nama_bahan LIKE '$bahan%'
                    GROUP BY
                    DATE_FORMAT(TGL_TRANSAKSI,'%e %b %Y')";
                }
            }else {
                //SALDO AKHIR KESELURUHAN
                if(strlen($bahan)!=0){
                    $sql = "SELECT
                    DATE_FORMAT(TGL_TRANSAKSI,'%Y%m%d') AS TGL_TRANSAKSI, 
                    DATE_FORMAT(TGL_TRANSAKSI,'%b %Y') AS PERIODE, 
                    SATUAN,
                    SALDO_AWAL,
                    DEBET,
                    KREDIT,
                    SALDO,
                    HARGA
                    FROM v_saldo_periode WHERE id_cabang=:id_cabang 
                    AND periode LIKE '$periode%' 
                    AND nama_bahan LIKE '$bahan%'";
                }else{
                    $sql = "SELECT * FROM v_saldo_akhir WHERE id_cabang=:id_cabang group by ID_BAHAN, HARGA";
                }
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute([":id_cabang" => $id]);
            $data = $stmt->fetchAll();
            if ($stmt->rowCount() > 0) {
                $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>$data);
            }else{
                $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'FAILED','CODE'=>500,'DATA'=>null);
            }
            
            $newResponse = $response->withJson($result);
            return $newResponse;
        });
        //END V SALDO
        //V SALDO PERIODE HARIAN 
        $app->get("/vsaldoHarian/{id_cabang}", function (Request $request, Response $response, $args){
            $id = $args["id_cabang"];
            $sql = "SELECT id_cabang,nama_cabang,id_bahan,nama_bahan,sum(debet) as debet, sum(kredit) as kredit,tgl_transaksi,harga, sum(debet)-sum(kredit) as total_saldo,
            (select sum(debet)-sum(kredit) from v_saldo where id_bahan = vs.id_bahan and id_cabang = vs.id_cabang and date_format(tgl_transaksi,'%Y-%m-%d') < date_format(now(),'%Y-%m-%d')) as saldo_awal  
            FROM `v_saldo` vs where id_cabang = :id_cabang
            group by id_bahan";
            $stmt = $this->db->prepare($sql);

            $data = [
                ":id_cabang" => $id
            ];

            if($stmt->execute($data)){
                if ($stmt->rowCount() > 0) {
                    $data = $stmt->fetchAll();

                    $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>$data);
                }else{
                    $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'FAILED','CODE'=>500,'DATA'=>null);
                }
            }
            
            $newResponse = $response->withJson($result);
            return $newResponse;
        });

        $app->get("/vsaldoHarian/{id_cabang}/detail/{id_bahan}", function (Request $request, Response $response, $args){
            $id = $args["id_cabang"];
            $id_bahan = $args["id_bahan"];
            $sql = "SELECT * FROM `v_saldo` WHERE date_format(tgl_transaksi,'%Y-%m-%d') = date_format(now(),'%Y-%m-%d') and id_bahan = :id_bahan
            and ID_CABANG = :id_cabang";

            $stmt = $this->db->prepare($sql);

            $data = [
                ":id_cabang" => $id,
                ":id_bahan" => $id_bahan
            ];

            if($stmt->execute($data)){
                if ($stmt->rowCount() > 0) {
                    $data = $stmt->fetchAll();
                    $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>$data);
                }else{
                    $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'Tidak ada data yang ditampilkan','CODE'=>404,'DATA'=>null);
                }
            }
            
            $newResponse = $response->withJson($result);
            return $newResponse;
        });
        //END VIEW
    });


};
