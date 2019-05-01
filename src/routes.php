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

            $new_users = $request->getParsedBody();
            
            $sql = "INSERT INTO tbl_cabang (nama_cabang, alamat, no_hp, nama_pemilik, jam_buka, jam_tutup, printer) VALUES (:nama_cabang, :alamat, :no_hp, :nama_pemilik, :jam_buka, :jam_tutup, :printer)";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":nama_cabang" => $new_users["nama_cabang"],
                ":alamat" => $new_users["alamat"],
                ":no_hp" => $new_users["no_hp"],
                ":nama_pemilik" => $new_users["nama_pemilik"],
                ":jam_buka" => $new_users["jam_buka"],
                ":jam_tutup" => $new_users["jam_tutup"],
                ":printer" => $new_users["printer"]
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
            $new_users = $request->getParsedBody();
            $sql = "UPDATE tbl_cabang SET nama_cabang=:nama_cabang, alamat=:alamat, no_hp=:no_hp, nama_pemilik=:nama_pemilik, jam_buka=:jam_buka, jam_tutup=:jam_tutup, printer=:printer WHERE id_cabang=:id";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id" => $id,
                 ":nama_cabang" => $new_users["nama_cabang"],
                ":alamat" => $new_users["alamat"],
                ":no_hp" => $new_users["no_hp"],
                ":nama_pemilik" => $new_users["nama_pemilik"],
                ":jam_buka" => $new_users["jam_buka"],
                ":jam_tutup" => $new_users["jam_tutup"],
                ":printer" => $new_users["printer"],
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

            $new_menudetail = $request->getParsedBody();
            
            $sql = "INSERT INTO tbl_kategori (nama_kategori) VALUES (:nama_kategori)";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":nama_kategori" => $new_menudetail["nama_kategori"],
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
            $new_menudetail = $request->getParsedBody();
            $sql = "UPDATE tbl_kategori SET nama_kategori=:nama_kategori, dtm_upd=:dtm_upd WHERE id_kategori=:id";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id" => $id,
                ":nama_kategori" => $new_menudetail["nama_kategori"],
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

            $new_menudetail = $request->getParsedBody();
            
            $sql = "INSERT INTO tbl_bahan_baku (id_kategori,nama_bahan,satuan) VALUES (:id_kategori,:nama_bahan,:satuan)";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id_kategori" => $new_menudetail["id_kategori"],
                ":nama_bahan" => $new_menudetail["nama_bahan"],
                ":satuan" => $new_menudetail["satuan"],
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
                ":id_kategori" => $new_menudetail["id_kategori"],
                ":nama_bahan" => $new_menudetail["nama_bahan"],
                ":satuan" => $new_menudetail["satuan"],
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


    });


};
