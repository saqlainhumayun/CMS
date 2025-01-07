<?php
require 'Faq.php';
require 'vendor/autoload.php'; // Ensure Ratchet is installed via Composer

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class FAQServer implements MessageComponentInterface {
    private $clients;
    private $faq;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->faq = new Faq('localhost', 'root', '', 'edusphere_cms');
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);

        if ($data['action'] === 'ask_question') {
            $question = $data['question'];

            // Generate an automated answer (mock logic)
            $answer = $this->generateAnswer($question);

            // Save the FAQ to the database
            $this->faq->addFaq($question, $answer);

            // Broadcast the new FAQ to all connected clients
            foreach ($this->clients as $client) {
                $client->send(json_encode([
                    'action' => 'new_faq',
                    'question' => $question,
                    'answer' => $answer
                ]));
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    private function generateAnswer($question) {
        // Simple mock logic for generating answers
        if (stripos($question, 'socket') !== false) {
            return "Socket programming allows real-time communication between clients and servers.";
        } elseif (stripos($question, 'php') !== false) {
            return "PHP is a widely-used open-source scripting language for web development.";
        } else {
            return "Thank you for your question. Our team will get back to you soon.";
        }
    }
}

// Start the WebSocket server
use Ratchet\App;

$app = new App('localhost', 8080, '0.0.0.0');
$app->route('/faq', new FAQServer, ['*']);
$app->run();
