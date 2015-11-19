#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <arpa/inet.h>
#include <netinet/in.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <netdb.h>
#include <unistd.h>
#include <regex.h>

#define BUF_SIZE 1024
#define SERVER "239.255.255.250"
#define PORT 1900   //The port on which to send data
#define TIMEOUT 5
#define MAX_ERROR_MSG 0x1000

static int compile_regex (regex_t * r, const char * regex_text)
{
    int status = regcomp (r, regex_text, REG_EXTENDED|REG_ICASE);
    if (status != 0) {
    char error_message[MAX_ERROR_MSG];
    regerror (status, r, error_message, MAX_ERROR_MSG);
        printf ("Regex error compiling '%s': %s\n",
                 regex_text, error_message);
        return 1;
    }
    return 0;
}

/*
  Match the string in "to_match" against the compiled regular
  expression in "r".
 */

static int match_regex (regex_t * r, const char * to_match)
{
    /* "P" is a pointer into the string which points to the end of the
       previous match. */
    const char * p = to_match;
    /* "N_matches" is the maximum number of matches allowed. */
    const int n_matches = 1;
    /* "M" contains the matches found. */
    regmatch_t m[n_matches];

    int nomatch = regexec (r, p, n_matches, m, 0);
    if (nomatch) {
        return 0;
    }
    return 1;
}

int main(int argc, const char * argv[]) {
    struct timeval timeout;
    timeout.tv_sec = TIMEOUT;
    timeout.tv_usec = 0;
    
    regex_t r;
    const char * regex_text;
    regex_text = "Sonos";
    compile_regex(& r, regex_text);
    socklen_t len = sizeof(struct sockaddr_in);
    char buf[BUF_SIZE];
    struct hostent *host;
    int n, s;
    char message[] = "M-SEARCH * HTTP/1.1\r\nHOST: 239.255.255.250:1900\r\nMAN: \"ssdp:discover\"\r\nMX: 3\r\nST: urn:schemas-upnp-org:device:ZonePlayer:1\r\nUSER-AGENT: UDAP/2.0\r\n\r\n";
    
    
    host = gethostbyname(SERVER);
    if (host == NULL) {
        perror("gethostbyname");
        return 1;
    }
    
    
    /* initialize socket */
    if ((s=socket(AF_INET, SOCK_DGRAM, IPPROTO_UDP)) == -1) {
        perror("socket");
        return 1;
    }
    if (setsockopt (s, SOL_SOCKET, SO_RCVTIMEO, (char *)&timeout,
                    sizeof(timeout)) < 0)
        perror("setsockopt failed\n");
    
    if (setsockopt (s, SOL_SOCKET, SO_SNDTIMEO, (char *)&timeout,
                    sizeof(timeout)) < 0)
        perror("setsockopt failed\n");    struct sockaddr_in server;
    
    /* initialize server addr */
    memset((char *) &server, 0, sizeof(struct sockaddr_in));
    server.sin_family = AF_INET;
    server.sin_port = htons(PORT);
    server.sin_addr = *((struct in_addr*) host->h_addr);
    
    /* send message */
    if (sendto(s, message, strlen(message), 0, (struct sockaddr *) &server, len) == -1) {
        perror("sendto()");
        return 1;
    }
    
    /* receive echo.
     ** for single message, "while" is not necessary. But it allows the client
     ** to stay in listen mode and thus function as a "server" - allowing it to
     ** receive message sent from any endpoint.
     */
    while ((n = recvfrom(s, buf, BUF_SIZE, 0, (struct sockaddr *) &server, &len)) != -1) {
        /*printf("Received from %s:%d: ",
         inet_ntoa(server.sin_addr),
         ntohs(server.sin_port)); */
        fflush(stdout);
        if(write(1, buf, n) == -1 ||
           write(1, "\n", 1) == -1)
        {
          return 1;
        }

        int i = match_regex(& r, buf);
        if ( i) {
            regfree (& r);
            close(s);
           return 0;
        }
    }
    regfree (& r);
    close(s);
    return 0;
}
