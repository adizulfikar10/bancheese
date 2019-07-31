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

            $sql = "SELECT a.id_user,a.username,a.role,a.nama_user,a.id_cabang,a.id_user,b.nama_cabang,b.alamat FROM tbl_user a 
            left join tbl_cabang b on a.id_cabang = b.id_cabang WHERE 
            a.username =convert(:username using utf8mb4) collate utf8mb4_bin AND a.password=:password";
            $stmt = $this->db->prepare($sql);

            $data = [
                ":username" => $username,
                ":password" => $password
            ];

            if($stmt->execute($data)){
                if ($stmt->rowCount() > 0) {
                    $data = $stmt->fetch();
                    if (strtoupper($data['role'])=='ADMIN'){
                        $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>$data);
                    }else{
                        $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'User tidak memiliki akses','CODE'=>400,'DATA'=>null);    
                    }
                }else{
                    $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'Username atau Password tidak ditemukan','CODE'=>400,'DATA'=>null);
                }
            }else{
                $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'Error executing query','CODE'=>500,'DATA'=>null);

            }
            $newResponse = $response->withJson($result);
            return $newResponse;

        });

        $app->post("/loginMobile", function (Request $request, Response $response, $args){
            $user = $request->getParsedBody();
            $username = $user['username'];
            $password = sha1($user['password']);
            $id_device = $user['id_device'];



            $sql = "SELECT a.username,a.role,a.nama_user,a.id_cabang,a.id_user,b.nama_cabang,b.alamat FROM tbl_user a 
            join tbl_cabang b on a.id_cabang = b.id_cabang WHERE 
            a.username =convert(:username using utf8mb4) collate utf8mb4_bin AND a.password=:password";
            $stmt = $this->db->prepare($sql);

            $data = [
                ":username" => $username,
                ":password" => $password
            ];

            if($stmt->execute($data)){
                if ($stmt->rowCount() > 0) {
                    $data = $stmt->fetch();
                    if($data['role'] == 'admin' or $data['role'] == 'ADMIN'){
                        $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>$data);
                        $newResponse = $response->withJson($result);
                        return $newResponse;
                    }else{
                        $checkDevice = "SELECT device_name FROM `tbl_device` WHERE id_device = :id_device";
                        $stmtCheck = $this->db->prepare($checkDevice);
                        
                        $dataCheck = [
                            ":id_device" => $id_device
                        ];
                        $stmtCheck->execute($dataCheck);
                        if ($stmtCheck->rowCount() == 0) {
                            $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'Perangkat ini belum terdaftar','CODE'=>500,'DATA'=>null);
                            $newResponse = $response->withJson($result);
                            return $newResponse;
                        }

                        //CHECK USER

                        $checkUser = "SELECT device_name FROM `tbl_device` WHERE id_device = :id_device and id_cabang = :id_cabang";
                        $stmtCheckUser = $this->db->prepare($checkUser);
                        
                        $dataCheckUser = [
                            ":id_device" => $id_device,
                            ":id_cabang" => $data['id_cabang']
                        ];
                        $stmtCheckUser->execute($dataCheckUser);
                        if ($stmtCheckUser->rowCount() == 0) {
                            $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'Anda tidak diijinkan masuk dengan perangkat ini','CODE'=>500,'DATA'=>null);
                            $newResponse = $response->withJson($result);
                            return $newResponse;
                        }
                        $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>$data);
                    }
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
        $app->get("/kategori/jenis/{jenis}", function (Request $request, Response $response,$args){
            $jenis = $args["jenis"];
            $sql = "SELECT * FROM tbl_kategori where jenis = :jenis ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([":jenis" => $jenis]);
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
            
            $sql = "INSERT INTO tbl_kategori (nama_kategori,jenis) VALUES (:nama_kategori,:jenis)";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":nama_kategori" => $new_kategori["nama_kategori"],
                ":jenis" => $new_kategori["jenis"],
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
            $sql = "UPDATE tbl_kategori SET nama_kategori=:nama_kategori,jenis =:jenis, dtm_upd=:dtm_upd WHERE id_kategori=:id";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id" => $id,
                ":nama_kategori" => $new_kategori["nama_kategori"],
                ":jenis" => $new_kategori["jenis"],
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
            $new_bahan = $request->getParsedBody();
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

        $app->get("/debet/{harga}/{id_bahan}/{id_cabang}", function (Request $request, Response $response, $args){
            $harga = $args["harga"];
            $id_bahan = $args["id_bahan"];
            $id_cabang = $args["id_cabang"];
            $sql = "SELECT 
                    DATE_FORMAT(a.TGL_DEBET,'%d %M %y') AS TANGGAL,
                    a.ID_DEBET,
                    a.ID_BAHAN,
                    a.ID_CABANG,
                    a.HARGA,
                    a.QTY,
                    b.NAMA_BAHAN
                    FROM tbl_debet a
                    INNER JOIN tbl_bahan_baku b
                    ON a.ID_BAHAN = b.ID_BAHAN
                    WHERE a.harga=:harga 
                    AND a.id_bahan=:id_bahan 
                    AND a.id_cabang=:id_cabang ORDER BY TGL_DEBET DESC";
            $stmt = $this->db->prepare($sql);
            $data = [
                ":harga" => $harga,
                ":id_bahan" => $id_bahan,
                ":id_cabang" => $id_cabang,
                ];
            $stmt->execute($data);
            $data = $stmt->fetchAll();
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

        $app->get("/kredit/{harga}/{id_bahan}/{id_cabang}", function (Request $request, Response $response, $args){
            $harga = $args["harga"];
            $id_bahan = $args["id_bahan"];
            $id_cabang = $args["id_cabang"];
            $sql = "SELECT 
                    DATE_FORMAT(a.TGL_KREDIT,'%d %M %y') AS TANGGAL,
                    a.ID_DEBET,
                    a.ID_KREDIT,
                    a.ID_BAHAN,
                    a.ID_CABANG,
                    a.HARGA,
                    a.QTY,
                    b.NAMA_BAHAN
                    FROM tbl_kredit a
                    INNER JOIN tbl_bahan_baku b
                    ON a.ID_BAHAN = b.ID_BAHAN
                    WHERE a.harga=:harga 
                    AND a.id_bahan=:id_bahan 
                    AND a.id_cabang=:id_cabang ORDER BY TGL_KREDIT DESC";
            $stmt = $this->db->prepare($sql);

            $data = [
                ":harga" => $harga,
                ":id_bahan" => $id_bahan,
                ":id_cabang" => $id_cabang,
            ];
            
            $stmt->execute($data);
            $data = $stmt->fetchAll();
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
            
            $sql = "SELECT * from (SELECT dbt.ID_DEBET, dbt.TGL_DEBET, dbt.ID_BAHAN, dbt.ID_CABANG,dbt.ID_USER,dbt.HARGA, dbt.qty-COALESCE(sum(krd.QTY),0) as QTY from tbl_debet as dbt 
            left join tbl_kredit as krd on dbt.ID_DEBET = krd.ID_DEBET 
            group by dbt.ID_DEBET) res where res.qty > 0 and id_bahan = :id_bahan and ID_CABANG = :id_cabang ORDER BY res.TGL_DEBET";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id_bahan" => $new_kredit["id_bahan"],
                ":id_cabang" => $new_kredit["id_cabang"]
            ];
            
            if($stmt->execute($data)){
                if ($stmt->rowCount() > 0) {
                    $data = $stmt->fetchAll();
                    $total = 0;

                    foreach($data as $dt){
                        $total += $dt['QTY']; 
                    }

                    if($total < $new_kredit['qty']){
                        $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'Saldo tidak mencukupi','CODE'=>500,'DATA'=>NULL);
                        $newResponse = $response->withJson($result);
                        return $newResponse;
                    }

                    $i = 0;
                    $sisa = 0;
                    $input_qty = $new_kredit['qty'];

                    while($input_qty > 0){
                        $sisa_qty = 0;
                        if($input_qty > $data[$i]['QTY']){
                            $input_qty -= $data[$i]['QTY'];
                            $sisa_qty = $data[$i]['QTY'];
                        }else{
                            $input_qty -= $data[$i]['QTY'];
                            $sisa_qty = $data[$i]['QTY']+$input_qty;
                        }
                        if ($sisa_qty > 0) {
                            $setHarga = ($new_kredit['harga']>0)?$new_kredit['harga']:$data[$i]['HARGA'];
                            $query = "INSERT INTO `tbl_kredit` (`ID_DEBET`, `ID_BAHAN`, `ID_CABANG`, `ID_USER`, `QTY`, `HARGA`) 
                            VALUES (".$data[$i]['ID_DEBET'].", ".$data[$i]['ID_BAHAN'].", ".$data[$i]['ID_CABANG'].", ".$new_kredit['id_user'].",
                            ".$sisa_qty.", ".$setHarga.");";

                            $stmt2 = $this->db->prepare($query);
                            $stmt2->execute();
                        }

                        $sisa += $data[$i]['QTY'];
                        $i++;
                    }
                   $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>NULL);
                }else{
                    $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'Saldo tidak mencukupi','CODE'=>500,'DATA'=>null);
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

        // BAGIAN TRANSAKSI KREDIT
        // $app->get("/transaksiKredit/{id}", function (Request $request, Response $response, $args){
        //     $id = $args["id"];
        //     $sql = "SELECT * FROM tbl_transaksi_kredit where id_cabang =:id";
        //     $stmt = $this->db->prepare($sql);
        //     $stmt->execute([":id"=>$id]);
        //     $data = $stmt->fetchAll();
        //     if ($stmt->rowCount() > 0) {
        //         $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>$data);
        //     }else{
        //         $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'FAILED','CODE'=>500,'DATA'=>null);
        //     }
            
        //     $newResponse = $response->withJson($result);
        //     return $newResponse;
        // });

        // $app->post("/transaksiKredit", function (Request $request, Response $response){

        //     $new_tKredit = $request->getParsedBody();
            
        //     $sql = "INSERT INTO tbl_transaksi_kredit (id_cabang,id_user,id_kategori,biaya,keterangan) VALUES (:id_cabang,:id_user,:bayar,:keterangan)";
        //     $stmt = $this->db->prepare($sql);
            
        //     $data = [
        //         ":id_cabang" => $new_tKredit["id_cabang"],
        //         ":id_user" => $new_tKredit["id_user"],
        //         ":id_kategori" => $new_tKredit["id_kategori"],
        //         ":biaya" => $new_tKredit["bayar"],
        //         ":keterangan" => $new_tKredit["keterangan"],
        //     ];
            
        //     if($stmt->execute($data)){
        //         if ($stmt->rowCount() > 0) {
        //             $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>$data);
        //         }else{
        //             $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'FAILED','CODE'=>500,'DATA'=>null);
        //         }
        //     }

        //     $newResponse = $response->withJson($result);
        //     return $newResponse;
        // });

        // $app->post("/transaksiKredit/{id}", function (Request $request, Response $response, $args){
        //     $id = $args["id"];
        //     $new_tKredit = $request->getParsedBody();
        //     $sql = "UPDATE tbl_transaksi_kredit SET id_cabang=:id_cabang,id_user=:id_user,bayar=:bayar,
        //     keterangan=:keterangan, dtm_upd=:dtm_upd WHERE id_transaksi_kredit=:id";
        //     $stmt = $this->db->prepare($sql);
            
        //     $data = [
        //         ":id" => $id,
        //         ":id_cabang" => $new_tKredit["id_cabang"],
        //         ":id_user" => $new_tKredit["id_user"],
        //         ":bayar" => $new_tKredit["bayar"],
        //         ":keterangan" => $new_tKredit["keterangan"],
        //         ":dtm_upd" => date("Y-m-d H:i:s")
        //     ];
            
        //     if($stmt->execute($data)){
        //         if ($stmt->rowCount() > 0) {
        //             $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>$data);
        //         }else{
        //             $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'FAILED','CODE'=>500,'DATA'=>null);
        //         }
        //     }

        //     $newResponse = $response->withJson($result);
        //     return $newResponse;
        // });

        // $app->delete("/transaksiKredit/{id}", function (Request $request, Response $response, $args){
        //     $id = $args["id"];
        //     $sql = "DELETE FROM tbl_transaksi_kredit WHERE id_transaksi_kredit=:id";
        //     $stmt = $this->db->prepare($sql);

        //     $data = [
        //         ":id" => $id
        //     ];

        //     if($stmt->execute($data)){
        //         if ($stmt->rowCount() > 0) {
        //             $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>$data);
        //         }else{
        //             $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'FAILED','CODE'=>500,'DATA'=>null);
        //         }
        //     }
        //     $newResponse = $response->withJson($result);
        //     return $newResponse;
        // });
        // END TRANSAKSI KREDIT


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

        $app->post("/transaksi/{id}/void", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $new_transaksi = $request->getParsedBody();
            $sql = "UPDATE tbl_transaksi SET status='VOID' WHERE id_transaksi=:id";
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

        //BAGIAN TRANSAKSI KREDIT
        
        $app->get("/transaksi_kredit", function (Request $request, Response $response){
            $sql = "SELECT a.*,b.NAMA_KATEGORI FROM tbl_transaksi_kredit a join tbl_kategori b on a.id_kategori = b.id_kategori";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetchAll();
            if ($stmt->rowCount() > 0) {
                $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>$data);
            }else{
                $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'Tidak ada data yang ditampilkan','CODE'=>404,'DATA'=>null);
            }
            
            $newResponse = $response->withJson($result);
            return $newResponse;
        });

        $app->get("/transaksi_kredit/{id}", function (Request $request, Response $response,$args){
            $id = $args["id"];
            $sql = "SELECT * FROM tbl_transaksi_kredit where id_transaksi_kredit = :id";
            $stmt = $this->db->prepare($sql);
            $data = [":id" => $id];
            if($stmt->execute($data)){
                $data = $stmt->fetch();
                if ($stmt->rowCount() > 0) {
                    $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>$data);
                }else{
                    $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'Tidak ada data yang ditampilkan','CODE'=>404,'DATA'=>null);
                }
            }else{
                $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'Terjadi kesalahan saat mengeksekusi','CODE'=>500,'DATA'=>null);

            }
            
            $newResponse = $response->withJson($result);
            return $newResponse;
        });

        $app->get("/transaksi_kredit/cabang/{id}", function (Request $request, Response $response,$args){
            $id = $args["id"];
            $sql = "SELECT a.*,b.NAMA_KATEGORI FROM tbl_transaksi_kredit a join tbl_kategori b on a.id_kategori = b.id_kategori where a.id_cabang = :id ORDER BY DTM_CRT DESC";
            $stmt = $this->db->prepare($sql);
            $data = [":id" => $id];
            if($stmt->execute($data)){
                $data = $stmt->fetchAll();
                if ($stmt->rowCount() > 0) {
                    $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>$data);
                }else{
                    $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'Tidak ada data yang ditampilkan','CODE'=>404,'DATA'=>null);
                }
            }else{
                $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'Terjadi kesalahan saat mengeksekusi','CODE'=>500,'DATA'=>null);

            }
            
            $newResponse = $response->withJson($result);
            return $newResponse;
        });

        $app->get("/transaksi_kredit/user/{id}/{cabang}", function (Request $request, Response $response,$args){
            $id = $args["id"];
            $cabang = $args["cabang"];
            $sql = "SELECT a.*,b.NAMA_KATEGORI FROM tbl_transaksi_kredit a join tbl_kategori b on a.id_kategori = b.id_kategori 
                where a.id_user = :id and a.id_cabang = :cabang and date_format(a.dtm_crt,'%Y-%m-%d') = date_format(sysdate(),'%Y-%m-%d')";
            $stmt = $this->db->prepare($sql);
            $data = [":id" => $id, ":cabang" => $cabang];
            if($stmt->execute($data)){
                $data = $stmt->fetchAll();
                if ($stmt->rowCount() > 0) {
                    $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>$data);
                }else{
                    $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'Tidak ada data yang ditampilkan','CODE'=>404,'DATA'=>null);
                }
            }else{
                $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'Terjadi kesalahan saat mengeksekusi','CODE'=>500,'DATA'=>null);
            }
            
            $newResponse = $response->withJson($result);
            return $newResponse;
        });

        $app->get("/transaksi_kredit/kategori/{id}", function (Request $request, Response $response,$args){
            $id = $args["id"];
            $sql = "SELECT a.*,b.NAMA_KATEGORI FROM tbl_transaksi_kredit a join tbl_kategori b on a.id_kategori = b.id_kategori where a.id_kategori = :id";
            $stmt = $this->db->prepare($sql);
            $data = [":id" => $id];
            if($stmt->execute($data)){
                $data = $stmt->fetchAll();
                if ($stmt->rowCount() > 0) {
                    $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>$data);
                }else{
                    $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'Tidak ada data yang ditampilkan','CODE'=>404,'DATA'=>null);
                }
            }else{
                $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'Terjadi kesalahan saat mengeksekusi','CODE'=>500,'DATA'=>null);
            }
            
            $newResponse = $response->withJson($result);
            return $newResponse;
        });
        

        $app->post("/transaksi_kredit", function (Request $request, Response $response){
            $new_transaksi_detail = $request->getParsedBody();
            $sql = "INSERT into tbl_transaksi_kredit 
                (`ID_TRANSAKSI_KREDIT`, `ID_CABANG`, `ID_USER`, `ID_KATEGORI`, `KETERANGAN`, `BIAYA`, `DTM_CRT`) 
                values (null,:id_cabang,:id_user,:id_kategori,:keterangan,:biaya,CURRENT_TIMESTAMP)";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id_cabang" => $new_transaksi_detail["id_cabang"],
                ":id_user" => $new_transaksi_detail["id_user"],
                ":id_kategori" => $new_transaksi_detail["id_kategori"],
                ":keterangan" => $new_transaksi_detail["keterangan"],
                ":biaya" => $new_transaksi_detail["biaya"]
            ];
            
            if($stmt->execute($data)){
                if ($stmt->rowCount() > 0) {
                    $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'Data berhasil disimpan','CODE'=>201,'DATA'=>$data);
                }else{
                    $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'Tidak ada data yang ditambah','CODE'=>304,'DATA'=>null);
                }
            }else{
                $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'Data tidak dapat disimpan','CODE'=>500,'DATA'=>null);
            }

            $newResponse = $response->withJson($result);
            return $newResponse;
        });

        $app->post("/transaksi_kredit/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $new_transaksi_detail = $request->getParsedBody();
            $sql = "UPDATE tbl_transaksi_kredit SET id_cabang=:id_cabang,
                id_user=:id_user, id_kategori=:id_kategori, keterangan=:keterangan, biaya=:biaya WHERE id_transaksi_kredit=:id";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id" => $id,
                ":id_cabang" => $new_transaksi_detail["id_cabang"],
                ":id_user" => $new_transaksi_detail["id_user"],
                ":id_kategori" => $new_transaksi_detail["id_kategori"],
                ":keterangan" => $new_transaksi_detail["keterangan"],
                ":biaya" => $new_transaksi_detail["biaya"]
            ];
            
            if($stmt->execute($data)){
                if ($stmt->rowCount() > 0) {
                    $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'Data berhasil disimpan','CODE'=>201,'DATA'=>$data);
                }else{
                    $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'Data tidak berubah','CODE'=>304,'DATA'=>null);
                }
            }else{
                $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'Data tidak dapat diubah','CODE'=>500,'DATA'=>null);
            }

            $newResponse = $response->withJson($result);
            return $newResponse;
        });

        $app->delete("/transaksi_kredit/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $sql = "DELETE FROM tbl_transaksi_kredit WHERE id_transaksi_kredit=:id";
            $stmt = $this->db->prepare($sql);

            $data = [":id" => $id];

            if($stmt->execute($data)){
                if ($stmt->rowCount() > 0) {
                    $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'Data dihapus','CODE'=>200,'DATA'=>$data);
                }else{
                $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'Tidak ada data yang dihapus','CODE'=>304,'DATA'=>null);
                }
            }else{
                $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'Tidak dapat menghapus data','CODE'=>500,'DATA'=>null);
            }
            $newResponse = $response->withJson($result);
            return $newResponse;
        });

        //END TRANSAKSI KREDIT

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
            $status = $request->getQueryParam("status");

            
            if($periode=='Daily'){
                $where= "DATE_FORMAT(TGL_TRANSAKSI,'%Y%m%d') = '$tanggal'";
            }else if($periode=='Monthly'){
                $where= "DATE_FORMAT(TGL_TRANSAKSI,'%Y%m') = '$tanggal'";    
            }else{
                $where= "DATE_FORMAT(TGL_TRANSAKSI,'%Y') = '$tanggal'";    
            }
            
            $where.=($cabang!='all')?" AND ID_CABANG=$cabang":"";
            $where.=($status!='')?" AND STATUS='$status'":"";

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
            $status = $request->getQueryParam("status");
            $cabang = $request->getQueryParam("cabang");
            $kasir = $request->getQueryParam("kasir");
            $metode = $request->getQueryParam("metode");
            $tgl_tansaksi = $request->getQueryParam("tgl_transaksi");
            $status = $request->getQueryParam("status");
            $periode = $request->getQueryParam("periode");

            $where = ($cabang != "")?"=$cabang":"LIKE '%'";
            $where .= ($status != "")?" AND STATUS = '$status'":"LIKE '%'";

            if ($periode == 'Daily'){
                $sql ="SELECT 
                    ID_CABANG,
                    DATE_FORMAT(TGL_TRANSAKSI,'%Y%m%d') AS PERIODE
                    ,DATE_FORMAT(TGL_TRANSAKSI,'%e %b %Y') AS TGL_TRANSAKSI
                    ,sum(NET_HARGA) AS NET_HARGA,sum(QTY) AS QTY
                    ,COALESCE((select sum(harga*qty) from tbl_debet where ID_CABANG = 
                        v_transaksi.ID_CABANG AND 
                        DATE_FORMAT(TGL_DEBET,'%e %b %Y') =  
                        DATE_FORMAT(v_transaksi.TGL_TRANSAKSI,'%e %b %Y')),0) 
                    AS DEBET
                    ,COALESCE((select sum(biaya) from tbl_transaksi_kredit where ID_CABANG = 
                        v_transaksi.ID_CABANG AND 
                        DATE_FORMAT(DTM_CRT,'%e %b %Y') =  
                        DATE_FORMAT(v_transaksi.TGL_TRANSAKSI,'%e %b %Y')),0) 
                    AS PENGELUARAN 
                    FROM v_transaksi
                    WHERE id_cabang $where
                    AND tgl_transaksi LIKE '$tgl_tansaksi%' 
                    GROUP BY DATE_FORMAT(TGL_TRANSAKSI,'%Y-%m-%d')
                    ORDER BY DATE_FORMAT(TGL_TRANSAKSI,'%Y%m%d')";
            }else if ($periode == 'Monthly'){
                $sql ="SELECT 
                    ID_CABANG
                    ,DATE_FORMAT(TGL_TRANSAKSI,'%Y%m') AS PERIODE
                    ,DATE_FORMAT(TGL_TRANSAKSI,'%b %Y') AS TGL_TRANSAKSI
                    ,sum(NET_HARGA) AS NET_HARGA,sum(QTY) AS QTY
                    ,COALESCE((select sum(harga*qty) from tbl_debet where ID_CABANG = 
                        v_transaksi.ID_CABANG AND 
                        DATE_FORMAT(TGL_DEBET,'%b %Y') =  
                        DATE_FORMAT(v_transaksi.TGL_TRANSAKSI,'%b %Y')),0) 
                    AS DEBET 
                    ,COALESCE((select sum(biaya) from tbl_transaksi_kredit where ID_CABANG = 
                        v_transaksi.ID_CABANG AND 
                        DATE_FORMAT(DTM_CRT,'%b %Y') =  
                        DATE_FORMAT(v_transaksi.TGL_TRANSAKSI,'%b %Y')),0)
                    AS PENGELUARAN  
                    FROM v_transaksi
                    WHERE id_cabang $where 
                    AND tgl_transaksi LIKE '$tgl_tansaksi%' 
                    GROUP BY DATE_FORMAT(TGL_TRANSAKSI,'%Y-%m')
                    ORDER BY DATE_FORMAT(TGL_TRANSAKSI,'%Y%m')";
            }else{
                $sql ="SELECT ID_CABANG
                    ,DATE_FORMAT(TGL_TRANSAKSI,'%Y') AS PERIODE
                    ,DATE_FORMAT(TGL_TRANSAKSI,'%Y') AS TGL_TRANSAKSI
                    ,sum(NET_HARGA) AS NET_HARGA,sum(QTY) AS QTY
                    ,COALESCE((select sum(harga*qty) from tbl_debet where ID_CABANG = 
                        v_transaksi.ID_CABANG AND 
                        DATE_FORMAT(TGL_DEBET,'%Y') =  
                        DATE_FORMAT(v_transaksi.TGL_TRANSAKSI,'%Y')),0) 
                    AS DEBET
                    ,COALESCE((select sum(biaya) from tbl_transaksi_kredit where ID_CABANG = 
                        v_transaksi.ID_CABANG AND 
                        DATE_FORMAT(DTM_CRT,'%Y') =  
                        DATE_FORMAT(v_transaksi.TGL_TRANSAKSI,'%Y')),0) 
                    AS PENGELUARAN 
                    FROM v_transaksi
                    WHERE id_cabang $where
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
            $sql = "SELECT * FROM v_menu where STATUS <> 0";
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
            $sql = "SELECT * FROM v_menu WHERE id_cabang=:id ";
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
                    AND harga = '$harga'
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
                    AND harga = '$harga'
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
                    AND HARGA = '$harga'
                    AND periode LIKE '$periode%' 
                    AND nama_bahan LIKE '$bahan%'";
                }else{
                    $sql = "SELECT 
                    ID_DEBET,
                    ID_BAHAN,
                    NAMA_BAHAN,
                    SATUAN, 
                    SUM(KREDIT)AS KREDIT,
                    SUM(DEBET)AS DEBET,
                    SUM(DEBET)-SUM(KREDIT) 
                    AS SALDO,
                    HARGA 
                    FROM v_saldo_akhir 
                    WHERE id_cabang=:id_cabang  
                    group by ID_BAHAN, HARGA";
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
        $app->post("/daftarCabang/{id_cabang}", function (Request $request, Response $response, $args){
            $id = $args["id_cabang"];
            $cabang = $request->getParsedBody();
            $id_device = $cabang['id_device'];
            $device_name = $cabang['device_name'];

            $sqlDelete = "DELETE FROM `tbl_device` WHERE `tbl_device`.`id_device` = :id_device";
            $stmtDelete = $this->db->prepare($sqlDelete);

            $data_hapus = [
                ":id_device" => $id_device
            ];

            $stmtDelete->execute($data_hapus);



            $sql = "INSERT INTO `tbl_device` (`id_device`, `id_cabang`, `device_name`) VALUES (:id_device, :id_cabang, :device_name);";
            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id_device" => $id_device,
                ":id_cabang" => $id,
                ":device_name" => $device_name
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
        $app->delete("/daftarCabang/{id_cabang}", function (Request $request, Response $response, $args){
            $id = $args["id_cabang"];
            $cabang = $request->getParsedBody();
            $id_device = $cabang['id_device'];

            $sqlDelete = "DELETE FROM `tbl_device` WHERE `tbl_device`.`id_device` = :id_device and `tbl_device`.`id_cabang` = :id_cabang";
            $stmtDelete = $this->db->prepare($sqlDelete);

            $data_hapus = [
                ":id_device" => $id_device,
                ":id_cabang" => $id
            ];

            if($stmtDelete->execute($data_hapus)){
                if ($stmtDelete->rowCount() > 0) {
                    $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'Data berhasil dibapus','CODE'=>200,'DATA'=>null);
                }else{
                    $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'FAILED','CODE'=>500,'DATA'=>$data_hapus);
                }
            }

            $newResponse = $response->withJson($result);
            return $newResponse;
        });
        
        $app->get("/daftarCabang/{menu}", function (Request $request, Response $response, $args){
            $menu = $args["menu"];
            
            $id_device = urldecode($request->getQueryParam("id_device"));
            $sql = "";

            if($menu == 'all'){
                $sql = " SELECT DISTINCT a.ID_CABANG, a.NAMA_CABANG, a.ALAMAT FROM `tbl_cabang` a where a.id_cabang not in (select b.id_cabang from `tbl_device` b where b.id_device = :id_device)";
            }else if($menu == 'store'){
                $sql = "SELECT distinct a.ID_CABANG, a.NAMA_CABANG, a.ALAMAT FROM `tbl_cabang` a join `tbl_device` b on a.id_cabang = b.id_cabang where b.id_device = :id_device";
            }else{
                $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'Not Found','CODE'=>404,'DATA'=>null);
                $newResponse = $response->withJson($result);
                return $newResponse;
            }

            $stmt = $this->db->prepare($sql);
            
            $data = [
                ":id_device" => $id_device
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
        
        $app->get("/getVersion", function (Request $request, Response $response, $args){
            $sql = "SELECT ID_VERSI, CONCAT(MAJOR,'.',MINOR,'.',PATCH) AS VERSION, LINK, DESKRIPSI from tbl_version";
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

         
        $app->get("/getLatestVersion", function (Request $request, Response $response, $args){
            $sql = "SELECT LINK,DESKRIPSI, CONCAT(MAJOR,'.',MINOR,'.',PATCH) AS VERSION FROM `tbl_version` order by major desc, minor desc, patch desc limit 1";
            $stmt = $this->db->prepare($sql);
            if($stmt->execute()){
                if ($stmt->rowCount() > 0) {
                    $data = $stmt->fetch();
                    $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>$data);
                }else{
                    $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'FAILED','CODE'=>500,'DATA'=>null);
                }
            }
            $newResponse = $response->withJson($result);
            return $newResponse;
        });

        $app->post("/addVersion", function (Request $request, Response $response, $args){
            $versi = $request->getParsedBody();
            $major = $versi['major'];
            $minor = $versi['minor'];
            $patch = $versi['patch'];
            $link = $versi['link'];
            $desc = $versi['deskripsi'];

            $sql = "INSERT INTO `tbl_version` (`ID_VERSI`, `MAJOR`, `MINOR`, `PATCH`, `LINK`, `DESKRIPSI`) VALUES (NULL, :major, :minor, :patch, :link, :deskripsi);";
            $stmt = $this->db->prepare($sql);

            $data = [
                ":major" => $major,
                ":minor" => $minor,
                ":patch" => $patch,
                ":link" => $link,
                ":deskripsi" => $desc
            ];

            if($stmt->execute($data)){
                if ($stmt->rowCount() > 0) {
                    $result = array('STATUS' => 'SUCCESS', 'MESSAGE' => 'SUCCESS','CODE'=>200,'DATA'=>$data);
                }else{
                    $result = array('STATUS' => 'FAILED', 'MESSAGE' => 'Gagal untuk menambah versi aplikasi','CODE'=>500,'DATA'=>null);
                }
            }

            $newResponse = $response->withJson($result);
            return $newResponse;
        });

        // NOTIF
        $app->get("/notif", function (Request $request, Response $response, $args){
            $sql = "SELECT * FROM V_NOTIF WHERE MARK = 0";
            $stmt = $this->db->prepare($sql);
            if($stmt->execute()){
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

        $app->post("/notif/{id}", function (Request $request, Response $response, $args){
            $id = $args["id"];
            $new_transaksi_detail = $request->getParsedBody();
            $sql = "UPDATE tbl_notif SET VIEWED=1 WHERE id_notif=:id";
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
        
        $app->post("/notif", function (Request $request, Response $response, $args){
            $sql = "UPDATE tbl_notif SET VIEWED=1, MARK=1";
            $stmt = $this->db->prepare($sql);
            
            if($stmt->execute()){
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
