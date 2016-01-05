set env(HOME) /usr/local
package require http

proc url-encode {str} {
    set uStr [encoding convertto utf-8 $str]
    set chRE {[^-A-Za-z0-9._~\n]};    # Newline is special case!
    set replacement {%[format "%02X" [scan "\\\0" "%c"]]}
    regsub -all $chRE $uStr $replacement replacement
    return [string map {"\n" "%0A"} [subst $replacement]]
}

proc utf8 {hex} {
    set hex [string map {% {}} $hex]
    return [encoding convertfrom utf-8 [binary format H* $hex]]
}

proc url-decode str {
    # rewrite "+" back to space
    # protect \ from quoting another '\'
    set str [string map [list + { } "\\" "\\\\" "\[" "\\\["] $str]

    # Replace UTF-8 sequences with calls to the utf8 decode proc...
    regsub -all {(%[0-9A-Fa-f0-9]{2})+} $str {[utf8 \0]} str

    # process \u unicode mapped chars
    log $str "w"
    return [subst -novar  $str]
}


set fconfig [file join [file dirname [info script]] settings/sonos.cfg]
set flog sonos.log
set bin [file join [file dirname [info script]] bin]
# namespace Config
# read and write sonos.cfg

namespace eval Cfg {
  namespace export Load
  namespace export Save

  variable FILENAME $fconfig
  # Liste der Player-IPs
  variable sonoszone [list PLayer1 192.168.0.50 PLayer2 192.168.0.51 PLayer3 192.168.0.52 ]
  # Speicherort der Nachrichten zur Durchsage
  variable messagespeicher  "/Temp"
  # Standardlautstärke wenn nichts anderes mit angegeben wurde
  variable stdvolume  10
  # Standardwert für Lautstärkeeinstellen über "Volumeup" & "Volumedown"
  variable volumeup  3;# Lautstärke um 3 % lauter
  variable volumedown  5;# Lautstärke um 5% leiser
  # Standardlautstärke wenn nichts anderes angegeben wurde für message durchsagen
  variable messagevolume  20
  # Einstellungen zu Lautstärke
  # Hier ist es möglich unterschiedliche Arten des Ansteigen der Lautstärke zu definieren
  # z.B. sleep - für den Wecker / Musik morgens, damit dieser langsamm lauter wird
  #
  #  "sleep"  - von aktueller Lautstärke auf die Ziel Lautstärke ändernd, fest eingestellt in 17 Sekunden.
  #  "alarm"  - von 0 auf die Ziel Lautstärke ansteigend.
  #  "auto"      - von 0 auf die Ziel Lautstärke ansteigend, sehr schnell und gleichmäßig.
  #
  variable rampto auto

  # timeout für Soap-Requests
  variable timeout 3
  # radio-list
  variable radio [list  \
      s8007   "OE3 Hitradio 99.9" \
      s8254   "Deluxe Radio" \
      s230507 "Blue Note 101" \
      s58004  "ENERGY Sachsen 96.4" \
      s1346   "MDR 1 RADIO SACHSEN 92.2" \
      s56260  "MDR JUMP 89.0" \
      s56364  "MDR FIGARO 95.4" \
      s55843  "Deutschlandfunk 97.3" \
      s56109  "Deutschlandradio Kultur 101.3" \
      s6814   "Radio Swiss Jazz" \
      s27517  "WBLS 107.5" \
      s80304  "Radio Zuerisee" \
      s25005  "Fritz vom rbb 102.6" \
      s24939  "BBC Radio 1 98.8" \
      s24940  "BBC Radio 2 89.1" \
      s25419  "BBC Radio 4 93.5" \
      s52972  "France Bleu 107.1" \
      s84589  "Radio Dresden 103.5" \
      s71436  "Radio PSR 102.4" \
      s85023  "R.SA 91.2" \
  ]


  proc Load { {error 0} } {
    variable FILENAME
    variable sonoszone
    variable messagespeicher
    variable stdvolume
    variable volumeup
    variable volumedown
    variable messagevolume 
    variable rampto
    variable radio
    variable timeout
    if { $error == "0"} {
      set content [loadFile $FILENAME]
    } { 
      set content ""
    }
    if { $content == "" } {
      set error 1
    } else {
      regsub -all "\r" $content {} content ;#" %>
      set radio {}
      set i 0
      set content [split $content "\n"]
      foreach line $content {
        set line [string trim $line]
        if {[string match  "#*" $line]} {
          #puts "Kommentar: $line"
          if {[string match  "# After this*" $line]} { set i 1 }
        } elseif { $line != ""} {
          incr i
          if {$i == "1"} {
            if { $::tcl_version != "8.2" } {
              set sonoszone [regexp -all -inline {\S+} $line] 
            } else {
              regsub -all {\s+} $line " " sonoszone ;# whitespace zu Space
            }
          } elseif { $i == "2"} {
            array set content_array $line
            if [info exists content_array(messagespeicher)] {
              set messagespeicher $content_array(messagespeicher)
            }
            if [info exists content_array(stdvolume)] {
              set stdvolume $content_array(stdvolume)
            }
            if [info exists content_array(volumeup)] {
              set volumeup $content_array(volumeup)
            }
            if [info exists content_array(volumedown)] {
              set volumedown $content_array(volumedown)
            }
            if [info exists content_array(messagevolume)] {
              set messagevolume $content_array(messagevolume)
            }
            if [info exists content_array(rampto)] {
              set rampto $content_array(rampto)
            }
            if [info exists content_array(timeout)] {
              set timeout $content_array(timeout)
            }
          } else {
            set sid [lindex $line 0]
            set station [string trim [join [lreplace $line 0 0 {}]]]
            lappend radio $sid $station
          }
        }
      }
    }
    if { $error != "0"} {
      Save
    }
     
  }

  proc Save { } {
    variable FILENAME
    variable sonoszone
    variable messagespeicher
    variable stdvolume
    variable volumeup
    variable volumedown
    variable messagevolume 
    variable rampto
    variable radio
    variable timeout

    array set content {}
    set content(messagespeicher) $messagespeicher
    set content(stdvolume) $stdvolume
    set content(volumeup) $volumeup
    set content(volumedown) $volumedown
    set content(messagevolume) $messagevolume
    set content(rampto) $rampto
    set content(timeout) $timeout
    
    set fd [open $FILENAME w]
    fconfigure $fd -encoding utf-8
    puts $fd "# The first line after this comment is the ZonePlayer-List"
    puts $fd $sonoszone
    puts $fd ""
    puts $fd "# After this line are some settings"
    puts $fd [array get content]
    puts $fd ""
    puts $fd "# Now starts the radio list"
    puts $fd "# one station a line"
    puts $fd "# sid name"
    foreach { sid station} $radio  {
      puts $fd "$sid\t$station"
    }
    close $fd
  }
}

# general procedures

proc log { {str ""} {mode a}} {
    variable flog
    set fd [open $flog $mode]
    fconfigure $fd -encoding utf-8
    set t [clock seconds]
    set timestamp [format "%s.%03d" \
      [clock format $t  -format %T] \
      [expr {$t % 1000}]]

    puts $fd "[clock format [clock seconds] -format %Y-%m-%d] $timestamp: $str"
    close $fd
}

proc getMessage { {message ""} {art info}} {
    set text {    
<div class="alert fade in alert-$art" id="message">
<script type="text/javascript">
    document.write("<button type='button' class='close' data-dismiss='alert' >×</button>");
</script>$message</div>

}
return [subst $text]
}

proc loadFile {FILENAME} { 
set content ""
set fd -1
  catch {
    set fd [ open $FILENAME r]
    if { $fd > -1 } {
      fconfigure $fd -encoding utf-8
      set content [read $fd]
      close $fd
    }
  }
return $content
}

proc saveFile {FILENAME content} { 
set fd -1
  catch {
    set fd [open $FILENAME w]
    if { $fd > -1 } {
      fconfigure $fd -encoding utf-8
      puts $fd "$content"
      close $fd
    }
  }
}

proc init { {message ""} } {
  #log $message
  set newMessage ""
  global args env br hr bold unbold pre unpre h1 unh1 h2 unh2 table table1 untable td tdstatic untd tr tr1 untr thead unthead th th2 unth sonosArray
  set zone $sonosArray(Zone)
  if  [regexp ".*nicht gefunden.*" $zone] {
    set zone "fehlt"
  }
  if {[info exists args(action)]} {
      set action $args(action)
    if {[info exists args($action)]} {
        set parameter $args($action)
      } else {
        set parameter ""
      }
    } else {
      set action ""
      set parameter ""
    }

  if {[info exists env(HTTP_HOST)]} {
    puts "Content-type:text/html\n"
    puts {<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="utf-8">
<link href="./public/css/bootstrap.min.css" rel="stylesheet">
<link href="./public/css/bootstrapValidator.min.css" rel="stylesheet">
<link href="./public/css/custombootstrap.css" rel="stylesheet">
<link href="./public/css/custom.css" rel="stylesheet">
</head>
<body style="zoom: 1; margin-top: 60px;"> 
}
    Navbar $zone $action $parameter
    puts {
<div class="container center1 col-md-8" id="content"> 
}
    puts "$message"
    Player $zone $action $parameter

    set h1 {<h1>} ; set unh1 {</h1>}
    set h2 {<h2>} ; set unh2 {</h2>}
    set br {<br>} ; set hr {<hr />}
    set bold {<strong>} ; set unbold {</strong>}
    set pre  {<pre>} ; set unpre {</pre>}
    set table "<table  class='table table-striped table-bordered'>" ; set untable "</table>"
    set table1 "<table  class='table table-bordered'>" 
    set td "<td>" ; set untd "</td>"
    set tdstatic "<td width='20%'>"
    set tr "<tr>" ; set untr "</tr>"
    set tr1 "<tr class='info'>" ; set untr "</tr>"
    set thead "<thead>" ; set unthead "</thead>"
    set th "<th>" ; set th2 "<th colspan='2'>" ; set unth "</th>"
  } else { 
    set br "\n"
    set hr "\n--------------------------------------------------------------------------------\n"
    set h1 "" ; set unh1 "" ; set h2 "" ; set unh2 "" ; set bold "" ; set unbold "" 
    set pre  "" ; set unpre "" ; set table "" ;set table1 "" ; set untable "" ; set td "" ; set untd "" 
    set tdstatic "" ; set tr "" ; set tr1 "" ; set untr "" ; set thead "" ; set unthead "" ; set th "" 
    set th2 "" ; set unth ""
    regexp "</script>(.*?)</div>" $message dummy newMessage
    puts $newMessage
  }
}

