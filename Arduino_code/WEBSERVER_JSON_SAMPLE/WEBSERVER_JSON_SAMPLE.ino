/*
  Web Server
 
 A simple web server that shows the value of the analog input pins.
 using an Arduino Wiznet Ethernet shield. 
 
 Circuit:
 * Ethernet shield attached to pins 10, 11, 12, 13
 * Analog inputs attached to pins A0 through A5 (optional)
 
 created 18 Dec 2009
 by David A. Mellis
 modified 9 Apr 2012
 by Tom Igoe
 
 */

#include <SPI.h>
#include <Ethernet.h>

int relayPIN1 = 22;
int relayPIN2 = 23;
int relayPIN3 = 24;
int relayPIN4 = 25;
int redLED = 43;
int onboardLED = 13;
int ldrPIN = A7;
int potiPIN = A8;

// Enter a MAC address and IP address for your controller below.
// The IP address will be dependent on your local network:
byte mac[] = { 
  0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0x23 };
IPAddress ip(192,168,0, 123);

// Initialize the Ethernet server library
// with the IP address and port you want to use 
// (port 80 is default for HTTP):
EthernetServer server(80);

void setup() {
  pinMode(relayPIN1,OUTPUT);
  pinMode(relayPIN2,OUTPUT);
  pinMode(relayPIN3,OUTPUT);
  pinMode(relayPIN4,OUTPUT);
  pinMode(redLED,OUTPUT);
  pinMode(onboardLED,OUTPUT);
  
 // Open serial communications and wait for port to open:
  Serial.begin(9600);
   while (!Serial) {
    ; // wait for serial port to connect. Needed for Leonardo only
  }


  // start the Ethernet connection and the server:
  Ethernet.begin(mac, ip);
  server.begin();
  Serial.print("server is at ");
  Serial.println(Ethernet.localIP());
}


void loop() {
  // some logic 
  if (analogRead(potiPIN) > 550) {
     digitalWrite(redLED,HIGH);
  } else {
     digitalWrite(redLED,LOW);
  }
  
  if (analogRead(ldrPIN) > 800) {
    digitalWrite(relayPIN1,HIGH);
    digitalWrite(relayPIN2,HIGH);
    digitalWrite(relayPIN3,LOW);
    digitalWrite(relayPIN4,LOW);
  } else {
    digitalWrite(relayPIN1,LOW);
    digitalWrite(relayPIN2,LOW);
    digitalWrite(relayPIN3,HIGH);
    digitalWrite(relayPIN4,HIGH);
  }
  
  // listen for incoming clients
  EthernetClient client = server.available();
  if (client) {
    Serial.println("new client");
    // an http request ends with a blank line
    boolean currentLineIsBlank = true;
    while (client.connected()) {
      if (client.available()) {
        char c = client.read();
        Serial.write(c);
        // if you've gotten to the end of the line (received a newline
        // character) and the line is blank, the http request has ended,
        // so you can send a reply
        if (c == '\n' && currentLineIsBlank) {
          // send a standard http response header
          client.println("HTTP/1.1 200 OK");
          client.println("Content-Type: text/html");
          client.println("Connnection: close");
          client.println();

          //Generate JSON

          /*
          { 
          "DATA": 
          	{ "LDR": 
          		{ "LightDependetResistor 1": "1", 
          		}, 
          	  "DigitalPins": 
          		{ "PIN": "0", 
          		  "Wohnzim": "0", 
          		  "Schalfz": "0", 
          		  "Esszimm": "1"
          		}, 
          	  "DS1820": 
          		{ "Vorl_Matt": "34.38", 
          		  "Rueck_Matt": "33.69", 
          		  "Vorl_Bunk": "47.75", 
          		} 
          	}
          } 
          */
 
 /*
 int relayPIN1 = 22;
int relayPIN2 = 23;
int relayPIN3 = 24;
int relayPIN4 = 25;
int redLED = 43;
int onboardLED = 13;
int ldrPIN = A7;
int potiPIN = A8;
*/
          
          client.println("{ \"DATA\" : {"); //opening DATA
          
            client.println(" \"POTI\" :"); //opening LDR
              client.print(" { \"Poti eins\" : \"");
              client.print(analogRead(potiPIN));
              client.println("\" },");
            
            client.println(" \"LDR\" :"); //opening LDR
              client.print(" { \"LightDepententResistor1\" : \"");
              client.print(analogRead(ldrPIN));
              client.println("\" },");

            client.println(" \"Relays\" : {"); //opening LDR
              client.print("\"Relay 1 (Pumpe)\" : \"");
              client.print(digitalRead(relayPIN1));
              client.println("\",");

              client.print("\"Relay 2 (Stellantrieb 1)\" : \"");
              client.print(digitalRead(relayPIN2));
              client.println("\",");

              client.print("\"Relay 3 (Stellantrieb 2)\" : \"");
              client.print(digitalRead(relayPIN3));
              client.println("\",");

              client.print("\"Relay 4 (Pumpe2)\" : \"");
              client.print(digitalRead(relayPIN4));
              client.println("\"");

            client.println("},"); //closing brakte for LDR
            
            client.println(" \"LED\" :"); //opening LDR
              client.print(" { \"red LED\" : \"");
              client.print(digitalRead(redLED));
              client.println("\",");

              client.print("  \"onboard LED\" : \"");
              client.print(digitalRead(onboardLED));
              client.println("\" ");
            client.println("}"); //closing brakte for LDR
          client.println("}}"); //closing braket for DATA
          break;
        }
        if (c == '\n') {
          // you're starting a new line
          currentLineIsBlank = true;
        } 
        else if (c != '\r') {
          // you've gotten a character on the current line
          currentLineIsBlank = false;
        }
      }
    }
    // give the web browser time to receive the data
    delay(1);
    // close the connection:
    client.stop();
    Serial.println("client disonnected");
  }
}

