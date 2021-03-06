<?php
/**
* @package project
* @author Wizard <sergejey@gmail.com>
* @copyright http://majordomo.smartliving.ru/ (c)
* @version 0.1 (wizard, 13:03:10 [Mar 13, 2016])
*/
//
//
ini_set ('display_errors', 'off');

class wol extends module {
/**
* yandex_tts
*
* Module class constructor
*
* @access private
*/
function wol() {
  $this->name="wol";
  $this->title="WakeOnLan";
  $this->module_category="<#LANG_SECTION_DEVICES#>";
  $this->checkInstalled();
}
/**
* saveParams
*
* Saving module parameters
*
* @access public
*/
function saveParams($data=0) {
 $p=array();
 if (IsSet($this->id)) {
  $p["id"]=$this->id;
 }
 if (IsSet($this->view_mode)) {
  $p["view_mode"]=$this->view_mode;
 }
 if (IsSet($this->edit_mode)) {
  $p["edit_mode"]=$this->edit_mode;
 }
 if (IsSet($this->tab)) {
  $p["tab"]=$this->tab;
 }
 return parent::saveParams($p);
}
/**
* getParams
*
* Getting module parameters from query string
*
* @access public
*/
function getParams() {
  global $id;
  global $mode;
  global $view_mode;
  global $edit_mode;
  global $tab;
  if (isset($id)) {
   $this->id=$id;
  }
  if (isset($mode)) {
   $this->mode=$mode;
  }
  if (isset($view_mode)) {
   $this->view_mode=$view_mode;
  }
  if (isset($edit_mode)) {
   $this->edit_mode=$edit_mode;
  }
  if (isset($tab)) {
   $this->tab=$tab;
  }



}
/**
* Run
*
* Description
*
* @access public
*/
function run() {
 global $session;
  $out=array();
  if ($this->action=='admin') {
   $this->admin($out);
  } else {
   $this->usual($out);
  }
  if (IsSet($this->owner->action)) {
   $out['PARENT_ACTION']=$this->owner->action;
  }
  if (IsSet($this->owner->name)) {
   $out['PARENT_NAME']=$this->owner->name;
  }
  $out['VIEW_MODE']=$this->view_mode;
  $out['EDIT_MODE']=$this->edit_mode;
  $out['MODE']=$this->mode;
  $out['ACTION']=$this->action;
  $out['TAB']=$this->tab;
  $this->data=$out;
  $p=new parser(DIR_TEMPLATES.$this->name."/".$this->name.".html", $this->data, $this);
  $this->result=$p->result;

//echo 'view_mode:'.$this->view_mode;

if ($this->view_mode=='wake') {
//if ($this->type=='button') {
if (!$this->mac) {

//$mac=$this->mac;
global $mac;
//echo  ' mac:'.$mac;
$cmd_rec = SQLSelectOne("SELECT * FROM wol_devices where MAC='$mac'");
if (!$cmd_rec['ID']) 
{
$cmd_rec['MAC']=$mac;
SQLInsert('wol_devices', $cmd_rec);
}
}
else 
$mac=$this->mac;
//{
//$mac=SQLSelectOne("SELECT * FROM wol_devices where MAC='$mac'")['MAC'];
//}

 $this->WakeOnLan('255.255.255.255',$mac);
 $this->WakeOnLan('192.168.255.255',$mac);
 $this->WakeOnLan('192.168.0.255',$mac);
 $this->WakeOnLan('192.168.1.255',$mac);
 }


 if ($this->view_mode=='indata_del') {
   $this->delete($this->id);}	

 if ($this->view_mode=='addtopinghost') {
   $this->add_to_pinghost($this->id);
}	



if ($this->view_mode=='ping') {
  $this->pingall();
}

if ($this->view_mode=='discover') {
  $this->discover();

}

if ($this->view_mode=='nmap') {
  $this->nmap();

}


if ($this->view_mode=='clearall') {
  $this->clearall();

}



}

