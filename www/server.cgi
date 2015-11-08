#!/usr/bin/env tclsh
source [file join [file dirname [info script]] sonos2inc.tcl] ;# Include-File
set info  [encoding convertfrom utf-8 [url-decode [ gets stdin ]]]
regexp "(Logtext=.*)" $info dummy info
puts "Content-type:text/html\n"
set append "a"
set Logtext ""
set udp ""
if { $info != "" } {
    foreach {line} [split $info "&"] {
        switch -glob -- $line {
            Logtext* {
               regexp  "Logtext=(.*)" "$line" dummy Logtext
             }
            append* {
                set append "w"
            }
            udp* {
                set udp 1
            }
        }
    }
}
if { $Logtext != "" } {
    log $Logtext $append
}
if { $udp != "" } {
    puts -nonewline [Udp 3]
}
