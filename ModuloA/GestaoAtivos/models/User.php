<?php

class User {
    private $id;
    private $username;
    private $password;
    private $email;
    private $nome;
    private $nivel_acesso;

    public function __construct($username = '', $password = '') {
        $this->username = $username;
        $this->password = $password;
    }

    public function getId() {
        return $this->id;
    }

    public function getUsername() {
        return $this->username;
    }

    public function setUsername($username) {
        $this->username = $username;
    }

    public function getPassword() {
        return $this->password;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function save(Database $db) {
        $sql = "INSERT INTO usuarios (nome, email, senha, nivel_acesso) 
                VALUES (?, ?, ?, ?)";
        return $db->query($sql, [
            $this->nome,
            $this->email,
            password_hash($this->password, PASSWORD_DEFAULT),
            $this->nivel_acesso
        ]);
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setNome($nome) {
        $this->nome = $nome;
    }

    public function setNivelAcesso($nivel) {
        $this->nivel_acesso = $nivel;
    }
}

?>