 function discover() {
//echo php_uname();
//echo PHP_OS;

if (substr(php_uname(),0,5)=='Linux')  {
//echo "это линус";
//$cmd='nmap -sn 192.168.1.0/24';

//$cmd='echo 192.168.1.{1..254}|xargs -n1 -P0 ping -c1|grep "bytes from"';
  
//альтернативный вариант, можно будет его использовать  без установки net-utils  
//cat /proc/net/arp  
$cmd='arp -a';
$answ=shell_exec($cmd);
//echo $answ;
$data2 =preg_split('/\\r\\n?|\\n/',$answ);

for($i=0;$i<count($data2);$i++) {
$name=explode(' ',$data2[$i])[0];
$ipadr=str_replace(')','',str_replace('(','',explode(' ',$data2[$i])[1]));


$mac=explode(' ',$data2[$i])[3];

$vendor=$this->getvendor($mac);


$cmd_rec = SQLSelectOne("SELECT * FROM wol_devices where MAC='$mac'");
$cmd_rec['MAC']=$mac;
$cmd_rec['IPADDR']=$ipadr;
$cmd_rec['TITLE']=$name;
$cmd_rec['VENDOR']=$vendor;


if (!$cmd_rec['ID']) 
{
//$cmd_rec['ONLINE']=$onlinest;
if (strlen($mac)>4) SQLInsert('wol_devices', $cmd_rec);
} else {
SQLUpdate('wol_devices', $cmd_rec);
}
}


} 

else 

 {
//echo "это виндовс";
//$cmd='nmap -sn 192.168.1.0/24';

//$cmd='echo 192.168.1.{1..254}|xargs -n1 -P0 ping -c1|grep "bytes from"';
$cmd='arp -a';
$answ=shell_exec($cmd);
//echo $answ;
$data2 =preg_split('/\\r\\n?|\\n/',$answ);

for($i=0;$i<count($data2);$i++) {
//echo $data2[$i]."<br>";

$ar=explode(' ',$data2[$i]);
//print_r($ar);
//echo "<br>";

$ipadr=$ar[2];
$mac=$ar[12];

//$mac=explode(' ',$data2[$i])[1];

//echo  $ipadr.":".$mac;
//$name=explode(' ',$data2[$i]);

//echo $name.":".$ipadr.":".$mac ."<br>";


$cmd='arp -a';
$answ=shell_exec($cmd);
//echo $answ;


$name=$this->nbt_getName($ipadr);
//$name=$this->nbt_getName('192.168.1.63');
//echo $name;

$vendor=$this->getvendor($mac);



$cmd_rec = SQLSelectOne("SELECT * FROM wol_devices where MAC='$mac'");


echo $mac.":".$vendor;
$cmd_rec['MAC']=$mac;
$cmd_rec['IPADDR']=$ipadr;
$cmd_rec['TITLE']=$name;
$cmd_rec['VENDOR']=$vendor;


if (!$cmd_rec['ID']) 
{
if (strlen($mac)>4) SQLInsert('wol_devices', $cmd_rec);
} else {
SQLUpdate('wol_devices', $cmd_rec);
}


}
}





$this->pingall();
}


 function clearall() {
$cmd_rec = SQLSelect("delete  FROM wol_devices  ");
}

 function pingall() {
$mhdevices=SQLSelect("SELECT * FROM wol_devices");
$total = count($mhdevices);
for ($i = 0; $i < $total; $i++)
{ 
$ip=$mhdevices[$i]['IPADDR'];
$lastping=$mhdevices[$i]['LASTPING'];
//echo time()-$lastping;
if ((!$lastping)||(time()-$lastping>300))

{



$cmd='
$online=ping(processTitle("'.$ip.'"));
if ($online) 
{SQLexec("update wol_devices set ONLINE=1, LASTPING='.time().' where IPADDR=\''.$ip.'\'");} 
else 
{SQLexec("update wol_devices set ONLINE=0, LASTPING='.time().' where IPADDR=\''.$ip.'\'");}

';
 SetTimeOut('wol_devices_ping'.$i,$cmd, '1'); 


}

}
/*


$cmd_rec = SQLSelect("SELECT * FROM wol_devices  ");
foreach ($cmd_rec as $rc) {
//echo $rc['IPADDR'];
$online=ping(processTitle($rc['IPADDR']));
if ($online) {$onlinest="1";} else {$onlinest="0";} 

$cmd_rec['ONLINE']=$onlinest;

SQLUpdate('wol_devices', $cmd_rec);
}
*/

}

 function delete($id) {
  $rec=SQLSelectOne("SELECT * FROM wol_devices WHERE ID='$id'");
  // some action for related tables
  SQLExec("DELETE FROM wol_devices WHERE ID='".$rec['ID']."'");
 }