proc Player { zone {action ""} {parameter "" }} {
  global a info
  if { $action != ""} {
    if { $parameter != ""} {
      set parameter "<input name='$action' type='hidden' value='$parameter'/>"
    }
    set action "<input name='action' type='hidden' value='$action' />"
  }
  set o ""
  set found 0
  if { "No Sonosplayer found!" == $Cfg::sonoszone} {
    set o "${o}<option  disabled>No Sonosplayer found!</option>"
  } else {
    foreach {z ip} $Cfg::sonoszone {
      set name "$z (nicht erreichbar)"
      set z [string tolower $z] 
      set a(IP) $ip
      catch {
        GetZoneAttributes "a"
        if { [info exists info(CurrentZoneName)] } {
           set name "$info(CurrentZoneName)"
        }
        unset info
      }
      set selected ""
      set disabled ""
      if {[string match "*(nicht erreichbar)" $name]} {
        set disabled " disabled"
      } elseif { $z == [string tolower $zone] } {
        set selected " selected"
        set found 1
      }
      set o "${o}<option value='$z'$selected$disabled>$name</option>"
    }
  }
  if { $found == "0" } {
    set o "<option value='fehlt' selected>Bitte Player w&auml;hlen</option>$o"
  }
  set form {
<form class='form-horizontal' name='f_edit' id='f_edit' action='sonos2.cgi' method='get' onsubmit=''>
$action$parameter<fieldset><div class="form-group">
  <label class="col-md-2 control-label" for="player">Player (Zone)</label>
  <div class="col-md-4">
    <select id="player" name="player" class="form-control" required  onchange="javascript: if(this.value != '') this.form.submit(); else alert('hello');" >
    $o</select>
  <span class="help-block helpplayer"></span>  
  </div>
  <div class="col-md-2"><noscript>
    <button type='submit' class='btn btn-default ' >Select</button>
    </noscript></div>
</div>

</fieldset>
</form>
}
puts [subst $form]
}

proc Navbar {zone {action ""} {parameter "" }} {
  global radio info
  set radioli ""
  set infoline ""
  set play ""
  if { $zone != "fehlt"  } {
    set infoline [subst {     <li class="dropdown dropdownsuper">
      <a href="#" class="dropdown-toggle" data-toggle="dropdown">Info<b class="caret"></b></a>
      <ul class="dropdown-menu supermenu">
            <li><a href="sonos2.cgi?zone=$zone&action=info&info=audioinputattribute">Audioinput-Attribute</a></li>
            <li><a href="sonos2.cgi?zone=$zone&action=info&info=zoneattribute">Zone-Attribute</a></li>
            <li><a href="sonos2.cgi?zone=$zone&action=info&info=zonegroupattribute">Zonegroup-Attribute</a></li>
            <li><a href="sonos2.cgi?zone=$zone&action=info&info=zoneinfo">Zone-Info</a></li>
            <li><a href="sonos2.cgi?zone=$zone&action=info&info=transportinfo">Transport-Info</a></li>
            <li><a href="sonos2.cgi?zone=$zone&action=info&info=alarm">List Alarms</a></li>
            <li><a href="sonos2.cgi?zone=$zone&action=info&info=radioiduri">Radio-ID and URI</a></li>
            <li><a href="sonos2.cgi?zone=$zone&action=info&info=PositionMediaInfo">Position/Media-Info</a></li>
            <li><a href="sonos2.cgi?zone=$zone&action=info&info=AskRadio">Ask Radiotime</a></li>
            <li class="divider"></li>
            <li><a href="sonos2.cgi?zone=$zone&action=info&info=CurrentPlaylist">Current Playlist</a></li>
            <li><a href="sonos2.cgi?zone=$zone&action=info&info=SonosPlaylists">Sonos Playlists</a></li>
            <li><a href="sonos2.cgi?zone=$zone&action=info&info=ImportedPlaylists">Imported Playlists</a></li>
      </ul>
      </li>
    }]
    #<li><a href="sonos2.cgi?zone=$zone&action=info&info=PositionInfo">Media-Info</a></li>
    set radioli {<li class="dropdown dropdownsuper">
      <a href="#" class="dropdown-toggle" data-toggle="dropdown">Set Radio<b class="caret"></b></a>
      <ul class="dropdown-menu supermenu">}
    foreach {id sender} $Cfg::radio {
      set radioli "${radioli}<li><a href='sonos2.cgi?zone=$zone&action=radio&radio=$id'>$sender</a></li>"
    }
    set radioli "$radioli</ul></li>"
    GetTransportInfo
    if { [IsRadio] != "0"} {
      set aus "stop"
    } else {
      set aus "pause"
    }
    if { [info exists info(CurrentTransportState)] && $info(CurrentTransportState) == "PLAYING" } {
      set play "<li><a href='sonos2.cgi?zone=$zone&action=$aus'><img src='public/img/$aus.png' alt=''></a></li>"
    } else {
      set play "<li><a href='sonos2.cgi?zone=$zone&action=play'><img src='public/img/play.png' alt=''></a></li>"
    }
  }
  if {$zone != "" && $zone != "fehlt"} { set zone "?zone=$zone"} else {set zone ""}
  set navbar {
<div class="navbar navbar-default navbar-fixed-top" role="navigation">
  <div class="container">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
        <span class="sr-only">Toggle navigation</span>
         <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="http://www.sonos.de"  target="_blank"><img src="public/img/logo.png" alt=""> &nbsp;
 Sonos &nbsp;</a>
      </div>
   <div class="collapse navbar-collapse">
      <ul class="nav navbar-nav ">
        <li class="active"><a href="status.cgi$zone">Home</a></li>
          $infoline
      $radioli
      $play
      </ul>
      <ul class="nav navbar-nav navbar-right">
        <li><a href="sonos2.cgi$zone">Sonos-Script</a></li>
        <li><a href="settings/udp.cgi$zone">Topology</a></li>
       <li><a href="settings/settings.cgi$zone">Einstellungen</a></li>
       </ul>        
     </div><!--/.nav-collapse -->
  </div><!-- class="container" -->
</div><!-- class="navbar ..." -->
}
puts [subst $navbar]
}

proc theEnd {} {
  global env
  if {[info exists env(HTTP_HOST)]} {
    puts {</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<script src="./public/js/bootstrap.min.js"></script>
</body></html>
    }
  }
}
proc parseQuery { } {
  global args env argc argv query
  
  if { [info exists env(QUERY_STRING)] } {
    
    set query  [string tolower [url-decode $env(QUERY_STRING)]]
    foreach item [split $query &] {
      if { [regexp {([^=]+)=(.+)} $item dummy key value] } {
        if { $key == "zonen"} {set key zone}
        if { $key == "player"} {set key zone}
        set args($key) $value
      }
    }
  } else {
    if { $argc > 0 } {
      set args(zone) [string tolower [lindex $argv 0]]
    }
    if { $argc > 1 } {
      set action [string tolower [lindex $argv 1]]
      set args(action) $action
    }
    if { $argc > 2 } {
      if { $action == "removemember" || $action == "addmember" } {set action "member"}
      set args($action)  [string tolower [lindex $argv 2]]
    } 

  }
  
}

proc pBr { {v ""} } {
  global br
  if {"<br>" == $br} {
    set v [string map {"\n" "<br />"} $v]
    puts "$v$br"
  } else {
    puts "$v"
  }
}

