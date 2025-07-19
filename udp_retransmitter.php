<?php
// This script should be run from the command line: php your_udp_retransmitter.php

set_time_limit(0); // Allow the script to run indefinitely
ob_implicit_flush(true); // Ensure immediate output for CLI messages

$inputUdpPort = 1234;   // Port to listen for incoming TS data
$outputUdpIp = '239.0.0.1'; // Multicast IP or target IP for retransmission (e.g., 192.168.1.100)
$outputUdpPort = 5678;  // Port to send TS data
$bufferSize = 1316;     // Common TS packet size (188 bytes) * 7 packets = 1316 bytes (or 65536 for max UDP packet)

echo "Starting UDP TS retransmitter...\n";
echo "Listening on UDP port: $inputUdpPort\n";
echo "Retransmitting to UDP: $outputUdpIp:$outputUdpPort\n";

// --- Create Input Socket (Receiver) ---
$inputSocket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
if ($inputSocket === false) {
    die("Error creating input socket: " . socket_strerror(socket_last_error()) . "\n");
}

// Allow reusing address and port (important for multicast or rapid restarts)
if (!socket_set_option($inputSocket, SOL_SOCKET, SO_REUSEADDR, 1)) {
    echo "Warning: Could not set SO_REUSEADDR on input socket.\n";
}
if (!socket_set_option($inputSocket, SOL_SOCKET, SO_REUSEPORT, 1)) {
    echo "Warning: Could not set SO_REUSEPORT on input socket.\n"; // Only on some systems
}

// Bind the input socket to listen for incoming data
if (!socket_bind($inputSocket, '0.0.0.0', $inputUdpPort)) { // Bind to all available interfaces
    die("Error binding input socket to port $inputUdpPort: " . socket_strerror(socket_last_error()) . "\n");
}

// For multicast input, join the multicast group
// if (filter_var($inputUdpIp, FILTER_VALIDATE_IP, FILTER_FLAG_MULTICAST)) {
//     $mreq = array('group' => $inputUdpIp, 'interface' => '0.0.0.0'); // Replace '0.0.0.0' with specific interface if needed
//     if (!socket_set_option($inputSocket, IPPROTO_IP, MCAST_JOIN_GROUP, $mreq)) {
//         echo "Warning: Could not join multicast group for input: " . socket_strerror(socket_last_error()) . "\n";
//     }
// }


// --- Create Output Socket (Sender) ---
$outputSocket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
if ($outputSocket === false) {
    die("Error creating output socket: " . socket_strerror(socket_last_error()) . "\n");
}

// For multicast output, set Time-To-Live (TTL)
// if (filter_var($outputUdpIp, FILTER_VALIDATE_IP, FILTER_FLAG_MULTICAST)) {
//     $ttl = 16; // Typically between 1 (local subnet) and 255
//     if (!socket_set_option($outputSocket, IPPROTO_IP, IP_MULTICAST_TTL, $ttl)) {
//         echo "Warning: Could not set multicast TTL on output socket.\n";
//     }
// }

echo "Sockets created and bound. Waiting for data...\n";

while (true) {
    $buffer = '';
    $fromIp = '';
    $fromPort = 0;

    // Receive data from the input socket
    // Max UDP packet size is 65507 bytes, but common for TS is often smaller, e.g., 1316 (7x188)
    $bytesReceived = socket_recvfrom($inputSocket, $buffer, 65536, 0, $fromIp, $fromPort);

    if ($bytesReceived === false) {
        echo "Error receiving data: " . socket_strerror(socket_last_error($inputSocket)) . "\n";
        sleep(1); // Wait a bit before retrying
        continue;
    }
    // else if ($bytesReceived == 0) {
    //     echo "No data received (socket closed?)\n"; // For UDP, 0 bytes might indicate nothing received yet
    //     usleep(1000); // Small pause to prevent tight loop
    //     continue;
    // }

    echo "Received $bytesReceived bytes from $fromIp:$fromPort\n";

    // Send the received data to the output socket
    $bytesSent = socket_sendto($outputSocket, $buffer, $bytesReceived, 0, $outputUdpIp, $outputUdpPort);

    if ($bytesSent === false) {
        echo "Error sending data: " . socket_strerror(socket_last_error($outputSocket)) . "\n";
        sleep(1); // Wait a bit before retrying
        continue;
    }
    echo "Sent $bytesSent bytes to $outputUdpIp:$outputUdpPort\n";

    // Small pause to prevent CPU overuse if data comes in too fast,
    // though UDP is typically event-driven by incoming packets.
    // usleep(100);
}

// Close sockets (this part will likely not be reached in a continuous loop)
socket_close($inputSocket);
socket_close($outputSocket);

?>