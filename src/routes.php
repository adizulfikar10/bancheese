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
        $app->get("/vtransaksi", function (Request $request, Response $response, $args){
            $cabang = $request->getQueryParam("cabang");
            $kasir = $request->getQueryParam("kasir");
            $metode = $request->getQueryParam("metode");
            $tgl_tansaksi = $request->getQueryParam("tgl_transaksi");
            $status = $request->getQueryParam("status");
            $periode = $request->getQueryParam("periode");

            $where_cabang = ($cabang != "")?"=$cabang":"LIKE '%'";

            if ($periode == 'Daily'){
                $sql ="SELECT ID_CABANG,NAMA_CABANG,DATE_FORMAT(TGL_TRANSAKSI,'%Y-%m-%d') AS TGL_TRANSAKSI
                    ,sum(NET_HARGA) AS NET_HARGA,sum(QTY) AS QTY 
                    FROM v_transaksi
                    WHERE id_cabang $where_cabang 
                    AND tgl_transaksi LIKE '$tgl_tansaksi%' 
                    GROUP BY NAMA_CABANG,DATE_FORMAT(TGL_TRANSAKSI,'%Y-%m-%d'),ID_CABANG 
                    ORDER BY TGL_TRANSAKSI";
            }else{
                $sql = "SELECT * FROM v_transaksi WHERE id_cabang $where_cabang AND metode_pembayaran LIKE '$metode%' 
                AND nama_kasir LIKE '%$kasir%'
                AND tgl_transaksi LIKE '$tgl_tansaksi%' AND status LIKE '%$status%'";
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

            if($periode != ''){
                //SALDO PER PERIODE TAHUN-BULAN
                if(strlen($periode)==4){
                    $sql = "SELECT * FROM v_saldo_periode WHERE id_cabang=:id_cabang 
                    AND periode LIKE '$periode%' 
                    AND nama_bahan LIKE '$bahan%'";
                }else{
                    $sql = "SELECT * FROM v_saldo WHERE id_cabang=:id_cabang 
                    AND tgl_transaksi LIKE '$periode%' 
                    AND nama_bahan LIKE '$bahan%'";
                }
            }else {
                //SALDO AKHIR KESELURUHAN
                if(strlen($bahan)!=0){
                    $sql = "SELECT * FROM v_saldo_periode WHERE id_cabang=:id_cabang 
                    AND periode LIKE '$periode%' 
                    AND nama_bahan LIKE '$bahan%'";
                }else{
                    $sql = "SELECT * FROM v_saldo_akhir WHERE id_cabang=:id_cabang";
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
        //END VIEW
    });


};
