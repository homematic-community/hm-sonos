<?php
// URL to gen documentation on my (br) system http://192.168.0.5:82/user/PhpDocumentor/

/**
 * PHPSonos.inc.php
 *
 * PHPSonos class originally released as: Sonos PHP Script - Copyright: Michael Maroszek - Version: 1.0, 09.07.2009
 *
 * Wiki: {@link http://www.ip-symcon.de/wiki/PHPSonos PHPSonos IPS-WIKI }
 *
 * @author Michael Maroszek <office@ip-symcon.de> and many others
 * @version see date of edit
 * @package PHPSonos.inc.php
 * @copyright original 1.0 version Michael Maroszek; later additions if not otherwise stated are released to public domain
 */

/**
 * PHPSonos Class
 *
 * PHPSonos Class originally released as: Sonos PHP Script - Copyright: Michael Maroszek - Version: 1.0, 09.07.2009
 *
 * Wiki: {@link http://www.ip-symcon.de/wiki/PHPSonos PHPSonos IPS-WIKI }
 *
 * Die PHPSonos Klasse wurde von Paresy zuerst im Thread Einzelne Sonos Player ansteuern ver�ffentlicht und sp�ter in einer aktualisierten Fassung im Thread PHP Sonos (Klasse zum Ansteuern einzelner Player) als Klasse zur Verf�gung gestellt.
 *
 * Verschiedene IPS Benutzer haben die Klasse inzwischen erweitert oder im zuletzt genannten Thread Hinweise zum Handling ausgetauscht.
 *
 * Paresy released his first version of PHPsonos in this forum thread Einzelne Sonos Player ansteuern. He later build a class and rereleased his routines in this thread: PHP Sonos (Klasse zum Ansteuern einzelner Player).
 *
 * There is a growing community of sonos enthusiasts supporting and developing PHPSONOS - we share knowledge and developements in this thread PHP Sonos (Klasse zum Ansteuern einzelner Player)
 *
 *
 *
 * <br>
 * <b>Changelog:</b><br>
 * <pre>
 *
 * - andre added setter functions
 * - 110108  - br added comments based on UPNP information from devicespy;
 *  also added the function XMLsendPacket to get non filtered answers
 * - 110120 - br added Set and GetLEDState
 * - 110202 - br added GetZoneAttributes
 * - 110202 - br added GetZoneInfo
 * - 110203 - br added gestposinfo TrackURI (contains x-rincon of the zone master if we are slave)
 * - 110206 - br added AddMember(Rincon...) and RemoveMember(Rincon...)
 * - 110207 - br added RamptoVolume
 * - 110208 - br added calculation of Content-Length to some functions
 * - 110318 - br fiddled with Playmode (maybe fixed a bug)
 * - 110318 - br added Get and Set CrossfadeMode
 * - 110318 - br added SaveQueue
 * - 110328 - ta lun added GetPlaylist($value)
 * - 110328 - ta lun added GetImportedPlaylists()
 * - 110328 - ta lun added GetSonosPlaylists()
 * - 110328 - ta lun added GetCurrentPlaylist()
 * - 110328 - br corrected titel to title and other things...
 * - 110328 - br added optional parameter id to SaveQueue
 * - 110406 - br edited Seek to accept UPNP Unit parameter as option (sec. arg is Target then)
 * - 110406 - br edited GetPositionInfo to also reflect UPNP return value names
 * - 110406 - br fixed non valid soap request in seek()
 * - 110406 - br added return of CurrentURI and CurrentUriMetaData to Get (Current File or QUEUE)
            This info is needed to restart a queue, pl or radiostation
 * - 110407 - br consolidated SetRadio, SetQueue and SetAVTransportURI
            the two last now also support MetaData as optional, 2nd parameter; SetRadio supports the name of a radiostation as an optional second parameter
 * - 110529 - br GetMediaInfo now returns Radio station in the array, key is title
 * - 110529 - ta lun added browse functions
 * - 110529 - iamking added GetAlarmTime()
 * - 110529 - ta lun fixed misc bugs and GetAlarmTime() now returns an array
 * - 110530 - br edited Browse() to reflect upnp possibilities
 * - 110601 - br renamed GetAlarmtime to ListAlarms to reflect upnp Names
 * - 110601 - iamking improved ListAlarms - added ProgramURI, ProgramMetaData, PlayMode, Volume, IncludeLinkedZones
 * - 110614 - br added parameters to Browse / http://www.ip-symcon.de/forum/f53/php-sonos-klasse-ansteuern-einzelner-player-7676/index10.html#post120977
 * - 110627 - talun edited GetSonosPlaylists: urlencode file
 * - 110711 - br fixed Problems in SetAVTransportUri
 * - 110821 - br/talun edited Browse to return duration
 * - 110821 - br added GetAudioInputAttributes
 * - 110828 - br added Radiotime Radio Cover for GetPositionInfo (patching�in Raditiome Cover URI)
 * - 110901 - br shifted Radiotime Functions from getpositioninfo to new Function RadiotimeGetNowPlaying (returns array)
 * - 110905 - br fixed Bug in getpositioninfo for getting the actual Track in a playlist (quickfix)
 * - 110907 - br deleted, added and edited comment lines
 * - 110908 - br added phpdoc tags for documentation
 * - 110909 - br found sendpacket commented out in RemoveMember and reenabled the sending now (TEST?!)
 * - 110911 - br added phpdoc tags for documentation
 * - 110915 - br added PhpSonos API key/ partnerid for Intune (usable for ANYONE who uses the PHPSonos class noncommercial or commercial) - thanks to Ben@tunein for advocation and great support!
 *             Drop me a note if you are a PHPSonos developer and need access to documentation of tunein APIs.
 * - 110916 - br added GetCurrentTransportActions() which returns the currently valid actions (nearly always returns all actions "Play, Stop, Pause, Seek, Next, Previous" for me)...
 * - 110916 - br added Get and SetInvisible to make it possible to change visibilty for party mode or other things (kids should not see their player in the night *g)
 * - 110916 - br improved phpdoc tags
 * - 111021 - br added soap parameters to ramptovolume (function did not work anymore without)
 * - 111028 - br added parameters id and parentid to setradio (only very rare use for this)
 * - 111130 - br minor improvements: phpdoc tags
 * - 120205 - PeterChrisben/br improved phpdoc for setplaymode and gettransportsettings
 * </pre>
 *
 * <b>Links:</b><br>
 * {@link http://travelmarx.blogspot.com/2010/06/exploring-sonos-via-upnp.html }<br>
 * {@link http://travelmarx.blogspot.com/2011/01/extracting-sonos-playlist-simple-sonos.html }<br>
 * {@link http://opentools.homeip.net/dev-tools-for-upnp }<br>
 * {@link http://www.ip-symcon.de/forum/f53/php-sonos-klasse-ansteuern-einzelner-player-7676/ }<br>
 * {@link http://your.sonos.i.p:1400/status }<br>
 * {@link http://your.sonos.i.p1400/support/review }<br>
 * {@link http://play.er.i.p:1400/xml/zone_player.xml }<br>
 *
 * @author Michael Maroszek <office@ip-symcon.de> and many others
 * @version see date of edit
 * @package PHPSonos
 * @copyright original 1.0 version Michael Maroszek; later additions if not otherwise stated are released to public domain
 **/


class PHPSonos {
   private $address = "";
   public function __construct( $address ) {
      $this->address = $address;
// Maybe we could integrate/provide some more info here (ZoneAttr / Zoneinfo etc.) ?

   }


/*
 * urn:schemas-upnp-org:device:ZonePlayer:1 
 * http://play.er.i.p:1400/xml/zone_player.xml
 */

/*
 * urn:upnp-org:serviceId:AlarmClock
 *   Not fully implemented
 */
 
/**
 * Returns a list of alarms from sonos device
 *
 *
 * - <b>Device:</b> urn:schemas-upnp-org:device:ZonePlayer:1
 * - <b>WSDL:</b> http://play.er.i.p:1400/xml/zone_player.xml
 * - <b>Service:</b> urn:upnp-org:serviceId:AlarmClock
 *
 * @return Array
 *
 * @link http://www.ip-symcon.de/forum/f53/php-sonos-klasse-ansteuern-einzelner-player-7676/index9.html#post120731 Forum-Post
 */
 public function ListAlarms()
    {

$header='POST /AlarmClock/Control HTTP/1.1
SOAPACTION: "urn:schemas-upnp-org:service:AlarmClock:1#ListAlarms"
CONTENT-TYPE: text/xml; charset="utf-8"
HOST: '.$this->address.':1400';
$xml='<?xml version="1.0" encoding="utf-8"?> <s:Envelope s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"
xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
<s:Body>
<u:AlarmClock xmlns:u="urn:schemas-upnp-org:service:AlarmClock:1"/>
</s:Body>
</s:Envelope>';
$content=$header . '
Content-Length: '. strlen($xml) .'

'. $xml;

                $returnContent = $this->XMLsendPacket($content);
                $returnContent = substr($returnContent, stripos($returnContent, '&lt;'));
        $returnContent = substr($returnContent, 0, strrpos($returnContent, '&gt;') + 4);
        $returnContent = str_replace(array("&lt;", "&gt;", "&quot;", "&amp;", "%3a", "%2f", "%25"), array("<", ">", "\"", "&", ":", "/", "%"), $returnContent);
        $xmlr = new SimpleXMLElement($returnContent);
        $liste = array();
        for($i=0,$size=count($xmlr);$i<$size;$i++)
        {
        $attr = $xmlr->Alarm[$i]->attributes();
            $liste[$i]['ID'] = (string)$attr['ID'];
            $liste[$i]['StartTime'] = (string)$attr['StartTime'];
            $liste[$i]['Duration'] = (string)$attr['Duration'];
            $liste[$i]['Recurrence'] = (string)$attr['Recurrence'];
            $liste[$i]['Enabled'] = (string)$attr['Enabled'];
            $liste[$i]['RoomUUID'] = (string)$attr['RoomUUID'];
            $liste[$i]['ProgramURI'] = (string)$attr['ProgramURI'];
            $liste[$i]['ProgramMetaData'] = (string)$attr['ProgramMetaData'];
            $liste[$i]['PlayMode'] = (string)$attr['PlayMode'];
            $liste[$i]['Volume'] = (string)$attr['Volume'];
            $liste[$i]['IncludeLinkedZones'] = (string)$attr['IncludeLinkedZones'];

        }
        return $liste;
     }
     
 /**
 * Updates an existing alarm
 *
 *
 * - <b>Device:</b> urn:schemas-upnp-org:device:ZonePlayer:1
 * - <b>WSDL:</b> http://play.er.i.p:1400/xml/zone_player.xml
 * - <b>Service:</b> urn:upnp-org:serviceId:AlarmClock
 * - <b>Returns:</b> None
 * - <b>NOTE:</b> fill in
 *
 * @param string $id             Id of the Alarm
 * @param string $startzeit       StartLocalTime
 * @param string $duration       Duration
 * @param string $welchetage       Recurrence 
 * @param string $an             Enabled? (true/false)
 * @param string $roomid         Room UUID
 * @param string $programm       ProgramUri
 * @param string $programmmeta   ProgramMetadata
 * @param string $playmode       PlayMode
 * @param string $volume          Volume
 * @param string $linkedzone       IncludeLinkedZones
 *
 * @return Void
 *
 * @link http://www.ip-symcon.de/forum/f53/php-sonos-klasse-ansteuern-einzelner-player-7676/index9.html#post120710 Forum-post
 */
public function UpdateAlarm($id, $startzeit, $duration, $welchetage, $an, $roomid, $programm, $programmeta, $playmode, $volume, $linkedzone)
{
    $payload = '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">
<s:Body><u:UpdateAlarm xmlns:u="urn:schemas-upnp-org:service:AlarmClock:1">
<ID>'.$id.'</ID>
<StartLocalTime>'.$startzeit.'</StartLocalTime>
<Duration>'.$duration.'</Duration>
<Recurrence>'.$welchetage.'</Recurrence>
<Enabled>'.$an.'</Enabled>
<RoomUUID>'.$roomid.'</RoomUUID>
<ProgramURI>'.htmlspecialchars($programm).'</ProgramURI>
<ProgramMetaData>'.htmlspecialchars($programmeta).'</ProgramMetaData>
<PlayMode>'.$playmode.'</PlayMode>
<Volume>'.$volume.'</Volume>
<IncludeLinkedZones>'.$linkedzone.'</IncludeLinkedZones>
</u:updateAlarm></s:Body></s:Envelope>';


$content='POST /AlarmClock/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: '.strlen($payload).'
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:AlarmClock:1#UpdateAlarm"

'.$payload;

        $this->sendPacket($content);
    }

   
/* urn:upnp-org:serviceId:AudioIn */
   // Not fully implemented
   
