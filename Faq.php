<?php
class Faq
{
    private $conn;

    public function __construct($host, $username, $password, $dbname)
    {
        $this->conn = new mysqli($host, $username, $password, $dbname);

        if ($this->conn->connect_error) {
            die("Database connection failed: " . $this->conn->connect_error);
        }
    }

    // Create (Add) a new FAQ
    public function addFaq($question, $answer)
    {
        $stmt = $this->conn->prepare("INSERT INTO faq (question, answer) VALUES (?, ?)");
        $stmt->bind_param("ss", $question, $answer);
        $stmt->execute();
        $stmt->close();
        return $this->conn->insert_id;
    }

    // Read all FAQs
    public function getAllFaqs()
    {
        $query = "SELECT * FROM faq ORDER BY created_at DESC";
        $result = $this->conn->query($query);

        $faqs = [];
        while ($row = $result->fetch_assoc()) {
            $faqs[] = $row;
        }
        return $faqs;
    }

    // Update an FAQ
    public function updateFaq($id, $question, $answer)
    {
        $stmt = $this->conn->prepare("UPDATE faq SET question = ?, answer = ? WHERE id = ?");
        $stmt->bind_param("ssi", $question, $answer, $id);
        $stmt->execute();
        $stmt->close();
        return $stmt->affected_rows > 0;
    }

    // Delete an FAQ
    public function deleteFaq($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM faq WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
        return $stmt->affected_rows > 0;
    }

    public function __destruct()
    {
        $this->conn->close();
    }
}
?>
