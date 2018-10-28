<?php
/**
* @package project
* @author Wizard <sergejey@gmail.com>
* @copyright http://majordomo.smartliving.ru/ (c)
* @version 0.1 (wizard, 13:03:10 [Mar 13, 2016])
*/
//
//
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
  $this->module_category="<#LANG_SECTION_APPLICATIONS#>";
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



if ($this->view_mode=='wake') {
   $this->wake($this->mac);
 }


}
/**
* BackEnd
*
* Module backend
*
* @access public
*/
function admin(&$out) {

}

function wake($mac='D0:50:99:54:24:DD') {


//magicPacket('D0:50:99:54:24:DD');
//$ip_addy = "192.168.1.63";

# Wake on LAN - (c) HotKey@spr.at, upgraded by Murzik
# Modified by Allan Barizo http://www.hackernotcracker.com
flush();

// Port number where the computer is listening. Usually, any number between 1-50000 will do. Normally people choose 7 or 9.
$socket_number = "7";
// MAC Address of the listening computer's network device

// IP address of the listening computer. Input the domain name if you are using a hostname (like when under Dynamic DNS/IP)
//$ip_addy = gethostbyname("myhomeserver.dynamicdns.org");
$ip_addy = "192.168.1.63";

$this->WakeOnLan($ip_addy, $mac,$socket_number);


}


function WakeOnLan($addr, $mac,$socket_number) {
  $addr_byte = explode(':', $mac);
  $hw_addr = '';
  for ($a=0; $a <6; $a++) $hw_addr .= chr(hexdec($addr_byte[$a]));
  $msg = chr(255).chr(255).chr(255).chr(255).chr(255).chr(255);
  for ($a = 1; $a <= 16; $a++) $msg .= $hw_addr;
  // send it to the broadcast address using UDP
  // SQL_BROADCAST option isn't help!!
  $s = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
  if ($s == false) {
    echo "Error creating socket!\n";
    echo "Error code is '".socket_last_error($s)."' - " . socket_strerror(socket_last_error($s));
    return FALSE;
    } else {
    // setting a broadcast option to socket:
    $opt_ret = socket_set_option($s, 1, 6, TRUE);
    if($opt_ret <0) {
      echo "setsockopt() failed, error: " . strerror($opt_ret) . "\n";
      return FALSE;      }
    if(socket_sendto($s, $msg, strlen($msg), 0, $addr, $socket_number)) {
      echo "Magic Packet sent successfully!";
      socket_close($s);
      return TRUE;
      }   else {      echo "Magic packet failed!";
      return FALSE;
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
  parent::dbInstall($data);
 }
 
 function uninstall() {

  parent::uninstall();
 }
 
// --------------------------------------------------------------------
}
/*
*
* TW9kdWxlIGNyZWF0ZWQgTWFyIDEzLCAyMDE2IHVzaW5nIFNlcmdlIEouIHdpemFyZCAoQWN0aXZlVW5pdCBJbmMgd3d3LmFjdGl2ZXVuaXQuY29tKQ==
*
*/