 /**
 * Get information of devices inputs
 *
 *
 * - <b>Device:</b> urn:schemas-upnp-org:device:ZonePlayer:1
 * - <b>WSDL:</b> http://play.er.i.p:1400/xml/zone_player.xml
 * - <b>Service:</b> urn:upnp-org:serviceId:AudioIn
 * - <b>Returns:</b> Array
 * - <b>NOTE:</b> fill in
 *
 * @return Array
 *
 * @link http://www.ip-symcon.de/forum/f53/php-sonos-klasse-ansteuern-einzelner-player-7676/index15.html#post131481 Forum-Post
 */
   public function GetAudioInputAttributes() // added br
   {

$header='POST /AudioIn/Control HTTP/1.1
SOAPACTION: "urn:schemas-upnp-org:service:AudioIn:1#GetAudioInputAttributes"
CONTENT-TYPE: text/xml; charset="utf-8"
HOST: '.$this->address.':1400';
$xml='<?xml version="1.0" encoding="utf-8"?>
<s:Envelope s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"
xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
<s:Body>
<u:GetAudioInputAttributes xmlns:u="urn:schemas-upnp-org:service:AudioIn:1"/>
</s:Body>
</s:Envelope>';
$content=$header . '
Content-Length: '. strlen($xml) .'

'. $xml;

$returnContent = $this->XMLsendPacket($content);


      $xmlParser = xml_parser_create("UTF-8");
      xml_parser_set_option($xmlParser, XML_OPTION_TARGET_ENCODING, "ISO-8859-1");
      xml_parse_into_struct($xmlParser, $returnContent, $vals, $index);
      xml_parser_free($xmlParser);



      $AudioInReturn = Array();

      $key="CurrentName"; // Lookfor
      if ( isset($index[strtoupper($key)][0]) and isset($vals[ $index[strtoupper($key)][0] ]['value'])) {$AudioInReturn[$key] = $vals[ $index[strtoupper($key)][0] ]['value'];
      } else { $AudioInReturn[$key] = ""; }

      $key="CurrentIcon"; // Lookfor
      if ( isset($index[strtoupper($key)][0]) and isset($vals[ $index[strtoupper($key)][0] ]['value'])) {$AudioInReturn[$key] = $vals[ $index[strtoupper($key)][0] ]['value'];
      } else { $AudioInReturn[$key] = ""; }


      return $AudioInReturn; //Assoziatives Array
    }
 
   
/* urn:upnp-org:serviceId:DeviceProperties */


 /**
 * Reads Zone Attributes
 *
 *
 * - <b>Device:</b> urn:schemas-upnp-org:device:ZonePlayer:1
 * - <b>WSDL:</b> http://play.er.i.p:1400/xml/zone_player.xml
 * - <b>Service:</b> urn:upnp-org:serviceId:DeviceProperties
 * - <b>Returns:</b> Example:
 * <code> Array
 * (
 *  [CurrentZoneName] => Kxz Boxyz
 *  [CurrentIcon] => x-rincon-roomicon:office
 * )
 * </code>
 * @return Array
 *
 * @link http://www.ip-symcon.de/wiki/PHPSonos Wiki
 *
 **/

   public function GetZoneAttributes() // added br
   {
$header='POST /DeviceProperties/Control HTTP/1.1
SOAPACTION: "urn:schemas-upnp-org:service:DeviceProperties:1#GetZoneAttributes"
CONTENT-TYPE: text/xml; charset="utf-8"
HOST: '.$this->address.':1400';
$xml='<?xml version="1.0" encoding="utf-8"?>
<s:Envelope s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"
xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
<s:Body>
<u:GetZoneAttributes xmlns:u="urn:schemas-upnp-org:service:DeviceProperties:1"/>
</s:Body>
</s:Envelope>';
$content=$header . '
Content-Length: '. strlen($xml) .'

'. $xml;

$returnContent = $this->XMLsendPacket($content);


      $xmlParser = xml_parser_create("UTF-8");
      xml_parser_set_option($xmlParser, XML_OPTION_TARGET_ENCODING, "ISO-8859-1");
      xml_parse_into_struct($xmlParser, $returnContent, $vals, $index);
      xml_parser_free($xmlParser);


      $ZoneAttributes = Array();

      $key="CurrentZoneName"; // Lookfor
      if ( isset($index[strtoupper($key)][0]) and isset($vals[ $index[strtoupper($key)][0] ]['value'])) {$ZoneAttributes[$key] = $vals[ $index[strtoupper($key)][0] ]['value'];
      } else { $ZoneAttributes[$key] = ""; }

      $key="CurrentIcon"; // Lookfor
      if ( isset($index[strtoupper($key)][0]) and isset($vals[ $index[strtoupper($key)][0] ]['value'])) {$ZoneAttributes[$key] = $vals[ $index[strtoupper($key)][0] ]['value'];
      } else { $ZoneAttributes[$key] = ""; }


      return $ZoneAttributes; //Assoziatives Array
    }

 /**
 * Reads Zone Information
 *
 *
 * - <b>Device:</b> urn:schemas-upnp-org:device:ZonePlayer:1
 * - <b>WSDL:</b> http://play.er.i.p:1400/xml/zone_player.xml
 * - <b>Service:</b> urn:upnp-org:serviceId:DeviceProperties
 * - <b>Returns:</b> Example:
 * <code> Array
 * (
 *   [SerialNumber] => 00-zz-58-32-yy-xx:5
 *    [SoftwareVersion] => 15.4-442xx
 *    [DisplaySoftwareVersion] => 3.5.x
 *    [HardwareVersion] => 1.16.3.z-y
 *    [IPAddress] => yyy.168.z.xxx
 *    [MACAddress] => 00:zz:58:32:yy:xx
 *    [CopyrightInfo] => � 2004-2007 Sonos, Inc. All Rights Reserved.
 *    [ExtraInfo] => OTP: 1.1.x(1-yy-3-0.x)
 *)
 * </code>
 *
 * @return Array
 *
 * @link http://www.ip-symcon.de/wiki/PHPSonos Wiki
 */
   public function GetZoneInfo() // added br
   {
$header='POST /DeviceProperties/Control HTTP/1.1
SOAPACTION: "urn:schemas-upnp-org:service:DeviceProperties:1#GetZoneInfo"
CONTENT-TYPE: text/xml; charset="utf-8"
HOST: '.$this->address.':1400';
$xml='<?xml version="1.0" encoding="utf-8"?>
<s:Envelope s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"
xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
<s:Body>
<u:GetZoneInfo xmlns:u="urn:schemas-upnp-org:service:DeviceProperties:1"/>
</s:Body>
</s:Envelope>';
$content=$header . '
Content-Length: '. strlen($xml) .'

'. $xml;

$returnContent = $this->XMLsendPacket($content);


      $xmlParser = xml_parser_create("UTF-8");
      xml_parser_set_option($xmlParser, XML_OPTION_TARGET_ENCODING, "ISO-8859-1"); 
      xml_parse_into_struct($xmlParser, $returnContent, $vals, $index);
      xml_parser_free($xmlParser);


      $ZoneInfo = Array();

      $key="SerialNumber"; // Lookfor
      if ( isset($index[strtoupper($key)][0]) and isset($vals[ $index[strtoupper($key)][0] ]['value'])) {$ZoneInfo[$key] = $vals[ $index[strtoupper($key)][0] ]['value'];
      } else { $ZoneInfo[$key] = ""; }

      $key="SoftwareVersion"; // Lookfor
      if ( isset($index[strtoupper($key)][0]) and isset($vals[ $index[strtoupper($key)][0] ]['value'])) {$ZoneInfo[$key] = $vals[ $index[strtoupper($key)][0] ]['value'];
      } else { $ZoneInfo[$key] = ""; }

      $key="SoftwareVersion"; // Lookfor
      if ( isset($index[strtoupper($key)][0]) and isset($vals[ $index[strtoupper($key)][0] ]['value'])) {$ZoneInfo[$key] = $vals[ $index[strtoupper($key)][0] ]['value'];
      } else { $ZoneInfo[$key] = ""; }

      $key="DisplaySoftwareVersion"; // Lookfor
      if ( isset($index[strtoupper($key)][0]) and isset($vals[ $index[strtoupper($key)][0] ]['value'])) {$ZoneInfo[$key] = $vals[ $index[strtoupper($key)][0] ]['value'];
      } else { $ZoneInfo[$key] = ""; }

      $key="HardwareVersion"; // Lookfor
      if ( isset($index[strtoupper($key)][0]) and isset($vals[ $index[strtoupper($key)][0] ]['value'])) {$ZoneInfo[$key] = $vals[ $index[strtoupper($key)][0] ]['value'];
      } else { $ZoneInfo[$key] = ""; }

      $key="IPAddress"; // Lookfor
      if ( isset($index[strtoupper($key)][0]) and isset($vals[ $index[strtoupper($key)][0] ]['value'])) {$ZoneInfo[$key] = $vals[ $index[strtoupper($key)][0] ]['value'];
      } else { $ZoneInfo[$key] = ""; }


      $key="MACAddress"; // Lookfor
      if ( isset($index[strtoupper($key)][0]) and isset($vals[ $index[strtoupper($key)][0] ]['value'])) {$ZoneInfo[$key] = $vals[ $index[strtoupper($key)][0] ]['value'];
      } else { $ZoneInfo[$key] = ""; }


      $key="CopyrightInfo"; // Lookfor
      if ( isset($index[strtoupper($key)][0]) and isset($vals[ $index[strtoupper($key)][0] ]['value'])) {$ZoneInfo[$key] = $vals[ $index[strtoupper($key)][0] ]['value'];
      } else { $ZoneInfo[$key] = ""; }


      $key="ExtraInfo"; // Lookfor
      if ( isset($index[strtoupper($key)][0]) and isset($vals[ $index[strtoupper($key)][0] ]['value'])) {$ZoneInfo[$key] = $vals[ $index[strtoupper($key)][0] ]['value'];
      } else { $ZoneInfo[$key] = ""; }


      return $ZoneInfo; //Assoziatives Array
    }

 /**
 * Sets the state of the white LED
 *
 *
 * - <b>Device:</b> urn:schemas-upnp-org:device:ZonePlayer:1
 * - <b>WSDL:</b> http://play.er.i.p:1400/xml/zone_player.xml
 * - <b>Service:</b> urn:upnp-org:serviceId:DeviceProperties
 *
 * @param string $state             true||false value or On / Off
 *
 * @return Boolean
 *
 * @link http://www.ip-symcon.de/wiki/PHPSonos Wiki
 */
   public function SetLEDState($state) // added br
   {
   if($state=="On") { $state = "On"; } else
      {   if($state=="Off") { $state = "Off"; } else {
            if($state) { $state = "On"; } else { $state = "Off"; }
         }
      }
      
$content='POST /DeviceProperties/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: 250
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:DeviceProperties:1#SetLEDState"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:SetLEDState xmlns:u="urn:schemas-upnp-org:service:DeviceProperties:1"><DesiredLEDState>' .$state. '</DesiredLEDState><u:SetLEDState></s:Body></s:Envelope>';

      return (bool)$this->sendPacket($content);
   }

 /**
 * Gets the state of the white LED
 *
 *
 * - <b>Device:</b> urn:schemas-upnp-org:device:ZonePlayer:1
 * - <b>WSDL:</b> http://play.er.i.p:1400/xml/zone_player.xml
 * - <b>Service:</b> urn:upnp-org:serviceId:DeviceProperties
 *
 *
 * @return Boolean
 *
 * @link http://www.ip-symcon.de/wiki/PHPSonos Wiki
 */
   public function GetLEDState() // added br
   {

$content='POST /DeviceProperties/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: 250
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:DeviceProperties:1#GetLEDState"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:GetLEDState xmlns:u="urn:schemas-upnp-org:service:DeviceProperties:1"><InstanceID>0</InstanceID><u:GetLEDState></s:Body></s:Envelope>';

      if ($this->sendPacket($content)=="On") { return(true); }else return(false);
   }


