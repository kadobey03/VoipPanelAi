<?php
namespace App\Controllers;

use App\Helpers\DB;

class PaymentMethodsController {
    private function start(){ if (session_status()===PHP_SESSION_NONE) session_start(); if (!isset($_SESSION['user'])) { \App\Helpers\Url::redirect('/login'); } if (($_SESSION['user']['role']??'')!=='superadmin') { http_response_code(403); echo 'Yetkisiz'; exit; } }

    public function index(){
        $this->start(); $db=DB::conn(); $res=$db->query('SELECT * FROM payment_methods ORDER BY id DESC'); $items=[]; while($r=$res->fetch_assoc()){$items[]=$r;} require __DIR__.'/../Views/payments/index.php';
    }

    public function create(){
        $this->start(); $db=DB::conn(); $ok=null; $error=null;
        if ($_SERVER['REQUEST_METHOD']==='POST'){
            $name=trim($_POST['name']??''); $type=trim($_POST['method_type']??'manual'); $details=trim($_POST['details']??''); $fee_percent=(float)($_POST['fee_percent']??0); $fee_fixed=(float)($_POST['fee_fixed']??0); $active=isset($_POST['active'])?1:0;
            if($name===''){ $error='İsim gerekli'; } else { $stmt=$db->prepare('INSERT INTO payment_methods (name, method_type, details, fee_percent, fee_fixed, active) VALUES (?,?,?,?,?,?)'); $stmt->bind_param('sssddi', $name,$type,$details,$fee_percent,$fee_fixed,$active); $ok=$stmt->execute()?'Kaydedildi':'Kaydedilemedi'; $stmt->close(); }
        }
        require __DIR__.'/../Views/payments/create.php';
    }

    public function edit(){
        $this->start(); $db=DB::conn(); $id=(int)($_GET['id']??0); if(!$id){ \App\Helpers\Url::redirect('/payment-methods'); }
        $ok=null; $error=null;
        if ($_SERVER['REQUEST_METHOD']==='POST'){
            $name=trim($_POST['name']??''); $type=trim($_POST['method_type']??'manual'); $details=trim($_POST['details']??''); $fee_percent=(float)($_POST['fee_percent']??0); $fee_fixed=(float)($_POST['fee_fixed']??0); $active=isset($_POST['active'])?1:0;
            if($name===''){ $error='İsim gerekli'; } else { $stmt=$db->prepare('UPDATE payment_methods SET name=?, method_type=?, details=?, fee_percent=?, fee_fixed=?, active=? WHERE id=?'); $stmt->bind_param('sssddii', $name,$type,$details,$fee_percent,$fee_fixed,$active,$id); $ok=$stmt->execute()?'Güncellendi':'Güncellenemedi'; $stmt->close(); }
        }
        $stmt=$db->prepare('SELECT * FROM payment_methods WHERE id=?'); $stmt->bind_param('i',$id); $stmt->execute(); $item=$stmt->get_result()->fetch_assoc(); $stmt->close();
        require __DIR__.'/../Views/payments/edit.php';
    }

    public function delete(){ $this->start(); $db=DB::conn(); $id=(int)($_POST['id']??0); if($id){ $stmt=$db->prepare('DELETE FROM payment_methods WHERE id=?'); $stmt->bind_param('i',$id); $stmt->execute(); $stmt->close(); } \App\Helpers\Url::redirect('/payment-methods'); }
}