proc fromHtml {s} {
  return [string map {"&apos;" "'" "&ouml;" "ö" "&uuml;" "ü" "&auml;" "ä" "&Ouml;" "Ö" "&Uuml;" "Ü" "&Auml;" "Ä" "&szlig;" "ß" "&lt;" "<" "&gt;" ">" "&le;" "<=" "&ge;" ">=" "&amp;" "&" "&quot;" \" "&#039;" "'"} $s];# " nur wegen Syntaxhighlighting
}
proc toHtml {s} {
  return [string map {"'" "&apos;" "ö" "&ouml;" "ü" "&uuml;" "ä" "&auml;" "Ö" "&Ouml;" "Ü" "&Uuml;" "Ä" "&Auml;" "ß" "&szlig;" "<" "&lt;" ">" "&gt;" "<=" "&le;" ">=" "&ge;" "&" "&amp;" \" "&quot;" "'" "&#039;"} $s];# " nur wegen Syntaxhighlighting
}

namespace eval Xml {
   namespace export Parse
   namespace export getXmlValue
   namespace export getXmlAttribute
   variable part 
   variable body 
   variable attribute
   variable rest

  proc Parse { xml tag } {
    variable part "" attribute "" body "" attribute "" rest ""
    set posStart "-1 0"
    set posSelf "-1 0"
    # there is no real negative lookbehind in ARE tcl
    set startTag [subst -nobackslashes -nocommands {(<\y${tag}\y\s*?[^>]*?>)}] ;# /[]
    set closeTag [subst -nobackslashes -nocommands {(</\y${tag}\y\s*?>)}]
    set selfTag [subst -nobackslashes -nocommands {(<\y${tag}\y\s*?[^>]*?/>)}]
    regexp -indices $startTag $xml posStart
    regexp -indices $selfTag $xml posSelf
    regexp -indices $closeTag $xml posClose
    if {[lindex $posSelf 0] == "-1" && [lindex $posStart 0] == "-1"} {return 0} ;# tag nicht gefunden
    set method self
    if {[lindex $posSelf 0] == "-1"} { ;# kein selftag
        set method start
    } elseif {[lindex $posStart 0] < [lindex $posSelf 0]} { ;# zuerst starttag
        set method start
     }
    if { $method == "self"} {
        set rr [subst -nobackslashes -nocommands {<\y${tag}\y\s*([^>]*?)/>}] ;# ohne body
        if {! [regexp $rr $xml part attribute]  } {
            return 0
        }
    } else  {
        set rr [subst -nobackslashes -nocommands {<\y${tag}\y\s*?([^>]*?)>(.*?)</\y${tag}\y\s*?>}]
        if {! [regexp $rr $xml part attribute body] } {
            return 0
        }
    }
    # test ob das gleiche Tag nochmal im Tag steckt
    set trunk  $body
    while {[regexp $startTag $trunk]} {
        set posDanach [expr [lindex $posClose 1] + 1]
        set nachStr [string range $xml $posDanach end]
        regexp -indices $closeTag $nachStr newPos
        if {[catch {set dummy $newPos} ifd]} {
          puts $xml
          puts ---------------------------------------
          puts $startTag
          puts $trunk
          exit
        }
        set posClose [list [expr [lindex $posClose 0] + [lindex $newPos 1]] [expr [lindex $posClose 1] + [lindex $newPos 1]]  ]
        set trunk [string range $nachStr 0 [expr [lindex $posClose 0] -1]] ;# Trunk verschiebt es nach hinten
        set body [string range $xml [expr [lindex $posStart 1] +1] [lindex $posClose 0] ] ;# Body wird länger
        set part [string range $xml [lindex $posStart 0]  [expr [lindex $posClose 1] + 1] ] ;# part wird länger
        #puts "New trunk: $trunk"
        #puts "New body: $body"
    }
    if { $method != "self"} {
      set vorString [string range $xml 0 [expr [lindex $posStart 0]-1]]
      set nachString [string range $xml [expr [lindex $posClose 1]+1] end]
    } {
      set vorString [string range $xml 0 [expr [lindex $posSelf 0]-1]]
      set nachString [string range $xml [expr [lindex $posSelf 1]+1] end]
    }
    set rest "$vorString$nachString"
    set body [string trim $body]
    regsub -all "=" [string trim $attribute] { } attribute
    set rest [string trim $rest]
    return 1
  }
  proc getXmlValue {xml tag} {
    variable body
    Parse $xml $tag
    return  $body
  }
  proc getXmlAttribute {attribut} {
    variable attribute
    set result ""
    set i [lsearch -exact $attribute $attribut]
    if {$i > -1} {
      incr i
      set result [lindex $attribute $i]
    }
    return $result
  }
}



# procedures for the sonos array
# creates the sonosArray and fills some values
proc sonosCreate {zone} {
  global args sonosArray
  set nz [string tolower $Cfg::sonoszone]
  regsub {ü} $nz {ue} nz
  regsub {ü} $zone {ue} zone
  regsub {ö} $nz {oe} nz
  regsub {ö} $zone {oe} zone
  regsub {ä} $nz {ae} nz
  regsub {ä} $zone {ae} zone
  regsub {ß} $nz {ss} nz
  regsub {ß} $zone {ss} zone
  set i [lsearch -exact $nz [string tolower $zone]]
  if {$i > -1} {
    set zone [lindex $Cfg::sonoszone $i]
    incr i
    set sonos [lindex $Cfg::sonoszone $i]
  } {
    return 0
  }
  if {[info exists args(volume)]  && [string is double $args(volume)]} {
    set volume [ expr round($args(volume)) ]
    if { $volume > 100 } { set volume 100 } elseif { $volume < 0 } { set volume 0 }
  } {
    set volume $Cfg::stdvolume
  }
  if {[info exists args(rampto)]} {
    set Cfg::rampto $args(rampto)
  }
  switch $Cfg::rampto {
    "sleep" {set Cfg::rampto "SLEEP_TIMER_RAMP_TYPE"}
    "auto" {set Cfg::rampto "AUTOPLAY_RAMP_TYPE"}
    default {set Cfg::rampto "ALARM_RAMP_TYPE"}
  }
  set sonosArray(Zone) $zone
  set sonosArray(IP) $sonos
  set sonosArray(Volume) $volume
  set sonosArray(Rampto) $Cfg::rampto
  return 1
}

# creates a second sonosArray and fills some values
proc sonosCreateOther {newzonen {volume ""} {newRampto "auto"} } {
  global  otherSonosArray
  set nz [string tolower $Cfg::sonoszone]
  regsub {ü} $nz {ue} nz
  regsub {ü} $newzonen {ue} newzonen
  regsub {ö} $nz {oe} nz
  regsub {ö} $newzonen {oe} newzonen
  regsub {ä} $nz {ae} nz
  regsub {ä} $newzonen {ae} newzonen
  regsub {ß} $nz {ss} nz
  regsub {ß} $newzonen {ss} newzonen
  set i [lsearch -exact $nz [string tolower $newzonen]]
  if {$i > -1} {
    incr i
    set sonos [lindex $Cfg::sonoszone $i]
  } {
    set newzonen "fehlt"
    set sonos "0.0.0.0"
  }
  if { $volume != "" && [string is double $volume] } {
    set volume [ expr round($volume) ]
    if { $volume > 100 } { set volume 100 } elseif { $volume < 0 } { set volume 0 }
  } {
    set volume $Cfg::stdvolume
  }
  switch $newRampto {
    "sleep" {set newRampto "SLEEP_TIMER_RAMP_TYPE"}
    "auto" {set newRampto "AUTOPLAY_RAMP_TYPE"}
    default {set newRampto "ALARM_RAMP_TYPE"}
  }
  set otherSonosArray(Zone) $newzonen
  set otherSonosArray(IP) $sonos
  set otherSonosArray(Volume) $volume
  set otherSonosArray(Rampto) $newRampto
}

# gets a value for a key, returns the value or nothing, default array sonosArray
proc sonosGet {key {array "sonosArray"}} {
  upvar #0 $array theArray
  if {[info exists theArray($key)]} {
    return $theArray($key)
  } {
    return ""
  }
}
# sets a value for a key, default array sonosArray
proc sonosSet {key value {array "sonosArray"}} {
  upvar #0 $array theArray
  set theArray($key) $value
}

proc List2Values { val1 val2} {
  global hr bold unbold pre unpre h1 unh1 h2 unh2 table untable td untd tr untr thead unthead th unth th2
  puts "$table"
  puts "$thead${tr}${th}$val1 $untd$untr$unthead"
  puts "$tr$td$val2$untd$untr"
  puts "$untable" 
}

# list value and keys of an array, default array sonosArray, optional title
proc listArray {{array "sonosArray"} {title ""} {sorted ""}} {
  upvar #0 $array theArray
  global hr bold unbold pre unpre h1 unh1 h2 unh2 table untable td untd tr untr thead unthead th unth th2 tdstatic
  puts "$table"
  if { $title == ""} {
    puts "$thead${tr}${th2}Array $array $untd$untr$unthead"
  } {
    puts "$thead${tr}${th2}$title$unth$untr$unthead"
  }
  if { $sorted != ""} {
    foreach key [lsort [array names theArray]] {
      puts "$tr$tdstatic$key:---- $untd$td$theArray($key)$untd$untr"
    }
  } else {
    foreach {key value} [array get theArray] {
      switch $key {
        Now_playing { set key "Now playing"}
      }
      puts "$tr$tdstatic$key: $untd$td$value$untd$untr"
    }
  }
  puts "$untable"
}


#returns http-request
proc getHttp { url {chunk 4096} } {
   catch {set token [::http::geturl $url  \
          -blocksize $chunk -timeout 3000]} {
            return "Host is down"
          }
   # This ends the line started by httpCopyProgress
   #puts stderr ""

   upvar #0 $token state
   set max 0
   foreach {name value} $state(meta) {
      if {[string length $name] > $max} {
         set max [string length $name]
      }
      if {[regexp -nocase ^location$ $name]} {
         # Handle URL redirects
         puts stderr "Location:$value"
         return [httpcopy [string trim $value] $chunk]
      }
   }
   incr max
   foreach {name value} $state(meta) {
      #puts [format "%-*s %s" $max $name: $value]
   }
  if {$state(status) == "ok" } {
    set t $state(body)
      regsub -all {\n[0-9a-zA-Z]+\n} "$t" {} t
      regsub -all {^[0-9a-zA-Z]+\n} "$t" {} t
    return [encoding convertfrom utf-8 $t]
  }
  return $state(status)
}

# helper for GetSonosUUID
proc getUUID {zoneplayerIp} {
  set response [getHttp "http://$zoneplayerIp:1400/status/zp"]
  return [Xml::getXmlValue $response LocalUID]
}


# soap-request with socket 
proc soapSendOld {message IP } {
  set s [socket $IP 1400]
  puts $s $message
  flush $s
  set ret [read $s]
  close $s
  return $ret
}
# soap-request with socket 
proc soapSend {message IP } {
  variable Cfg::timeout
  variable ____im $message
  global accumulate end
  set s [socket -async $IP 1400]
  fconfigure $s -blocking 0
  fileevent $s writable [list connected $s]
  proc connected {s} {
      variable ____im
      fileevent $s writable {}
      puts $s $____im 
      flush $s
      fileevent $s readable [list accumulateBytes $s]
  }

  set accumulate ""
  proc accumulateBytes {s} {
      global accumulate end
      append accumulate [read $s]
      if {[eof $s]} {
          set end 0
      }
  }
  after ${Cfg::timeout}000 set end 1
  vwait end
  catch {close $s}
  if {$end} {
    return "timed out"
  } else {
    return  "$accumulate"
  }
}
# translated procedures from php

# wrapper for soap-request
proc getResponse {controlUrl procName {IP sonosArray} {xml ""} } {
   if {[sonosGet IP $IP] != ""} {
        set IP [sonosGet IP $IP]
    }
    set l [split $controlUrl /]
    if {[lindex $l 0] == {}} { ;# Initial 
        set l [lreplace $l 0 0]
    }
    if {[llength $l] == 2} {
        set controlUrl "/[lindex $l 0]/[lindex $l 1]" 
        set type [lindex $l 1]
    } elseif {[llength $l] == 1} {
         set controlUrl "/[lindex $l 0]" 
        set type [lindex $l 0]
    } else {
        return "Wrong controlUrl: $controlUrl"
    }
    set type "urn:schemas-upnp-org:service:${type}:1"
    if {$xml == ""} {
        set xml [subst {<u:$procName xmlns:u="$type"/>}]
    } else {
        set x {<u:$procName xmlns:u="$type">$xml</u:$procName>}
        set xml [subst $x]
    }
set xml [subst {<?xml version="1.0" encoding="utf-8"?>
<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><s:Body>$xml</s:Body></s:Envelope>}]
set header {POST $controlUrl/Control HTTP/1.1
SOAPACTION: "$type#$procName"
HOST: $IP:1400
CONTENT-TYPE: text/xml; charset="utf-8"
CONTENT-LENGTH: [string length $xml]

}
set response [soapSend "[subst $header]$xml" $IP]
if {[string match -nocase {*TRANSFER-ENCODING: chunked*} $response]} {
      regsub -all {\n[0-9a-zA-Z]+\n} "$response" {} response
      regsub -all {^[0-9a-zA-Z]+\n} "$response" {} response
}
return [fromHtml $response]
}


# puts some xml-values (args) into a list
proc getResponseList {response args} {
  set d [list]
  foreach arg $args {
    set ret [string trim [fromHtml [Xml::getXmlValue $response $arg]]]
    if { $ret != ""} {
      lappend d $arg $ret
    }
  }
  return $d
}

# puts some xml-values (args) into a global array info
proc getResponseArray {response args} {
  global info
  array set info {}
  foreach arg $args {
    set ret [string trim [fromHtml [Xml::getXmlValue $response $arg]]]
    if { $ret != ""} {
      switch $arg {
        "dc:title" {set newArg "Title"}
        "dc:creator" {set newArg "Artist"}
        "upnp:album" {set newArg "Album"}
        "upnp:albumArtURI" {set newArg "albumArtURI"}
        "r:streamContent" {set newArg "Info"}
        default {set newArg $arg}
      }
      set info($newArg) $ret
    }
  }
}

# gets the UUID for the sonosArray(ip) and writes it in sonosArray(UUID)
# it could be another sonosArray like otherSonosArray
proc GetSonosUUID { {array "sonosArray"}} {
  upvar #0 $array theArray
  set theArray(UUID) [getUUID [sonosGet IP $array]]
}
#
# Returns a list of alarms from sonos device and puts it into the sonosArray
#
proc ListAlarms { {array "sonosArray"}} {
  global info
  set response [getResponse /AlarmClock ListAlarms $array]
  set CurrentAlarmListVersion [Xml::getXmlValue $response CurrentAlarmListVersion]
  set alarms [Xml::getXmlValue $response Alarms]
  if {$alarms != ""} {
    set d [list]
    set i 0
    set f [string first "<" $alarms ]
    while {$f >= 0} {
      set l [string first ">" $alarms [expr $f + 1]]
      set str [string range $alarms $f $l]
      set str [string map {{""} {""} "<Alarm " "" "=" " " "/>" ""  \" ""} $str]
      #pBr $str
      set f [string first "<" $alarms $l]
      lappend d $i $str
      incr i
    }
    set info(CurrentAlarmListVersion) $CurrentAlarmListVersion
    set info(CurrentAlarmList) $d
  } else {
    set info(CurrentAlarmListVersion)  $CurrentAlarmListVersion
    set info(CurrentAlarmList)  {keine Alarms}
  }
}

#
# Updates an existing alarm with the id of a sonosArray
#
proc UpdateAlarm {id startzeit duration welchetage an roomid programm programmeta playmode volume linkedzone {array "sonosArray"}} {
  upvar #0 $array theArray
set xml {<ID>$id</ID>
<StartLocalTime>$startzeit</StartLocalTime>
<Duration>$duration</Duration>
<Recurrence>$welchetage</Recurrence>
<Enabled>$an</Enabled>
<RoomUUID>$roomid</RoomUUID>
<ProgramURI>[toHtml $programm]</ProgramURI>
<ProgramMetaData>[toHtml $programmeta]</ProgramMetaData>
<PlayMode>$playmode</PlayMode>
<Volume>$volume</Volume>
<IncludeLinkedZones>$linkedzone</IncludeLinkedZones>}
  set response [getResponse /AlarmClock UpdateAlarm $array [subst $xml]]
  if {[string match -nocase {HTTP/1.1 200 OK*} $response]} {
    return "Alarm mit ID $id geändert" 
  } else {
    return "Alarm mit ID $id konnte nicht geändert werden" 
  }
}

#
# Get information of devices inputs and puts it into the sonosArray
#
proc GetAudioInputAttributes { {array "sonosArray"}} {
  global info
  upvar #0 $array theArray
  set response [getResponse /AudioIn GetAudioInputAttributes $array]
  if {[string match -nocase {HTTP/1.1 200 OK*} $response]} {
    getResponseArray $response CurrentName CurrentIcon
  } {
    set info(AudioInputAttributes) "keine gefunden"
  }
}
  
#
# Reads Zone Attributes and puts it into the sonosArray
#
proc GetZoneAttributes { {array "sonosArray"}} {
  global info
  set response [getResponse DeviceProperties GetZoneAttributes $array]
  if {[string match -nocase {HTTP/1.1 200 OK*} $response]} {
    getResponseArray $response CurrentZoneName CurrentIcon CurrentConfiguration
  }
}

#
# Reads Zone Information and puts it into the sonosArray
#
proc GetZoneInfo { {array "sonosArray"}} {
  global info
  set response [getResponse DeviceProperties GetZoneInfo $array]
  if {[string match -nocase {HTTP/1.1 200 OK*} $response]} {
    getResponseArray $response SerialNumber SoftwareVersion DisplaySoftwareVersion HardwareVersion IPAddress MACAddress CopyrightInfo ExtraInfo HTAudioIn]
  }
}



#
# Sets the state of the white LED and puts it into the sonosArray
#
proc SetLEDState {{state On}  {array "sonosArray"}} {
  upvar #0 $array theArray
  if {[string compare -nocase $state "On"] == 0 } { 
    set state "On" 
  } elseif { [string compare -nocase $state "Off"] == 0} {
    set state  "Off"
  } elseif { [string compare -nocase $state "0"] == 0} {
    set state  "Off"
  } else {
    set state  "On"
  }
set xml "<DesiredLEDState>$state</DesiredLEDState>"
  set response [getResponse /DeviceProperties SetLEDState $array $xml]
  if {[string match -nocase {HTTP/1.1 200 OK*} $response]} {
    set theArray(CurrentLEDState) $state
    return "LEDStatus auf $state geändert" 
  } else {
    return "LEDStatus konnte nicht auf $state geändert werden" 
  }
}

proc AddPlaylistItem {item id nr {parentID ""}} {
  global playlist
  global info
  #log $item "w"
  getResponseArray $item upnp:album upnp:albumArtURI dc:creator dc:title parentID
  set duration ""
  if [ Xml::Parse $item res ] {
    set duration [Xml::getXmlAttribute duration]
  }
  set playlist($nr,id) $id 
  if { $duration != "" } {set playlist($nr,Dauer) $duration}
  if [info exists info(Album)] { set playlist($nr,Album) $info(Album) }
  if [info exists info(Artist)] { set playlist($nr,Artist) $info(Artist) }
  if { $parentID != "" } { set playlist($nr,parentID) $parentID }
  if [info exists info(Title)] { set playlist($nr,Title) $info(Title) }
  if [info exists info(albumArtURI)] { set playlist($nr,albumArtURI) $info(albumArtURI) }
}
#
# Browse Currentplaylist
#
proc GetCurrentPlaylist {{array "sonosArray"}} {
  global playlistcount
  set xml "<ObjectID>Q:0</ObjectID><BrowseFlag>BrowseDirectChildren</BrowseFlag><Filter></Filter><StartingIndex>0</StartingIndex><RequestedCount>1000</RequestedCount><SortCriteria></SortCriteria>"
  set response [getResponse /MediaServer/ContentDirectory Browse $array $xml]  
  if {[string match -nocase {HTTP/1.1 200 OK*} $response]} {
    #log "$response" "w"
    set trunk [url-decode $response]
    set playlistcount [Xml::getXmlValue $trunk "NumberReturned"]
    set i 0
    while {[ Xml::Parse $trunk item]} {
      incr i
      set item $Xml::body
      set id [Xml::getXmlAttribute id]
      set trunk $Xml::rest
      AddPlaylistItem $item $id $i
    }
  } else {
    log $response "w" 
  }
}

#
# Browse Sonosplaylist
#
proc GetSonosPlaylists {{array "sonosArray"}} {
  global playlistcount
  set xml "<ObjectID>SQ:</ObjectID><BrowseFlag>BrowseDirectChildren</BrowseFlag><Filter></Filter><StartingIndex>0</StartingIndex><RequestedCount>1000</RequestedCount><SortCriteria></SortCriteria>"
  set response [getResponse /MediaServer/ContentDirectory Browse $array $xml]
  if {[string match -nocase {HTTP/1.1 200 OK*} $response]} {
  log $response "w"
    set trunk [url-decode $response]
    set playlistcount [Xml::getXmlValue $trunk "NumberReturned"]
    set i 0
    while {[ Xml::Parse $trunk container]} {
      incr i
      set item $Xml::body
      set id [Xml::getXmlAttribute id]
      set trunk $Xml::rest
      AddPlaylistItem $item $id $i
    }
  } else {
    log $response "w" 
  }
}

#
# Browse Sonosplaylist
#
proc GetImportedPlaylists {{array "sonosArray"}} {
  global playlistcount
  set xml "<ObjectID>A:PLAYLISTS</ObjectID><BrowseFlag>BrowseDirectChildren</BrowseFlag><Filter></Filter><StartingIndex>0</StartingIndex><RequestedCount>1000</RequestedCount><SortCriteria></SortCriteria>"
  set response [getResponse /MediaServer/ContentDirectory Browse $array $xml]
  if {[string match -nocase {HTTP/1.1 200 OK*} $response]} {
    set trunk [url-decode $response]
    set playlistcount [Xml::getXmlValue $trunk "NumberReturned"]
    set i 0
    while {[ Xml::Parse $trunk container]} {
      incr i
      set item $Xml::body
      set id [Xml::getXmlAttribute id]
      set trunk $Xml::rest
      AddPlaylistItem $item $id $i
    }
  } else {
    log $response "w" 
  }
}

proc GetPlaylist {objectID {array "sonosArray"}} {
  global playlistcount
  set xml "<ObjectID>$objectID</ObjectID><BrowseFlag>BrowseDirectChildren</BrowseFlag><Filter></Filter><StartingIndex>0</StartingIndex><RequestedCount>1000</RequestedCount><SortCriteria></SortCriteria>"
  set response [getResponse /MediaServer/ContentDirectory Browse $array $xml]
  if {[string match -nocase {HTTP/1.1 200 OK*} $response]} {
    set trunk [url-decode $response]
    set playlistcount [Xml::getXmlValue $trunk "NumberReturned"]
    set i 0
    while {[ Xml::Parse $trunk item]} {
      incr i
      set item $Xml::body
      set id [Xml::getXmlAttribute id]
      set trunk $Xml::rest
      AddPlaylistItem $item $id $i
    }
  } else {
    log $response "w" 
  }
}


proc setPartymodus {{array "sonosArray"} } {
  if {"No Sonosplayer found!" == $Cfg::sonoszone} {
    exit
  }
  set zone [sonosGet Zone $array]
  foreach {name ip} $Cfg::sonoszone {
    if {$zone !=  $name } {
      addMember $name
    }
  }
}

proc addMember {playerName  {array "sonosArray"} } {
  global  otherSonosArray
  upvar #0 $array theArray
  GetSonosUUID
  sonosCreateOther $playerName
  set Rincon $theArray(UUID)
  set xml "<InstanceID>0</InstanceID><CurrentURI>x-rincon:$Rincon</CurrentURI><CurrentURIMetaData></CurrentURIMetaData>"
  set response [getResponse /MediaRenderer/AVTransport SetAVTransportURI otherSonosArray $xml]
  if {[string match -nocase {HTTP/1.1 200 OK*} $response]} {
    return "addMember okay" 
  } else {
    return "addMember mit Fehler beendet" 
  }
}

proc removeMember {playerName } {
  global otherSonosArray
  sonosCreateOther $playerName
  set xml "<InstanceID>0</InstanceID>"
  set response [getResponse /MediaRenderer/AVTransport BecomeCoordinatorOfStandaloneGroup otherSonosArray $xml]
  if {[string match -nocase {HTTP/1.1 200 OK*} $response]} {
    return "removeMember okay" 
  } else {
    return "removeMember mit Fehler beendet" 
  }
}

proc Browse {objectID {meta "BrowseDirectChildren"} {filter "" } {sindex "0"} {rcount "1000"} {sc ""}  {array "sonosArray"} } {
  global playlistcount
  if { $rcount == ""} {
    set rcount 10
  }
  if { $sindex == ""} {
    set sindex "0"
  }
  if { $meta == ""} {
    set meta "BrowseDirectChildren"
  }
  set xml "<ObjectID>$objectID</ObjectID><BrowseFlag>$meta</BrowseFlag><Filter>$filter</Filter><StartingIndex>$sindex</StartingIndex><RequestedCount>$rcount</RequestedCount><SortCriteria>$sc</SortCriteria>"
  set response [getResponse /MediaServer/ContentDirectory Browse $array $xml]
  if {[string match -nocase {HTTP/1.1 200 OK*} $response]} {
    #log $response "w"
    set trunk [url-decode $response]
    set playlistcount [Xml::getXmlValue $trunk "NumberReturned"]
    set i 0
    set it shit
    if {[ Xml::Parse $trunk item]} {
      set it  "item"
    } elseif {[ Xml::Parse $trunk container]} {
      set it  "container"
    }
    while {[ Xml::Parse $trunk $it]} {
      incr i
      set item $Xml::body
      set id [Xml::getXmlAttribute id]
      set parentID [Xml::getXmlAttribute parentID]
      set trunk $Xml::rest
      AddPlaylistItem $item $id $i $parentID
    }
  } else {
    log $response "w" 
  }
}

proc listPlaylist { {array "playlist"} {title ""} } {
  upvar #0 $array theArray
  global hr bold unbold pre unpre h1 unh1 h2 unh2 table table1 untable td untd tr tr1 untr thead unthead th unth th2 tdstatic
  puts "$table1"
  if { $title == ""} {
    puts "$thead${tr}${th2}Playlist $array $untd$untr$unthead"
  } {
    puts "$thead${tr}${th2}$title$unth$untr$unthead"
  }
  set i 1
  while {[info exists theArray($i,id)]} {
    puts "$tr1${tdstatic}Nummer: $untd$td$i$untd$untr"
    puts "$tr${tdstatic}ID: $untd$td$theArray($i,id)$untd$untr"
    if [info exists theArray($i,Artist)] {puts "$tr${tdstatic}Artist: $untd$td$theArray($i,Artist)$untd$untr"}
    if [info exists theArray($i,Title)] {puts "$tr${tdstatic}Title: $untd$td$theArray($i,Title)$untd$untr"}
    if [info exists theArray($i,Album)] {puts "$tr${tdstatic}Album: $untd$td$theArray($i,Album)$untd$untr"}
    if [info exists theArray($i,Dauer)] {puts "$tr${tdstatic}Dauer: $untd$td$theArray($i,Dauer)$untd$untr"}
    incr i
  }
  puts "$untable"
}
#
# Gets the state of the white LED and puts it into the sonosArray
# 
proc GetLEDState { {array "sonosArray"} } {
  upvar #0 $array theArray
  set response [getResponse /DeviceProperties GetLEDState $array]
  if {[string match -nocase {HTTP/1.1 200 OK*} $response]} {
    set theArray(CurrentLEDState) [Xml::getXmlValue $response CurrentLEDState]
  } {
    set theArray([Xml::getXmlValue) $response CurrentLEDState] "nicht gefunden"
  }
}

#
# Reads ZoneZoneGroupAttributes and puts it into the sonosArray
#
proc GetZoneGroupAttributes { {array "sonosArray"}} {
  global info
  set response [getResponse /ZoneGroupTopology GetZoneGroupAttributes $array]
  if {[string match -nocase {HTTP/1.1 200 OK*} $response]} {
    getResponseArray $response CurrentZoneGroupName CurrentZoneGroupID CurrentZonePlayerUUIDsInGroup
  } else {
    set info(CurrentZoneGroupName) "GetZoneGroupAttributes mit Fehler beendet"
    return "GetZoneGroupAttributes mit Fehler beendet" 
  }
}

#/**
# * Play Radio station with id
# *
# * This is only a SetAVTransportURI Wrapper to switch to a radio station
proc SetRadio {id {array "sonosArray"}} { 
  global radio
  set Name dummy
  if {[regexp -nocase -- {s\d*$} $id] } {
    if {[info exists radio($id)]} {set Name $radio($id) }
  } else {
    foreach {key val} [array get radio] {
      if { [string match -nocase "$id*" $val]} {
        set id $key 
        set Name $val
        set found 1
        break
      }
    }
    if { ! [info exists found]} {
      return "ID or station not found!"
    }
  }
  set station "x-sonosapi-stream:$id?sid=254&amp;flags=32&amp;sn=0"
  set MetaData [fromHtml "&lt;DIDL-Lite xmlns:dc=&quot;http://purl.org/dc/elements/1.1/&quot; xmlns:upnp=&quot;urn:schemas-upnp-org:metadata-1-0/upnp/&quot; xmlns:r=&quot;urn:schemas-rinconnetworks-com:metadata-1-0/&quot; xmlns=&quot;urn:schemas-upnp-org:metadata-1-0/DIDL-Lite/&quot;&gt;&lt;item id=&quot;R:0/0/0&quot; parentID=&quot;R:0/0&quot; restricted=&quot;true&quot;&gt;&lt;dc:title&gt;$Name&lt;/dc:title&gt;&lt;upnp:class&gt;object.item.audioItem.audioBroadcast&lt;/upnp:class&gt;&lt;desc id=&quot;cdudn&quot; nameSpace=&quot;urn:schemas-rinconnetworks-com:metadata-1-0/&quot;&gt;SA_RINCON65031_&lt;/desc&gt;&lt;/item&gt;&lt;/DIDL-Lite&gt;"]
  SetAVTransportURI $station [toHtml $MetaData] $array
}
#
# Sets Av Transport URI
# Main SOAP method to set play URI - this is the plain SetAVTransportURI

proc SetAVTransportURI {tspuri {MetaData ""}  {array "sonosArray"}} {
  set xml [subst {<InstanceID>0</InstanceID><CurrentURI>[toHtml $tspuri]</CurrentURI><CurrentURIMetaData>$MetaData</CurrentURIMetaData>}]
  set response [getResponse /MediaRenderer/AVTransport SetAVTransportURI $array $xml]
  if {[string match -nocase {HTTP/1.1 200 OK*} $response]} {
    return "SetAVTransportURI okay" 
  } else {
    return $xml
    return "SetAVTransportURI mit Fehler beendet" 
  }
}

# Get Trackname etc and puts it into info
proc GetPositionInfo {{array "sonosArray"}} {
  global info
  set xml "<InstanceID>0</InstanceID>"
  set response [getResponse /MediaRenderer/AVTransport GetPositionInfo $array $xml]
  if {[string match -nocase {HTTP/1.1 200 OK*} $response]} {
    #log $response "w"
    getResponseArray $response Track TrackDuration upnp:album upnp:albumArtURI dc:creator dc:title r:streamContent TrackURI RelTime AbsTime RelCount AbsCount
  } else {
    return "GetPositionInfo mit Fehler beendet" 
  }

}


# Get just radio url
proc GetRadioUri {{array "sonosArray"}} {
  if {[IsRadio $array] == "0"} {
    return "It is no radio!"
  }
  global info
  GetPositionInfo $array
  set URI  $info(TrackURI)
  unset info
  return $URI
}

# Get Radio info and puts it into info
proc AskRadiotime { {id 0} {array "sonosArray"}} {
  global info
  set serial [sonosGet SerialNumber $array]
  if { $serial == "" } {
    GetZoneInfo $array
  }
  set serial [sonosGet SerialNumber $array]
  if { $id == "0" } {
    GetMediaInfo $array
    set CurrentURI $info(CurrentURI)
    regexp {x-sonosapi-stream:(s.*)\?sid.*} $CurrentURI dummy id
  }
  if {[info exists id]} {
    set url "http://opml.radiotime.com/Describe.ashx?c=nowplaying&id=$id&partnerId=Sonos&serial=$serial"
    set ret [fromHtml [getHttp $url]]
    if {[string match -nocase {*<status>200</status>*} $ret]} {
      set body [Xml::getXmlValue $ret body]
      regexp {<outline type="text" text="(.*)" guide_id=".*" key="station" image="(.*)" preset_id} $body dummy station image
      if {[info exists info]} {unset info}
      set info(ID) $id
      set info(Station) $station
      set info(Image) $image    
      regsub {<outline .*?/>} $body {} body
      set body [fromHtml [string trim $body]]
      set i 0
      while { [regexp {<outline type="text"} $body dummy] } {
        incr i
        regexp {<outline type="text" text="(.*?)".*} $body dummy text($i)
        regsub {<outline .*?/>} $body {} body
        set body [string trim $body]
      }
      switch $i {
        "1" {
          set ort $text(1)
          set info(Info) $ort   
        }
        "2" {
          set genre $text(1)
          set ort $text(2)
          set info(Genre) $genre  
          set info(Info) $ort 
        }
        default { 
          set now $text(1)
          set genre $text(2)
          set ort $text(3)
          set info(Genre) $genre  
          set info(Info) $ort 
          set info(Now_playing) $now  
        }
      }
    } {
      set info(Info)  "AskRadiotime mit Fehler beendet"
    }
  } {
    set info(Info)  "No Radio found!"
  }
}

# Is it a Radio, returns id or 0 ?
proc IsRadio {{array "sonosArray"}}  {
  global info
  GetMediaInfo $array
  if { ! [info exists info(CurrentURI)] } {
    return 0
  }
  set CurrentURI $info(CurrentURI)
  regexp {x-sonosapi-stream:(s.*)\?sid.*} $CurrentURI dummy id
  if {[info exists id]} {
    return $id
  } {
    return 0
  }

}
# play the default or another Sonos-Player
proc Play {{array "sonosArray"}} {
  set response [getResponse /MediaRenderer/AVTransport Play   $array "<InstanceID>0</InstanceID><Speed>1</Speed>"]
  if {[string match -nocase {HTTP/1.1 200 OK*} $response]} {
    return "Play okay"
  } else {
    return "Play mit Fehler beendet" 
  }

}
# Seek the default or another Sonos-Player
# @param string $arg1           Unit ("TRACK_NR" || "REL_TIME" || "SECTION")
# @param string $arg2           Target (if this Arg is not set Arg1 is considered to be "REL_TIME and the real arg1 value is set as arg2 value)

proc Seek {pos1 { pos2 'NONE'} {array "sonosArray"}} {
  if { $pos2 == "NONE" } {
    set Unit "REL_TIME"
    set position $pos1
  } else {
    set Unit $pos1 
    set position $pos2 
  }
  set xml "<InstanceID>0</InstanceID><Unit>$Unit</Unit><Target>$position</Target>"
  set response [getResponse /MediaRenderer/AVTransport Seek  $array  $xml]
  if {[string match -nocase {HTTP/1.1 200 OK*} $response]} {
    return "Seek okay"
  } else {
    return "Seek mit Fehler beendet" 
  }
}

proc SetTrack {TrackNr {array "sonosArray"}} {
  Seek "TRACK_NR" $TrackNr $array
}

proc Rewind  {{array "sonosArray"}} {
  Seek "00:00:00" "NONE" $array
}

# next the default or another Sonos-Player
proc Next {{array "sonosArray"}} {
  set response [getResponse /MediaRenderer/AVTransport Next  $array  "<InstanceID>0</InstanceID>"]
  if {[string match -nocase {HTTP/1.1 200 OK*} $response]} {
    return "Next okay"
  } else {
    return "Next mit Fehler beendet" 
  }

}

# next the default or another Sonos-Player
proc Previous {{array "sonosArray"}} {
  set response [getResponse /MediaRenderer/AVTransport Previous  $array  "<InstanceID>0</InstanceID>"]
  if {[string match -nocase {HTTP/1.1 200 OK*} $response]} {
    return "Previous okay"
  } else {
    return "Previous mit Fehler beendet" 
  }

}

# pause the default or another Sonos-Player
proc Pause {{array "sonosArray"}} {
  set response [getResponse /MediaRenderer/AVTransport Pause  $array  "<InstanceID>0</InstanceID>"]
  if {[string match -nocase {HTTP/1.1 200 OK*} $response]} {
    return "Pause okay"
  } else {
    return "Pause mit Fehler beendet" 
  }

}

# stop playing the default or another Sonos-Player
proc Stop {{array "sonosArray"}} {
  set response [getResponse /MediaRenderer/AVTransport Stop   $array "<InstanceID>0</InstanceID>"]
  if {[string match -nocase {HTTP/1.1 200 OK*} $response]} {
    return "Stop okay"
  } else {
    return "Stop mit Fehler beendet" 
  }

}

# toggle pause and play the default or another Sonos-Player
proc Toggle { {array "sonosArray"} } {
  global info
  GetTransportInfo $array
  set status $info(CurrentTransportState)
  if { "PLAYING" == "$status" } {
    Pause $array
  } else {
    Play $array
  }
}

# ramp to given volume
# "SLEEP_TIMER_RAMP_TYPE" - mutes and ups Volume per default within 17 seconds to desiredVolume
# "ALARM_RAMP_TYPE" -Switches audio off and slowly goes to volume<br>
# "AUTOPLAY_RAMP_TYPE" - very fast and smooth; Implemented from Sonos for the autoplay feature.

proc RampToVolume { {volume "" }  {ramp_type "auto" } {array "sonosArray"}} {
  switch $ramp_type {
    "SLEEP_TIMER_RAMP_TYPE" -
    "sleep" {set ramp_type "SLEEP_TIMER_RAMP_TYPE"}
    "AUTOPLAY_RAMP_TYPE" -
    "auto" {set ramp_type "AUTOPLAY_RAMP_TYPE"}
    default {set ramp_type "ALARM_RAMP_TYPE"}
  }
  if { $volume == "" } {
    set volume [sonosGet Volume $array]
  }
  if {[ string is double $volume ]} {
    set volume [ expr round($volume) ]
    if { $volume > 100 } { set volume 100 } elseif { $volume < 0 } { set volume 0 }
  } {
    set volume [sonosGet Volume $array]
  }
  set xml "<InstanceID>0</InstanceID><Channel>Master</Channel><RampType>$ramp_type</RampType><DesiredVolume>$volume</DesiredVolume>
<ResetVolumeAfter>false</ResetVolumeAfter><ProgramURI></ProgramURI>"
  set response [getResponse /MediaRenderer/RenderingControl RampToVolume $array $xml]
  if {[string match -nocase {HTTP/1.1 200 OK*} $response]} {
    sonosSet Volume $volume $array
    return "RampToVolume okay" 
  } else {
    return "RampToVolume mit Fehler beendet" 
  }
}
# Save current queue off to sonos
# If you don´t set the id to the playlist´s id you want to edit, you´ll get duplicate playlists with the same name $title!!
#
proc SaveQueue {title {id ""} {array "sonosArray"}} {
  set xml "<InstanceID>0</InstanceID><Title>$title</Title><ObjectID>$id</ObjectID>"
  set response [getResponse /MediaRenderer/AVTransport SaveQueue $array $xml]
  if {[string match -nocase {HTTP/1.1 200 OK*} $response]} {
    return "SaveQueue okay" 
  } else {
    return "SaveQueue mit Fehler beendet" 
  }
}

proc ClearQueue { {array "sonosArray"}} {
  global info
  set xml "<InstanceID>0</InstanceID>"
  set response [getResponse /MediaRenderer/AVTransport RemoveAllTracksFromQueue $array $xml]
  if {[string match -nocase {HTTP/1.1 200 OK*} $response]} {
    return "ClearQueue okay" 
  } {
    return "ClearQueue mit Fehler beendet"
  }
}

proc RemoveTrackFromQueue { trackNr {array "sonosArray"}} {
  global info
  set xml "<InstanceID>0</InstanceID><ObjectID>Q:0/$trackNr</ObjectID>"
  set response [getResponse /MediaRenderer/AVTransport RemoveTrackFromQueue $array $xml]
  if {[string match -nocase {HTTP/1.1 200 OK*} $response]} {
    return "RemoveFromQueue okay" 
  } {
    return "RemoveFromQueue mit Fehler beendet"
  }
}


proc AddURIToQueue { trackURI {array "sonosArray"}} {
  global info
  set xml "<InstanceID>0</InstanceID><EnqueuedURI>$trackURI</EnqueuedURI><EnqueuedURIMetaData></EnqueuedURIMetaData><DesiredFirstTrackNumberEnqueued>0</DesiredFirstTrackNumberEnqueued><EnqueueAsNext>1</EnqueueAsNext>"
  set response [getResponse /MediaRenderer/AVTransport AddURIToQueue $array $xml]
  if {[string match -nocase {HTTP/1.1 200 OK*} $response]} {
    return "AddURIToQueue okay" 
  } {
    return "AddURIToQueue mit Fehler beendet"
  }
}


#/**
# * Get info on actual crossfademode and puts it into the sonosArray
proc GetCrossfadeMode {{array "sonosArray"}} {
  set xml "<InstanceID>0</InstanceID>"
  set response [getResponse /MediaRenderer/AVTransport GetCrossfadeMode $array $xml]
  if {[string match -nocase {HTTP/1.1 200 OK*} $response]} {
    set cfm [Xml::getXmlValue $response CrossfadeMode]
    sonosSet CrossfadeMode $cfm $array
    return $cfm
  } else {
    sonosSet CrossfadeMode "GetCrossfadeMode mit Fehler beendet" $array
    return "GetCrossfadeMode mit Fehler beendet" 
  }

}
#/**
# * Set crossfade to true or false and puts it into the sonosArray
proc SetCrossfadeMode { {mode "0"} {array "sonosArray"}} {
  if {[string compare -nocase $mode "false"] == 0 } { 
    set mode "0" 
  } elseif { [string compare -nocase $mode "Off"] == 0} {
    set mode  "0"
  } elseif { [string compare -nocase $mode "0"] == 0} {
    set mode  "0"
  } else {
    set mode  "1"
  }
  set xml "<InstanceID>0</InstanceID><CrossfadeMode>$mode</CrossfadeMode>"
  set response [getResponse /MediaRenderer/AVTransport SetCrossfadeMode $array $xml]
  if {[string match -nocase {HTTP/1.1 200 OK*} $response]} {
    sonosSet CrossfadeMode $mode $array
    return "SetCrossfadeMode to $mode okay" 
  } else {
    return "SetCrossfadeMode mit Fehler beendet" 
  }

}

#/**
# * Gets transport actions and puts it into the sonosArray
proc GetCurrentTransportActions {{array "sonosArray"}} {
  set xml "<InstanceID>0</InstanceID>"
  set response [getResponse /MediaRenderer/AVTransport GetCurrentTransportActions $array $xml]
  if {[string match -nocase {HTTP/1.1 200 OK*} $response]} {
    set actions [Xml::getXmlValue $response Actions]
    return $actions
  } else {
    return "GetCurrentTransportActions mit Fehler beendet" 
  }

}

#/**
# * Gets transport settings  and puts it into the sonosArray
proc GetTransportInfo {{array "sonosArray"}} {
  global info
  set xml "<InstanceID>0</InstanceID>"
  set response [getResponse /MediaRenderer/AVTransport GetTransportInfo $array $xml]
  if {[string match -nocase {HTTP/1.1 200 OK*} $response]} {
    getResponseArray $response CurrentTransportState CurrentSpeed
    set info(CurrentTransportActions) [GetCurrentTransportActions] 
  } else {
    set info(CurrentTransportState) "GetTransportInfo mit Fehler beendet"
  }

}
#/**
# * Sets Playmode for a renderer (could affect more than one zone!) and puts it into the sonosArray
proc SetPlayMode {{repeat 0} {shuffle 0} {array "sonosArray"}} {
  if {[string match -nocase true $repeat]} {
    set repeat true
  } elseif { $repeat == "1"} {
    set repeat true
  } elseif {[string match -nocase On $repeat]} {
    set repeat true
  } else {
    set repeat false      
  }
  if {[string match -nocase true $shuffle]} {
    set shuffle true
  } elseif { $shuffle == "1"} {
    set shuffle true
  } elseif {[string match -nocase On $shuffle]} {
    set shuffle true
  } else {
    set shuffle false     
  }
  if { $repeat == "false" && $shuffle == "false"} {
    set pm "NORMAL"
  } elseif { $repeat == "true" && $shuffle == "false"} {
    set pm "REPEAT_ALL"
  } elseif { $repeat == "false" && $shuffle == "true"} {
    set pm "SHUFFLE_NOREPEAT"
  } else {
    set pm "SHUFFLE"
  }
  global info
  set xml "<InstanceID>0</InstanceID><NewPlayMode>$pm</NewPlayMode>"
  set response [getResponse /MediaRenderer/AVTransport SetPlayMode $array $xml]
  if {[string match -nocase {HTTP/1.1 200 OK*} $response]} {
    sonosSet shuffle $shuffle $array
    sonosSet repeat $repeat $array
    return "SetPlayMode to $pm okay" 
  } else {
    return "SetPlayMode mit Fehler beendet" 
  }
}


#/**
# * Gets transport settings for a renderer and puts it into the sonosArray
# *
# * NORMAL = SHUFFLE and REPEAT -->FALSE
# * REPEAT_ALL = REPEAT --> TRUE, Shuffle --> FALSE
# * SHUFFLE_NOREPEAT = SHUFFLE -->TRUE / REPEAT = FALSE
# * SHUFFLE = SHUFFLE and REPEAT -->TRUE </PRE>
proc GetTransportSettings {{array "sonosArray"}} {
  global info
  set xml "<InstanceID>0</InstanceID>"
  set response [getResponse /MediaRenderer/AVTransport GetTransportSettings $array $xml]
  if {[string match -nocase {HTTP/1.1 200 OK*} $response]} {
    getResponseArray $response PlayMode RecQualityMode
    set pm $info(PlayMode)
    unset info(PlayMode)
    switch $pm {
      "NORMAL" {
          set info(shuffle) false
          set info(repeat) false
        }
      "REPEAT_ALL" {
          set info(shuffle) false
          set info(repeat) true
        }
      "SHUFFLE_NOREPEAT" {
          set info(shuffle) true
          set info(repeat) false
        }
      "SHUFFLE" {
          set info(shuffle) true
          set info(repeat) true
        }
      default {
          set info(shuffle) true
          set info(repeat) false
        }
    }
    sonosSet shuffle $info(shuffle) $array
    sonosSet repeat $info(repeat) $array
    unset info
  } else {
    return "GetTransportSettings mit Fehler beendet" 
  }

}

# Gets Media Info and puts it into info
# [CurrentURI] => http://192.168.0.2:10243/WMPNSSv4/1458092455/0_ezg1ODYxQzMwLTEyNzgtNDc0Ri05MkQ0LTQxNzE1MDQ0MjMyMX0uMC40.mp3
# [CurrentURIMetaData] => <DIDL-Lite xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:upnp="urn:schemas-upnp-org:metadata-1-0/upnp/" xmlns="urn:schemas-upnp-org:metadata-1-0/DIDL-Lite/">    <item id="{85861C30-1278-474F-92D4-417150442321}.0.4" restricted="0" parentID="4">        <dc:title>Car Crazy Cutie</dc:title>        <dc:creator>Beach Boys</dc:creator>        <res size="2753092" duration="0:02:50.000" bitrate="16000" protocolInfo="http-get:*:audio/mpeg:DLNA.ORG_OP=01;DLNA.ORG_FLAGS=01500000000000000000000000000000" sampleFrequency="44100" bitsPerSample="16" nrAudioChannels="2" microsoft:codec="{00000055-0000-0010-8000-00AA00389B71}" xmlns:microsoft="urn:schemas-microsoft-com:WMPNSS-1-0/">http://192.168.0.2:10243/WMPNSSv4/1458092455/0_ezg1ODYxQzMwLTEyNzgtNDc0Ri05MkQ0LTQxNzE1MDQ0MjMyMX0uMC40.mp3</res>        <res duration="0:02:50.000" bitrate="16000" protocolInfo="http-get:*:audio/mpeg:DLNA.ORG_PN=MP3;DLNA.ORG_OP=10;DLNA.ORG_CI=1;DLNA.ORG_FLAGS=01500000000000000000000000000000" sampleFrequency="44100" nrAudioChannels="1" microso
# [title] => Car Crazy Cutie                         )

proc GetMediaInfo {{array "sonosArray"}} {
  global info
  set xml "<InstanceID>0</InstanceID>"
  set response [getResponse /MediaRenderer/AVTransport GetMediaInfo $array $xml]
  if {[string match -nocase {HTTP/1.1 200 OK*} $response]} {
    getResponseArray $response NrTracks MediaDuration CurrentURI dc:title NextURI NextURIMetaData PlayMedium RecordMedium WriteStatus
  } {
    return "GetMediaInfo mit Fehler beendet" 
  }
}

# play a message
proc playMessage { message volume {array "sonosArray"}} {
  global info playlist playlistcount sonosArray
  set title ""
  GetMediaInfo $array
  if {[info exists info(Title)]} {set title $info(Title)}
  GetPositionInfo $array
  set trackUri ""
  set track 0
  set relTime "0:00:00"
  if {[info exists info(TrackURI)]} {set trackUri $info(TrackURI)}
  if {[info exists info(Track)]} {set track $info(Track)}
  if {[info exists info(RelTime)]} {set relTime $info(RelTime)}
  set mute [GetMute $array]
  set oldVolume [GetVolume $array]
  GetSonosUUID
  GetTransportSettings
  set repeat  $sonosArray(repeat)
  set shuffle  $sonosArray(shuffle)
  GetTransportInfo $array
  set state "STOPPED"
  if {[info exists info(CurrentTransportState)]} {set state $info(CurrentTransportState)}
  GetCurrentPlaylist $array
  #puts "Playlistcount: $playlistcount"
  incr playlistcount
  #parray playlist
  #puts "Mute: $mute"
  #puts "old Volume: $oldVolume"
  #puts "Volume: $volume"
  #puts "TrackUri: $trackUri"
  #puts "Track: $track"
  #puts "State: $state"
  #puts "Shuffle: $shuffle"
  #puts "Repeat: $repeat"
  #puts "Title: $title"
  if { $trackUri == "" } {
    set wiederherstellen  ""
    #puts "keine Musik"
    # es wird keine Musik abgespielt, also einfach nur nachricht abspielen
  } elseif {[string range $trackUri 0 17] == "x-rincon-mp3radio:"} {  
    #puts "Radio"
    # zum Wiederherstellen es lief ein Radio Sender
    set wiederherstellen  "Radio"
    SetAVTransportURI "x-rincon-queue:$sonosArray(UUID)#0"
  } elseif {[string range $trackUri 0 11] == "x-file-cifs:"} {
    #puts "Playlist"
    set wiederherstellen  "Playlist"
  } elseif {[string range $trackUri 0 5] == "npsdy:"} {  
    #puts "Napster"
    # zum Wiederherstellen es lief ein Radio Sender
    set wiederherstellen  "Playlist"
  } else {  
    #puts "???"
    #puts [string range $trackUri 0 12]
    # zum Wiederherstellen es lief ein Radio Sender
    set wiederherstellen  "Playlist"
  }
  AddURIToQueue "x-file-cifs:$Cfg::messagespeicher/$message"
  SetTrack $playlistcount 
  SetMute 0
  SetVolume $volume
  Play ;# Abspielen
  set abort 0
  while { $abort == 0} {
    GetPositionInfo
    if { $info(TrackDuration) <= $info(RelTime) } {;# abgespielt
      after 3000 ;# bissel länger
      set abort 1
    }
  }
  # Wieder alten Zustand herstellen
  SetVolume $oldVolume
  SetMute $mute
  if { $wiederherstellen == "Playlist" } {
    
    # alte Playlist weiterspielen
    if { $track > 0 } {
      SetTrack $track
    }
    Seek $relTime "NONE" 
    
    # Wenn alte Playlist in Pause war dann Pause setzen an sonsten Play
    # save_Status //1 = PLAYING //2 = PAUSED_PLAYBACK //3 = STOPPED
    # $save_TransportSettings  repeat shuffle ... 
    
    # wenn Repeat oder Shuffle aktiviert ist und die Musik nicht läuft, 
    # muss die Pause gesetzt werden, da sonst die Musik anläuft
    if { $state != "PLAYING" 
      && ($shuffle == "true" || $repeat == "true" ) } {
      Pause
    }  
    if {$state == "PLAYING"} {            
      Play
    } elseif {$state == "STOPPED" } {
      Stop
    }   
  } elseif {$wiederherstellen == "Radio"} {
    
    # je nach dem ob Radio vorhier Lief oder nicht Zustand wieder herstellen.
    # status //1 = PLAYING //2 = PAUSED_PLAYBACK //3 = STOPPED
    # $save_TransportSettings  repeat shuffle ... 
    
    # alten Radiosender weiterspielen
    set MetaData [fromHtml "&lt;DIDL-Lite xmlns:dc=&quot;http://purl.org/dc/elements/1.1/&quot; xmlns:upnp=&quot;urn:schemas-upnp-org:metadata-1-0/upnp/&quot; xmlns:r=&quot;urn:schemas-rinconnetworks-com:metadata-1-0/&quot; xmlns=&quot;urn:schemas-upnp-org:metadata-1-0/DIDL-Lite/&quot;&gt;&lt;item id=&quot;R:0/0/0&quot; parentID=&quot;R:0/0&quot; restricted=&quot;true&quot;&gt;&lt;dc:title&gt;$title&lt;/dc:title&gt;&lt;upnp:class&gt;object.item.audioItem.audioBroadcast&lt;/upnp:class&gt;&lt;desc id=&quot;cdudn&quot; nameSpace=&quot;urn:schemas-rinconnetworks-com:metadata-1-0/&quot;&gt;SA_RINCON65031_&lt;/desc&gt;&lt;/item&gt;&lt;/DIDL-Lite&gt;"]
    SetAVTransportURI $trackUri [toHtml $MetaData] $array
    if {$state == "PLAYING"} {
      Play
    }
  }
  # sometimes it doesn't play after message
  if {$state == "PLAYING"} {
    GetTransportInfo $array
    if {[info exists info(CurrentTransportState)]}   {
      set newstate $info(CurrentTransportState)
    }
    if {$state != $newstate} {
      Play
    }
  }
  RemoveTrackFromQueue $playlistcount
}

#/**
# * Sets current volume information from player
proc SetVolume {volume {array "sonosArray"}} {
  if {[ string is double $volume ]} {
    set volume [ expr round($volume) ]
    if { $volume > 100 } { set volume 100 } elseif { $volume < 0 } { set volume 0 }
  } {
    set volume [sonosGet Volume $array]
  }
  set xml "<InstanceID>0</InstanceID><Channel>Master</Channel><DesiredVolume>$volume</DesiredVolume>"
  set response [getResponse /MediaRenderer/RenderingControl SetVolume $array $xml]
  if {[string match -nocase {HTTP/1.1 200 OK*} $response]} {
    sonosSet Volume $volume $array]
    return "SetVolume to $volume okay" 
  } else {
    return "SetVolume mit Fehler beendet" 
  }

}

#/**
# * Gets current volume information from player
proc GetVolume {{array "sonosArray"}} {
  set xml "<InstanceID>0</InstanceID><Channel>Master</Channel>"
  set response [getResponse /MediaRenderer/RenderingControl GetVolume $array $xml]
  if {[string match -nocase {HTTP/1.1 200 OK*} $response]} {
    return [Xml::getXmlValue $response CurrentVolume]
  } else {
    return "GetVolume mit Fehler beendet" 
  }

}

#/**
# * Sets mute/ unmute for a player
proc SetMute {{mute 1} {array "sonosArray"}} {
  if {[string match -nocase true $mute]} {
    set mute 1
  } elseif {[string match -nocase On $mute]} {
    set mute 1
  } elseif { $mute == "1"} {
    set mute 1
  } else {
    set mute 0      
  }
  set xml "<InstanceID>0</InstanceID><Channel>Master</Channel><DesiredMute>$mute</DesiredMute>"
  set response [getResponse /MediaRenderer/RenderingControl SetMute $array $xml]
  if {[string match -nocase {HTTP/1.1 200 OK*} $response]} {
    return "SetMute to $mute okay" 
  } else {
    return "SetMute mit Fehler beendet" 
  }

}

#/**
# * Gets mute/ unmute for a player
proc GetMute { {array "sonosArray"}} {
  set xml "<InstanceID>0</InstanceID><Channel>Master</Channel>"
  set response [getResponse /MediaRenderer/RenderingControl GetMute $array $xml]
  if {[string match -nocase {HTTP/1.1 200 OK*} $response]} {
    return [Xml::getXmlValue $response CurrentMute] 
  } else {
    return "GetMute mit Fehler beendet" 
  }

}

proc VolumeUp {{array "sonosArray"}} {
  set mute [GetMute $array]
  if { $mute == "1"} {
    puts [SetMute 0 $array]
  }
  set volume [GetVolume $array]
  if { $volume < [expr {100 - $Cfg::volumeup}] } {
    set volume [expr {$volume + $Cfg::volumeup}]
  } {
    set volume 100
  }
  SetVolume $volume $array
}
proc VolumeDown {{array "sonosArray"}} {
  set mute [GetMute $array]
  if { $mute == "1"} {
    puts [SetMute 0 $array]
  }
  set volume [GetVolume $array]
  if { $volume > [expr {$Cfg::volumedown}] } {
    set volume [expr {$volume - $Cfg::volumedown}]
  } {
    set volume 0
  }
  SetVolume $volume $array
}

proc Udp { {verbose 0} } {
  variable bin

  set file_data ""
  catch {
      set file_data [exec $bin/newudp | grep -i -B 1 Sonos\/]
  }
  set ZoneGroupTopology(ZoneGroupCount) 0
  if { $file_data != ""} {
    regsub -all "\n\n" $file_data "" file_data
    set player [split $file_data "\n"]
    set location [lindex $player 0 ]
    regsub -all "LOCATION: http://" $location "" location
    regsub -all ":1400/xml/device_description.xml" $location "" topo
    set response [getResponse /ZoneGroupTopology GetZoneGroupState $topo]
    return [ParseZonegroups $response $verbose]
  } else {
    return "No Sonosplayer found!"
  }     
}

proc ParseZonegroups {response {verbose 0} } {
  global ZoneGroupTopology
  global strZoneGroupState  
  catch {unset ZoneGroupTopology}
  set l {}
  set bridge {}
  if [Xml::Parse $response ZoneGroups] {
    set zonegroups  $Xml::body
    #set zonegroups [string map {"><" ">\n<"} $Xml::body]
    #puts $zonegroups
    set i 0
    while { [Xml::Parse $zonegroups ZoneGroup] } {
      incr i
      if {$Xml::attribute != "" } {
          foreach {key val} $Xml::attribute {
            set ZoneGroupTopology($i,$key) "$val"
          }
      }
      set zonegroupmember $Xml::body
      set zonegroups $Xml::rest 
      set j 0
      set ZoneGroupTopology($i,ZoneGroupMemberCount) $j
      while { [Xml::Parse $zonegroupmember ZoneGroupMember] } {
        incr j
        set ZoneGroupTopology($i,ZoneGroupMemberCount) $j
        set zonegroupmember $Xml::rest 
        if {$Xml::attribute != "" } {
          set ZoneGroupTopology($i,$j,Invisible) 0
          set ZoneGroupTopology($i,$j,IsZoneBridge) 0
          foreach {key val} $Xml::attribute {
            set ZoneGroupTopology($i,$j,$key) "$val"
          }
          if { ($ZoneGroupTopology($i,$j,IsZoneBridge) != "1") &&  ($ZoneGroupTopology($i,$j,Invisible) == "0")} {
              set ZoneGroupTopology($i,ZoneName) $ZoneGroupTopology($i,$j,ZoneName)
              lappend l $ZoneGroupTopology($i,$j,ZoneName)
              regexp "http://(.*?):1400/xml/" $ZoneGroupTopology($i,$j,Location) junk ip
              lappend l $ip
          }
          if { $ZoneGroupTopology($i,$j,IsZoneBridge) == "1" } {
              lappend bridge $ZoneGroupTopology($i,$j,ZoneName)
              regexp "http://(.*?):1400/xml/" $ZoneGroupTopology($i,$j,Location) junk ip
              lappend bridge $ip
          }
        }
        set ZoneGroupTopology($i,$j,SatelliteCount) 0
        if {$Xml::body != "" } {
          set k 0
          set satellite $Xml::body
          while { [Xml::Parse $satellite Satellite ] } {
            incr k
            set ZoneGroupTopology($i,$j,SatelliteCount) $k
            set satellite $Xml::rest 
            if {$Xml::attribute != "" } {
              set ZoneGroupTopology($i,$j,Satellite$k,Invisible) 0
              set ZoneGroupTopology($i,$j,Satellite$k,IsZoneBridge) 0
              foreach {key val} $Xml::attribute {
                set ZoneGroupTopology($i,$j,Satellite$k,$key) "$val"
              }
            }
          }
        }
      }            
    }
    set ZoneGroupTopology(ZoneGroupCount) $i
  }
  # sort l   lsort -stride gibts in tcl 8.2 nicht
  set newlist {}
  foreach {name ip} $l {
      lappend newlist [list $name $ip]
  }
  set l [join [lsort -index 0 $newlist]]
  set sos ""
  foreach {key value} $l {
    set sos "$sos$key,$value," 
  }
  regsub ",\$" $sos "" strZoneGroupState
  saveFile ZoneGroupState.txt $strZoneGroupState
  set bridge [concat $bridge $l]
   set sos ""
  foreach {key value} $bridge {
    set sos "$sos$key,$value," 
  }
  regsub ",\$" $sos "" strZoneGroupStateBridge


  if {$verbose == "1"} {
      parray ZoneGroupTopology
  } elseif {$verbose == "2"} {
    regsub -all "><" $response ">\n<" dummy
    puts $dummy
  } elseif {$verbose == "3"} {
    return $strZoneGroupState
  } elseif {$verbose == "4"} {
    return $strZoneGroupStateBridge
  }
  return $l
}
###
proc testMember { {onlyFirst "1"} {array "sonosArray"}} {
  global ZoneGroupTopology
  set ip  [sonosGet IP]
  if {"$ip" == ""} {
    set ret "Zone not found!"
    return $ret
  }
  set name  [sonosGet Zone ]
  set response [getResponse /ZoneGroupTopology GetZoneGroupState $ip]
  ParseZonegroups $response 
  if { ! [info exists ZoneGroupTopology(ZoneGroupCount)] } {
    set ret "Topology parse error!"
    return $ret
  }
  for {set x 1} {$x<=$ZoneGroupTopology(ZoneGroupCount)} {incr x} {
    if {$ZoneGroupTopology($x,1,IsZoneBridge) == "0"} {
      set count 0
      set l {}
      for { set y 1} {$y<=$ZoneGroupTopology($x,ZoneGroupMemberCount) } {incr y} {
        if { $ZoneGroupTopology($x,$y,Invisible) == "0" } {
          incr count
          #puts "count: $count hier: $x $y $ZoneGroupTopology($x,$y,ZoneName)"
          if {$ZoneGroupTopology($x,$y,ZoneName) != $name} {
            lappend l $ZoneGroupTopology($x,$y,ZoneName)
          }
        }
      }
      if { $count == "1" && $ZoneGroupTopology($x,1,ZoneName) == $name} {
        set ret "$name is alone"
        return "";#$ret""
      } 
      if { $count > "1" } {
        set ret $l
      }
     
    }
  }
  if { $onlyFirst == "1"} {
    return "[lindex $ret 0]"
  } {
    return $ret
  }
}