 /**
 * Sets ZP to visible or unvisable
 *
 *
 * - <b>Device:</b> urn:schemas-upnp-org:device:ZonePlayer:1
 * - <b>WSDL:</b> http://play.er.i.p:1400/xml/zone_player.xml
 * - <b>Service:</b> urn:upnp-org:serviceId:DeviceProperties
 * - <b>Returns:</b> True or False for invisble status
 * - <b>NOTE:</b> It is highly *NOT* recommended to try this function if you don�t know what it will do. Don�t cry if you miss a Zoneplayer!!
 *
 * @param string $state             integer true||false value or string True/ False
 *
 * @return Boolean
 *
 * @link http://www.ip-symcon.de/wiki/PHPSonos Wiki
 */
   public function SetInvisible($state) // added br 110916
   {
   if($state=="True") { $state = "True"; } else
      {   if($state=="False") { $state = "False"; } else {
            if($state) { $state = "True"; } else { $state = "False"; }
         }
  }

$content='POST /DeviceProperties/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: 250
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:DeviceProperties:1#SetInvisible"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:SetInvisible xmlns:u="urn:schemas-upnp-org:service:DeviceProperties:1"><DesiredInvisible>' .$state. '</DesiredInvisible><u:SetInvisible></s:Body></s:Envelope>';

      return (bool)$this->sendPacket($content);
   }

 /**
 * Gets ZP invisible information
 *
 *
 * - <b>Device:</b> urn:schemas-upnp-org:device:ZonePlayer:1
 * - <b>WSDL:</b> http://play.er.i.p:1400/xml/zone_player.xml
 * - <b>Service:</b> urn:upnp-org:serviceId:DeviceProperties
 * - <b>Returns:</b> True or False for invisble status
 * - <b>NOTE:</b> If you miss a Zoneplayer try this!!
 *
 * @return Boolean
 *
 * @link http://www.ip-symcon.de/wiki/PHPSonos Wiki
 */
   public function GetInvisible() // added br 110916
   {

$content='POST /DeviceProperties/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: 250
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:DeviceProperties:1#GetInvisible"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:GetInvisible xmlns:u="urn:schemas-upnp-org:service:DeviceProperties:1"><InstanceID>0</InstanceID><u:GetInvisible></s:Body></s:Envelope>';

      if ($this->sendPacket($content)=="1") { return(true); }else return(false);
   }



/* urn:upnp-org:serviceId:GroupManagement */


   function SubscribeZPGroupManagement($callback){ // added br
$content='SUBSCRIBE /GroupManagement/Event HTTP/1.1
HOST: '.$this->address.':1400
CALLBACK: <'.$callback.'>
NT: upnp:event
TIMEOUT: Second-300
Content-Length: 0

';
$this->sendPacket($content);
}

 /**
 * Adds a Member to a existing ZoneGroup
 * (a single player is also considered a existing group)
 *
 * - <b>Device:</b> urn:schemas-upnp-org:device:ZonePlayer:1
 * - <b>WSDL:</b> http://play.er.i.p:1400/xml/zone_player.xml
 * - <b>Service:</b>  urn:upnp-org:serviceId:GroupManagement
 * - <b>Returns:</b> array with CurrentTransportsettings and GroupUUIDJoined as keys
 *
 *
 * @param string $MemberID             LocalUUID/ Rincon of Player to add
 *
 * @return Array
 *
 * @link http://www.ip-symcon.de/wiki/PHPSonos Wiki
 */

   public function AddMember($MemberID) // added br
      {

$header='POST /GroupManagement/Control HTTP/1.1
SOAPACTION: "urn:schemas-upnp-org:service:GroupManagement:1#AddMember"
CONTENT-TYPE: text/xml; charset="utf-8"
HOST: '.$this->address.':1400';
$xml='<?xml version="1.0" encoding="utf-8"?><s:Envelope s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
<s:Body><u:AddMember xmlns:u="urn:schemas-upnp-org:service:GroupManagement:1"><MemberID>' . $MemberID . '</MemberID>
</u:AddMember></s:Body></s:Envelope>';
$content=$header . '
Content-Length: '. strlen($xml) .'

'. $xml;


$returnContent = $this->XMLsendPacket($content);

      $xmlParser = xml_parser_create("UTF-8");
      xml_parser_set_option($xmlParser, XML_OPTION_TARGET_ENCODING, "ISO-8859-1");
      xml_parse_into_struct($xmlParser, $returnContent, $vals, $index);
      xml_parser_free($xmlParser);


      $ZoneAttributes = Array();

      $key="CurrentTransportSettings"; // Lookfor
      if ( isset($index[strtoupper($key)][0]) and isset($vals[ $index[strtoupper($key)][0] ]['value'])) {$ZoneAttributes[$key] = $vals[ $index[strtoupper($key)][0] ]['value'];
      } else { $ZoneAttributes[$key] = ""; }

      $key="GroupUUIDJoined"; // Lookfor
      if ( isset($index[strtoupper($key)][0]) and isset($vals[ $index[strtoupper($key)][0] ]['value'])) {$ZoneAttributes[$key] = $vals[ $index[strtoupper($key)][0] ]['value'];
      } else { $ZoneAttributes[$key] = ""; }


      return $ZoneAttributes; //Assoziatives Array
      // set AVtransporturi ist notwendig
    }


 /**
 * Removes a Member from an existing ZoneGroup
 * (a single player is also considered an existing group and the action will result in muting the player)
 *
 * - <b>Device:</b> urn:schemas-upnp-org:device:ZonePlayer:1
 * - <b>WSDL:</b> http://play.er.i.p:1400/xml/zone_player.xml
 * - <b>Service:</b>  urn:upnp-org:serviceId:GroupManagement
 * - <b>Returns:</b>  for now the sendPacketAnswer
 *
 * @param string $MemberID             LocalUUID/ Rincon of Player to remove
 *
 * @return Sring
 *
 * @todo br 20110909   return $this->sendPacket($content);  this Line was commented out; i dont understand why... changed this
 *
 * @link http://www.ip-symcon.de/wiki/PHPSonos Wiki
 */
      public function RemoveMember($MemberID) // added br

      {

$header='POST /GroupManagement/Control HTTP/1.1
SOAPACTION: "urn:schemas-upnp-org:service:GroupManagement:1#RemoveMember"
CONTENT-TYPE: text/xml; charset="utf-8"
HOST: '.$this->address.':1400';
$xml='<?xml version="1.0" encoding="utf-8"?><s:Envelope s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
<s:Body><u:RemoveMember xmlns:u="urn:schemas-upnp-org:service:GroupManagement:1"><MemberID>' . $MemberID . '</MemberID>
</u:RemoveMember></s:Body></s:Envelope>';
$content=$header . '
Content-Length: '. strlen($xml) .'

'. $xml;
  return $this->sendPacket($content); 

    }





/* urn:upnp-org:serviceId:MusicServices */
   // Not implemented
/* urn:upnp-org:serviceId:SystemProperties */
   // Not implemented
/* urn:upnp-org:serviceId:ZoneGroupTopology */
   // Not implemented


/******************* urn:schemas-upnp-org:device:MediaRenderer:1 ***********

***************************************************************************/

/* urn:upnp-org:serviceId:RenderingControl */

 /**
 * Ramps Volume to $volume using $ramp_type ; different algorithms are possible
 *
 * - <b>Device:</b> urn:schemas-upnp-org:device:MediaRenderer:1
 * - <b>WSDL:</b> fill in
 * - <b>Service:</b>  urn:upnp-org:serviceId:RenderingControl
 * - <b>Returns:</b> Function Should return Rampseconds but this is NOT implemented!
 * @todo Function Should return Rampseconds but this is NOT implemented!
 * @param string $ramp_type            Ramp_type<br>
 *   Ramps Volume to $volume using the Method mentioned in $ramp_type as string:<br>
 *   "SLEEP_TIMER_RAMP_TYPE" - mutes and ups Volume per default within 17 seconds to desiredVolume<br>
 *   "ALARM_RAMP_TYPE" -Switches audio off and slowly goes to volume<br>
 *   "AUTOPLAY_RAMP_TYPE" - very fast and smooth; Implemented from Sonos for the autoplay feature.<br>
 *
 * @param string $volume               DesiredVolume
 *
 * @return Void
 *
 *
 * @link http://www.ip-symcon.de/wiki/PHPSonos Wiki
 */
   public function RampToVolume($ramp_type, $volume) //added br // added soap parameters 20111021
   {


$header='POST /MediaRenderer/RenderingControl/Control HTTP/1.1
HOST: '.$this->address.':1400
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:RenderingControl:1#RampToVolume"
';
$xml='<?xml version="1.0" encoding="utf-8"?><s:Envelope s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
<s:Body><u:RampToVolume xmlns:u="urn:schemas-upnp-org:service:RenderingControl:1"><InstanceID>0</InstanceID><Channel>Master</Channel><RampType>'.$ramp_type.'</RampType><DesiredVolume>'.$volume.'</DesiredVolume>
<ResetVolumeAfter>false</ResetVolumeAfter><ProgramURI></ProgramURI>
</u:RampToVolume></s:Body></s:Envelope>';
$content=$header . 'Content-Length: '. strlen($xml) .'

'. $xml;


      return (int) $this->sendPacket($content);

   }
/* urn:upnp-org:serviceId:AVTransport */

 /**
 * TEST Function for MediaRenderAVT Callback and IPS Register Vars
 *
 * - <b>Device:</b> urn:schemas-upnp-org:device:MediaRenderer:1
 * - <b>WSDL:</b> fill in
 * - <b>Service:</b> urn:schemas-upnp-org:service:AVTransport:1 none
 * - <b>Returns:</b> Sendpacket contents
 *
 * @param string $callback             CallbackURL Well gat a HTTP Callback at this URl (SOAP)
 * @return Void
 */

   function SubscribeMRAVTransport($callback){ // added br
$content='SUBSCRIBE /MediaRenderer/AVTransport/Event HTTP/1.1
HOST: '.$this->address.':1400
CALLBACK: <'.$callback.'>
NT: upnp:event
TIMEOUT: Second-300
Content-Length: 0

';
$this->sendPacket($content);
}

 /**
 * Save current queue off to sonos
 *
 * - <b>NOTE:</b> If you don�t set the id to the playlist�s id you want to edit, you�ll get duplicate playlists with the same name $title!!
 * - <b>Device:</b> urn:schemas-upnp-org:device:MediaRenderer:1
 * - <b>WSDL:</b> fill in
 * - <b>Service:</b> urn:schemas-upnp-org:service:AVTransport:1
 * - <b>Returns:</b> Sendpacket contents
 *
 *
 * @param string $title          Title of Playlist
 * @param string $id             Playlists ID (optional)
 *
 * @return string
 *
 * @link http://www.ip-symcon.de/wiki/PHPSonos Wiki
 */
    public function SaveQueue($title,$id="") // added br
    {

        $header='POST /MediaRenderer/AVTransport/Control HTTP/1.1
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#SaveQueue"
CONTENT-TYPE: text/xml; charset="utf-8"
HOST: '.$this->address.':1400';
$xml='<?xml version="1.0" encoding="utf-8"?>
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body>
<u:SaveQueue xmlns:u="urn:schemas-upnp-org:service:AVTransport:1"><InstanceID>0</InstanceID><Title>'.$title.'</Title><ObjectID>'.$id.'</ObjectID></u:SaveQueue>
</s:Body>
</s:Envelope>';
$content=$header . '
Content-Length: '. strlen($xml) .'

'. $xml;

    $returnContent = $this->sendPacket($content);
   }

