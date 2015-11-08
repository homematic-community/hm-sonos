#!/usr/bin/env tclsh
source [file join [file dirname [info script]] ../sonos2inc.tcl] ;# Include-File


# read POST
set radio ""
set sonoszone ""
set stdvolume ""
set volumeup ""
set volumedown ""
set messagevolume ""
set messagespeicher ""
set timeout ""
set helpRadio ""
set helpSonoszone ""
set helpStdvolume ""
set helpVolumeup ""
set helpVolumedown ""
set helpMessagevolume ""
set helpMessagespeicher ""
set helpTimeout ""
set message ""
set button ""
set udp ""
set udpbutton ""
set info  [encoding convertfrom utf-8 [url-decode [ gets stdin ]]]
regexp "(sonoszone=.*)" $info dummy info
regsub -all "\r" $info {} info ;#" %>
regsub -all {\&} $info "\r" info ;#" %>
if { $info != "" } {
    foreach {line} [split $info "\r"] {
        switch -glob -- $line {
            sonoszone=* {
                regexp "sonoszone=(.*)" "$line" dummy sonoszone
            }
            radio=* {
                regexp  "radio=(.*)" "$line" dummy radio
            }
            stdvolume=* {
                regexp  "stdvolume=(.*)" "$line" dummy stdvolume
            }
            volumeup=* {
                regexp  "volumeup=(.*)" "$line" dummy volumeup
            }
            volumedown=* {
                regexp  "volumedown=(.*)" "$line" dummy volumedown
            }
            messagevolume=* {
                regexp  "messagevolume=(.*)" "$line" dummy messagevolume
            }
            messagespeicher* {
                regexp  "messagespeicher=(.*)" "$line" dummy messagespeicher
            }
            timeout* {
                regexp  "timeout=(.*)" "$line" dummy timeout
            }
            resetBtn* {
                regexp  "resetBtn=(.*)" "$line" dummy button
            }
            udpbutton* {
                regexp  "udpbutton=(.*)" "$line" dummy udpbutton
            }
            udp* {
                regexp  "udp=(.*)" "$line" dummy udp
            }
            js_aktiv* {
                regexp  "js_aktiv=(.*)" "$line" dummy js_aktiv
                if { $js_aktiv == "0"} {
                    set message [getMessage "Sie haben Javascript deaktiviert, deshalb funktionieren manche Formulare nur eingeschränkt! <strong>Bitte aktivieren Sie wenn möglich Javascript</strong>" warning]
                }
            }
        }
    }
    if { $button != ""} {
        set message "$message [getMessage "Daten  neu geladen" info]" 
        set button "reload"
    } elseif { $udp == "1" || $udpbutton == "udp" } {
        Cfg::Load
        set Cfg::sonoszone [Udp] 
        Cfg::Save
        set message "$message [getMessage "Sonoszonen per Udp geladen" info]" 
        set button "reload"
    } else {
        if {$sonoszone != ""} {
            if { [string length $sonoszone] < 20  } { 
                set helpSonoszone "<p class='text-danger'><small>Die Sonoszonen müssen mindestens 20 Zeichen lang sein</small></p>"
            } 
        } else {
            set helpSonoszone "<p class='text-danger'><small>Die Sonoszonen müssen eingegeben werden</small></p>"
        }
        if {$radio != ""} {
            if { [string length $radio] < 5  } { 
                set helpRadio "<p class='text-danger'><small>Die Radioliste sollte mindestens 5 Zeichen lang sein</small></p>"
            } 
        }
        if {$messagespeicher == ""} {
            set helpMessagespeicher "<p class='text-danger'><small>Der Messagepfad muss eingegeben werden</small></p>"
        }
        if {$stdvolume != ""} {
            if [string is integer $stdvolume] {
                set stdvolume [ expr round($stdvolume) ]
                if { $stdvolume > 100 || $stdvolume < 0 } { 
                    set helpStdvolume "<p class='text-danger'><small>Das Standard-Volume muss zwischen 0 und 100 sein</small></p>"
                } 
            } else {
                set helpStdvolume  "<p class='text-danger'><small>Das Standard-Volume muss eine ganze Zahl sein</small></p>"
            }
        } else {
            set helpStdvolume "<p class='text-danger'><small>Das Standard-Volume muss eingegeben werden</small></p>"
        }
        if {$timeout != ""} {
            if [string is integer $timeout] {
                set timeout [ expr round($timeout) ]
                if { $timeout > 10 || $timeout < 1 } { 
                    set helpTimeout "<p class='text-danger'><small>Das Timeout muss zwischen 1 und 10 sein</small></p>"
                } 
            } else {
                set helpTimeout  "<p class='text-danger'><small>Das Timeout muss eine ganze Zahl sein</small></p>"
            }
        } else {
            set helpTimeout "<p class='text-danger'><small>Das Timeout muss eingegeben werden</small></p>"
        }
        if {$volumeup != ""} {
            if [string is integer $volumeup] {
                set volumeup [ expr round($volumeup) ]
                if { $volumeup > 20 || $volumeup < 1 } { 
                    set helpVolumeup "<p class='text-danger'><small>Das VolumeUp muss zwischen 1 und 20 sein</small></p>"
                } 
            } else {
                set helpVolumeup  "<p class='text-danger'><small>Das VolumeUp muss eine ganze Zahl sein</small></p>"
            }
        } else {
            set helpVolumeup "<p class='text-danger'><small>Das VolumeUp muss eingegeben werden</small></p>"
        }
        if {$volumedown != ""} {
            if [string is integer $volumedown] {
                set volumedown [ expr round($volumedown) ]
                if { $volumedown > 20 || $volumedown < 1 } { 
                    set helpVolumedown "<p class='text-danger'><small>Das VolumeDown muss zwischen 1 und 20 sein</small></p>"
                } 
            } else {
                set helpVolumedown  "<p class='text-danger'><small>Das VolumeDown muss eine ganze Zahl sein</small></p>"
            }
        } else {
            set helpVolumedown "<p class='text-danger'><small>Das VolumeDown muss eingegeben werden</small></p>"
        }
        if {$messagevolume != ""} {
            if [string is integer $messagevolume] {
                set messagevolume [ expr round($messagevolume) ]
                if { $messagevolume > 100 || $messagevolume < 0 } { 
                    set helpMessagevolume "<p class='text-danger'><small>Das Message-Volume muss zwischen 0 und 100 sein</small></p>"
                } 
            } else {
                set helpMessagevolume  "<p class='text-danger'><small>Das Message-Volume muss eine ganze Zahl sein</small></p>"
            }
        } else {
            set helpMessagevolume "<p class='text-danger'><small>Das Message-Volume muss eingegeben werden</small></p>"
        }
        if { "$helpMessagevolume$helpVolumedown$helpVolumeup$helpStdvolume$helpMessagespeicher$helpSonoszone$helpRadio$helpTimeout" != ""} {
            set message "$message [getMessage "Die Daten konnten wegen Eingabefehlern nicht gespeichert werden!" danger]" 

        } else {
            set message "$message [getMessage "Daten  gespeichert" success]" 
            set Cfg::stdvolume $stdvolume
            set Cfg::volumeup $volumeup
            set Cfg::volumedown $volumedown
            set Cfg::messagevolume $messagevolume
            set Cfg::messagespeicher $messagespeicher
            set Cfg::timeout $timeout
            if { $::tcl_version != "8.2" } {
              set Cfg::sonoszone [regexp -all -inline {\S+} $sonoszone] 
            } else {
              regsub -all {\s+} $sonoszone " " Cfg::sonoszone ;# whitespace zu Space
            }
            regsub -all "\r" $radio {} radio 
            set radio [split $radio "\n"]
            set Cfg::radio {}
            foreach line $radio {
                set line [string trim $line]
                set sid [lindex $line 0]
                set station [string trim [join [lreplace $line 0 0 {}]]]
                lappend Cfg::radio $sid $station
            }
            Cfg::Save
            set button "reload"
        }
    }
} else {
    set button "reload"
}
if {$button == "reload"} {
    Cfg::Load
    set radio ""
    regsub -all {(\d{1,3}.\d{1,3}.\d{1,3}.\d{1,3})\s+} $Cfg::sonoszone "\\1\n" sonoszone ;#" %>
    foreach {sid station} $Cfg::radio {
        set radio "$radio\n$sid $station"
    }
    set timeout $Cfg::timeout
    set stdvolume $Cfg::stdvolume
    set volumeup $Cfg::volumeup
    set volumedown $Cfg::volumedown
    set messagevolume $Cfg::messagevolume
    set messagespeicher $Cfg::messagespeicher
}
set content [loadFile settings.html]
set zone ""
parseQuery
if [info exists args(zone)] {
    if {$args(zone) != "" && $args(zone) != "fehlt"} { set zone "?zone=$args(zone)"}
}
regsub -all {<%zone%>} $content  $zone content ;#" %>
regsub -all {<%message%>} $content  $message content ;#" %>
regsub -all {<%sonoszone%>} $content "$sonoszone" content ;#" %>
regsub -all {<%stdvolume%>} $content $stdvolume content ;#" %>
regsub -all {<%volumeup%>} $content $volumeup content ;#" %>
regsub -all {<%volumedown%>} $content $volumedown content ;#" %>
regsub -all {<%messagevolume%>} $content $messagevolume content ;#" %>
regsub -all {<%messagespeicher%>} $content $messagespeicher content ;#" %>
regsub -all {<%timeout%>} $content $timeout content ;#" %>
regsub -all {<%radio%>} $content $radio content ;#" %>
regsub -all {<%helpSonoszone%>} $content "$helpSonoszone" content ;#" %>
regsub -all {<%helpStdvolume%>} $content $helpStdvolume content ;#" %>
regsub -all {<%helpVolumeup%>} $content $helpVolumeup content ;#" %>
regsub -all {<%helpVolumedown%>} $content $helpVolumedown content ;#" %>
regsub -all {<%helpMessagevolume%>} $content $helpMessagevolume content ;#" %>
regsub -all {<%helpMessagespeicher%>} $content $helpMessagespeicher content ;#" %>
regsub -all {<%helpTimeout%>} $content $helpTimeout content ;#" %>
regsub -all {<%helpRadio%>} $content $helpRadio content ;#" %>

puts "Content-type:text/html\n"
puts $content
