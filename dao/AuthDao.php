<?php

// require_once __DIR__ . '/BaseDao.php'; // BaseDao je već uključen u index.php prije ovoga

class AuthDao extends BaseDao {

    public function __construct() {
        // Pretpostavljamo da je ime tabele za korisnike 'users'
        // Ako je drugačije, promijenite ovdje
        parent::__construct("users"); 
    }

    /**
     * Dobavlja korisnika na osnovu email adrese.
     * 
     * @param string $email Email korisnika.
     * @return array|false Niz sa podacima o korisniku ako je pronađen, inače false.
     */
    public function getUserByEmail($email) {
        // Koristimo backticks oko imena kolone 'email' za svaki slučaj
        return $this->query_unique("SELECT * FROM `" . $this->table_name . "` WHERE `email` = :email", ['email' => $email]);
    }

    /**
     * Dobavlja korisnika na osnovu ID-a.
     * Ovo je već implementirano u BaseDao kao getById(), ali ako treba specifična logika za AuthDao:
     */
    // public function getUserById($id) {
    //     return $this->getById($id); 
    // }

    // Ovdje možete dodati druge metode specifične za autentifikaciju ako budu potrebne,
    // npr. za ažuriranje tokena za resetovanje lozinke, logovanje pokušaja prijave itd.
}

?> 