 /**
 * Get info on actual crossfademode
 *
 * - <b>Device:</b> urn:schemas-upnp-org:device:MediaRenderer:1
 * - <b>WSDL:</b> fill in
 * - <b>Service:</b> urn:schemas-upnp-org:service:AVTransport:1
 * - <b>Returns:</b> Boolean
 *
 *
 * @return Boolean
 */
   public function GetCrossfadeMode() // added br
   {

$header='POST /MediaRenderer/AVTransport/Control HTTP/1.1
HOST: '.$this->address.':1400
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#GetCrossfadeMode"
';
$xml='<?xml version="1.0" encoding="utf-8"?><s:Envelope s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
<s:Body><u:GetCrossfadeMode xmlns:u="urn:schemas-upnp-org:service:AVTransport:1"><InstanceID>0</InstanceID>
</u:GetCrossfadeMode></s:Body></s:Envelope>';
$content=$header . 'Content-Length: '. strlen($xml) .'

'. $xml;

      return (bool)$this->sendPacket($content);
   }

 /**
 * Set crossfade to true or false
 *
 * - <b>Device:</b> urn:schemas-upnp-org:device:MediaRenderer:1
 * - <b>WSDL:</b> fill in
 * - <b>Service:</b> urn:schemas-upnp-org:service:AVTransport:1
 * - <b>Returns:</b> Void; shoud return sendpacket return
 *
 * @param string $mode          Enable/ Disable = 1/0 (string) = true /false (boolean)
 *
 * @return Void
 *
 * @link http://www.ip-symcon.de/wiki/PHPSonos Wiki
 */
   public function SetCrossfadeMode($mode) // added br
   {


      if($mode) { $mode = "1"; } else { $mode = "0"; }
$header='POST /MediaRenderer/AVTransport/Control HTTP/1.1
HOST: '.$this->address.':1400
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#SetCrossfadeMode"
';
$xml='<?xml version="1.0" encoding="utf-8"?><s:Envelope s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/" xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
<s:Body><u:SetCrossfadeMode xmlns:u="urn:schemas-upnp-org:service:AVTransport:1"><InstanceID>0</InstanceID><CrossfadeMode>'.$mode.'</CrossfadeMode></u:SetCrossfadeMode></u:SetCrossfadeMode></s:Body></s:Envelope>';
$content=$header . 'Content-Length: '. strlen($xml) .'

'. $xml;

   $this->sendPacket($content);
      

   }
 /**
 * STOP Stops playback
 *
 * - <b>NOTE:</b> It is sometimes necessary to send a stop after removing a zone from a group
 * - <b>Device:</b> urn:schemas-upnp-org:device:MediaRenderer:1
 * - <b>WSDL:</b> fill in
 * - <b>Service:</b> urn:schemas-upnp-org:service:AVTransport:1
 * - <b>Returns:</b> Void
 * @todo return should be sendpacket contents
 *
 * @return Void
 */
   public function Stop()
   {
$content='POST /MediaRenderer/AVTransport/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: 250
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#Stop"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:Stop xmlns:u="urn:schemas-upnp-org:service:AVTransport:1"><InstanceID>0</InstanceID></u:Stop></s:Body></s:Envelope>';

      $this->sendPacket($content);
   }


/**
 * PAUSE pauses playback
 *
 * - <b>NOTE:</b> It is NOT always possible to send a PAUSE command (so you may get an error)!!
 * Please look at the Soap Method GetCurrentTransportActions (which returns valid actions)
 * - <b>Device:</b> urn:schemas-upnp-org:device:MediaRenderer:1
 * - <b>WSDL:</b> fill in
 * - <b>Service:</b> urn:schemas-upnp-org:service:AVTransport:1
 * - <b>Returns:</b> Void
 * @todo return should be sendpacket contents
 *
 * @return Void
 */
   public function Pause()
   {
$content='POST /MediaRenderer/AVTransport/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: 252
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#Pause"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:Pause xmlns:u="urn:schemas-upnp-org:service:AVTransport:1"><InstanceID>0</InstanceID></u:Pause></s:Body></s:Envelope>';

      $this->sendPacket($content);
   }

/**
 * PLAY plays or continues playback
 *
 * - <b>NOTE:</b> It is sometimes necessary to send a play after messing with zonegroups and/or starting a new play on a new uri
 * Please look at the Soap Method GetCurrentTransportActions (which returns valid actions)
 * - <b>Device:</b> urn:schemas-upnp-org:device:MediaRenderer:1
 * - <b>WSDL:</b> fill in
 * - <b>Service:</b> urn:schemas-upnp-org:service:AVTransport:1
 * - <b>Returns:</b> Void; shoud be sendpacket contents
 *
 * @return Void
 */
   public function Play()
   {

$content='POST /MediaRenderer/AVTransport/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: 266
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#Play"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:Play xmlns:u="urn:schemas-upnp-org:service:AVTransport:1"><InstanceID>0</InstanceID><Speed>1</Speed></u:Play></s:Body></s:Envelope>';

      $this->sendPacket($content);
   }
   
/**
 * NEXT
 *
 * - <b>NOTE:</b>  Please look at the Soap Method GetCurrentTransportActions (which returns valid actions)
 * - <b>Device:</b> urn:schemas-upnp-org:device:MediaRenderer:1
 * - <b>WSDL:</b> fill in
 * - <b>Service:</b> urn:schemas-upnp-org:service:AVTransport:1
 * - <b>Returns:</b> Void; shoud be sendpacket contents
 *
 * @return Void
 */
   public function Next()
   {
   
$content='POST /MediaRenderer/AVTransport/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: 250
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#Next"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:Next xmlns:u="urn:schemas-upnp-org:service:AVTransport:1"><InstanceID>0</InstanceID></u:Next></s:Body></s:Envelope>';

      $this->sendPacket($content);
   }
   
/**
 * PREVIOUS
 *
 * - <b>NOTE:</b>  Please look at the Soap Method GetCurrentTransportActions (which returns valid actions)
 * - <b>Device:</b> urn:schemas-upnp-org:device:MediaRenderer:1
 * - <b>WSDL:</b> fill in
 * - <b>Service:</b> urn:schemas-upnp-org:service:AVTransport:1
 * - <b>Returns:</b> Void; shoud be sendpacket contents
 *
 * @return Void
 */
   public function Previous()
   {
   
$content='POST /MediaRenderer/AVTransport/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: 258
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#Previous"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:Previous xmlns:u="urn:schemas-upnp-org:service:AVTransport:1"><InstanceID>0</InstanceID></u:Previous></s:Body></s:Envelope>';

      $this->sendPacket($content);
   }
   
/**
 * SEEK
 *
 * - <b>NOTE:</b>  Please look at the Soap Method GetCurrentTransportActions (which returns valid actions)
 * - <b>Device:</b> urn:schemas-upnp-org:device:MediaRenderer:1
 * - <b>WSDL:</b> fill in
 * - <b>Service:</b> urn:schemas-upnp-org:service:AVTransport:1
 * - <b>Returns:</b> String; shoud be sendpacket contents as array
 *
 * @param string $arg1           Unit ("TRACK_NR" || "REL_TIME" || "SECTION")
 * @param string $arg2             Target (if this Arg is not set Arg1 is considered to be "REL_TIME and the real arg1 value is set as arg2 value)
 *
 * @return String
 */
   public function Seek($arg1,$arg2="NONE")
   {
// Abw�rtskompatibel zu Paresys Original sein // edited by br
   if ($arg2=="NONE"){
      $Unit="REL_TIME"; $position=$arg1;
   } else {$Unit=$arg1; $position=$arg2;}

 $header='POST /MediaRenderer/AVTransport/Control HTTP/1.1
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#Seek"
CONTENT-TYPE: text/xml; charset="utf-8"
CONNECTION: close
HOST: '.$this->address.':1400';
$xml='<?xml version="1.0" encoding="utf-8"?>
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:Seek xmlns:u="urn:schemas-upnp-org:service:AVTransport:1"><InstanceID>0</InstanceID><Unit>'. $Unit .'</Unit><Target>'.$position.'</Target></u:Seek></s:Envelope></s:Body></s:Envelope>';
$content=$header . '
Content-Length: '. strlen($xml) .'

'. $xml;

    $returnContent = $this->sendPacket($content);



   }
   
/**
 * REWIND
 *
 * - <b>Device:</b> urn:schemas-upnp-org:device:MediaRenderer:1
 * - <b>WSDL:</b> fill in
 * - <b>Service:</b> urn:schemas-upnp-org:service:AVTransport:1
 * - <b>Returns:</b> String
 * @todo should be sendpacket Return
 *
 * - <b>SOAP</b> this Functions calls seek REL_TIME with target set to 00:00:00
 * There is a also a function called previous.
 *
 * @return String
 */
   public function Rewind()
   {
   
$content='POST /MediaRenderer/AVTransport/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: 296
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#Seek"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:Seek xmlns:u="urn:schemas-upnp-org:service:AVTransport:1"><InstanceID>0</InstanceID><Unit>REL_TIME</Unit><Target>00:00:00</Target></u:Seek></s:Body></s:Envelope>';

      $this->sendPacket($content);
   }
   
/**
 * Sets volume for a player
 *
 * - <b>Device:</b> urn:schemas-upnp-org:device:MediaRenderer:1
 * - <b>WSDL:</b> fill in
 * - <b>Service:</b> urn:schemas-upnp-org:service:AVTransport:1
 * - <b>Returns:</b> String; sendpacket Return
 *
 *
 * @param string $volume          Volume in percent
 *
 * @return String
 */
   public function SetVolume($volume)
   {

$content='POST /MediaRenderer/RenderingControl/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: 32'.strlen($volume).'
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:RenderingControl:1#SetVolume"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:SetVolume xmlns:u="urn:schemas-upnp-org:service:RenderingControl:1"><InstanceID>0</InstanceID><Channel>Master</Channel><DesiredVolume>'.$volume.'</DesiredVolume></u:SetVolume></s:Body></s:Envelope>';

      $this->sendPacket($content);
   }
   
/**
 * Gets current volume information from player
 *
 * - <b>Device:</b> urn:schemas-upnp-org:device:MediaRenderer:1
 * - <b>WSDL:</b> fill in
 * - <b>Service:</b> urn:schemas-upnp-org:service:AVTransport:1
 * - <b>Returns:</b> String; sendpacket Return
 *
 * @return String
 */
   public function GetVolume()
   {

$content='POST /MediaRenderer/RenderingControl/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: 290
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:RenderingControl:1#GetVolume"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:GetVolume xmlns:u="urn:schemas-upnp-org:service:RenderingControl:1"><InstanceID>0</InstanceID><Channel>Master</Channel></u:GetVolume></s:Body></s:Envelope>';

      return (int)$this->sendPacket($content);
   }
   
/**
 * Sets mute/ unmute for a player
 *
 * - <b>Device:</b> urn:schemas-upnp-org:device:MediaRenderer:1
 * - <b>WSDL:</b> fill in
 * - <b>Service:</b> urn:schemas-upnp-org:service:AVTransport:1
 * - <b>Returns:</b> String; sendpacket Return
 *
 * @param string $mute           Mute unmute as (boolean)true/false or (string)1/0
 *
 * @return String
 */
   public function SetMute($mute)
   {

      if($mute) { $mute = "1"; } else { $mute = "0"; }

$content='POST /MediaRenderer/RenderingControl/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: 314
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:RenderingControl:1#SetMute"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:SetMute xmlns:u="urn:schemas-upnp-org:service:RenderingControl:1"><InstanceID>0</InstanceID><Channel>Master</Channel><DesiredMute>'.$mute.'</DesiredMute></u:SetMute></s:Body></s:Envelope>';

      $this->sendPacket($content);
   }
   
/**
 * Gets mute/ unmute status for a player
 *
 * - <b>Device:</b> urn:schemas-upnp-org:device:MediaRenderer:1
 * - <b>WSDL:</b> fill in
 * - <b>Service:</b> urn:schemas-upnp-org:service:AVTransport:1
 * - <b>Returns:</b> String; sendpacket Return
 *
 * @return string
 */

