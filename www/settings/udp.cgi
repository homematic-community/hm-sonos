#!/usr/bin/env tclsh
source [file join [file dirname [info script]] ../sonos2inc.tcl] ;# Include-File
Cfg::Load
global ZoneGroupTopology
set udp [Udp]
if { $udp != "No Sonosplayer found!" } {
  proc GetChannelMapSet {i j {k ""}} {
    global ZoneGroupTopology
    if {$k !=""} {set k ",Satellite$k"}
    set ChannelMapSet ""
      if [info exists ZoneGroupTopology($i,$j$k,ChannelMapSet)] {
        regexp "$ZoneGroupTopology($i,$j$k,UUID):(\[^;\]*)" $ZoneGroupTopology($i,$j$k,ChannelMapSet) dummy ChannelMapSet
      }
      if [info exists ZoneGroupTopology($i,$j$k,HTSatChanMapSet)] {
        regexp "$ZoneGroupTopology($i,$j$k,UUID):(\[^;\]*)" $ZoneGroupTopology($i,$j$k,HTSatChanMapSet) dummy ChannelMapSet
      }
      regsub "LF,RF" $ChannelMapSet "-LR" ChannelMapSet
      regsub "LF,LF" $ChannelMapSet "-L" ChannelMapSet
      regsub "RF,RF" $ChannelMapSet "-R" ChannelMapSet
      regsub "SW" $ChannelMapSet "-Subwoofer" ChannelMapSet
    return $ChannelMapSet
  }
  proc GetDescription {url} {
    set descr [getHttp $url]
    set l {}
    regexp {</specVersion>\s*<device>(.*?)</device>\s*</root>} $descr dummy descr
    if [Xml::Parse $descr friendlyName] {
      lappend l friendlyName $Xml::body
    } else {
      lappend l friendlyName {}
    }
    if [Xml::Parse $descr displayName] {
      lappend l displayName $Xml::body
    } else {
      lappend l displayName {}
    }
    if [Xml::Parse $descr roomName] {
      lappend l roomName $Xml::body
    } else {
      lappend l roomName {}
    }
    if [Xml::Parse $descr modelName] {
      lappend l modelName $Xml::body
    } else {
      lappend l modelName {}
    }
    if [Xml::Parse $descr icon] {
      if [Xml::Parse $Xml::body url] {
        lappend l icon $Xml::body
      } else {
        lappend l icon {}
      }
    } else {
      lappend l icon {}
    }
    return $l
  }

  set tt ""
  for {set i 1} {$i <= $ZoneGroupTopology(ZoneGroupCount)} {incr i} {
    set t ""
    set coordinator $ZoneGroupTopology($i,Coordinator)
    for {set j 1} {$j <= $ZoneGroupTopology($i,ZoneGroupMemberCount)} {incr j} {
      set ChannelMapSet [GetChannelMapSet $i $j]
      array set Description [GetDescription $ZoneGroupTopology($i,$j,Location)]
      regexp {http://(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}):1400/xml/device_description.xml} $ZoneGroupTopology($i,$j,Location) dummy Location
      # <span class='glyphicon glyphicon-search' aria-hidden='true'></span>
      if {$ZoneGroupTopology($i,$j,Invisible) == "1"} {
          set inv "<td data-toggle='tooltip' data-placement='left' title='Bridges, Satellites sowie Zonemembers, die nicht Koordinator sind, sind unsichtbar!' class='text-center vert-align'></td>"
      } else {
          set inv "<td data-toggle='tooltip' data-placement='left' title='Zonegroupkoordinators sind sichtbar!' class='text-center vert-align'><span class='glyphicon glyphicon-ok' aria-hidden='true'></span></td>"
      }
      if {$ZoneGroupTopology($i,$j,IsZoneBridge) == "1"} {
          set coordStr " (Koordinator)"
          set cl " class='warning'"
          set bri "<td data-toggle='tooltip' data-placement='left' title='Eine Bridge kann nicht als Player angesteuert werden' class='text-center vert-align'><span class='glyphicon glyphicon-ok' aria-hidden='true'></span></td>"
          set BRIDGE "<tr$cl><td class='vert-align' data-toggle='tooltip' data-placement='left' title='ID: $ZoneGroupTopology($i,ID)'>$ZoneGroupTopology($i,$j,ZoneName)</td>
                 <td class='vert-align'><img class='sonos' src='http://$Location:1400$Description(icon)'></td>
                 <td class='vert-align' data-toggle='tooltip' data-placement='left' title='UUID: $ZoneGroupTopology($i,$j,UUID)'>
                 $ZoneGroupTopology($i,$j,ZoneName)$ChannelMapSet$coordStr</td><td class='vert-align' data-toggle='tooltip' data-placement='left' title='$Description(friendlyName)'>$Location</td>
                 <td class='vert-align' data-toggle='tooltip' data-placement='left' title='$Description(friendlyName)'>$Description(modelName)</td>$bri$inv</tr>\n"
      } elseif {$coordinator == $ZoneGroupTopology($i,$j,UUID)} {
        #set t "ZoneGroup: $ZoneGroupTopology($i,$j,ZoneName) ID: $ZoneGroupTopology($i,ID)\n$t"
        set coordStr " (Koordinator)"
        set cl " class='active'"
        set bri "<td data-toggle='tooltip' data-placement='left' title='$ZoneGroupTopology($i,$j,ZoneName) ist keine Bridge'></td>"
          set r "<tr$cl><td class='vert-align' data-toggle='tooltip' data-placement='left' title='ID: $ZoneGroupTopology($i,ID)'>$ZoneGroupTopology($i,$j,ZoneName)</td>
                 <td class='vert-align'><img class='sonos' src='http://$Location:1400$Description(icon)'></td>
                 <td class='vert-align' data-toggle='tooltip' data-placement='left' title='UUID: $ZoneGroupTopology($i,$j,UUID)'> "
          set t "$r$ZoneGroupTopology($i,$j,ZoneName)$ChannelMapSet$coordStr</td><td class='vert-align' data-toggle='tooltip' data-placement='left' title='$Description(friendlyName)'>$Location</td>
                 <td class='vert-align' data-toggle='tooltip' data-placement='left' title='$Description(friendlyName)'>$Description(modelName)</td>$bri$inv</tr>\n$t"
      } else {
        set coordStr ""
        set cl ""
         set bri "<td data-toggle='tooltip' data-placement='left' title='$ZoneGroupTopology($i,$j,ZoneName) ist keine Bridge'></td>"
         set t "$t<tr$cl><td data-toggle='tooltip' data-placement='left' title='ID: $ZoneGroupTopology($i,ID)'></td>
                   <td class='vert-align'><img class='sonos' src='http://$Location:1400$Description(icon)'></td><td class='vert-align' data-toggle='tooltip' data-placement='left' title='UUID: $ZoneGroupTopology($i,$j,UUID)'> "
          set t "$t$ZoneGroupTopology($i,$j,ZoneName)$ChannelMapSet$coordStr</td><td class='vert-align' data-toggle='tooltip' data-placement='left' title='$Description(friendlyName)'>$Location</td>
                 <td class='vert-align' data-toggle='tooltip' data-placement='left' title='$Description(friendlyName)'>$Description(modelName)</td>$bri$inv</tr>\n"
      }
      #parray Description
      #$ZoneGroupTopology($i,$j,UUID)$coordStr 
      for { set k 1} {$k <= $ZoneGroupTopology($i,$j,SatelliteCount)} {incr k} {
        set ChannelMapSet [GetChannelMapSet $i $j $k]
        array set Description [GetDescription $ZoneGroupTopology($i,$j,Satellite${k},Location)]
        regexp {http://(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}):1400/xml/device_description.xml} $ZoneGroupTopology($i,$j,Satellite${k},Location) dummy Location
        #set t  "$t\t\tSatellite: $ZoneGroupTopology($i,$j,Satellite${k},ZoneName)$ChannelMapSet\t$ZoneGroupTopology($i,$j,UUID)$coordStr Location: $Location\n"
          if {$ZoneGroupTopology($i,$j,Satellite${k},Invisible) == "1"} {
              set inv "<td data-toggle='tooltip' data-placement='left' title='Bridges, Satellites sowie Zonemembers, die nicht Koordinator sind, sind unsichtbar!' class='text-center vert-align'></td>"
           } else {
              set inv "<td class='text-center vert-align'><span class='glyphicon glyphicon-ok' aria-hidden='true'></span></td>"
          }
        set bri "<td data-toggle='tooltip' data-placement='left' title='$ZoneGroupTopology($i,$j,Satellite${k},ZoneName) ist keine Bridge'></td>"
        set t "$t<tr><td data-toggle='tooltip' data-placement='left' title='ID: $ZoneGroupTopology($i,ID)'></td>
                 <td class='vert-align'><img class='sonos' src='http://$Location:1400$Description(icon)'></td><td class='vert-align' data-toggle='tooltip' data-placement='left' title='UUID: $ZoneGroupTopology($i,$j,Satellite${k},UUID)'> "
        set t "$t$ZoneGroupTopology($i,$j,Satellite${k},ZoneName)$ChannelMapSet (Satellite)</td><td class='vert-align' data-toggle='tooltip' data-placement='left' title='$Description(friendlyName)'>$Location</td>
               <td class='vert-align' data-toggle='tooltip' data-placement='left' title='$Description(friendlyName)'>$Description(modelName)</td>$bri$inv</tr>\n"
      }
    }
    set tt "$tt$t"
  }
  if {![info exists env(HTTP_HOST)] } {
      parray ZoneGroupTopology
      exit
  }
}
  
# read POST
set message ""
set info  [encoding convertfrom utf-8 [url-decode [ gets stdin ]]]
regexp "(js_aktiv=.*)" $info dummy info
regsub -all "\r" $info {} info ;#" %>
regsub -all {\&} $info "\r" info ;#" %>
if { $info != "" } {
    foreach {line} [split $info "\r"] {
        switch -glob -- $line {
            udpbutton* {
                set Cfg::sonoszone $udp
                Cfg::Save
                set message "$message [getMessage "Zoneplayers in Config gespeichert" info]" 
            }
            js_aktiv* {
                regexp  "js_aktiv=(.*)" "$line" dummy js_aktiv
                if { $js_aktiv == "0"} {
                    set message [getMessage "Sie haben Javascript deaktiviert, deshalb funktionieren manche Formulare nur eingeschränkt! <strong>Bitte aktivieren Sie wenn möglich Javascript</strong>" warning]
                }
            }
        }
    }
}
set content [loadFile udp.html]
set zone ""
parseQuery
if [info exists args(zone)] {
    if {$args(zone) != "" && $args(zone) != "fehlt"} { set zone "?zone=$args(zone)"}
}
regsub -all {<%zone%>} $content  $zone content ;#" %>
regsub -all {<%message%>} $content  $message content ;#" %>
if { $udp != "No Sonosplayer found!" } {
  regsub -all {<%table%>} $content  "$BRIDGE$tt" content ;#" %>
} else {
   regsub -all {<table .*?</form>} $content  [subst {<div class="panel panel-warning">
  <div class="panel-heading"><h3 class="panel-title">$udp</h3></div>
  <div class="panel-body">
    Die UDP-Anfrage hat in Ihrem Netzwerk keine Sonosplayer gefunden.
  </div>
</div>
}] content ;#" %> 
}

puts "Content-type:text/html\n"
puts $content