 function searchdevices(&$out) {

  $this->pingall();
/*
$mhdevices=SQLSelect("SELECT * FROM wol_devices");
$total = count($mhdevices);
for ($i = 0; $i < $total; $i++)
{ 
$ip=$mhdevices[$i]['IPADDR'];
$lastping=$mhdevices[$i]['LASTPING'];
//echo time()-$lastping;
if (time()-$lastping>300) {
$online=ping(processTitle($ip));
    if ($online) 
{SQLexec("update wol_devices set ONLINE='1', LASTPING=".time()." where IPADDR='$ip'");} 
else 
{SQLexec("update wol_devices set ONLINE='0', LASTPING=".time()." where IPADDR='$ip'");}
}}
*/

  require(DIR_MODULES.$this->name.'/search.inc.php');
 }



/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {
 if ($this->view_mode=='mac') {
   global $mac;
//$res=$this->wake($mac);
$res=$this->WakeOnLan("255.255.255.255", $mac);
 $this->WakeOnLan('192.168.255.255',$mac);
 $this->WakeOnLan('192.168.0.255',$mac);
 $this->WakeOnLan('192.168.1.255',$mac);
$out['RESULT']=print_r($res);
}

$this->searchdevices($out);

}


function wakeOnLan($broadcast, $mac)
{

//echo "sending ".$broadcast.':'.$mac."<br>";
    $mac_array = explode(':', $mac);

    $hwaddr = '';

    foreach($mac_array AS $octet)
    {
        $hwaddr .= chr(hexdec($octet));
    }

    // Create Magic Packet

    $packet = '';
    for ($i = 1; $i <= 6; $i++)
    {
        $packet .= chr(255);
    }

    for ($i = 1; $i <= 16; $i++)
    {
        $packet .= $hwaddr;
    }

    $sock = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    if ($sock)
    {
        $options = @socket_set_option($sock, 1, 6, true);

        if ($options >=0) 
        {    
            $e = @socket_sendto($sock, $packet, strlen($packet), 0, $broadcast, 7);
            $e = @socket_sendto($sock, $packet, strlen($packet), 0, $broadcast, 9);
            socket_close($sock);
        }    
    }
}


function wakeOnLan7($ipAddr,$macAddr) {
    // makeMagicPacket
    $magicPacket = "";
    for ($i=0; $i<6; $i++) {
        $magicPacket.= chr(0xff);
    }
    $aryMacAddr = explode(":", $macAddr, 6);
    $buffer = pack("H*H*H*H*H*H*", $aryMacAddr[0], $aryMacAddr[1], $aryMacAddr[2],
    $aryMacAddr[3], $aryMacAddr[4], $aryMacAddr[5]);
    for ($i=0; $i<16; $i++) {
        $magicPacket.= $buffer;
    }
 
    // makeBroadCastAddr
    $aryIpAddr = explode(".", $ipAddr, 4);
    if ($aryIpAddr[0] < 127) {
        $aryIpAddr[1] = "255";
    }
    if ($aryIpAddr[0] < 191) {
        $aryIpAddr[2] = "255";
    }
    if ($aryIpAddr[0] < 223) {
        $aryIpAddr[3] = "255";
    }
    $broadCastAddr = join(".", $aryIpAddr);
    // send
    $fp = fsockopen("udp://".$broadCastAddr, 2304, $errno, $errstr);
    if (!$fp) {
        print("ERROR: $errno - $errstr\n");
    } else {
        fwrite($fp, $magicPacket);
        fwrite($fp, $magicPacket);
        fclose($fp);
    }
 }


function WakeOnLan6($broadcast, $mac_addres){
$package = chr(255).chr(255).chr(255).chr(255).chr(255).chr(255);
$parts_mac = explode(':', $mac_addres);
for ($i=0; $i < 6; $i++) $transform_mac .= chr(hexdec($parts_mac[$i]));
for($i = 1; $i <= 16; $i++) $package .= $transform_mac;
$port=80;
$wol = fsockopen("udp://$broadcast", $port);
fwrite($wol, $package);
fclose($wol);

}



function WakeOnLan3($broadcast, $mac){
$mac_array = preg_split('#:#', $mac); //print_r($mac_array);
$hwaddr = '';
    foreach($mac_array AS $octet){
    $hwaddr .= chr(hexdec($octet));
    }
    //Magic Packet
    $packet = '';
    for ($i = 1; $i <= 6; $i++){
    $packet .= chr(255);
    }
    for ($i = 1; $i <= 16; $i++){
    $packet .= $hwaddr;
    }
    //set up socket
    $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    if ($sock){
    $options = socket_set_option($sock, 1, 6, true);
        if ($options >=0){    
        $e = socket_sendto($sock, $packet, strlen($packet), 0, $broadcast, 7);
        socket_close($sock);
        }    
    }
}  


function WakeOnLan1($addr, $mac,$socket_number) {
  $addr_byte = explode(':', $mac);
  $hw_addr = '';
  for ($a=0; $a <6; $a++) $hw_addr .= chr(hexdec($addr_byte[$a]));
  $msg = chr(255).chr(255).chr(255).chr(255).chr(255).chr(255);
  for ($a = 1; $a <= 16; $a++) $msg .= $hw_addr;
  // send it to the broadcast address using UDP
  // SQL_BROADCAST option isn't help!!
  $s = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
  if ($s == false) {
    $res= "Error creating socket!\n";
    $res.= "Error code is '".socket_last_error($s)."' - " . socket_strerror(socket_last_error($s));
    return $res;
    } else {
    // setting a broadcast option to socket:
    $opt_ret = socket_set_option($s, 1, 6, TRUE);
    if($opt_ret <0) {
    $res= "setsockopt() failed, error: " . strerror($opt_ret) . "\n";

      return $res;
      }
    if(socket_sendto($s, $msg, strlen($msg), 0, $addr, $socket_number)) {

    $res= "Magic Packet sent successfully!";
      socket_close($s);
      return $res;
      }   else {      
    $res= "Magic packet failed!";


      return $res;
      }   }  }


/**
* FrontEnd
*
* Module frontend
*
* @access public
*/
function usual(&$out) {
 $this->admin($out);
}
/**
* Install
*
* Module installation routine
*
* @access private
*/
 function install($data='') {
  parent::install();


 }
 