   public function GetMute()
   {

$content='POST /MediaRenderer/RenderingControl/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: 286
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:RenderingControl:1#GetMute"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:GetMute xmlns:u="urn:schemas-upnp-org:service:RenderingControl:1"><InstanceID>0</InstanceID><Channel>Master</Channel></u:GetMute></s:Body></s:Envelope>';

      return (bool)$this->sendPacket($content);
   }
   

/**
 * Sets Playmode for a renderer (could affect more than one zone!)
 *
 * - <b>Device:</b> urn:schemas-upnp-org:device:MediaRenderer:1
 * - <b>WSDL:</b> fill in
 * - <b>Service:</b> urn:schemas-upnp-org:service:AVTransport:1
 * - <b>Returns:</b> String; sendpacket Return
 * - <b>NOTE:</b>
 * <PRE>
 * NORMAL = SHUFFLE and REPEAT -->FALSE
 * REPEAT_ALL = REPEAT --> TRUE, Shuffle --> FALSE
 * SHUFFLE_NOREPEAT = SHUFFLE -->TRUE / REPEAT = FALSE
 * SHUFFLE = SHUFFLE and REPEAT -->TRUE </PRE>
 *
 * @param string $mode "NORMAL" || "REPEAT_ALL" || "SHUFFLE_NOREPEAT" || "SHUFFLE"
 *
 * @return String
 */
   public function SetPlayMode($mode)
   {

$content='POST /MediaRenderer/AVTransport/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: '.(291+strlen($mode)).'
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#SetPlayMode"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:SetPlayMode xmlns:u="urn:schemas-upnp-org:service:AVTransport:1"><InstanceID>0</InstanceID><NewPlayMode>'.$mode.'</NewPlayMode></u:SetPlayMode></s:Body></s:Envelope>';

      $this->sendPacket($content);
   }
   
/**
 * Gets transport settings for a renderer
 *
 * - <b>Device:</b> urn:schemas-upnp-org:device:MediaRenderer:1
 * - <b>WSDL:</b> fill in
 * - <b>Service:</b> urn:schemas-upnp-org:service:AVTransport:1
 * - <b>Returns:</b> Array with "repeat" and "shuffle" as keys and true/false as value
 * - <b>NOTE:</b>
 * <PRE>
 * SOAP return sometimes is PLAYING; I don�t know what this means, maybe only Radio (see the code below).
 *
 * NORMAL = SHUFFLE and REPEAT -->FALSE
 * REPEAT_ALL = REPEAT --> TRUE, Shuffle --> FALSE
 * SHUFFLE_NOREPEAT = SHUFFLE -->TRUE / REPEAT = FALSE
 * SHUFFLE = SHUFFLE and REPEAT -->TRUE </PRE>
 *
 * @todo: what is PLAYING??? TAG_NOTE_BR
 *
 * @return Array
 */
   public function GetTransportSettings()
   {

$content='POST /MediaRenderer/AVTransport/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: 282
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#GetTransportSettings"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:GetTransportSettings xmlns:u="urn:schemas-upnp-org:service:AVTransport:1"><InstanceID>0</InstanceID></u:GetTransportSettings></s:Body></s:Envelope>';

      $returnContent = $this->sendPacket($content);

//   echo "\n===" . $this->address. "====\n" . $returnContent . "\n===\n";

      if (strstr($returnContent, "NORMAL") !== false) {
         return Array (
            "repeat" => false,
            "shuffle" => false
         );
      } elseif (strstr($returnContent, "REPEAT_ALL") !== false) {
         return Array (
            "repeat" => true,
            "shuffle" => false
         );   
      
      } elseif (strstr($returnContent, "SHUFFLE_NOREPEAT") !== false) {
         return Array (
            "repeat" => false,
            "shuffle" => true
         );

      } elseif (strstr($returnContent, "SHUFFLE") !== false) {
         return Array (
            "repeat" => true,
            "shuffle" => true
         );
      }



   /*   } elseif (strstr($returnContent, "PLAYING") !== false) {
         return Array (
            "repeat" => false,
            "shuffle" => true
         );
      } */

   }

/**
 * Gets transport settings for a renderer
 *
 * - <b>Device:</b> urn:schemas-upnp-org:device:MediaRenderer:1
 * - <b>WSDL:</b> fill in
 * - <b>Service:</b> urn:schemas-upnp-org:service:AVTransport:1
 * - <b>Returns:</b> String (comma sep.) with available actions
 * - <b>NOTE:</b>
 *
 * @return String
 */
   public function GetCurrentTransportActions()
   {

$content='POST /MediaRenderer/AVTransport/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: 282
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#GetCurrentTransportActions"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:GetCurrentTransportActions xmlns:u="urn:schemas-upnp-org:service:AVTransport:1"><InstanceID>0</InstanceID></u:GetCurrentTransportActions></s:Body></s:Envelope>';

      $returnContent = $this->sendPacket($content);

//   echo "\n===" . $this->address. "====\n" . $returnContent . "\n===\n";

      $ret=preg_replace("#(.*)<Actions>(.*?)\</Actions>(.*)#is",'$2',$returnContent);
      return $ret;

   }


/**
 * Gets transport settings for a renderer
 *
 * - <b>Device:</b> urn:schemas-upnp-org:device:MediaRenderer:1
 * - <b>WSDL:</b> fill in
 * - <b>Service:</b> urn:schemas-upnp-org:service:AVTransport:1
 * - <b>Returns:</b> Array with "repeat" and "shuffle" as keys and true/false as value
 * - <b>NOTE:</b> SOAP return sometimes is PLAYING; I don�t know what this means, maybe only Radio (see the code below).
 *
 * @return Array
 */
   public function GetTransportInfo()
   {

$content='POST /MediaRenderer/AVTransport/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: 274
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#GetTransportInfo"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:GetTransportInfo xmlns:u="urn:schemas-upnp-org:service:AVTransport:1"><InstanceID>0</InstanceID></u:GetTransportInfo></s:Body></s:Envelope>';

      $returnContent = $this->sendPacket($content);
      
      if (strstr($returnContent, "PLAYING") !== false) {
         return 1;
      } elseif (strstr($returnContent, "PAUSED_PLAYBACK") !== false) {
         return 2;
      } elseif (strstr($returnContent, "STOPPED") !== false) {
         return 3;
      }
      
   }

/**
 * Gets Media Info
 *
 * - <b>Device:</b> urn:schemas-upnp-org:device:MediaRenderer:1
 * - <b>WSDL:</b> fill in
 * - <b>Service:</b> urn:schemas-upnp-org:service:AVTransport:1
 * - <b>Returns:</b> 
 * <code>
 * Array    (
 * [CurrentURI] => http://192.168.0.2:10243/WMPNSSv4/1458092455/0_ezg1ODYxQzMwLTEyNzgtNDc0Ri05MkQ0LTQxNzE1MDQ0MjMyMX0uMC40.mp3
 * [CurrentURIMetaData] => <DIDL-Lite xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:upnp="urn:schemas-upnp-org:metadata-1-0/upnp/" xmlns="urn:schemas-upnp-org:metadata-1-0/DIDL-Lite/">    <item id="{85861C30-1278-474F-92D4-417150442321}.0.4" restricted="0" parentID="4">        <dc:title>Car Crazy Cutie</dc:title>        <dc:creator>Beach Boys</dc:creator>        <res size="2753092" duration="0:02:50.000" bitrate="16000" protocolInfo="http-get:*:audio/mpeg:DLNA.ORG_OP=01;DLNA.ORG_FLAGS=01500000000000000000000000000000" sampleFrequency="44100" bitsPerSample="16" nrAudioChannels="2" microsoft:codec="{00000055-0000-0010-8000-00AA00389B71}" xmlns:microsoft="urn:schemas-microsoft-com:WMPNSS-1-0/">http://192.168.0.2:10243/WMPNSSv4/1458092455/0_ezg1ODYxQzMwLTEyNzgtNDc0Ri05MkQ0LTQxNzE1MDQ0MjMyMX0uMC40.mp3</res>        <res duration="0:02:50.000" bitrate="16000" protocolInfo="http-get:*:audio/mpeg:DLNA.ORG_PN=MP3;DLNA.ORG_OP=10;DLNA.ORG_CI=1;DLNA.ORG_FLAGS=01500000000000000000000000000000" sampleFrequency="44100" nrAudioChannels="1" microso
 * [title] => Car Crazy Cutie                         )
 *  </code>
 *
 * - <b>NOTE:</b> SOAP returns CurrentURIMetaData this has to be parsed 
 *
 * @return Array
 */

