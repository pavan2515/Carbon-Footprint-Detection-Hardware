#include "Adafruit_MQTT.h"
#include "Adafruit_MQTT_Client.h"
#include <WiFi.h>

// ---------- WiFi Configuration ----------
#define WIFI_SSID "carcontrol"
#define WIFI_PASS "password"

// ---------- Ultrasonic Sensor Pins ----------
#define TRIG_PIN 5
#define ECHO_PIN 18

// ---------- Analog Sensor Pins ----------
#define SENSOR1 34
#define SENSOR2 35
#define SENSOR3 32

// ---------- MQTT Configuration ----------
WiFiClient client;
Adafruit_MQTT_Client mqtt(&client, "broker.emqx.io", 1883, "", "");
Adafruit_MQTT_Publish carcontrol = Adafruit_MQTT_Publish(&mqtt, "carcontrol");

// ---------- MQTT Connection Function ----------
void MQTT_connect() {
  int8_t ret;
  if (mqtt.connected()) return;

  Serial.print("Connecting to MQTT... ");
  uint8_t retries = 3;

  while ((ret = mqtt.connect()) != 0) {
    Serial.println(mqtt.connectErrorString(ret));
    Serial.println("Retrying MQTT connection in 5 seconds...");
    mqtt.disconnect();
    delay(5000);
    retries--;
    if (retries == 0) {
      while (1);  // stop if unable to connect
    }
  }
  Serial.println("MQTT Connected!");
}

// ---------- Setup ----------
void setup() {
  Serial.begin(115200);
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASS);

  Serial.print("Connecting to WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi Connected!");
  Serial.print("Local IP: ");
  Serial.println(WiFi.localIP());

  // Ultrasonic setup
  pinMode(TRIG_PIN, OUTPUT);
  pinMode(ECHO_PIN, INPUT);
}

// ---------- Function: Get Distance ----------
float getDistanceCM() {
  digitalWrite(TRIG_PIN, LOW);
  delayMicroseconds(2);
  digitalWrite(TRIG_PIN, HIGH);
  delayMicroseconds(10);
  digitalWrite(TRIG_PIN, LOW);

  long duration = pulseIn(ECHO_PIN, HIGH);
  float distance = (duration * 0.0343) / 2;  // cm
  return distance;
}

// ---------- Main Loop ----------
void loop() {
  MQTT_connect();

  float distance = getDistanceCM();
  Serial.print("Distance: ");
  Serial.print(distance);
  Serial.println(" cm");

  // ✅ Trigger only when object within 5 cm
  if (distance <= 5.0 && distance > 0) {
    int val1 = analogRead(SENSOR1);
    int val2 = analogRead(SENSOR2);
    int val3 = analogRead(SENSOR3);

    String data = String(val1) + "," + String(val2) + "," + String(val3);
    carcontrol.publish(data.c_str());

    Serial.print("Published Data: ");
    Serial.println(data);
  } else {
    Serial.println("No object detected (distance > 5 cm) — not publishing.");
  }

  delay(1000); // repeat every second
}
