<?php
class main{

    private $private;
    private $public;

    public function __construct()
    {
        $this->get_keys();
        $result = $this->post_data();
        $this->check_code($result);
        $this->validation_date($result);
    }

    private function get_keys(){
        $this->private = openssl_pkey_get_private(file_get_contents("bfa87fee-private-key.key"));
        $this->public = openssl_pkey_get_public(file_get_contents("bfa87fee-public-key.pub"));
    }

    private function post_data(): string{
    $date = date("YmdHis");
    $url = 'http://payway.bubileg.cz/api/echo';
    $data = ("bfa87fee|" . $date);
    openssl_sign($data, $signature, $this->private);
    $signature = BASE64_ENCODE($signature);
    $postdata = array("merchantId" => "bfa87fee","dttm"=>$date,"signature"=>$signature);
    $ch = curl_init($url);
    curl_setopt( $ch, CURLOPT_POST, 1);
    curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode($postdata, JSON_UNESCAPED_UNICODE));
    curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt( $ch, CURLOPT_HEADER, 0);
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);
    return curl_exec($ch);
    }

    private function check_code($result): void{
        echo("<h1>Kontrola úspěšnosti komunikace se systémem</h1>");
        $data = json_decode($result, true);
        switch ($data["resultCode"])
        {
            case 0:
                echo ("Kód 0, OK <br>");
                break;
            case 1:
                echo ("Kód 1, Nepodporovana operace <br>");
                break;
            case 2:
                echo ("Kód 2, Spatny pocet datovych polozek <br>");
                break;
            case 3:
                echo ("Kód 3, Absence povinne polozky <br>");
                break;
            case 4:
                echo ("Kód 4, Spatny format polozky <br>");
                break;
            case 5:
                echo ("Kód 5, Neexistujicicí nebo zablokovany obchodnik <br>");
                break;
            case 6:
                echo ("Kód 6, Neoverena data <br>");
                break;
            default:
                echo "Error";
        }
    }

    private function validation_date($result): void{
        echo("<h1>Validace přijatých dat od systému</h1>");
        $data = json_decode($result, true);
        $string = $data["resultCode"] . "|" . $data["resultMessage"] . "|" . $data["dttm"];
        if(!openssl_verify($string, BASE64_decode($data["signature"]), $this->public)){
            echo("Validace je v pořádku");
        }else{
            echo("Validace v pořádku není!");
        }
    }
}
$obj = new Main;
?>