 function dbInstall($data) {

 $data = <<<EOD
 wol_devices: ID int(10) unsigned NOT NULL auto_increment
 wol_devices: TITLE varchar(100) NOT NULL DEFAULT ''
 wol_devices: MAC varchar(100) NOT NULL DEFAULT ''
 wol_devices: IPADDR varchar(100) NOT NULL DEFAULT ''
 wol_devices: NAME varchar(100) NOT NULL DEFAULT ''
 wol_devices: LASTPING varchar(100) NOT NULL DEFAULT ''
 wol_devices: ONLINE varchar(100) NOT NULL DEFAULT ''
 wol_devices: VENDOR varchar(100) NOT NULL DEFAULT ''
EOD;


  parent::dbInstall($data);
 }
 
 function uninstall() {
SQLExec('DROP TABLE IF EXISTS wol_devices');
  parent::uninstall();
 }




// Sends NBSTAT packet and decodes response
/* Коды ошибок:

  -1 Не удалось получить ответ
   2 Количество секций в ответе не совпадает с ожидаемым
   3 Неверный формат пакета ответа
*/
function nbt_getinfo($ip) {
// Пакет NetBIOS с запросом NBSTAT
    $data = chr(0x81) . chr(0x0c) . chr(0x00) . chr(0x00) . chr(0x00) . chr(0x01) .
        chr(0x00) . chr(0x00) . chr(0x00) . chr(0x00) . chr(0x00) . chr(0x00) .
        chr(0x20) . chr(0x43) . chr(0x4b) . chr(0x41) . chr(0x41) . chr(0x41) .
        chr(0x41) . chr(0x41) . chr(0x41) . chr(0x41) . chr(0x41) . chr(0x41) .
        chr(0x41) . chr(0x41) . chr(0x41) . chr(0x41) . chr(0x41) . chr(0x41) .
        chr(0x41) . chr(0x41) . chr(0x41) . chr(0x41) . chr(0x41) . chr(0x41) .
        chr(0x41) . chr(0x41) . chr(0x41) . chr(0x41) . chr(0x41) . chr(0x41) .
        chr(0x41) . chr(0x41) . chr(0x41) . chr(0x00) . chr(0x00) . chr(0x21) .
        chr(0x00) . chr(0x01);

    $fp = fsockopen("udp://$ip:137");
    fputs($fp, $data);
    stream_set_timeout($fp, 1);
    $response['transaction_id'] = fread($fp, 2);
    if (empty($response['transaction_id']))
        return -1;
    else
    $response['transaction_id'] = $this->word2num($response['transaction_id']);
    $response['flags'] = $this->word2num(fread($fp, 2));
    $response['questions'] = $this->word2num(fread($fp, 2));
    $response['answers'] = $this->word2num(fread($fp, 2));
    $response['authority'] = $this->word2num(fread($fp, 2));
    $response['additional'] = $this->word2num(fread($fp, 2));
    if (!($response['questions'] == 0 && $response['answers'] == 1 &&
        $response['authority'] == 0 && $response['additional'] == 0))
        return 2;

//  Answer section
    $buf = fread($fp, 1);
    if ($buf != chr(0x20))
        return 3;

//  Answer Name
    $response['answer_name'] = '';
    while ($buf != chr(0)) {
        $buf = fread($fp, 1);
        $response['answer_name'] .= $buf;
    }

//  Type (should be NBSTAT)
    $response['answer_type'] = $this->word2num(fread($fp, 2));
    if ($response['answer_type'] != 33)
        return 3;

//  Class (should be 1, but we won't check that)
    $response['answer_class'] = $this->word2num(fread($fp, 2));

//  TTL
    $response['answer_ttl'] = $this->dword2num(fread($fp, 4));

//  Data length
    $response['answer_length'] = $this->word2num(fread($fp, 2));

//  Number of names
    $response['answer_number'] = ord(fread($fp, 1));

//  Getting names
    for ($i = 1; $i <= $response['answer_number']; $i++) {
        $response['answer_value'][$i] = fread($fp, 15);
        $response['answer_type_'][$i] = ord(fread($fp, 1));
        $response['answer_flags'][$i] = $this->word2num(fread($fp, 2));
    }

//  Unit ID (MAC)
    $response['answer_mac'] = fread($fp, 6);

//  There more data follows, but we don't need it, so we can drop it.
    fclose($fp);
    return $response;
}

// Issues nbt_getinfo() and returns target machine NetBIOS from response
function nbt_getName($ip) {
    $response = $this->nbt_getinfo($ip);
    $i = 1;
    foreach ($response['answer_type_'] as $answer_type_) {
        if ($answer_type_ == 0)
            return $response['answer_value'][$i];
        $i++;
    }
}

function word2num($word) {
    return ord($word[1]) + ord($word[0]) * 16;
}

function dword2num($dword) {
    return ord($dword[3]) + ord($dword[2]) * 16 + ord($dword[2]) * 16 * 16 + ord($dword[0]) * 16 * 16 * 16;
}