      public function GetMediaInfo()
   {

$content='POST /MediaRenderer/AVTransport/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: 266
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#GetMediaInfo"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:GetMediaInfo xmlns:u="urn:schemas-upnp-org:service:AVTransport:1"><InstanceID>0</InstanceID></u:GetMediaInfo></s:Body></s:Envelope>';

      $returnContent = $this->XMLsendPacket($content);

      $xmlParser = xml_parser_create("UTF-8");
      xml_parser_set_option($xmlParser, XML_OPTION_TARGET_ENCODING, "ISO-8859-1");
      xml_parse_into_struct($xmlParser, $returnContent, $vals, $index);
      xml_parser_free($xmlParser);

      $mediaInfo = Array();
   


      if (isset($vals[$index["CURRENTURI"][0]]["value"])) {
         $mediaInfo["CurrentURI"] = $vals[$index["CURRENTURI"][0]]["value"];
      } else {
         $mediaInfo["CurrentURI"] = "";
      }

      if (isset($vals[$index["CURRENTURIMETADATA"][0]]["value"])) {
         $mediaInfo["CurrentURIMetaData"] = $vals[$index["CURRENTURIMETADATA"][0]]["value"];
         
      // print_r($index);
      // print_r($vals);


                  $xmlParser = xml_parser_create("UTF-8");
                  xml_parser_set_option($xmlParser, XML_OPTION_TARGET_ENCODING, "ISO-8859-1");
                  xml_parse_into_struct($xmlParser, $mediaInfo["CurrentURIMetaData"], $vals, $index);
                  xml_parser_free($xmlParser);

    //print_r($index);
    //print_r($vals);

                  if (isset($index["DC:TITLE"]) and isset($vals[$index["DC:TITLE"][0]]["value"])) {
                     $mediaInfo["title"] = $vals[$index["DC:TITLE"][0]]["value"];
                  } else {
                     $mediaInfo["title"] = "";
                  }

      } else {
         $mediaInfo["CurrentURIMetaData"] = "";
      }
      return $mediaInfo;
   }

/**
 * Gets position info
 *
 * - <b>NOTE:</b> this is one of the most interesting and complex functions with most interesting informations! You may get radio and zonegroup info out of parsing this information!
 *
 * - <b>Device:</b> urn:schemas-upnp-org:device:MediaRenderer:1
 * - <b>WSDL:</b> fill in
 * - <b>Service:</b> urn:schemas-upnp-org:service:AVTransport:1
 * - <b>Returns:</b> Example: 
 * <code> Array
 *  (
 *    [position] => 0:00:59
 *    [RelTime] => 0:00:59
 *    [duration] => 0:01:51
 *    [TrackDuration] => 0:01:51
 *    [URI] => http://zzz.yyy.0.x:10243/WMPNSSv4/1458092455/0_ezRENTU5NjFDLUE3MDctNDIwRC04NTc4LUFDODgxQTVFMzMxQX0uMC40.mp3
 *    [TrackURI] => http://192.168.0.x:10243/WMPNSSv4/1458092455/0_ezRENTU5NjFDLUE3MDctNDIwRC04NTc4LUFDODgxQTVFMzMxQX0uMC40.mp3
 *    [artist] => Beach Bxxx....
 *    [title] => Cher... What?
 *    [album] => Little Deuce...
 *    [albumArtURI] => http://zzz.168.y.xxx:1400/getaa?u=http://zzz.168.y.xxx:10243/WMPNSSv4/1458092455/0_ezRENTU5NjFDLUE3MDctNDIwRC04NTc4LUFDODgxQTVFMzMxQX0uMC40.mp3&v=279
 *    [albumArtist] => Beach xxx....
 *    [albumTrackNumber] => 5
 *    [streamContent] =>
 *    [trackURI] =>
 *    [Track] => 1
 *  ) </code>
 * 
 * @return Array
 */
   public function GetPositionInfo()
   {
$content='POST /MediaRenderer/AVTransport/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: 272
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#GetPositionInfo"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:GetPositionInfo xmlns:u="urn:schemas-upnp-org:service:AVTransport:1"><InstanceID>0</InstanceID></u:GetPositionInfo></s:Body></s:Envelope>';

      $returnContent = $this->sendPacket($content);
   
      $position = substr($returnContent, stripos($returnContent, "NOT_IMPLEMENTED") - 7, 7);

      $returnContent = substr($returnContent, stripos($returnContent, '&lt;'));
      $returnContent = substr($returnContent, 0, strrpos($returnContent, '&gt;') + 4);
      $returnContent = str_replace(array("&lt;", "&gt;", "&quot;", "&amp;", "%3a", "%2f", "%25"), array("<", ">", "\"", "&", ":", "/", "%"), $returnContent);
      
      
      $xmlParser = xml_parser_create("UTF-8");
      xml_parser_set_option($xmlParser, XML_OPTION_TARGET_ENCODING, "UTF-8");
      xml_parse_into_struct($xmlParser, $returnContent, $vals, $index);
      xml_parser_free($xmlParser);
   
      $positionInfo = Array ();
      
      $positionInfo["position"] = $position;
      $positionInfo["RelTime"] = $position;
      

      if (isset($index["RES"]) and isset($vals[$index["RES"][0]]["attributes"]["DURATION"])) {
         $positionInfo["duration"] = $vals[$index["RES"][0]]["attributes"]["DURATION"];
            $positionInfo["TrackDuration"] = $vals[$index["RES"][0]]["attributes"]["DURATION"];
      } else {
         $positionInfo["duration"] = "";
            $positionInfo["TrackDuration"] = "";
      }

      if (isset($index["RES"]) and isset($vals[$index["RES"][0]]["value"])) {
         $positionInfo["URI"] = $vals[$index["RES"][0]]["value"];
         $positionInfo["TrackURI"] = $vals[$index["RES"][0]]["value"];
      } else {
         $positionInfo["URI"] = "";
         $positionInfo["TrackURI"] = "";
      }
      
      if (isset($index["DC:CREATOR"]) and isset($vals[$index["DC:CREATOR"][0]]["value"])) {
         $positionInfo["artist"] = $vals[$index["DC:CREATOR"][0]]["value"];
      } else {
         $positionInfo["artist"] = "";
      }
      
      if (isset($index["DC:TITLE"]) and isset($vals[$index["DC:TITLE"][0]]["value"])) {
         $positionInfo["title"] = $vals[$index["DC:TITLE"][0]]["value"];
      } else {
         $positionInfo["title"] = "";
      }
      
      if (isset($index["UPNP:ALBUM"]) and isset($vals[$index["UPNP:ALBUM"][0]]["value"])) {
         $positionInfo["album"] = $vals[$index["UPNP:ALBUM"][0]]["value"];
      } else {
         $positionInfo["album"] = "";
      }
      
      if (isset($index["UPNP:ALBUMARTURI"]) and isset($vals[$index["UPNP:ALBUMARTURI"][0]]["value"])) {
         $positionInfo["albumArtURI"] = "http://" . $this->address . ":1400" . $vals[$index["UPNP:ALBUMARTURI"][0]]["value"];
      } else {
      
/*
               // Ask Radiotime (added br as a test)
               $mi=$this->GetMediaInfo();
            
               $station=preg_replace("#(.*)x-sonosapi-stream:(.*?)\?sid(.*)#is",'$2',$mi['CurrentURI']);
            //   echo "\n!!!!!!!!!!!!!!!!!!!!!!!!!!".$station."########\n";
               if (($station!="")and $station[0]=="s"){
                  $content = @file_get_contents("http://opml.radiotime.com/Describe.ashx?c=nowplaying&id=".$station."&partnerId=Sonos&serial=00-0E-58-25-41-12:4");
               //   echo "----". $content;
                  $albumArtURI=preg_replace("#(.*)<LOGO>(.*?)\</LOGO>(.*)#is",'$2',$content);
               //   echo $albumArtURI;
                  $positionInfo["albumArtURI"] = $albumArtURI;
               } else{
               
*/
                     $positionInfo["albumArtURI"] = "";
/*               }
*/
      }

      if (isset($index["R:ALBUMARTIST"]) and isset($vals[$index["R:ALBUMARTIST"][0]]["value"])) {
         $positionInfo["albumArtist"] = $vals[$index["R:ALBUMARTIST"][0]]["value"];
      } else {
         $positionInfo["albumArtist"] = "";
      }
      
      if (isset($index["UPNP:ORIGINALTRACKNUMBER"]) and isset($vals[$index["UPNP:ORIGINALTRACKNUMBER"][0]]["value"])) {
         $positionInfo["albumTrackNumber"] = $vals[$index["UPNP:ORIGINALTRACKNUMBER"][0]]["value"];
      } else {
         $positionInfo["albumTrackNumber"] = "";
      }
      
      if (isset($index["R:STREAMCONTENT"]) and isset($vals[$index["R:STREAMCONTENT"][0]]["value"])) {
         $positionInfo["streamContent"] = $vals[$index["R:STREAMCONTENT"][0]]["value"];
         
         
      } else {
         $positionInfo["streamContent"] = "";
      }
      // added br if this contains "rincon" we are slave to a coordinator mentioned in this field (otherwise path to the file is provided)!
      // implemented via second XMLsendpacket to not break michaels current code

   if (isset($index["RES"][0]) and isset($vals[($index["RES"][0])]["value"])) {
         $positionInfo["trackURI"] = $vals[($index["RES"][0])]["value"];

      } else {
         $returnContent = $this->XMLsendPacket($content);

         $xmlParser = xml_parser_create("UTF-8");
         xml_parser_set_option($xmlParser, XML_OPTION_TARGET_ENCODING, "ISO-8859-1");
         xml_parse_into_struct($xmlParser, $returnContent, $vals, $index);
         xml_parser_free($xmlParser);
     }
    
         if (isset($index["TRACKURI"][0]) and isset($vals[($index["TRACKURI"][0])]["value"])) {
         $positionInfo["trackURI"] = $vals[($index["TRACKURI"][0])]["value"];
         $positionInfo["TrackURI"] = $vals[($index["TRACKURI"][0])]["value"];
         } else {
            $positionInfo["trackURI"] = "";
         }
      
         // Track Number in Playlist
         $returnContent = $this->XMLsendPacket($content);

         $xmlParser = xml_parser_create("UTF-8");
         xml_parser_set_option($xmlParser, XML_OPTION_TARGET_ENCODING, "ISO-8859-1");
         xml_parse_into_struct($xmlParser, $returnContent, $vals, $index);
         xml_parser_free($xmlParser);

         if (isset($index["TRACK"][0]) and isset($vals[($index["TRACK"][0])]["value"])) {
         $positionInfo["Track"] = $vals[($index["TRACK"][0])]["value"];

         } else {
            $positionInfo["Track"] = "";
         }
   
      return $positionInfo;
   }


/**
 * Play Radio station
 *
 * - <b>NOTE:</b> This is only a SetAVTransportURI Wrapper to switch to a radio station
 *
 * - <b>Device:</b> urn:schemas-upnp-org:device:MediaRenderer:1
 * - <b>WSDL:</b> fill in
 * - <b>Service:</b> urn:schemas-upnp-org:service:AVTransport:1
 * - <b>Returns:</b> Array with $radio and $ MetaData as key
 *
 * @param string $radio            radio url
 * @param string $Name            Name of station (optional, default IP-Symcon Radio)
 * @param string $id             ID of Station (optional, default R:0/0/0)
 * @param string $parentID           parentID (optional, default R:0/0)
 * @return array
 */
   public function SetRadio($radio,$Name="IP-Symcon Radio",$id="R:0/0/0",$parentID="R:0/0")
   { 
   $MetaData="&lt;DIDL-Lite xmlns:dc=&quot;http://purl.org/dc/elements/1.1/&quot; xmlns:upnp=&quot;urn:schemas-upnp-org:metadata-1-0/upnp/&quot; xmlns:r=&quot;urn:schemas-rinconnetworks-com:metadata-1-0/&quot; xmlns=&quot;urn:schemas-upnp-org:metadata-1-0/DIDL-Lite/&quot;&gt;&lt;item id=&quot;".$id."&quot; parentID=&quot;".$parentID."&quot; restricted=&quot;true&quot;&gt;&lt;dc:title&gt;".$Name."&lt;/dc:title&gt;&lt;upnp:class&gt;object.item.audioItem.audioBroadcast&lt;/upnp:class&gt;&lt;desc id=&quot;cdudn&quot; nameSpace=&quot;urn:schemas-rinconnetworks-com:metadata-1-0/&quot;&gt;SA_RINCON65031_&lt;/desc&gt;&lt;/item&gt;&lt;/DIDL-Lite&gt;";

    $this->SetAVTransportURI($radio,$MetaData);

   }

/**
 * Sets Av Transport URI
 *
 * - <b>NOTE:</b> Main SOAP method to set play URI - this is the plain SetAVTransportURI
 *
 * - <b>Device:</b> urn:schemas-upnp-org:device:MediaRenderer:1
 * - <b>WSDL:</b> fill in
 * - <b>Service:</b> urn:schemas-upnp-org:service:AVTransport:1
 * - <b>Returns:</b> sendpacket return
 *
 * @param string $tspuri      Transport URI
 * @param string $MetaData    (optional for MetaData)
 *
 * @return String
 */
   public function SetAVTransportURI($tspuri,$MetaData="")
   {

$content='POST /MediaRenderer/AVTransport/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: '.(342+strlen(htmlspecialchars($tspuri))+strlen($MetaData)).'
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#SetAVTransportURI"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:SetAVTransportURI xmlns:u="urn:schemas-upnp-org:service:AVTransport:1"><InstanceID>0</InstanceID><CurrentURI>'.htmlspecialchars($tspuri).'</CurrentURI><CurrentURIMetaData>'.$MetaData.'.</CurrentURIMetaData></u:SetAVTransportURI></s:Body></s:Envelope>';

      $this->sendPacket($content);
   }


/**
 * Sets Queue
 *
 * - <b>NOTE:</b> This is only a Wrapper for setting a queue via SetAVTransportURI
 *
 * - <b>Device:</b> urn:schemas-upnp-org:device:MediaRenderer:1
 * - <b>WSDL:</b> fill in
 * - <b>Service:</b> urn:schemas-upnp-org:service:AVTransport:1
 * - <b>Returns:</b> Void
 * @todo SHOULD return something else
 *
 * @param string $queue      transport URI or Queue
 * @param string $MetaData    (optional for MetaData)
 *
 * @return Void
 */
   public function SetQueue($queue,$MetaData="")
   {
    $this->SetAVTransportURI($queue,$MetaData);

   }

/**
 * Clears devices Queue
 *
 * - <b>NOTE:</b> This function clears the actual playing queue but not the actually selected playlist
 *
 * - <b>Device:</b> urn:schemas-upnp-org:device:MediaRenderer:1
 * - <b>WSDL:</b> fill in
 * - <b>Service:</b> urn:schemas-upnp-org:service:AVTransport:1
 * - <b>Returns:</b> Sendpacket returns
 *
 * @return String
 */
   public function ClearQueue()
   {

$content='POST /MediaRenderer/AVTransport/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: 290
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#RemoveAllTracksFromQueue"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:RemoveAllTracksFromQueue xmlns:u="urn:schemas-upnp-org:service:AVTransport:1"><InstanceID>0</InstanceID></u:RemoveAllTracksFromQueue></s:Body></s:Envelope>';

      $this->sendPacket($content);
   }

/**
 * Adds URI to Queue (not the Playlist!!)
 *
 * - <b>NOTE:</b> Works on queue
 *
 * - <b>Device:</b> urn:schemas-upnp-org:device:MediaRenderer:1
 * - <b>WSDL:</b> fill in
 * - <b>Service:</b> urn:schemas-upnp-org:service:AVTransport:1
 * - <b>Returns:</b> Sendpacket returns
 *
 * @param string $file     Uri or Filename
 *
 * @return String
 */
   public function AddToQueue($file)
   {
   
$content='POST /MediaRenderer/AVTransport/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: '.(438+strlen(htmlspecialchars($file))).'
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#AddURIToQueue"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:AddURIToQueue xmlns:u="urn:schemas-upnp-org:service:AVTransport:1"><InstanceID>0</InstanceID><EnqueuedURI>'.htmlspecialchars($file).'</EnqueuedURI><EnqueuedURIMetaData></EnqueuedURIMetaData><DesiredFirstTrackNumberEnqueued>0</DesiredFirstTrackNumberEnqueued><EnqueueAsNext>1</EnqueueAsNext></u:AddURIToQueue></s:Body></s:Envelope>';

      $this->sendPacket($content);
   }
   
/**
 * Removes track from queue (not the Playlist!!)
 *
 * - <b>NOTE:</b> Works on queue
 *
 * - <b>Device:</b> urn:schemas-upnp-org:device:MediaRenderer:1
 * - <b>WSDL:</b> fill in
 * - <b>Service:</b> urn:schemas-upnp-org:service:AVTransport:1
 * - <b>Returns:</b> Sendpacket returns
 *
 * @param string $track  Tracknumber/id to remove from current sonos queue (!)
 *
 * @return string
 */
   public function RemoveFromQueue($track)
   {

$content='POST /MediaRenderer/AVTransport/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: '.(307+strlen($track)).'
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#RemoveTrackFromQueue"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:RemoveTrackFromQueue xmlns:u="urn:schemas-upnp-org:service:AVTransport:1"><InstanceID>0</InstanceID><ObjectID>Q:0/'.$track.'</ObjectID></u:RemoveTrackFromQueue></s:Body></s:Envelope>';

      $this->sendPacket($content);
   }
   
/**
 * Jumps directly to the track
 *
 * - <b>NOTE:</b> I think I never used this method (br) ... ever used direkt seek call. So note this is only a wrapper!
 *
 * - <b>Device:</b> urn:schemas-upnp-org:device:MediaRenderer:1
 * - <b>WSDL:</b> fill in
 * - <b>Service:</b> urn:schemas-upnp-org:service:AVTransport:1
 * - <b>Returns:</b> Sendpacket returns
 *
 * @param string $track    Number/ID of the track to play in queue
 *
 * @return string
 */
   public function SetTrack($track)
   {
   
$content='POST /MediaRenderer/AVTransport/Control HTTP/1.1
CONNECTION: close
HOST: '.$this->address.':1400
CONTENT-LENGTH: '.(288+strlen($track)).'
CONTENT-TYPE: text/xml; charset="utf-8"
SOAPACTION: "urn:schemas-upnp-org:service:AVTransport:1#Seek"

<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body><u:Seek xmlns:u="urn:schemas-upnp-org:service:AVTransport:1"><InstanceID>0</InstanceID><Unit>TRACK_NR</Unit><Target>'.$track.'</Target></u:Seek></s:Body></s:Envelope>';

      $this->sendPacket($content);
   }
   

/******************* // urn:schemas-upnp-org:device:MediaServer:1 ***********

***************************************************************************/

/* urn:upnp-org:serviceId:ContentDirectory */

/**
 * Returns an array with the songs of the actual sonos queue
 *
 * - <b>NOTE:</b> Wrapper for Browse
 *
 * - <b>Device:</b> urn:schemas-upnp-org:device:MediaServer:1
 * - <b>WSDL:</b> fill in
 * - <b>Service:</b> urn:upnp-org:serviceId:ContentDirectory
 * - <b>Returns:</b> (String) Playlist ID
 *
 * @return String
 */
     public function GetCurrentPlaylist()
    {
        $header='POST /MediaServer/ContentDirectory/Control HTTP/1.1
SOAPACTION: "urn:schemas-upnp-org:service:ContentDirectory:1#Browse"
CONTENT-TYPE: text/xml; charset="utf-8"
HOST: '.$this->address.':1400';
$xml='<?xml version="1.0" encoding="utf-8"?>
<s:Envelope s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"
xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
<s:Body>
<u:Browse xmlns:u="urn:schemas-upnp-org:service:ContentDirectory:1"><ObjectID>Q:0</ObjectID><BrowseFlag>BrowseDirectChildren</BrowseFlag><Filter></Filter><StartingIndex>0</StartingIndex><RequestedCount>1000</RequestedCount><SortCriteria></SortCriteria></u:Browse>
</s:Body>
</s:Envelope>';
$content=$header . '
Content-Length: '. strlen($xml) .'

'. $xml;

    $returnContent = $this->sendPacket($content);

        $returnContent = substr($returnContent, stripos($returnContent, '&lt;'));
        $returnContent = substr($returnContent, 0, strrpos($returnContent, '&gt;') + 4);
        $returnContent = str_replace(array("&lt;", "&gt;", "&quot;", "&amp;", "%3a", "%2f", "%25"), array("<", ">", "\"", "&", ":", "/", "%"), $returnContent);

        $xml = new SimpleXMLElement($returnContent);
        $liste = array();
        for($i=0,$size=count($xml);$i<$size;$i++)
        {
            $aktrow = $xml->item[$i];
            $albumart = $aktrow->xpath("upnp:albumArtURI");
            $title = $aktrow->xpath("dc:title");
            $artist = $aktrow->xpath("dc:creator");
            $album = $aktrow->xpath("upnp:album");
            $liste[$i]['listid']=$i+1;
            if(isset($albumart[0])){
                $liste[$i]['albumArtURI']="http://" . $this->address . ":1400".(string)$albumart[0];
            }else{
                $liste[$i]['albumArtURI'] ="";
            }
            $liste[$i]['title']=(string)$title[0];
            if(isset($artist[0])){
                $liste[$i]['artist']=(string)$artist[0];
            }else{
                $liste[$i]['artist']="";
            }
            if(isset($album[0])){
                $liste[$i]['album']=(string)$album[0];
            }else{
                $liste[$i]['album']="";
            }
        }
return $liste;
}

/**
 * Returns an array with all sonos playlists
 *
 * - <b>NOTE:</b> Wrapper for Browse
 *
 * - <b>Device:</b> urn:schemas-upnp-org:device:MediaServer:1
 * - <b>WSDL:</b> fill in
 * - <b>Service:</b> urn:upnp-org:serviceId:ContentDirectory
 * - <b>Returns:</b> Array with all Sonos Pl
 *
 * @return Array
 */
    public function GetSonosPlaylists()
    {
        $header='POST /MediaServer/ContentDirectory/Control HTTP/1.1
SOAPACTION: "urn:schemas-upnp-org:service:ContentDirectory:1#Browse"
CONTENT-TYPE: text/xml; charset="utf-8"
HOST: '.$this->address.':1400';
$xml='<?xml version="1.0" encoding="utf-8"?>
<s:Envelope s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"
xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
<s:Body>
<u:Browse xmlns:u="urn:schemas-upnp-org:service:ContentDirectory:1"><ObjectID>SQ:</ObjectID><BrowseFlag>BrowseDirectChildren</BrowseFlag><Filter></Filter><StartingIndex>0</StartingIndex><RequestedCount>100</RequestedCount><SortCriteria></SortCriteria></u:Browse>
</s:Body>
</s:Envelope>';
$content=$header . '
Content-Length: '. strlen($xml) .'

'. $xml;

    $returnContent = $this->sendPacket($content);
    $returnContent = substr($returnContent, stripos($returnContent, '&lt;'));
        $returnContent = substr($returnContent, 0, strrpos($returnContent, '&gt;') + 4);
        $returnContent = str_replace(array("&lt;", "&gt;", "&quot;", "&amp;", "%3a", "%2f", "%25"), array("<", ">", "\"", "&", ":", "/", "%"), $returnContent);

        $xml = new SimpleXMLElement($returnContent);
        $liste = array();
        for($i=0,$size=count($xml);$i<$size;$i++)
        {
            $attr = $xml->container[$i]->attributes();
            $liste[$i]['id'] = (string)$attr['id'];
            $title = $xml->container[$i];
            $title = $title->xpath('dc:title');
            $liste[$i]['title'] = (string)$title[0];
            $liste[$i]['typ'] = "Sonos";
           $liste[$i]['file'] = urlencode((string)$xml->container[$i]->res);

        }


return $liste;
    }

/**
 * Returns an array with all imported PL
 *
 * - <b>NOTE:</b> Wrapper for Browse
 *
 * - <b>Device:</b> urn:schemas-upnp-org:device:MediaServer:1
 * - <b>WSDL:</b> fill in
 * - <b>Service:</b> urn:upnp-org:serviceId:ContentDirectory
 * - <b>Returns:</b> Array with all imported PL (Share, Mediaplayer, itunes....)
 *
 * @return Array
 */
    public function GetImportedPlaylists()
    {
        $header='POST /MediaServer/ContentDirectory/Control HTTP/1.1
SOAPACTION: "urn:schemas-upnp-org:service:ContentDirectory:1#Browse"
CONTENT-TYPE: text/xml; charset="utf-8"
HOST: '.$this->address.':1400';
$xml='<?xml version="1.0" encoding="utf-8"?>
<s:Envelope s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"
xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
<s:Body>
<u:Browse xmlns:u="urn:schemas-upnp-org:service:ContentDirectory:1"><ObjectID>A:PLAYLISTS</ObjectID><BrowseFlag>BrowseDirectChildren</BrowseFlag><Filter></Filter><StartingIndex>0</StartingIndex><RequestedCount>100</RequestedCount><SortCriteria></SortCriteria></u:Browse>
</s:Body>
</s:Envelope>';
$content=$header . '
Content-Length: '. strlen($xml) .'

'. $xml;

    $returnContent = $this->sendPacket($content);
    $returnContent = substr($returnContent, stripos($returnContent, '&lt;'));
        $returnContent = substr($returnContent, 0, strrpos($returnContent, '&gt;') + 4);
       $returnContent = str_replace(array("&lt;", "&gt;", "&quot;", "&amp;", "%3a", "%2f", "%25"), array("<", ">", "\"", "&", ":", "/", "%"), $returnContent);

        $xml = new SimpleXMLElement($returnContent);
        $liste = array();
        for($i=0,$size=count($xml);$i<$size;$i++)
        {
            $attr = $xml->container[$i]->attributes();
            $liste[$i]['id'] = (string)$attr['id'];
            $title = $xml->container[$i];
            $title = $title->xpath('dc:title');
            // br substring use cuts my playlist names at the 4th char
         
            $liste[$i]['title'] = (string)$title[0];
               $liste[$i]['title']=preg_replace("/^(.+)\.m3u$/i","\\1",$liste[$i]['title']);
            $liste[$i]['typ'] = "Import";
            $liste[$i]['file'] = (string)$xml->container[$i]->res;
        }


return $liste;
    }

/**
 * Returns an array with all songs of a specific Playlist
 *
 * - <b>NOTE:</b> Wrapper for Browse
 *
 * - <b>Device:</b> urn:schemas-upnp-org:device:MediaServer:1
 * - <b>WSDL:</b> fill in
 * - <b>Service:</b> urn:upnp-org:serviceId:ContentDirectory
 * - <b>Returns:</b> Array with MetaData on the songs
 *
 * @param string $value Id of the playlist
 *
 * @return Array
 */
    public function GetPlaylist($value)
    {
        $header='POST /MediaServer/ContentDirectory/Control HTTP/1.1
SOAPACTION: "urn:schemas-upnp-org:service:ContentDirectory:1#Browse"
CONTENT-TYPE: text/xml; charset="utf-8"
HOST: '.$this->address.':1400';
$xml='<?xml version="1.0" encoding="utf-8"?>
<s:Envelope s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"
xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
<s:Body>
<u:Browse xmlns:u="urn:schemas-upnp-org:service:ContentDirectory:1"><ObjectID>'.$value.'</ObjectID><BrowseFlag>BrowseDirectChildren</BrowseFlag><Filter></Filter><StartingIndex>0</StartingIndex><RequestedCount>1000</RequestedCount><SortCriteria></SortCriteria></u:Browse>
</s:Body>
</s:Envelope>';
$content=$header . '
Content-Length: '. strlen($xml) .'

'. $xml;

    $returnContent = $this->sendPacket($content);
    $xmlParser = xml_parser_create();
        $returnContent = substr($returnContent, stripos($returnContent, '&lt;'));
        $returnContent = substr($returnContent, 0, strrpos($returnContent, '&gt;') + 4);
        $returnContent = str_replace(array("&lt;", "&gt;", "&quot;", "&amp;", "%3a", "%2f", "%25"), array("<", ">", "\"", "&", ":", "/", "%"), $returnContent);

        $xml = new SimpleXMLElement($returnContent);
        $liste = array();
        for($i=0,$size=count($xml);$i<$size;$i++)
        {
            $aktrow = $xml->item[$i];
            $albumart = $aktrow->xpath("upnp:albumArtURI");
            $title = $aktrow->xpath("dc:title");
            $artist = $aktrow->xpath("dc:creator");
            $album = $aktrow->xpath("upnp:album");
            $liste[$i]['listid']=$i+1;
            if(isset($albumart[0])){
                $liste[$i]['albumArtURI']="http://" . $this->address . ":1400".(string)$albumart[0];
            }else{
                $liste[$i]['albumArtURI'] ="";
            }
            $liste[$i]['title']=(string)$title[0];
            if(isset($interpret[0])){
                $liste[$i]['artist']=(string)$artist[0];
            }else{
                $liste[$i]['artist']="";
            }
            if(isset($album[0])){
                $liste[$i]['album']=(string)$album[0];
            }else{
                $liste[$i]['album']="";
            }
        }
return $liste;
    }

/**
 * Universal function to browse ContentDirectory
 *
 * - <b>NOTE:</b> please use upnp and sonos documentation to get an idea of the return
 *
 * - <b>Device:</b> urn:schemas-upnp-org:device:MediaServer:1
 * - <b>WSDL:</b> fill in
 * - <b>Service:</b> urn:upnp-org:serviceId:ContentDirectory
 * - <b>Returns:</b> Array with metadata; please use upnp and sonos documentation to get an idea of the return
 *
 * @param string $value    ObjectID 
 * @param string $meta     BrowseFlag
 * @param string $filter   Filter
 * @param string $sindex   SearchIndex
 * @param string $rcount   ResultCount
 * @param string $sc       SortCriteria
 *
 * @return Array
 */
     public function Browse($value,$meta="BrowseDirectChildren",$filter="",$sindex="0",$rcount="1000",$sc="")
    {

       switch($meta){
       case 'BrowseDirectChildren':
       case 'c':
       case 'child':
         $meta="BrowseDirectChildren";
       break;
       case 'BrowseMetadata':
       case 'm':
       case 'meta':
             $meta = "BrowseMetadata";
       break;
       default:
       return false;
      }
        $header='POST /MediaServer/ContentDirectory/Control HTTP/1.1
SOAPACTION: "urn:schemas-upnp-org:service:ContentDirectory:1#Browse"
CONTENT-TYPE: text/xml; charset="utf-8"
HOST: '.$this->address.':1400';
$xml='<?xml version="1.0" encoding="utf-8"?>
<s:Envelope s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"
xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
<s:Body>
<u:Browse xmlns:u="urn:schemas-upnp-org:service:ContentDirectory:1"><ObjectID>'.htmlspecialchars($value).'</ObjectID><BrowseFlag>'.$meta.'</BrowseFlag><Filter>'.$filter.'</Filter><StartingIndex>'.$sindex.'</StartingIndex><RequestedCount>'.$rcount.'</RequestedCount><SortCriteria>'.$sc.'</SortCriteria></u:Browse>
</s:Body>
</s:Envelope>';
$content=$header . '
Content-Length: '. strlen($xml) .'

'. $xml;

    $returnContent = $this->sendPacket($content);
    $xmlParser = xml_parser_create();
        $returnContent = substr($returnContent, stripos($returnContent, '&lt;'));
        $returnContent = substr($returnContent, 0, strrpos($returnContent, '&gt;') + 4);
        $returnContent = str_replace(array("&lt;", "&gt;", "&quot;", "&amp;", "%3a", "%2f", "%25"), array("<", ">", "\"", "&", ":", "/", "%"), $returnContent);

        $xml = new SimpleXMLElement($returnContent);
        $liste = array();
        for($i=0,$size=count($xml);$i<$size;$i++)
        {
            //Wenn Container vorhanden, dann ist es ein Browse Element
            //Wenn Item vorhanden, dann ist es ein Song.
            if(isset($xml->container[$i])){
                  $aktrow = $xml->container[$i];
                  $attr = $xml->container[$i]->attributes();
                  $liste[$i]['typ'] = "container";
             }else if(isset($xml->item[$i])){
               //Item vorhanden also nur noch Musik
                  $aktrow = $xml->item[$i];
                  $attr = $xml->item[$i]->attributes();
                  $liste[$i]['typ'] = "item";
            }else{
               //Fehler aufgetreten
               return;
            }
                  $id = $attr['id'];
                  $parentid = $attr['parentID'];
                  $albumart = $aktrow->xpath("upnp:albumArtURI");
                  $titel = $aktrow->xpath("dc:title");
                  $interpret = $aktrow->xpath("dc:creator");
                  $album = $aktrow->xpath("upnp:album");
                  if(isset($aktrow->res)){
                     $res = (string)$aktrow->res;
                     $liste[$i]['res'] = urlencode($res);

                   }else{
                      $liste[$i]['res'] = "leer";
                   }
                      $resattr = $aktrow->res->attributes();
                           if(isset($resattr['duration'])){
                         $liste[$i]['duration']=(string)$resattr['duration'];
                      }else{
                         $liste[$i]['duration']="leer";
                      }
                  if(isset($albumart[0])){
                   $liste[$i]['albumArtURI']="http://" . $this->address . ":1400".(string)$albumart[0];
                  }else{
                   $liste[$i]['albumArtURI'] ="leer";
                  }
                  $liste[$i]['title']=(string)$titel[0];
                  if(isset($interpret[0])){
                      $liste[$i]['artist']=(string)$interpret[0];
                  }else{
                     $liste[$i]['artist']="leer";
                  }
                  if(isset($id) && !empty($id)){
                      $liste[$i]['id']=urlencode((string)$id);
                  }else{
                      $liste[$i]['id']="leer";
                  }
                  if(isset($parentid) && !empty($parentid)){
                      $liste[$i]['parentid']=urlencode((string)$parentid);
                  }else{
                      $liste[$i]['parentid']="leer";
                  }
                    if(isset($album[0])){
                   $liste[$i]['album']=(string)$album[0];
                  }else{
                   $liste[$i]['album']="leer";
                  }

        }
return $liste;
    }

/***************************************************************************
            Radiotime / Tunein
***************************************************************************/

/**
 * 
 Get Now Playing information from Radiotime via opml
 *
 * - <b>NOTE:</b> it�s maybe better to use SOAP to get this information
 *
 * - <b>Device:</b>       -
 * - <b>WSDL:</b>       -
 * - <b>Service:</b>    -
 * - <b>Returns:</b> Array with Status, Version info and Logos
 *
 * @return Array
 */
 
// Note: Our partnerId is in here
public function RadiotimeGetNowPlaying() // added br
    {
    $list["version"] = "";
    $list["status"] = "";
    $list["logo"] = "";
    
    // Serial for Tunein is our MAC - prevents block / throttling (maybe we should shift this off)
    $zoneinfo=$this->GetZoneInfo($this->address);
    $serial=$zoneinfo['MACAddress'];

    
               // Get mi
               $mi=$this->GetMediaInfo();
               // Filter out station id
               $station=preg_replace("#(.*)x-sonosapi-stream:(.*?)\?sid(.*)#is",'$2',$mi['CurrentURI']);
         
            
               // Only Ask Radiotime / Tunein for valid stationids (!!)
               if (($station!="")and $station[0]=="s"){
                  // Ask with PHPSonos PartnerID and serial (mac)
                  $content = @file_get_contents("http://opml.radiotime.com/Describe.ashx?c=nowplaying&id=".$station."&partnerId=IAeIhU42&serial=".$serial);
                  // DEBUG DEEP ONLY
                  // echo "----". $content;
                  $list["version"]=preg_replace('#(.*)version="(.*?)\">(.*)#is','$2',$content);
                  $list["status"]=preg_replace('#(.*)<status>(.*?)\</status>(.*)#is','$2',$content);
               
                  
                  $list["outline"]=preg_replace('#(.*)<body>(.*)<outline type="text" text="(.*?)\" guide_id="(.*)\" key#is','$2',$content);
                  
               
                  $list["logo"]=preg_replace('#(.*)<LOGO>(.*?)\</LOGO>(.*)#is','$2',$content);
                  // TAG_DEBUG_DEEP for Intune-Throttling (or blocking!)
                  // echo "\n!!!!!!!!!!!!!!!!!INTUNE REQUEST EXECUTED!!!!!!!!!!!!!!\n";

               }
return $list;
      }


/***************************************************************************
            Helper / sendPacket
***************************************************************************/

/**
 * XMLsendPacket
 *
 * - <b>NOTE:</b> This function does send of a soap query and DOES NOT filter a xml answer
 * - <b>Returns:</b> Answer as XML
 *
 * @return Array
 */
   private function XMLsendPacket( $content )
   {
      $fp = fsockopen($this->address, 1400 /* Port */, $errno, $errstr, 10);
      if (!$fp)
          throw new Exception("Error opening socket: ".$errstr." (".$errno.")");
          
      fputs ($fp, $content);
      $ret = "";
      $buffer = "";
      while (!feof($fp)) {
         $buffer = fgets($fp,128);
      //   echo "\n;" . $buffer . ";\n"; //DEBUG
         $ret.= $buffer;
      }

      // echo "\n\nReturn:" . $ret . "!!\n";
      fclose($fp);

      if(strpos($ret, "200 OK") === false)
         throw new Exception("Error sending command: ".$ret);
      $array = preg_split("/\n/", $ret);
      
      return $array[count($array) - 1];
   }

/**
 * sendPacket - communicate with the device
 *
 * - <b>NOTE:</b> This function does send of a soap query and may filter xml answers
 * - <b>Returns:</b> Answer
 *
 * @return Array
 */
   function getHeaderData($header) { 
        $headerParts = preg_split("/\n/", $header); 
         
        $headerData = array(); 
         
        foreach($headerParts as $headerPart) { 
            $key_value = explode(':',$headerPart); 
            $headerData[trim($key_value[0])] = (isset($key_value[1]) ? trim($key_value[1]): ""); 
        } 
         
        return $headerData; 
    }  
   
