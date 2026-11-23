<?php
class Database
{
    private string $host;
    private string $name;
    private string $user;
    private string $pass;
    private string $charset;
    private ?PDO $conn = null;

    public function __construct()
    {
        $this->host    = DB_HOST;
        $this->name    = DB_NAME;
        $this->user    = DB_USER;
        $this->pass    = DB_PASS;
        $this->charset = DB_CHARSET;
    }

    public function getConnection(): PDO
    {
        if ($this->conn === null) {
            $dsn = "mysql:host={$this->host};dbname={$this->name};charset={$this->charset}";
            $this->conn = new PDO($dsn, $this->user, $this->pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            $this->conn->exec("SET time_zone = '+08:00'"); // samakan dengan WITA
        }
        return $this->conn;
    }
}
