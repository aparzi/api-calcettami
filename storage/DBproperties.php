<?php

define("DB_SERVER", "localhost");
define("DB_USERNAME", "root");
define("DB_PASSWORD", "");
define("DB_DATABASE", "calcettiamo");

define("OFFLINE", -1);
define("ONLINE", 0);

/**
 * Description of DBproprierties
 *
 * This class describes information about MySql Data Base Configuration
 * 
 * @author Angelo
 */
class DBproprierties {

    private $dbConn;

    /**
     * Effettua la connessione al database
     * @return type
     */
    public function getConnection() {

        $this->dbConn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

        return $this->dbConn;
    }

    /**
     * Chiude la connessione con il database
     */
    public function closeConnection() {

        unset($this->dbConn);
    }
    
    /**
     * Metodo che verifica se il client è connesso.
     * @return OFFLINE se non esiste connessione
     * @return ONLINE se esiste connesso
     */
    public function controlConnection() {
        if (!$sock = @fsockopen('www.google.com', 80, $num, $error, 5)){
            return OFFLINE;
        }
        else{
            return ONLINE;
        }
            
    }

}