   private function sendPacket( $content ) 
    { 
        $fp = fsockopen($this->address, 1400 /* Port */, $errno, $errstr, 10); 
        if (!$fp) 
            throw new Exception("Error opening socket: ".$errstr." (".$errno.")"); 

        fputs ($fp, $content); 

        $ret = ""; 
        while (!feof($fp)) { 
            $ret.= fgetss($fp,128); // filters xml answer 
        } 
        fclose($fp); 
         
        if(strpos($ret, "200 OK") === false) { 
             IPS_LogMessage ( "sendPacket", $ret); 
            throw new Exception("Error sending command: ".$ret); 
        } 
         
        // TAG_DEBUG_DEEP sendpacketdebug 
        //echo "sendPacketDebug: "; //DEBUG 
        //IPS_LogMessage("WHOLE",$ret); 
         
        $parts = preg_split("/\r\n\r\n/", $ret); // split to header und body 
        $header = $parts[0]; 
         
        $headerData = $this->getHeaderData($header); 
         
        if (isset($headerData["TRANSFER-ENCODING"]) && $headerData["TRANSFER-ENCODING"] === "chunked") { 
            $chunked = true;     
        } 
        else { 
            $chunked = false;  
        } 
         
        $body     = $parts[1]; 
         
        $array = preg_split("/\r\n/", $body); // split to header und body 

        if (count($array) > 0) { 
            $index = count($array) - 1; 
        } 
        else { 
            $index = null; 
        } 
         
        for ($i=0; $i < count($array); $i++) { 
            if (strpos($array[$i], '&lt;DIDL') !== false) { 
                $index = $i; 
            } 
            else { 
                if (!ctype_xdigit(trim($array[$i]))) { // is not hex 
                    $array[$index] .= $array[$i]; 
                } 
            } 
        } 
        
        if ($index !== null) { 
                $returnContent = $array[$index]; 
        } 
        else { 
                $returnContent = $body; 
        } 
        return $returnContent; 
    }  

}

?>