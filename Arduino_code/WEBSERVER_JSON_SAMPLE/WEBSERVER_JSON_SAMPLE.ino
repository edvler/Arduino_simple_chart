/**
Project: Arduino simple charts
Author: Matthias Maderer
Date: April 2013

Links:
Description: www.edvler-blog.de/arduino-simple-chart-setup-howto-english
Installationguide: www.edvler-blog.de/arduino-simple-charts-diagramm-visualisierung-messwerte
GitHub: www.github.com/edvler/arduino_simple_charts


Howto:
1. Set the network parameters. See mac and IPAddress
2. Customize your JSON output. Search for JSON below line 100 to find the code
3. Validate your JSON. You can use the website http://json.parser.online.fr

4. Only proceed if the validation return no failure
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

          //Generate JSON this is a sample template

          /*
			{

				"DATA":{
					"POTI":{
						"Poti eins":"450"
					},
					"LDR":{
						"LightDepententResistor1":"350"
					},
					"Relays":{
						"Relay 1 (Pumpe)":"0",
						"Relay 2 (Stellantrieb 1)":"0",
						"Relay 3 (Stellantrieb 2)":"1",
						"Relay 4 (Pumpe2)":"1"
					},
					"LED":{
						"red LED":"0",
						"onboard LED":"0"
					}
				}

			} 
          */
          
          client.println("{ \"DATA\" : {"); //opening JSON and DATA
          
            client.println(" \"POTI\" : {"); //opening group POTI
              client.print(" \"Poti eins\" : \"");
              client.print(analogRead(potiPIN));
              client.print("\"");
			client.println("},"); //closing group POTI
            
            client.println(" \"LDR\" : {"); //opening LDR
              client.print(" \"LightDepententResistor1\" : \"");
              client.print(analogRead(ldrPIN));
              client.print("\"");
			client.println("},"); //closing LDR

            client.println(" \"Relays\" : {"); //opening Relay
              client.print(" \"Relay 1 (Pumpe)\" : \""); //Relay 1
              client.print(digitalRead(relayPIN1));
              client.print("\"");
			  client.print(",");

              client.print("\"Relay 2 (Stellantrieb 1)\" : \""); //Relay 2
              client.print(digitalRead(relayPIN2));
              client.print("\"");
			  client.print(",");

              client.print("\"Relay 3 (Stellantrieb 2)\" : \""); //Relay 3
              client.print(digitalRead(relayPIN3));
              client.print("\"");
			  client.print(",");

              client.print("\"Relay 4 (Pumpe2)\" : \""); //Relay 4
              client.print(digitalRead(relayPIN4));
              client.print("\"");
			  client.print(",");

            client.println("},"); //closing Relay
            
            client.println(" \"LED\" : {"); //opening LED
              client.print(" \"red LED\" : \""); //read LED
              client.print(digitalRead(redLED));
              client.print("\"");
			  client.print(",");

              client.print("  \"onboard LED\" : \""); //onboard LED
              client.print(digitalRead(onboardLED));
              client.println("\" ");
			  
            client.println("}"); //closing LED
			
          client.println("}}"); //closing braket for DATA and JSON
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