 function add_to_pinghost($id) {
  if (!$id) {
      $id = ($_GET["id"]);
  }
  $ph=SQLSelectOne("SELECT * FROM wol_devices WHERE ID='".$id."'");
// print_r($ph);
  $pinghosts=array(); // опции добавления
  $pinghosts['TITLE'] = $ph['TITLE'];
  $pinghosts['TYPE'] = '0';
  $pinghosts['OFFLINE_INTERVAL'] = '600';
  $pinghosts['ONLINE_INTERVAL'] = '600';
  $pinghosts['HOSTNAME'] = $ph['IPADDR'];
  $pinghosts['CODE_OFFLINE'] = 'say("Устройство ".$host[\'TITLE\']." пропало из сети, возможно его отключили" ,2);';
  $pinghosts['CODE_ONLINE'] = 'say("Устройство ".$host[\'TITLE\']." появилось в сети." ,2);';
  $pinghosts['LINKED_OBJECT'] = '';
  $pinghosts['LINKED_PROPERTY'] = "alive";
  $pinghosts['CHECK_NEXT'] = date("Y-m-d H:i:s");  
  $chek=SQLSelectOne("SELECT * FROM pinghosts WHERE HOSTNAME='".$ph['IPADDR']."'");
  if ($chek['ID']) {
          $chek['ID'] = SQLUpdate('pinghosts', $pinghosts);
      } else {	
          SQLInsert('pinghosts', $pinghosts);
     }
 }


 function getvendor($mac) {

$url="https://macvendors.co/api/$mac/json";
$file = file_get_contents($url);
$data=json_decode($file,true);
//echo $file;
//echo "<br>";
$vendor=$data['result']['company'];
return $vendor;


}
 
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgTWFyIDEzLCAyMDE2IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/



